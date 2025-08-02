-- 📁 C:\xampp\htdocs\BPM\sql\schema.sql
-- Create at 2508022030 Ver1.00

-- BPM Total Business Process Management 데이터베이스 스키마
-- 회사별 데이터 분리와 권한 관리 중심 설계

-- ===========================
-- 1. 회사(Company) 관리
-- ===========================

-- 회사 정보 테이블 (본점-지점 구조 지원)
CREATE TABLE bpm_companies (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_name VARCHAR(255) NOT NULL,
    tax_number VARCHAR(50) UNIQUE NOT NULL COMMENT '사업자등록번호/Tax Number',
    business_type VARCHAR(100),
    
    -- 회사 구조 관련
    company_type ENUM('headquarters', 'branch') DEFAULT 'headquarters',
    parent_company_id CHAR(36) NULL COMMENT '본점 ID (지점인 경우)',
    branch_name VARCHAR(255) NULL COMMENT '지점명',
    
    -- 대표자 정보
    representative_name VARCHAR(100) NOT NULL COMMENT '대표자명',
    representative_phone VARCHAR(50) COMMENT '대표전화번호',
    
    -- 관리자 연락처
    admin_email VARCHAR(255) NOT NULL COMMENT '대표관리자 이메일',
    admin_phone VARCHAR(50) COMMENT '대표관리자 전화번호',
    
    -- 주소 정보
    address TEXT COMMENT '주소',
    postal_code VARCHAR(20) COMMENT '우편번호',
    
    -- 법인 정보
    establishment_date DATE COMMENT '법인설립일자',
    
    -- 기타
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    status ENUM('active', 'suspended', 'inactive', 'pending_integration') DEFAULT 'active',
    settings JSON COMMENT '회사별 설정 (관리자 설정 권한 등)',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_company_id) REFERENCES bpm_companies(id) ON DELETE SET NULL,
    INDEX idx_tax_number (tax_number),
    INDEX idx_parent_company (parent_company_id),
    INDEX idx_company_type (company_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- ===========================
-- 2. 사용자 관리 (멀티테넌트)
-- ===========================

-- 사용자 기본 정보
CREATE TABLE bpm_users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(50),
    status ENUM('active', 'pending', 'inactive', 'suspended') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- ===========================
-- 3. 회사별 사용자 역할 관리
-- ===========================

-- 회사 내 사용자 역할 (멀티테넌트 핵심)
CREATE TABLE bmp_company_users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    role_type ENUM('founder', 'admin', 'process_owner', 'member') NOT NULL,
    department VARCHAR(255),
    job_title VARCHAR(255),
    employee_id VARCHAR(100),
    status ENUM('active', 'inactive', 'pending_approval') DEFAULT 'pending_approval',
    assigned_by CHAR(36) COMMENT '누가 이 역할을 지정했는지',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_company_user (company_id, user_id),
    INDEX idx_company_role (company_id, role_type),
    INDEX idx_user_company (user_id, company_id),
    INDEX idx_status (status)
);

-- ===========================
-- 4. 부서 및 조직 구조
-- ===========================

-- 부서 정보
CREATE TABLE bpm_departments (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    department_name VARCHAR(255) NOT NULL,
    parent_department_id CHAR(36),
    description TEXT,
    department_code VARCHAR(50),
    level INT DEFAULT 0 COMMENT '조직도 레벨 (0: 최상위)',
    head_user_id CHAR(36) COMMENT '부서장',
    created_by CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_department_id) REFERENCES bpm_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (head_user_id) REFERENCES bpm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_company_dept_name (company_id, department_name),
    INDEX idx_company_id (company_id),
    INDEX idx_parent_dept (parent_department_id),
    INDEX idx_level (level)
);

-- ===========================
-- 5. 프로세스 관리
-- ===========================

-- 비즈니스 프로세스
CREATE TABLE bpm_processes (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    process_name VARCHAR(255) NOT NULL,
    process_code VARCHAR(100),
    description TEXT,
    department_id CHAR(36),
    owner_user_id CHAR(36) COMMENT 'Process Owner',
    process_category ENUM('core', 'support', 'management') DEFAULT 'core',
    complexity_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('active', 'draft', 'under_review', 'archived') DEFAULT 'draft',
    version INT DEFAULT 1,
    parent_process_id CHAR(36) COMMENT '상위 프로세스',
    process_flow JSON COMMENT '프로세스 플로우 데이터',
    created_by CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES bpm_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (owner_user_id) REFERENCES bpm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_process_id) REFERENCES bpm_processes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_company_process_code (company_id, process_code),
    INDEX idx_company_id (company_id),
    INDEX idx_owner (owner_user_id),
    INDEX idx_category (process_category),
    INDEX idx_status (status)
);

-- ===========================
-- 6. 태스크 관리
-- ===========================

