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
    name VARCHAR(255) NOT NULL COMMENT '표시명',
    password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(50),
    status ENUM('active', 'pending', 'inactive', 'suspended') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    login_count INT DEFAULT 0 COMMENT '로그인 횟수',
    
    -- Remember Me 토큰 관련
    remember_token VARCHAR(100) NULL COMMENT 'Remember Me 토큰',
    remember_expires TIMESTAMP NULL COMMENT 'Remember 토큰 만료일',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT '소프트 삭제',
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_remember_token (remember_token),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
);

-- ===========================
-- 3. 회사별 사용자 역할 관리
-- ===========================

-- 회사 내 사용자 역할 (멀티테넌트 핵심)
CREATE TABLE bpm_company_users (
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
LEFT JOIN bpm_company_users cu ON c.id = cu.company_id AND cu.is_active = TRUE
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
JOIN bpm_company_users cu ON u.id = cu.user_id
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
CREATE FUNCTION bpm_check_user_permission(
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
    FROM bpm_company_users
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
CREATE PROCEDURE bpm_check_min_admin_count(
    IN p_company_id CHAR(36),
    OUT p_admin_count INT,
    OUT p_can_remove BOOLEAN
)
BEGIN
    DECLARE min_count INT DEFAULT 1;
    
    -- 현재 관리자 수 계산
    SELECT COUNT(*) INTO p_admin_count
    FROM bpm_company_users
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
CREATE INDEX idx_bpm_company_users_role_status ON bpm_company_users (company_id, role_type, status);
CREATE INDEX idx_bpm_processes_company_owner ON bpm_processes (company_id, owner_user_id);
CREATE INDEX idx_bpm_tasks_company_responsible ON bpm_tasks (company_id, responsible_user_id);
CREATE INDEX idx_bpm_departments_company_parent ON bpm_departments (company_id, parent_department_id);

-- ===========================
-- 12. AI 챗봇 시스템
-- ===========================

-- 챗봇 세션 테이블
CREATE TABLE bpm_chat_sessions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('active', 'ended', 'expired') DEFAULT 'active',
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_agent TEXT,
    ip_address VARCHAR(45),
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    INDEX idx_session_company_user (company_id, user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_session_status (status),
    INDEX idx_last_activity (last_activity)
);

-- 채팅 메시지 테이블
CREATE TABLE bpm_chat_messages (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    session_id CHAR(36) NOT NULL,
    message_type ENUM('user', 'bot', 'system') NOT NULL,
    content TEXT NOT NULL,
    response_time_ms INT DEFAULT 0 COMMENT 'AI 응답 시간(밀리초)',
    feedback_score TINYINT NULL COMMENT '사용자 피드백 점수(1-5)',
    feedback_comment TEXT NULL COMMENT '사용자 피드백 코멘트',
    context_data JSON COMMENT '대화 컨텍스트 데이터',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES bpm_chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_message_session (session_id),
    INDEX idx_message_type (message_type),
    INDEX idx_message_created (created_at),
    INDEX idx_feedback_score (feedback_score)
);

-- FAQ 관리 테이블
CREATE TABLE bpm_chat_faq (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NULL COMMENT '회사별 FAQ (NULL = 전역)',
    category VARCHAR(100) NOT NULL DEFAULT 'general',
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    keywords JSON COMMENT '검색 키워드 배열',
    priority INT DEFAULT 0 COMMENT '우선순위 (높을수록 우선)',
    usage_count INT DEFAULT 0 COMMENT '사용 횟수',
    is_active BOOLEAN DEFAULT TRUE,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    INDEX idx_faq_company_category (company_id, category),
    INDEX idx_faq_active (is_active),
    INDEX idx_faq_priority (priority DESC),
    INDEX idx_faq_usage (usage_count DESC),
    FULLTEXT idx_faq_search (question, answer)
);

-- 매뉴얼 관리 테이블
CREATE TABLE bpm_chat_manual (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NULL COMMENT '회사별 매뉴얼 (NULL = 전역)',
    module_name VARCHAR(100) NOT NULL COMMENT 'BPM 모듈명',
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    tags JSON COMMENT '태그 배열',
    version VARCHAR(20) DEFAULT '1.0',
    is_active BOOLEAN DEFAULT TRUE,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    INDEX idx_manual_company_module (company_id, module_name),
    INDEX idx_manual_active (is_active),
    INDEX idx_manual_version (version),
    FULLTEXT idx_manual_search (title, content)
);

-- 관리자 답변 템플릿 테이블
CREATE TABLE bpm_chat_admin_responses (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    category VARCHAR(100) NOT NULL,
    trigger_keywords JSON COMMENT '트리거 키워드 배열',
    response_template TEXT NOT NULL,
    variables JSON COMMENT '템플릿 변수 정의',
    usage_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by CHAR(36) NOT NULL,
    approved_by CHAR(36) NULL COMMENT '승인자',
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    INDEX idx_admin_response_company (company_id),
    INDEX idx_admin_response_category (category),
    INDEX idx_admin_response_active (is_active),
    INDEX idx_admin_response_approved (approved_by, approved_at)
);

-- 챗봇 학습 데이터 테이블
CREATE TABLE bpm_chat_training_data (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NULL COMMENT '회사별 데이터 (NULL = 전역)',
    data_type ENUM('conversation', 'feedback', 'correction') NOT NULL,
    input_text TEXT NOT NULL,
    expected_output TEXT NOT NULL,
    context_info JSON COMMENT '컨텍스트 정보',
    quality_score DECIMAL(3,2) DEFAULT 0.00 COMMENT '품질 점수 0-1',
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by CHAR(36) NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    INDEX idx_training_company_type (company_id, data_type),
    INDEX idx_training_verified (is_verified),
    INDEX idx_training_quality (quality_score DESC),
    FULLTEXT idx_training_search (input_text, expected_output)
);

-- 초기 FAQ 데이터
INSERT INTO bpm_chat_faq (company_id, category, question, answer, keywords, priority, created_by) VALUES
(NULL, 'login', 'BPM에 로그인할 수 없어요', 'BPM 로그인 문제는 다음과 같이 해결하세요:\n1. 이메일과 비밀번호를 정확히 입력했는지 확인\n2. 비밀번호를 잊었다면 "비밀번호 재설정" 클릭\n3. 계정이 비활성화되었다면 관리자에게 문의\n4. 여전히 문제가 있다면 브라우저 캐시를 삭제해보세요', '["로그인", "패스워드", "비밀번호", "접속", "계정"]', 10, (SELECT id FROM bpm_users LIMIT 1)),
(NULL, 'navigation', '모듈 간 이동은 어떻게 하나요?', '모듈 간 이동 방법:\n1. 좌측 사이드바에서 원하는 모듈 클릭\n2. 상단 브레드크럼에서 이전 페이지로 이동\n3. 모듈별 색상 테마로 현재 위치 확인 가능\n4. 검색 기능으로 빠른 페이지 찾기', '["모듈", "이동", "네비게이션", "메뉴", "사이드바"]', 9, (SELECT id FROM bpm_users LIMIT 1)),
(NULL, 'permissions', '권한이 없다고 나와요', '권한 관련 문제 해결:\n1. 현재 권한 레벨 확인 (Member/Process Owner/Admin/Founder)\n2. 해당 기능에 필요한 권한 확인\n3. 권한이 부족하면 관리자에게 권한 승급 요청\n4. 임시 권한이 만료되었는지 확인', '["권한", "접근", "승인", "관리자", "제한"]', 8, (SELECT id FROM bpm_users LIMIT 1));

-- 초기 매뉴얼 데이터
INSERT INTO bpm_chat_manual (company_id, module_name, title, content, tags, created_by) VALUES
(NULL, 'organization', '조직관리 모듈 사용법', '조직관리 모듈 사용 가이드:\n\n1. **회사 등록**\n   - 좌측 메뉴에서 "조직관리" 선택\n   - "회사 추가" 버튼 클릭\n   - 회사 정보 입력 후 저장\n\n2. **부서 관리**\n   - 부서 트리에서 "+" 버튼으로 하위 부서 추가\n   - 드래그 앤 드롭으로 부서 이동\n   - 부서별 담당자 지정\n\n3. **조직도 보기**\n   - "조직도 보기" 탭 선택\n   - 시각적 조직 구조 확인\n   - 확대/축소 및 인쇄 기능 활용', '["조직관리", "회사", "부서", "조직도"]', (SELECT id FROM bpm_users LIMIT 1)),
(NULL, 'members', '구성원관리 모듈 사용법', '구성원관리 모듈 사용 가이드:\n\n1. **구성원 초대**\n   - "구성원 초대" 버튼 클릭\n   - 이메일 입력 및 권한 선택\n   - 초대 메일 발송\n\n2. **권한 관리**\n   - 구성원 목록에서 권한 변경\n   - 4단계 권한: Founder > Admin > Process Owner > Member\n   - 권한별 접근 가능 기능 확인\n\n3. **부서 배치**\n   - 구성원을 해당 부서에 배치\n   - 복수 부서 소속 가능\n   - 인사이동 이력 자동 기록', '["구성원", "초대", "권한", "부서배치"]', (SELECT id FROM bpm_users LIMIT 1));

-- ===========================
-- 13. 사용자 초대 및 인증 시스템
-- ===========================

-- 사용자 초대 테이블
CREATE TABLE bpm_user_invitations (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role_type ENUM('founder', 'admin', 'process_owner', 'member') NOT NULL DEFAULT 'member',
    department VARCHAR(255),
    job_title VARCHAR(255),
    message TEXT COMMENT '초대 메시지',
    invite_token VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'sent', 'accepted', 'expired', 'cancelled') DEFAULT 'pending',
    invited_by CHAR(36) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    
    INDEX idx_invitation_token (invite_token),
    INDEX idx_invitation_email (email),
    INDEX idx_invitation_company (company_id),
    INDEX idx_invitation_status (status),
    INDEX idx_invitation_expires (expires_at)
);

-- 비밀번호 재설정 테이블
CREATE TABLE bpm_password_resets (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_reset (user_id),
    INDEX idx_reset_token (token),
    INDEX idx_reset_expires (expires_at),
    INDEX idx_reset_used (used)
);

-- 이메일 인증 테이블
CREATE TABLE bpm_email_verifications (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    email VARCHAR(255) NOT NULL,
    verification_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    
    INDEX idx_verification_token (verification_token),
    INDEX idx_verification_email (email),
    INDEX idx_verification_user (user_id),
    INDEX idx_verification_expires (expires_at)
);

-- 사용자 세션 관리 테이블
CREATE TABLE bpm_user_sessions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    company_id CHAR(36) NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    refresh_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    
    INDEX idx_session_token (session_token),
    INDEX idx_refresh_token (refresh_token),
    INDEX idx_session_user (user_id),
    INDEX idx_session_company (company_id),
    INDEX idx_session_active (is_active),
    INDEX idx_session_expires (expires_at)
);

