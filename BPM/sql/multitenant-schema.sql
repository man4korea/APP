-- 📁 C:\xampp\htdocs\BPM\sql\multitenant-schema.sql
-- Create at 2508031400 Ver1.00

/**
 * BPM 멀티테넌트 데이터베이스 스키마
 * 테넌트별 데이터 격리 및 Row-Level Security 구현
 */

-- =======================================================================
-- 1. 시스템 공통 테이블 (모든 테넌트 공유)
-- =======================================================================

-- 테넌트 관리
CREATE TABLE tenants (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    plan ENUM('starter', 'professional', 'enterprise') DEFAULT 'starter',
    max_users INT DEFAULT 10,
    storage_limit BIGINT DEFAULT 1073741824, -- 1GB in bytes
    features JSON,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status),
    INDEX idx_plan (plan)
);

-- 시스템 사용자 (글로벌 계정)
CREATE TABLE system_users (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_system_admin BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    two_factor_secret VARCHAR(255),
    two_factor_recovery_codes TEXT,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_system_admin (is_system_admin)
);

-- 테넌트-사용자 관계
CREATE TABLE tenant_users (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    role ENUM('admin', 'manager', 'user', 'viewer') DEFAULT 'user',
    permissions JSON,
    status ENUM('active', 'inactive', 'invited') DEFAULT 'active',
    invited_by VARCHAR(36),
    invited_at TIMESTAMP NULL,
    joined_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES system_users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_tenant_user (tenant_id, user_id),
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- =======================================================================
-- 2. 모듈별 테넌트 데이터 테이블
-- =======================================================================

-- 🔴 조직관리 모듈
CREATE TABLE organizations (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id VARCHAR(36),
    level INT NOT NULL DEFAULT 1,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    manager_id VARCHAR(36),
    contact_info JSON,
    settings JSON,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    UNIQUE KEY unique_tenant_code (tenant_id, code),
    INDEX idx_tenant_org (tenant_id, id),
    INDEX idx_parent (parent_id),
    INDEX idx_level (level),
    INDEX idx_active (is_active)
);

-- 🟠 구성원 관리
CREATE TABLE members (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    employee_id VARCHAR(50),
    organization_id VARCHAR(36) NOT NULL,
    position VARCHAR(100),
    title VARCHAR(100),
    department VARCHAR(100),
    hire_date DATE,
    employment_type ENUM('full_time', 'part_time', 'contract', 'intern') DEFAULT 'full_time',
    status ENUM('active', 'inactive', 'on_leave', 'terminated') DEFAULT 'active',
    manager_id VARCHAR(36),
    profile JSON,
    contact_info JSON,
    emergency_contact JSON,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    UNIQUE KEY unique_tenant_employee (tenant_id, employee_id),
    UNIQUE KEY unique_tenant_user (tenant_id, user_id),
    INDEX idx_tenant_member (tenant_id, id),
    INDEX idx_organization (organization_id),
    INDEX idx_status (status),
    INDEX idx_manager (manager_id)
);

-- 🟡 Task 관리
CREATE TABLE tasks (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'on_hold') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    category VARCHAR(100),
    tags JSON,
    assignee_id VARCHAR(36),
    reporter_id VARCHAR(36) NOT NULL,
    organization_id VARCHAR(36),
    due_date DATETIME,
    estimated_hours DECIMAL(8,2),
    actual_hours DECIMAL(8,2),
    completion_percentage INT DEFAULT 0,
    dependencies JSON, -- Array of task IDs
    attachments JSON,
    comments_count INT DEFAULT 0,
    watchers JSON, -- Array of user IDs
    custom_fields JSON,
    completed_at TIMESTAMP NULL,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (assignee_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (reporter_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    INDEX idx_tenant_task (tenant_id, id),
    INDEX idx_assignee (assignee_id),
    INDEX idx_reporter (reporter_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_organization (organization_id)
);

-- 🟢 문서 관리
CREATE TABLE documents (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    content_type ENUM('text', 'markdown', 'html', 'pdf', 'word', 'excel', 'image', 'other') DEFAULT 'text',
    file_path VARCHAR(500),
    file_size BIGINT,
    mime_type VARCHAR(100),
    version VARCHAR(10) DEFAULT '1.0',
    status ENUM('draft', 'review', 'approved', 'published', 'archived') DEFAULT 'draft',
    category VARCHAR(100),
    tags JSON,
    organization_id VARCHAR(36),
    access_level ENUM('public', 'internal', 'restricted', 'private') DEFAULT 'internal',
    permissions JSON,
    metadata JSON,
    checksum VARCHAR(64),
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    approved_by VARCHAR(36),
    approved_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    INDEX idx_tenant_doc (tenant_id, id),
    INDEX idx_organization (organization_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_access_level (access_level),
    INDEX idx_created_by (created_by),
    FULLTEXT idx_content (title, content)
);

-- 🔵 Process Map
CREATE TABLE processes (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    type ENUM('core', 'support', 'management') DEFAULT 'core',
    status ENUM('active', 'inactive', 'under_review', 'deprecated') DEFAULT 'active',
    version VARCHAR(10) DEFAULT '1.0',
    owner_id VARCHAR(36),
    organization_id VARCHAR(36),
    process_map JSON, -- 프로세스 맵 데이터
    steps JSON, -- 프로세스 단계들
    inputs JSON, -- 입력 요소들
    outputs JSON, -- 출력 요소들
    resources JSON, -- 필요 자원들
    metrics JSON, -- 성과 지표들
    risks JSON, -- 위험 요소들
    controls JSON, -- 통제 방안들
    last_review_date DATE,
    next_review_date DATE,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    UNIQUE KEY unique_tenant_code (tenant_id, code),
    INDEX idx_tenant_process (tenant_id, id),
    INDEX idx_organization (organization_id),
    INDEX idx_status (status),
    INDEX idx_owner (owner_id),
    INDEX idx_category (category)
);

-- 🟣 업무 Flow
CREATE TABLE workflows (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    process_id VARCHAR(36),
    trigger_type ENUM('manual', 'scheduled', 'event', 'api') DEFAULT 'manual',
    trigger_config JSON,
    definition JSON, -- 워크플로우 정의
    variables JSON, -- 워크플로우 변수
    is_active BOOLEAN DEFAULT TRUE,
    version VARCHAR(10) DEFAULT '1.0',
    execution_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    last_execution_at TIMESTAMP NULL,
    created_by VARCHAR(36) NOT NULL,
    updated_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    FOREIGN KEY (updated_by) REFERENCES system_users(id),
    
    INDEX idx_tenant_workflow (tenant_id, id),
    INDEX idx_process (process_id),
    INDEX idx_active (is_active),
    INDEX idx_trigger_type (trigger_type)
);

-- 워크플로우 실행 기록
CREATE TABLE workflow_executions (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tenant_id VARCHAR(36) NOT NULL,
    workflow_id VARCHAR(36) NOT NULL,
    status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    input_data JSON,
    output_data JSON,
    error_message TEXT,
    execution_time_ms INT,
    started_by VARCHAR(36),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
    FOREIGN KEY (started_by) REFERENCES system_users(id),
    
    INDEX idx_tenant_execution (tenant_id, id),
    INDEX idx_workflow (workflow_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);

-- =======================================================================
-- 3. 시스템 모니터링 및 로깅 테이블
-- =======================================================================

-- 시스템 로그
CREATE TABLE system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(36),
    level ENUM('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug') NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    channel VARCHAR(50) DEFAULT 'application',
    user_id VARCHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tenant_log (tenant_id, created_at),
    INDEX idx_level (level),
    INDEX idx_channel (channel),
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
);

-- 감사 로그
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id VARCHAR(36),
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE CASCADE,
    
    INDEX idx_tenant_audit (tenant_id, created_at),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_resource (resource_type, resource_id)
);

-- =======================================================================
-- 4. 캐시 및 세션 테이블
-- =======================================================================

-- 세션 저장소 (Redis 백업용)
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    tenant_id VARCHAR(36),
    user_id VARCHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    INDEX idx_tenant_session (tenant_id, user_id),
    INDEX idx_last_activity (last_activity)
);

-- 캐시 저장소 (Redis 백업용)
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value LONGTEXT NOT NULL,
    expiration INT NOT NULL,
    
    INDEX idx_expiration (expiration)
);

-- =======================================================================
-- 5. Row-Level Security를 위한 뷰 및 프로시저
-- =======================================================================

-- 현재 테넌트 설정 함수
DELIMITER //
CREATE FUNCTION current_tenant_id() RETURNS VARCHAR(36)
READS SQL DATA
DETERMINISTIC
SQL SECURITY DEFINER
BEGIN
    RETURN @current_tenant_id;
END //
DELIMITER ;

-- 테넌트별 데이터 접근 뷰 생성 예시 (organizations)
CREATE VIEW tenant_organizations AS
SELECT * FROM organizations 
WHERE tenant_id = current_tenant_id();

-- =======================================================================
-- 6. 초기 데이터 및 인덱스 최적화
-- =======================================================================

-- 시스템 관리자 계정 생성
INSERT INTO system_users (id, email, password_hash, name, is_system_admin, email_verified_at) 
VALUES (
    UUID(), 
    'admin@bpm-system.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'System Administrator', 
    TRUE, 
    NOW()
);

-- 데모 테넌트 생성
INSERT INTO tenants (id, name, subdomain, status, plan, max_users) 
VALUES (
    UUID(),
    'Demo Company',
    'demo',
    'active',
    'professional',
    100
);

-- 성능 최적화를 위한 복합 인덱스
CREATE INDEX idx_tasks_tenant_status_assignee ON tasks (tenant_id, status, assignee_id);
CREATE INDEX idx_documents_tenant_organization_status ON documents (tenant_id, organization_id, status);
CREATE INDEX idx_workflows_tenant_active_process ON workflows (tenant_id, is_active, process_id);
CREATE INDEX idx_members_tenant_organization_status ON members (tenant_id, organization_id, status);

-- 파티셔닝을 위한 설정 (대용량 로그 테이블)
ALTER TABLE system_logs PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
    PARTITION p_2025_01 VALUES LESS THAN (UNIX_TIMESTAMP('2025-02-01')),
    PARTITION p_2025_02 VALUES LESS THAN (UNIX_TIMESTAMP('2025-03-01')),
    PARTITION p_2025_03 VALUES LESS THAN (UNIX_TIMESTAMP('2025-04-01')),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);