-- 업무 태스크
CREATE TABLE bpm_tasks (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    process_id CHAR(36) NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    task_code VARCHAR(100),
    description TEXT,
    task_order INT DEFAULT 0,
    estimated_duration INT DEFAULT 0 COMMENT '예상 소요시간(분)',
    required_skills JSON,
    responsible_user_id CHAR(36) COMMENT '담당자',
    responsible_role VARCHAR(255) COMMENT '담당 역할/직책',
    task_complexity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    automation_candidate BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'draft', 'deprecated') DEFAULT 'draft',
    dependencies JSON COMMENT '선행 태스크들',
    task_steps JSON COMMENT '세부 단계',
    created_by CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (process_id) REFERENCES bpm_processes(id) ON DELETE CASCADE,
    FOREIGN KEY (responsible_user_id) REFERENCES bpm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_process_task_code (process_id, task_code),
    INDEX idx_company_id (company_id),
    INDEX idx_process_id (process_id),
    INDEX idx_responsible (responsible_user_id),
    INDEX idx_order (task_order),
    INDEX idx_automation (automation_candidate)
);

-- ===========================
-- 7. 지점 통합 승인 시스템
-- ===========================

-- 지점 통합 요청 테이블
CREATE TABLE bpm_branch_integration_requests (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    headquarters_id CHAR(36) NOT NULL COMMENT '본점 회사 ID',
    branch_id CHAR(36) NOT NULL COMMENT '지점 회사 ID',
    headquarters_tax_number VARCHAR(50) NOT NULL COMMENT '본점 사업자번호',
    branch_tax_number VARCHAR(50) NOT NULL COMMENT '지점 사업자번호',
    
    -- 요청자 정보
    requested_by CHAR(36) NOT NULL COMMENT '통합 요청한 본점 관리자',
    
    -- 승인자 정보 (지점 관리자들)
    branch_admin_email VARCHAR(255) NOT NULL COMMENT '지점 관리자 이메일',
    branch_admin_user_id CHAR(36) COMMENT '지점 관리자 사용자 ID',
    
    -- 요청 상태
    status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    
    -- 요청 내용
    integration_message TEXT COMMENT '통합 요청 메시지',
    rejection_reason TEXT COMMENT '거부 사유 (거부 시)',
    
    -- 승인 관련
    approved_by CHAR(36) NULL COMMENT '승인한 지점 관리자',
    approved_at TIMESTAMP NULL,
    rejected_by CHAR(36) NULL COMMENT '거부한 지점 관리자', 
    rejected_at TIMESTAMP NULL,
    
    -- 만료 시간 (7일 후 자동 만료)
    expires_at TIMESTAMP NOT NULL,
    
    -- 이메일 발송 관련
    notification_sent BOOLEAN DEFAULT FALSE,
    reminder_sent_count INT DEFAULT 0,
    last_reminder_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (headquarters_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (rejected_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_hq_branch_request (headquarters_id, branch_id, status),
    INDEX idx_branch_admin_email (branch_admin_email),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
);

-- 지점 통합 이력 테이블
CREATE TABLE bpm_branch_integration_history (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    request_id CHAR(36) NOT NULL,
    headquarters_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,
    action ENUM('requested', 'approved', 'rejected', 'expired', 'integrated') NOT NULL,
    performed_by CHAR(36),
    notes TEXT,
    integration_completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (request_id) REFERENCES bpm_branch_integration_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (headquarters_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    INDEX idx_request_id (request_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- ===========================
-- 8. 권한 및 로그 관리
-- ===========================

-- 권한 변경 로그
CREATE TABLE bpm_role_change_logs (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    target_user_id CHAR(36) NOT NULL,
    changed_by CHAR(36) NOT NULL,
    action ENUM('assign', 'revoke', 'modify') NOT NULL,
    old_role ENUM('founder', 'admin', 'process_owner', 'member'),
    new_role ENUM('founder', 'admin', 'process_owner', 'member'),
    reason TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    
    INDEX idx_company_target (company_id, target_user_id),
    INDEX idx_timestamp (timestamp)
);

-- 사용자 활동 로그
CREATE TABLE bpm_user_activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    action ENUM('login', 'logout', 'create', 'update', 'delete', 'view') NOT NULL,
    entity_type ENUM('process', 'task', 'department', 'user', 'company') NOT NULL,
    entity_id CHAR(36),
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    
    INDEX idx_company_user_time (company_id, user_id, timestamp),
    INDEX idx_action_time (action, timestamp),
    
    PARTITION BY RANGE (YEAR(timestamp)) (
        PARTITION p2024 VALUES LESS THAN (2025),
        PARTITION p2025 VALUES LESS THAN (2026),
        PARTITION p2026 VALUES LESS THAN (2027),
        PARTITION p_future VALUES LESS THAN MAXVALUE
    )
);

-- ===========================
-- 9. 뷰 및 프로시저
-- ===========================

-- 회사별 사용자 현황 뷰
CREATE VIEW bpm_company_user_overview AS
SELECT 
    c.id as company_id,
    c.company_name,
    c.tax_number,
    COUNT(cu.user_id) as total_users,
    SUM(CASE WHEN cu.role_type = 'founder' THEN 1 ELSE 0 END) as founder_count,
    SUM(CASE WHEN cu.role_type = 'admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN cu.role_type = 'process_owner' THEN 1 ELSE 0 END) as process_owner_count,
    SUM(CASE WHEN cu.role_type = 'member' THEN 1 ELSE 0 END) as member_count,
    SUM(CASE WHEN cu.status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN cu.status = 'pending_approval' THEN 1 ELSE 0 END) as pending_users
FROM bpm_companies c
LEFT JOIN bmp_company_users cu ON c.id = cu.company_id AND cu.is_active = TRUE
WHERE c.status = 'active'
GROUP BY c.id, c.company_name, c.tax_number;

-- 사용자별 권한 뷰
CREATE VIEW bpm_user_permissions AS
SELECT 
    u.id as user_id,
    u.email,
    u.username,
    c.id as company_id,
    c.company_name,
    cu.role_type,
    cu.department,
    cu.job_title,
    cu.status as user_status,
    d.department_name,
    COUNT(p.id) as owned_processes,
    COUNT(t.id) as assigned_tasks
FROM bpm_users u
JOIN bmp_company_users cu ON u.id = cu.user_id
JOIN bpm_companies c ON cu.company_id = c.id
LEFT JOIN bpm_departments d ON cu.department = d.department_name AND d.company_id = c.id
LEFT JOIN bpm_processes p ON u.id = p.owner_user_id AND p.company_id = c.id
LEFT JOIN bpm_tasks t ON u.id = t.responsible_user_id AND t.company_id = c.id
WHERE u.status = 'active' AND cu.is_active = TRUE
GROUP BY u.id, u.email, u.username, c.id, c.company_name, cu.role_type, 
         cu.department, cu.job_title, cu.status, d.department_name;

-- ===========================
-- 10. 초기 데이터 및 설정
-- ===========================

-- 시스템 설정 테이블
CREATE TABLE bpm_system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

-- 기본 시스템 설정
INSERT INTO bpm_system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('company_registration_enabled', 'true', 'boolean', '회사 등록 기능 활성화', TRUE),
('admin_can_assign_admin', 'true', 'boolean', '관리자가 다른 관리자 지정 가능', FALSE),
('min_admin_count', '1', 'number', '회사별 최소 관리자 수', FALSE),
('max_processes_per_company', '1000', 'number', '회사별 최대 프로세스 수', FALSE),
('enable_audit_logs', 'true', 'boolean', '감사 로그 활성화', FALSE),
('default_company_settings', '{"admin_can_assign_admin": true, "approval_required": false}', 'json', '회사 기본 설정', FALSE);

-- ===========================
-- 11. 권한 체크 함수
-- ===========================

-- 사용자 권한 체크 함수
DELIMITER //
CREATE FUNCTION bmp_check_user_permission(
    p_user_id CHAR(36),
    p_company_id CHAR(36),
    p_required_role ENUM('founder', 'admin', 'process_owner', 'member')
) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE user_role ENUM('founder', 'admin', 'process_owner', 'member');
    DECLARE result BOOLEAN DEFAULT FALSE;
    
    SELECT role_type INTO user_role
    FROM bmp_company_users
    WHERE user_id = p_user_id 
      AND company_id = p_company_id 
      AND is_active = TRUE 
      AND status = 'active';
    
    -- 권한 레벨: founder > admin > process_owner > member
    CASE user_role
        WHEN 'founder' THEN SET result = TRUE;
        WHEN 'admin' THEN 
            SET result = (p_required_role IN ('admin', 'process_owner', 'member'));
        WHEN 'process_owner' THEN 
            SET result = (p_required_role IN ('process_owner', 'member'));
        WHEN 'member' THEN 
            SET result = (p_required_role = 'member');
        ELSE SET result = FALSE;
    END CASE;
    
    RETURN result;
END //
DELIMITER ;

-- 관리자 수 체크 프로시저
DELIMITER //
CREATE PROCEDURE bmp_check_min_admin_count(
    IN p_company_id CHAR(36),
    OUT p_admin_count INT,
    OUT p_can_remove BOOLEAN
)
BEGIN
    DECLARE min_count INT DEFAULT 1;
    
    -- 현재 관리자 수 계산
    SELECT COUNT(*) INTO p_admin_count
    FROM bmp_company_users
    WHERE company_id = p_company_id
      AND role_type IN ('founder', 'admin')
      AND is_active = TRUE
      AND status = 'active';
    
    -- 최소 관리자 수 설정 조회
    SELECT CAST(setting_value AS UNSIGNED) INTO min_count
    FROM bpm_system_settings
    WHERE setting_key = 'min_admin_count';
    
    -- 제거 가능 여부 판단
    SET p_can_remove = (p_admin_count > min_count);
END //
DELIMITER ;

-- 추가 인덱스 최적화
CREATE INDEX idx_bmp_company_users_role_status ON bmp_company_users (company_id, role_type, status);
CREATE INDEX idx_bpm_processes_company_owner ON bpm_processes (company_id, owner_user_id);
CREATE INDEX idx_bpm_tasks_company_responsible ON bpm_tasks (company_id, responsible_user_id);
CREATE INDEX idx_bpm_departments_company_parent ON bpm_departments (company_id, parent_department_id);