-- 사용자 알림 설정 테이블
CREATE TABLE bpm_user_notification_settings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    company_id CHAR(36) NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    browser_notifications BOOLEAN DEFAULT TRUE,
    mobile_notifications BOOLEAN DEFAULT TRUE,
    notification_types JSON COMMENT '알림 유형별 설정',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_company_settings (user_id, company_id),
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_company (company_id)
);

-- ===========================
-- 14. 사용자 관련 뷰 및 프로시저
-- ===========================

-- 사용자 상세 정보 뷰
CREATE VIEW bpm_user_details AS
SELECT 
    u.id,
    u.email,
    u.username,
    u.name,
    u.first_name,
    u.last_name,
    u.phone,
    u.status,
    u.email_verified,
    u.last_login_at,
    u.login_count,
    cu.company_id,
    c.company_name,
    cu.role_type,
    cu.department,
    cu.job_title,
    cu.employee_id,
    cu.status as company_status,
    cu.assigned_at,
    d.department_name,
    d.head_user_id = u.id as is_department_head
FROM bpm_users u
JOIN bpm_company_users cu ON u.id = cu.user_id
JOIN bpm_companies c ON cu.company_id = c.id
LEFT JOIN bpm_departments d ON cu.department = d.department_name AND d.company_id = cu.company_id
WHERE u.status = 'active' AND cu.is_active = TRUE;

