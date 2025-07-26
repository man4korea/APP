// 지점 통합 승인 시스템
const crypto = require('crypto');
const nodemailer = require('nodemailer');

class BranchIntegrationService {
    constructor(db, emailConfig) {
        this.db = db;
        this.emailTransporter = nodemailer.createTransporter(emailConfig);
    }

    /**
     * 본점에서 지점 통합 요청
     * @param {String} headquartersId 본점 ID
     * @param {String} requestedBy 요청자 (본점 관리자)
     * @param {String} branchTaxNumber 지점 사업자번호
     * @param {String} integrationMessage 통합 요청 메시지
     */
    async requestBranchIntegration(headquartersId, requestedBy, branchTaxNumber, integrationMessage) {
        const connection = await this.db.getConnection();

        try {
            await connection.beginTransaction();

            // 1. 본점 권한 확인
            const hasPermission = await this.checkHeadquartersPermission(
                connection, requestedBy, headquartersId
            );
            
            if (!hasPermission) {
                throw new Error('본점 관리자 권한이 필요합니다.');
            }

            // 2. 지점 회사 조회
            const [branchResult] = await connection.execute(`
                SELECT c.*, cu.user_id as admin_user_id, u.email as admin_email
                FROM companies c
                JOIN company_users cu ON c.id = cu.company_id 
                    AND cu.role_type IN ('founder', 'admin') 
                    AND cu.is_active = TRUE
                JOIN users u ON cu.user_id = u.id
                WHERE c.tax_number = ? 
                    AND c.company_type = 'headquarters' 
                    AND c.status = 'active'
                    AND c.parent_company_id IS NULL
            `, [branchTaxNumber]);

            if (branchResult.length === 0) {
                throw new Error('해당 사업자번호의 독립 회사를 찾을 수 없습니다.');
            }

            const branchCompany = branchResult[0];

            // 3. 이미 통합 요청이 있는지 확인
            const [existingRequest] = await connection.execute(`
                SELECT id FROM branch_integration_requests
                WHERE headquarters_id = ? AND branch_id = ? AND status = 'pending'
            `, [headquartersId, branchCompany.id]);

            if (existingRequest.length > 0) {
                throw new Error('이미 통합 요청이 진행 중입니다.');
            }

            // 4. 본점 정보 조회
            const [headquartersResult] = await connection.execute(`
                SELECT company_name, tax_number FROM companies WHERE id = ?
            `, [headquartersId]);

            const headquarters = headquartersResult[0];

            // 5. 통합 요청 생성
            const requestId = this.generateUUID();
            const expiresAt = new Date();
            expiresAt.setDate(expiresAt.getDate() + 7); // 7일 후 만료

            await connection.execute(`
                INSERT INTO branch_integration_requests (
                    id, headquarters_id, branch_id, headquarters_tax_number, branch_tax_number,
                    requested_by, branch_admin_email, branch_admin_user_id,
                    integration_message, expires_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `, [
                requestId, headquartersId, branchCompany.id, headquarters.tax_number, branchTaxNumber,
                requestedBy, branchCompany.admin_email, branchCompany.admin_user_id,
                integrationMessage, expiresAt
            ]);

            // 6. 이력 기록
            await this.recordIntegrationHistory(connection, {
                requestId,
                headquartersId,
                branchId: branchCompany.id,
                action: 'requested',
                performedBy: requestedBy,
                notes: `본점 ${headquarters.company_name}에서 지점 통합 요청`
            });

            // 7. 지점 관리자에게 이메일 발송
            await this.sendIntegrationNotificationEmail(connection, requestId);

            await connection.commit();

            return {
                success: true,
                requestId,
                message: `${branchCompany.company_name} 지점에 통합 요청을 발송했습니다.`,
                branchInfo: {
                    companyName: branchCompany.company_name,
                    adminEmail: branchCompany.admin_email,
                    expiresAt
                }
            };

        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * 지점에서 통합 요청 승인
     * @param {String} requestId 통합 요청 ID
     * @param {String} branchAdminUserId 지점 관리자 사용자 ID
     */
    async approveBranchIntegration(requestId, branchAdminUserId) {
        const connection = await this.db.getConnection();

        try {
            await connection.beginTransaction();

            // 1. 통합 요청 조회 및 권한 확인
            const [requestResult] = await connection.execute(`
                SELECT bir.*, c.company_name as branch_name, hq.company_name as headquarters_name
                FROM branch_integration_requests bir
                JOIN companies c ON bir.branch_id = c.id
                JOIN companies hq ON bir.headquarters_id = hq.id
                WHERE bir.id = ? AND bir.status = 'pending' AND bir.expires_at > NOW()
            `, [requestId]);

            if (requestResult.length === 0) {
                throw new Error('유효하지 않거나 만료된 통합 요청입니다.');
            }

            const request = requestResult[0];

            // 2. 지점 관리자 권한 확인
            const hasPermission = await this.checkBranchPermission(
                connection, branchAdminUserId, request.branch_id
            );

            if (!hasPermission) {
                throw new Error('지점 관리자 권한이 필요합니다.');
            }

            // 3. 통합 요청 승인 처리
            await connection.execute(`
                UPDATE branch_integration_requests 
                SET status = 'approved', approved_by = ?, approved_at = NOW()
                WHERE id = ?
            `, [branchAdminUserId, requestId]);

            // 4. 지점을 본점 하위로 이동
            await connection.execute(`
                UPDATE companies 
                SET parent_company_id = ?, company_type = 'branch', status = 'active'
                WHERE id = ?
            `, [request.headquarters_id, request.branch_id]);

            // 5. 지점 사용자들을 본점으로 통합 (권한 유지)
            await connection.execute(`
                UPDATE company_users 
                SET company_id = ?, notes = CONCAT(IFNULL(notes, ''), '\\n[통합] ', ?, '에서 이관됨')
                WHERE company_id = ?
            `, [request.headquarters_id, request.branch_name, request.branch_id]);

            // 6. 지점 프로세스와 태스크를 본점으로 이관
            await connection.execute(`
                UPDATE processes SET company_id = ? WHERE company_id = ?
            `, [request.headquarters_id, request.branch_id]);

            await connection.execute(`
                UPDATE tasks SET company_id = ? WHERE company_id = ?
            `, [request.headquarters_id, request.branch_id]);

            // 7. 이력 기록
            await this.recordIntegrationHistory(connection, {
                requestId,
                headquartersId: request.headquarters_id,
                branchId: request.branch_id,
                action: 'approved',
                performedBy: branchAdminUserId,
                notes: `지점 ${request.branch_name}이 본점 ${request.headquarters_name}에 통합 승인`
            });

            await this.recordIntegrationHistory(connection, {
                requestId,
                headquartersId: request.headquarters_id,
                branchId: request.branch_id,
                action: 'integrated',
                performedBy: branchAdminUserId,
                notes: '통합 완료',
                integrationCompletedAt: new Date()
            });

            // 8. 본점 관리자에게 승인 완료 이메일 발송
            await this.sendIntegrationCompletedEmail(connection, requestId, 'approved');

            await connection.commit();

            return {
                success: true,
                message: `${request.branch_name}이 ${request.headquarters_name}에 성공적으로 통합되었습니다.`,
                integratedData: {
                    headquartersName: request.headquarters_name,
                    branchName: request.branch_name,
                    integratedAt: new Date()
                }
            };

        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * 지점에서 통합 요청 거부
     */
    async rejectBranchIntegration(requestId, branchAdminUserId, rejectionReason) {
        const connection = await this.db.getConnection();

        try {
            await connection.beginTransaction();

            // 통합 요청 조회 및 권한 확인
            const [requestResult] = await connection.execute(`
                SELECT bir.*, c.company_name as branch_name, hq.company_name as headquarters_name
                FROM branch_integration_requests bir
                JOIN companies c ON bir.branch_id = c.id
                JOIN companies hq ON bir.headquarters_id = hq.id
                WHERE bir.id = ? AND bir.status = 'pending'
            `, [requestId]);

            if (requestResult.length === 0) {
                throw new Error('유효하지 않은 통합 요청입니다.');
            }

            const request = requestResult[0];

            // 권한 확인
            const hasPermission = await this.checkBranchPermission(
                connection, branchAdminUserId, request.branch_id
            );

            if (!hasPermission) {
                throw new Error('지점 관리자 권한이 필요합니다.');
            }

            // 통합 요청 거부 처리
            await connection.execute(`
                UPDATE branch_integration_requests 
                SET status = 'rejected', rejected_by = ?, rejected_at = NOW(), rejection_reason = ?
                WHERE id = ?
            `, [branchAdminUserId, rejectionReason, requestId]);

            // 이력 기록
            await this.recordIntegrationHistory(connection, {
                requestId,
                headquartersId: request.headquarters_id,
                branchId: request.branch_id,
                action: 'rejected',
                performedBy: branchAdminUserId,
                notes: `거부 사유: ${rejectionReason}`
            });

            // 본점 관리자에게 거부 이메일 발송
            await this.sendIntegrationCompletedEmail(connection, requestId, 'rejected');

            await connection.commit();

            return {
                success: true,
                message: '통합 요청을 거부했습니다.',
                rejectionInfo: {
                    headquartersName: request.headquarters_name,
                    branchName: request.branch_name,
                    reason: rejectionReason
                }
            };

        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * 통합 요청 알림 이메일 발송
     */
    async sendIntegrationNotificationEmail(connection, requestId) {
        const [requestData] = await connection.execute(`
            SELECT bir.*, hq.company_name as headquarters_name, hq.representative_name,
                   b.company_name as branch_name
            FROM branch_integration_requests bir
            JOIN companies hq ON bir.headquarters_id = hq.id
            JOIN companies b ON bir.branch_id = b.id
            WHERE bir.id = ?
        `, [requestId]);

        const request = requestData[0];
        const approvalUrl = `${process.env.FRONTEND_URL}/branch-integration/approve/${requestId}`;
        const rejectUrl = `${process.env.FRONTEND_URL}/branch-integration/reject/${requestId}`;

        const emailContent = {
            to: request.branch_admin_email,
            subject: `[BPR Hub] ${request.headquarters_name}에서 지점 통합 요청`,
            html: `
                <h2>지점 통합 요청</h2>
                <p><strong>${request.headquarters_name}</strong> (사업자번호: ${request.headquarters_tax_number})에서 
                   귀하의 회사 <strong>${request.branch_name}</strong>을 지점으로 통합하고자 합니다.</p>
                
                <h3>요청 메시지</h3>
                <p>${request.integration_message}</p>
                
                <h3>통합 시 변경사항</h3>
                <ul>
                    <li>귀하의 회사가 ${request.headquarters_name}의 지점으로 변경됩니다.</li>
                    <li>기존 프로세스와 데이터는 그대로 유지됩니다.</li>
                    <li>지점 관리자 권한은 유지됩니다.</li>
                </ul>
                
                <p><strong>요청 만료일:</strong> ${new Date(request.expires_at).toLocaleDateString('ko-KR')}</p>
                
                <div style="margin: 20px 0;">
                    <a href="${approvalUrl}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; margin-right: 10px;">승인</a>
                    <a href="${rejectUrl}" style="background-color: #f44336; color: white; padding: 10px 20px; text-decoration: none;">거부</a>
                </div>
                
                <p><small>이 요청은 7일 후 자동으로 만료됩니다.</small></p>
            `
        };

        await this.emailTransporter.sendMail(emailContent);

        // 발송 기록 업데이트
        await connection.execute(`
            UPDATE branch_integration_requests 
            SET notification_sent = TRUE 
            WHERE id = ?
        `, [requestId]);
    }

    /**
     * 권한 확인 함수들
     */
    async checkHeadquartersPermission(connection, userId, companyId) {
        const [result] = await connection.execute(`
            SELECT COUNT(*) as count FROM company_users
            WHERE user_id = ? AND company_id = ? AND role_type IN ('founder', 'admin') 
                AND is_active = TRUE AND status = 'active'
        `, [userId, companyId]);
        return result[0].count > 0;
    }

    async checkBranchPermission(connection, userId, companyId) {
        return await this.checkHeadquartersPermission(connection, userId, companyId);
    }

    /**
     * 통합 이력 기록
     */
    async recordIntegrationHistory(connection, data) {
        const historyId = this.generateUUID();
        await connection.execute(`
            INSERT INTO branch_integration_history (
                id, request_id, headquarters_id, branch_id, action, 
                performed_by, notes, integration_completed_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            historyId, data.requestId, data.headquartersId, data.branchId,
            data.action, data.performedBy, data.notes, data.integrationCompletedAt || null
        ]);
    }

    generateUUID() {
        return crypto.randomUUID();
    }

    /**
     * 만료된 요청 정리 (크론잡용)
     */
    async cleanupExpiredRequests() {
        const connection = await this.db.getConnection();
        
        try {
            await connection.execute(`
                UPDATE branch_integration_requests 
                SET status = 'expired' 
                WHERE status = 'pending' AND expires_at < NOW()
            `);
        } finally {
            connection.release();
        }
    }
}

module.exports = BranchIntegrationService;