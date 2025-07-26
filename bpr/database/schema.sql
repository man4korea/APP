-- BPR AI 지원 기능을 위한 데이터베이스 스키마

-- 사용자 및 구독 관리
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    company VARCHAR(255),
    role ENUM('admin', 'manager', 'analyst', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- 구독 플랜 정보
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    plan_type ENUM('free', 'premium', 'premium_plus', 'enterprise') NOT NULL,
    monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    yearly_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    monthly_token_limit INT NOT NULL DEFAULT 0,
    daily_request_limit INT NOT NULL DEFAULT 0,
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_plan_type (plan_type),
    INDEX idx_is_active (is_active)
);

-- 사용자 구독 정보
CREATE TABLE user_subscriptions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    plan_id INT NOT NULL,
    plan_type ENUM('free', 'premium', 'premium_plus', 'enterprise') NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'suspended') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(50),
    subscription_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
);

-- AI 사용량 로그
CREATE TABLE ai_usage_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    feature_type ENUM('task_manual', 'task_suggestion', 'process_optimization', 'bpr_report', 'org_optimization', 'bulk_analysis') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tokens INT DEFAULT 0,
    prompt_tokens INT DEFAULT 0,
    completion_tokens INT DEFAULT 0,
    model VARCHAR(100),
    request_duration INT DEFAULT 0, -- milliseconds
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    request_metadata JSON,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_feature_time (user_id, feature_type, timestamp),
    INDEX idx_timestamp (timestamp),
    INDEX idx_success (success),
    PARTITION BY RANGE (YEAR(timestamp)) (
        PARTITION p2024 VALUES LESS THAN (2025),
        PARTITION p2025 VALUES LESS THAN (2026),
        PARTITION p2026 VALUES LESS THAN (2027),
        PARTITION p_future VALUES LESS THAN MAXVALUE
    )
);

-- 사용량 제한 위반 기록
CREATE TABLE usage_violations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    violation_type ENUM('monthly_tokens', 'daily_requests', 'hourly_requests', 'concurrent_requests') NOT NULL,
    current_usage INT NOT NULL,
    limit_value INT NOT NULL,
    feature_type ENUM('task_manual', 'task_suggestion', 'process_optimization', 'bpr_report', 'org_optimization', 'bulk_analysis'),
    violation_data JSON,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_timestamp (user_id, timestamp),
    INDEX idx_violation_type (violation_type)
);

-- BPR 프로젝트 정보
CREATE TABLE bpr_projects (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    industry VARCHAR(100),
    project_scope ENUM('department', 'company', 'multi_company') DEFAULT 'department',
    status ENUM('planning', 'analysis', 'design', 'implementation', 'completed', 'on_hold') DEFAULT 'planning',
    start_date DATE,
    target_completion_date DATE,
    actual_completion_date DATE,
    project_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_industry (industry)
);

-- 비즈니스 프로세스 정보
CREATE TABLE business_processes (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    project_id CHAR(36) NOT NULL,
    process_name VARCHAR(255) NOT NULL,
    description TEXT,
    process_category VARCHAR(100),
    current_owner VARCHAR(255),
    process_goal TEXT,
    process_type ENUM('core', 'support', 'management') DEFAULT 'core',
    complexity_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    automation_level ENUM('manual', 'semi_automated', 'fully_automated') DEFAULT 'manual',
    process_data JSON, -- 프로세스 세부 정보, 흐름도 등
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES bpr_projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_process_category (process_category),
    INDEX idx_complexity_level (complexity_level)
);

-- 업무 태스크 정보
CREATE TABLE business_tasks (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    process_id CHAR(36) NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    task_order INT DEFAULT 0,
    estimated_duration INT DEFAULT 0, -- minutes
    required_skills JSON,
    task_requirements JSON,
    task_steps JSON,
    responsible_role VARCHAR(255),
    task_complexity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    automation_candidate BOOLEAN DEFAULT FALSE,
    task_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (process_id) REFERENCES business_processes(id) ON DELETE CASCADE,
    INDEX idx_process_id (process_id),
    INDEX idx_task_order (task_order),
    INDEX idx_automation_candidate (automation_candidate)
);

