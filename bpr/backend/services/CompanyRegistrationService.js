// 확장된 회사 등록 서비스 (본점-지점 구조 지원)
const crypto = require('crypto');
const bcrypt = require('bcrypt');
const BranchIntegrationService = require('./BranchIntegrationService');

class CompanyRegistrationService {
    constructor(db, emailConfig) {
        this.db = db;
        this.saltRounds = 12;
        this.branchIntegrationService = new BranchIntegrationService(db, emailConfig);
    }

    /**
     * 회사 등록 (본점 또는 독립 회사)
     * @param {Object} companyData - 회사 정보
     * @param {Object} founderData - 설립자 정보
     * @param {Boolean} checkForHeadquarters - 본점 존재 여부 확인
     */
    async registerCompany(companyData, founderData, checkForHeadquarters = true) {
        const connection = await this.db.getConnection();
        
        try {
            await connection.beginTransaction();

            // 1. 필수 필드 검증
            this.validateCompanyData(companyData);
            this.validateFounderData(founderData);

            // 2. 사업자등록번호 중복 확인
            const [existingCompany] = await connection.execute(
                'SELECT id, company_name, company_type FROM companies WHERE tax_number = ?',
                [companyData.taxNumber]
            );

            if (existingCompany.length > 0) {
                throw new Error(`이미 등록된 사업자등록번호입니다. (${existingCompany[0].company_name})`);
            }

            // 3. 본점 존재 여부 확인 (지점일 가능성 체크)
            let potentialHeadquarters = null;
            if (checkForHeadquarters && companyData.companyType !== 'headquarters') {
                potentialHeadquarters = await this.findPotentialHeadquarters(connection, companyData);
            }

            // 4. 이메일 중복 확인
            const [existingUser] = await connection.execute(
                'SELECT id FROM users WHERE email = ?',
                [founderData.email]
            );

            if (existingUser.length > 0) {
                throw new Error('이미 등록된 이메일입니다.');
            }

            // 5. 회사 정보 등록
            const companyId = this.generateUUID();
            const result = await this.createCompany(connection, companyId, companyData);

            // 6. 설립자 계정 생성
            const userId = await this.createFounderAccount(connection, founderData);

            // 7. 설립자 역할 지정
            await this.assignFounderRole(connection, companyId, userId, founderData);

            // 8. 기본 부서 생성 (옵션)
            if (founderData.createDefaultDepartments) {
                await this.createDefaultDepartments(connection, companyId, userId);
            }

            // 9. 활동 로그 기록
            await this.logActivity(connection, {
                companyId,
                userId,
                action: 'create',
                entityType: 'company',
                entityId: companyId,
                details: { 
                    message: '회사 등록 완료',
                    companyType: companyData.companyType || 'headquarters'
                }
            });

            await connection.commit();

            // 10. 본점 존재 알림 (필요시)
            let integrationInfo = null;
            if (potentialHeadquarters) {
                integrationInfo = {
                    potentialHeadquarters,
                    message: '동일한 대표자명을 가진 본점이 존재합니다. 통합을 원하시면 별도 문의해주세요.'
                };
            }

            return {
                success: true,
                companyId,
                userId,
                message: '회사 등록이 완료되었습니다.',
                data: {
                    company: {
                        id: companyId,
                        name: companyData.companyName,
                        taxNumber: companyData.taxNumber,
                        type: companyData.companyType || 'headquarters'
                    },
                    founder: {
                        id: userId,
                        email: founderData.email,
                        role: 'founder'
                    }
                },
                integrationInfo
            };

        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * 지점 등록 (본점 하위)
     * @param {String} headquartersId 본점 ID
     * @param {String} requestedBy 요청자 (본점 관리자)
     * @param {Object} branchData 지점 정보
     * @param {Object} branchAdminData 지점 관리자 정보
     */
    async registerBranch(headquartersId, requestedBy, branchData, branchAdminData) {
        const connection = await this.db.getConnection();

        try {
            await connection.beginTransaction();

            // 1. 본점 관리자 권한 확인
            const hasPermission = await this.checkPermission(
                connection, requestedBy, headquartersId, 'admin'
            );
            
            if (!hasPermission) {
                throw new Error('본점 관리자 권한이 필요합니다.');
            }

            // 2. 사업자등록번호 중복 확인
            const [existingBranch] = await connection.execute(
                'SELECT id FROM companies WHERE tax_number = ?',
                [branchData.taxNumber]
            );

            if (existingBranch.length > 0) {
                throw new Error('이미 등록된 사업자등록번호입니다.');
            }

            // 3. 지점 회사 등록
            const branchId = this.generateUUID();
            const branchCompanyData = {
                ...branchData,
                companyType: 'branch',
                parentCompanyId: headquartersId
            };

            await this.createCompany(connection, branchId, branchCompanyData);

            // 4. 지점 관리자 계정 생성 또는 기존 계정 연결
            let branchAdminUserId;
            const [existingUser] = await connection.execute(
                'SELECT id FROM users WHERE email = ?',
                [branchAdminData.email]
            );

            if (existingUser.length > 0) {
                branchAdminUserId = existingUser[0].id;
            } else {
                branchAdminUserId = await this.createFounderAccount(connection, branchAdminData);
            }

            // 5. 지점 관리자 역할 지정 (본점과 지점 모두)
            await this.assignBranchAdminRole(
                connection, headquartersId, branchId, branchAdminUserId, requestedBy, branchAdminData
            );

            // 6. 활동 로그
            await this.logActivity(connection, {
                companyId: headquartersId,
                userId: requestedBy,
                action: 'create',
                entityType: 'company',
                entityId: branchId,
                details: { 
                    message: '지점 등록',
                    branchName: branchData.companyName,
                    branchTaxNumber: branchData.taxNumber
                }
            });

            await connection.commit();

            return {
                success: true,
                branchId,
                branchAdminUserId,
                message: `${branchData.companyName} 지점이 등록되었습니다.`,
                branchInfo: {
                    id: branchId,
                    name: branchData.companyName,
                    taxNumber: branchData.taxNumber,
                    adminEmail: branchAdminData.email
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
     * 회사 데이터 검증
     */
    validateCompanyData(data) {
        const required = ['companyName', 'taxNumber', 'representativeName', 'adminEmail'];
        const missing = required.filter(field => !data[field]);
        
        if (missing.length > 0) {
            throw new Error(`필수 정보가 누락되었습니다: ${missing.join(', ')}`);
        }

        // 사업자등록번호 형식 검증 (한국 기준: 10자리 숫자)
        if (data.taxNumber && !/^\d{3}-\d{2}-\d{5}$/.test(data.taxNumber)) {
            throw new Error('사업자등록번호 형식이 올바르지 않습니다. (예: 123-45-67890)');
        }

        // 이메일 형식 검증
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.adminEmail)) {
            throw new Error('올바른 이메일 형식이 아닙니다.');
        }
    }

    /**
     * 설립자 데이터 검증
     */
    validateFounderData(data) {
        const required = ['email', 'username', 'password', 'firstName', 'lastName'];
        const missing = required.filter(field => !data[field]);
        
        if (missing.length > 0) {
            throw new Error(`설립자 필수 정보가 누락되었습니다: ${missing.join(', ')}`);
        }

        if (data.password && data.password.length < 8) {
            throw new Error('비밀번호는 최소 8자리 이상이어야 합니다.');
        }
    }

    /**
     * 회사 생성
     */
    async createCompany(connection, companyId, data) {
        await connection.execute(`
            INSERT INTO companies (
                id, company_name, tax_number, business_type, company_type,
                parent_company_id, branch_name, representative_name, representative_phone,
                admin_email, admin_phone, address, postal_code, establishment_date,
                phone, email, website, status, settings
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        `, [
            companyId,
            data.companyName,
            data.taxNumber,
            data.businessType || null,
            data.companyType || 'headquarters',
            data.parentCompanyId || null,
            data.branchName || null,
            data.representativeName,
            data.representativePhone || null,
            data.adminEmail,
            data.adminPhone || null,
            data.address || null,
            data.postalCode || null,
            data.establishmentDate || null,
            data.phone || null,
            data.email || null,
            data.website || null,
            JSON.stringify({
                admin_can_assign_admin: true,
                approval_required: false,
                max_process_owners: 10
            })
        ]);

        return companyId;
    }

    /**
     * 설립자 계정 생성
     */
    async createFounderAccount(connection, data) {
        const userId = this.generateUUID();
        const hashedPassword = await bcrypt.hash(data.password, this.saltRounds);

        await connection.execute(`
            INSERT INTO users (
                id, email, username, password_hash, 
                first_name, last_name, phone, status, email_verified
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', TRUE)
        `, [
            userId,
            data.email,
            data.username,
            hashedPassword,
            data.firstName,
            data.lastName,
            data.phone || null
        ]);

        return userId;
    }

    /**
     * 설립자 역할 지정
     */
    async assignFounderRole(connection, companyId, userId, data) {
        const companyUserId = this.generateUUID();
        await connection.execute(`
            INSERT INTO company_users (
                id, company_id, user_id, role_type, 
                department, job_title, status, assigned_by, is_active
            ) VALUES (?, ?, ?, 'founder', ?, ?, 'active', ?, TRUE)
        `, [
            companyUserId,
            companyId,
            userId,
            data.department || '경영진',
            data.jobTitle || '대표이사',
            userId
        ]);
    }

    /**
     * 지점 관리자 역할 지정
     */
    async assignBranchAdminRole(connection, headquartersId, branchId, userId, assignedBy, data) {
        // 본점에서 지점 관리자 권한
        const hqCompanyUserId = this.generateUUID();
        await connection.execute(`
            INSERT INTO company_users (
                id, company_id, user_id, role_type, 
                department, job_title, status, assigned_by, is_active
            ) VALUES (?, ?, ?, 'process_owner', ?, ?, 'active', ?, TRUE)
        `, [
            hqCompanyUserId,
            headquartersId,
            userId,
            data.department || '지점관리',
            data.jobTitle || '지점장',
            assignedBy
        ]);

        // 지점에서 관리자 권한
        const branchCompanyUserId = this.generateUUID();
        await connection.execute(`
            INSERT INTO company_users (
                id, company_id, user_id, role_type, 
                department, job_title, status, assigned_by, is_active
            ) VALUES (?, ?, ?, 'admin', ?, ?, 'active', ?, TRUE)
        `, [
            branchCompanyUserId,
            branchId,
            userId,
            data.department || '경영진',
            data.jobTitle || '지점장',
            assignedBy
        ]);
    }

    /**
     * 잠재적 본점 찾기 (대표자명 기준)
     */
    async findPotentialHeadquarters(connection, companyData) {
        const [results] = await connection.execute(`
            SELECT id, company_name, tax_number, representative_name
            FROM companies 
            WHERE representative_name = ? 
                AND company_type = 'headquarters' 
                AND status = 'active'
                AND tax_number != ?
        `, [companyData.representativeName, companyData.taxNumber]);

        return results.length > 0 ? results : null;
    }

    /**
     * 기본 부서 생성
     */
    async createDefaultDepartments(connection, companyId, createdBy) {
        const defaultDepartments = [
            { name: '경영진', code: 'EXEC', level: 0 },
            { name: '인사팀', code: 'HR', level: 1 },
            { name: '재무팀', code: 'FIN', level: 1 },
            { name: '영업팀', code: 'SALES', level: 1 },
            { name: '운영팀', code: 'OPS', level: 1 }
        ];

        for (const dept of defaultDepartments) {
            const deptId = this.generateUUID();
            await connection.execute(`
                INSERT INTO departments (
                    id, company_id, department_name, department_code, 
                    level, created_by
                ) VALUES (?, ?, ?, ?, ?, ?)
            `, [
                deptId, companyId, dept.name, dept.code, dept.level, createdBy
            ]);
        }
    }

    /**
     * 권한 확인
     */
    async checkPermission(connection, userId, companyId, requiredRole) {
        const [result] = await connection.execute(
            'SELECT check_user_permission(?, ?, ?) as has_permission',
            [userId, companyId, requiredRole]
        );
        return result[0].has_permission === 1;
    }

    /**
     * 활동 로그 기록
     */
    async logActivity(connection, logData) {
        await connection.execute(`
            INSERT INTO user_activity_logs (
                company_id, user_id, action, entity_type, 
                entity_id, details, timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        `, [
            logData.companyId,
            logData.userId,
            logData.action,
            logData.entityType,
            logData.entityId,
            JSON.stringify(logData.details)
        ]);
    }

    generateUUID() {
        return crypto.randomUUID();
    }

    /**
     * 회사 정보 조회
     */
    async getCompanyInfo(companyId) {
        const [result] = await this.db.execute(`
            SELECT c.*, 
                   parent.company_name as parent_company_name,
                   (SELECT COUNT(*) FROM company_users cu 
                    WHERE cu.company_id = c.id AND cu.is_active = TRUE) as total_members,
                   (SELECT COUNT(*) FROM processes p 
                    WHERE p.company_id = c.id AND p.status = 'active') as total_processes,
                   (SELECT COUNT(*) FROM companies sub 
                    WHERE sub.parent_company_id = c.id AND sub.status = 'active') as branch_count
            FROM companies c 
            LEFT JOIN companies parent ON c.parent_company_id = parent.id
            WHERE c.id = ? AND c.status = 'active'
        `, [companyId]);

        return result[0] || null;
    }

    /**
     * 사업자등록번호로 회사 검색
     */
    async findCompanyByTaxNumber(taxNumber) {
        const [result] = await this.db.execute(`
            SELECT id, company_name, tax_number, company_type, 
                   representative_name, status, parent_company_id
            FROM companies 
            WHERE tax_number = ? AND status = 'active'
        `, [taxNumber]);

        return result[0] || null;
    }

    /**
     * 지점 목록 조회
     */
    async getBranches(headquartersId) {
        const [results] = await this.db.execute(`
            SELECT c.*, 
                   (SELECT COUNT(*) FROM company_users cu 
                    WHERE cu.company_id = c.id AND cu.is_active = TRUE) as member_count
            FROM companies c
            WHERE c.parent_company_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
        `, [headquartersId]);

        return results;
    }
}

module.exports = CompanyRegistrationService;