-- 초대 현황 뷰
CREATE VIEW bpm_invitation_status AS
SELECT 
    i.id,
    i.company_id,
    c.company_name,
    i.email,
    i.role_type,
    i.department,
    i.job_title,
    i.status,
    i.expires_at,
    i.created_at,
    u.name as inviter_name,
    CASE 
        WHEN i.expires_at < NOW() THEN 'expired'
        WHEN i.status = 'accepted' THEN 'completed'
        WHEN i.status = 'cancelled' THEN 'cancelled'
        ELSE 'pending'
    END as current_status
FROM bpm_user_invitations i
JOIN bpm_companies c ON i.company_id = c.id
JOIN bpm_users u ON i.invited_by = u.id;

-- 초대 만료 처리 프로시저
DELIMITER //
CREATE PROCEDURE bpm_expire_invitations()
BEGIN
    UPDATE bpm_user_invitations 
    SET status = 'expired' 
    WHERE status IN ('pending', 'sent') 
    AND expires_at < NOW();
END //
DELIMITER ;

-- 비밀번호 재설정 토큰 정리 프로시저
DELIMITER //
CREATE PROCEDURE bpm_cleanup_password_resets()
BEGIN
    DELETE FROM bpm_password_resets 
    WHERE expires_at < NOW() OR used = TRUE;