-- 조직 구조 정보
CREATE TABLE organizations (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    project_id CHAR(36) NOT NULL,
    org_name VARCHAR(255) NOT NULL,
    parent_org_id CHAR(36),
    org_level INT DEFAULT 0,
    org_type ENUM('company', 'division', 'department', 'team', 'role') DEFAULT 'department',
    head_title VARCHAR(255),
    employee_count INT DEFAULT 0,
    responsibilities JSON,
    communication_patterns JSON,
    performance_metrics JSON,
    org_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES bpr_projects(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_org_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_project_id (project_id),
    INDEX idx_parent_org_id (parent_org_id),
    INDEX idx_org_level (org_level)
);

-- AI 생성 결과 저장
CREATE TABLE ai_generated_results (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    result_type ENUM('task_manual', 'task_suggestions', 'process_optimization', 'bpr_report', 'org_optimization') NOT NULL,
    related_entity_id CHAR(36), -- task_id, process_id, project_id 등
    related_entity_type ENUM('task', 'process', 'project', 'organization'),
    title VARCHAR(500),
    content LONGTEXT,
    content_format ENUM('json', 'markdown', 'html', 'pdf') DEFAULT 'json',
    generation_metadata JSON, -- AI 모델, 토큰 사용량, 생성 시간 등
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by CHAR(36),
    approved_at TIMESTAMP NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_type (user_id, result_type),
    INDEX idx_related_entity (related_entity_id, related_entity_type),
    INDEX idx_created_at (created_at)
);

-- BPR 리포트 상세 정보
CREATE TABLE bpr_reports (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    result_id CHAR(36) NOT NULL,
    project_id CHAR(36) NOT NULL,
    report_type ENUM('comprehensive', 'executive', 'technical') DEFAULT 'comprehensive',
    report_format ENUM('pdf', 'docx', 'html') DEFAULT 'pdf',
    file_path VARCHAR(500),
    file_size INT DEFAULT 0, -- bytes
    download_count INT DEFAULT 0,
    report_sections JSON, -- 포함된 섹션들
    generation_duration INT DEFAULT 0, -- seconds
    report_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (result_id) REFERENCES ai_generated_results(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES bpr_projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_report_type (report_type),
    INDEX idx_created_at (created_at)
);

-- 성과 지표 및 메트릭
CREATE TABLE performance_metrics (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    entity_id CHAR(36) NOT NULL,
    entity_type ENUM('project', 'process', 'task', 'organization') NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    metric_category ENUM('efficiency', 'quality', 'cost', 'time', 'satisfaction') NOT NULL,
    current_value DECIMAL(15,4),
    target_value DECIMAL(15,4),
    baseline_value DECIMAL(15,4),
    unit VARCHAR(50),
    measurement_date DATE,
    data_source VARCHAR(255),
    metric_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_entity (entity_id, entity_type),
    INDEX idx_metric_category (metric_category),
    INDEX idx_measurement_date (measurement_date)
);

-- 사용자 피드백 및 평가
CREATE TABLE user_feedback (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    feedback_type ENUM('ai_result', 'feature', 'bug_report', 'general') NOT NULL,
    related_result_id CHAR(36),
    feature_type ENUM('task_manual', 'task_suggestion', 'process_optimization', 'bpr_report', 'org_optimization'),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    feedback_text TEXT,
    is_resolved BOOLEAN DEFAULT FALSE,
    resolution_notes TEXT,
    feedback_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_result_id) REFERENCES ai_generated_results(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_feedback_type (feedback_type),
    INDEX idx_rating (rating),
    INDEX idx_is_resolved (is_resolved)
);

-- 시스템 설정 및 환경변수
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE, -- 클라이언트에서 접근 가능한지
    updated_by CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

-- 초기 데이터 삽입

-- 구독 플랜 기본 데이터
INSERT INTO subscription_plans (plan_name, plan_type, monthly_price, yearly_price, monthly_token_limit, daily_request_limit, features) VALUES
('Free Plan', 'free', 0.00, 0.00, 0, 0, '{"ai_features": false, "basic_bpr": true, "max_projects": 1}'),
('Premium Plan', 'premium', 29.99, 299.99, 50000, 200, '{"ai_features": true, "task_manual": true, "task_suggestions": true, "max_projects": 5}'),
('Premium Plus', 'premium_plus', 59.99, 599.99, 100000, 500, '{"ai_features": true, "all_features": true, "bpr_reports": true, "max_projects": 20, "api_access": true}'),
('Enterprise', 'enterprise', 199.99, 1999.99, 500000, 2000, '{"ai_features": true, "all_features": true, "unlimited_projects": true, "api_access": true, "priority_support": true, "custom_integration": true}');

-- 시스템 설정 기본값
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('ai_default_engine', 'openai', 'string', 'Default AI engine to use', FALSE),
('ai_fallback_engine', 'anthropic', 'string', 'Fallback AI engine', FALSE),
('premium_monthly_token_limit', '100000', 'number', 'Monthly token limit for premium users', FALSE),
('premium_daily_request_limit', '500', 'number', 'Daily request limit for premium users', FALSE),
('max_report_size_mb', '50', 'number', 'Maximum report file size in MB', FALSE),
('enable_ai_features', 'true', 'boolean', 'Global AI features toggle', TRUE),
('maintenance_mode', 'false', 'boolean', 'System maintenance mode', TRUE),
('supported_ai_engines', '["openai", "anthropic", "google", "azure"]', 'json', 'List of supported AI engines', FALSE);

-- 인덱스 최적화를 위한 추가 인덱스
CREATE INDEX idx_usage_logs_monthly ON ai_usage_logs (user_id, YEAR(timestamp), MONTH(timestamp));
CREATE INDEX idx_usage_logs_daily ON ai_usage_logs (user_id, DATE(timestamp));
CREATE INDEX idx_results_user_created ON ai_generated_results (user_id, created_at DESC);
CREATE INDEX idx_feedback_feature_rating ON user_feedback (feature_type, rating);

-- 파티셔닝을 위한 프로시저 (년도별 파티션 자동 생성)
DELIMITER //
CREATE PROCEDURE CreateYearlyPartition(IN partition_year INT)
BEGIN
    SET @sql = CONCAT('ALTER TABLE ai_usage_logs ADD PARTITION (PARTITION p', partition_year, ' VALUES LESS THAN (', partition_year + 1, '))');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- 뷰 생성 (자주 사용되는 쿼리 최적화)

-- 사용자별 현재 구독 정보 뷰
CREATE VIEW user_current_subscriptions AS
SELECT 
    u.id as user_id,
    u.email,
    u.username,
    us.id as subscription_id,
    sp.plan_name,
    us.plan_type,
    us.status as subscription_status,
    us.start_date,
    us.end_date,
    us.auto_renew,
    sp.monthly_token_limit,
    sp.daily_request_limit,
    sp.features
FROM users u
LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active' AND us.end_date >= CURDATE()
LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
WHERE u.status = 'active';

-- 월별 사용량 집계 뷰
CREATE VIEW monthly_usage_summary AS
SELECT 
    user_id,
    YEAR(timestamp) as year,
    MONTH(timestamp) as month,
    feature_type,
    COUNT(*) as request_count,
    SUM(tokens) as total_tokens,
    SUM(prompt_tokens) as total_prompt_tokens,
    SUM(completion_tokens) as total_completion_tokens,
    AVG(request_duration) as avg_duration,
    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests,
    SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_requests
FROM ai_usage_logs
GROUP BY user_id, YEAR(timestamp), MONTH(timestamp), feature_type;

-- 프로젝트 진행 상황 뷰
CREATE VIEW project_progress_view AS
SELECT 
    p.id as project_id,
    p.project_name,
    p.user_id,
    p.status as project_status,
    p.start_date,
    p.target_completion_date,
    COUNT(DISTINCT bp.id) as process_count,
    COUNT(DISTINCT bt.id) as task_count,
    COUNT(DISTINCT agr.id) as ai_result_count,
    AVG(CASE 
        WHEN p.status = 'completed' THEN 100
        WHEN p.status = 'implementation' THEN 80
        WHEN p.status = 'design' THEN 60
        WHEN p.status = 'analysis' THEN 40
        WHEN p.status = 'planning' THEN 20
        ELSE 0
    END) as completion_percentage
FROM bpr_projects p
LEFT JOIN business_processes bp ON p.id = bp.project_id
LEFT JOIN business_tasks bt ON bp.id = bt.process_id
LEFT JOIN ai_generated_results agr ON p.id = agr.related_entity_id AND agr.related_entity_type = 'project'
GROUP BY p.id, p.project_name, p.user_id, p.status, p.start_date, p.target_completion_date;