END //
DELIMITER ;

-- 비활성 세션 정리 프로시저
DELIMITER //
CREATE PROCEDURE bpm_cleanup_inactive_sessions()
BEGIN
    UPDATE bpm_user_sessions 
    SET is_active = FALSE 
    WHERE expires_at < NOW() OR last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    DELETE FROM bpm_user_sessions 
    WHERE is_active = FALSE AND last_activity < DATE_SUB(NOW(), INTERVAL 90 DAY);
END //
DELIMITER ;

-- 챗봇 시스템 설정
INSERT INTO bpm_system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('chatbot_enabled', 'true', 'boolean', '챗봇 기능 활성화', TRUE),
('chatbot_response_timeout', '30', 'number', '챗봇 응답 타임아웃(초)', FALSE),
('chatbot_max_session_duration', '3600', 'number', '최대 세션 지속 시간(초)', FALSE),
('chatbot_gemini_model', 'gemini-1.5-flash', 'string', 'Gemini AI 모델명', FALSE),
('chatbot_default_language', 'ko', 'string', '기본 언어 설정', TRUE),
('invitation_expires_days', '7', 'number', '초대 만료 일수', FALSE),
('password_reset_expires_minutes', '30', 'number', '비밀번호 재설정 만료 시간(분)', FALSE),
('email_verification_expires_hours', '24', 'number', '이메일 인증 만료 시간', FALSE),
('session_expires_days', '30', 'number', '세션 만료 일수', FALSE),
('org_chart_auto_layout', 'true', 'boolean', '조직도 자동 레이아웃 활성화', FALSE),
('org_change_approval_required', 'true', 'boolean', '조직 변경 승인 필요', FALSE),
('org_chart_default_template', 'hierarchical', 'string', '기본 조직도 템플릿', TRUE),
('dept_head_assignment_approval', 'admin', 'string', '부서장 지정 승인 권한', FALSE),
('max_org_levels', '10', 'number', '최대 조직 레벨', FALSE);