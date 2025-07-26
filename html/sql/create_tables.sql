-- BPR Hub Database Schema
-- Business Process Reengineering Management System
-- Created: 2025-01-26

-- Set character set and collation
SET NAMES utf8mb4;
SET character_set_client = utf8mb4;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS bpr_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bpr_hub;

-- ============================================================================
-- USERS TABLE - 사용자 정보
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    phone VARCHAR(20),
    profile_image VARCHAR(500),
    
    -- 소셜 로그인 정보
    login_type ENUM('email', 'kakao', 'google') DEFAULT 'email',
    social_id VARCHAR(255),
    
    -- 계정 상태
    email_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 인덱스
    INDEX idx_email (email),
    INDEX idx_social (login_type, social_id),
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
);

-- ============================================================================
-- ORGANIZATIONS TABLE - 조직 정보
-- ============================================================================
CREATE TABLE organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    logo_url VARCHAR(500),
    website VARCHAR(255),
    
    -- 조직 설정
    timezone VARCHAR(50) DEFAULT 'Asia/Seoul',
    language VARCHAR(10) DEFAULT 'ko',
    max_members INT DEFAULT 50,
    
    -- 소유자 정보
    owner_id INT NOT NULL,
    
    -- 조직 상태
    is_active BOOLEAN DEFAULT TRUE,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- 인덱스
    INDEX idx_slug (slug),
    INDEX idx_owner (owner_id),
    INDEX idx_active (is_active)
);

-- ============================================================================
-- ORGANIZATION_MEMBERS TABLE - 조직 구성원
-- ============================================================================
CREATE TABLE organization_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- 멤버 역할
    role ENUM('owner', 'admin', 'manager', 'member') DEFAULT 'member',
    title VARCHAR(100),
    department VARCHAR(100),
    
    -- 멤버 상태
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    invited_by INT,
    joined_at TIMESTAMP NULL,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- 유니크 제약조건
    UNIQUE KEY unique_org_user (organization_id, user_id),
    
    -- 인덱스
    INDEX idx_org (organization_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- ============================================================================
-- USER_SUBSCRIPTIONS TABLE - 구독 정보
-- ============================================================================
CREATE TABLE user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- 구독 플랜
    plan_type ENUM('free', 'premium', 'enterprise') DEFAULT 'free',
    plan_name VARCHAR(100) NOT NULL,
    
    -- 구독 상태
    status ENUM('active', 'inactive', 'expired', 'cancelled') DEFAULT 'active',
    
    -- 구독 기간
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    
    -- 제한사항
    max_process_maps INT DEFAULT 5,
    max_organization_members INT DEFAULT 10,
    ai_tokens_limit INT DEFAULT 0,
    ai_tokens_used INT DEFAULT 0,
    
    -- 결제 정보
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'KRW',
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- 인덱스
    INDEX idx_user (user_id),
    INDEX idx_plan (plan_type),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
);

-- ============================================================================
-- PROCESS_MAPS TABLE - 프로세스 맵
-- ============================================================================
CREATE TABLE process_maps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    
    -- 소유자 정보
    organization_id INT NOT NULL,
    created_by INT NOT NULL,
    
    -- 프로세스 맵 데이터
    map_data JSON,
    thumbnail_url VARCHAR(500),
    
    -- 메타데이터
    version INT DEFAULT 1,
    is_template BOOLEAN DEFAULT FALSE,
    template_category VARCHAR(100),
    
    -- 공유 설정
    visibility ENUM('private', 'organization', 'public') DEFAULT 'organization',
    
    -- 상태
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    
    -- 통계
    view_count INT DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    -- 인덱스
    INDEX idx_organization (organization_id),
    INDEX idx_creator (created_by),
    INDEX idx_status (status),
    INDEX idx_visibility (visibility),
    INDEX idx_created (created_at)
);

-- ============================================================================
-- TASKS TABLE - 작업 관리
-- ============================================================================
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    
    -- 소속 정보
    organization_id INT NOT NULL,
    process_map_id INT,
    
    -- 작업 분류
    task_type ENUM('DB', 'CM', 'DM', 'OTHER') NOT NULL,
    category VARCHAR(100),
    
    -- 시간 정보 (분 단위)
    processing_time INT DEFAULT 0,  -- PT (Processing Time)
    lead_time INT DEFAULT 0,        -- LT (Lead Time)
    
    -- 담당자 정보
    assigned_to INT,
    assigned_by INT,
    assigned_at TIMESTAMP NULL,
    
    -- 작업 상태
    status ENUM('todo', 'in_progress', 'review', 'completed', 'cancelled') DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    
    -- 진행률
    progress_percentage INT DEFAULT 0,
    
    -- 일정
    due_date DATE,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    -- AI 분석 정보
    ai_analysis JSON,
    ai_suggestions TEXT,
    optimization_score INT DEFAULT 0,
    
    -- 태그
    tags JSON,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (process_map_id) REFERENCES process_maps(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- 인덱스
    INDEX idx_organization (organization_id),
    INDEX idx_process_map (process_map_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_task_type (task_type),
    INDEX idx_due_date (due_date),
    INDEX idx_created (created_at)
);

-- ============================================================================
-- AI_USAGE_LOG TABLE - AI 사용 로그
-- ============================================================================
CREATE TABLE ai_usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_id INT NOT NULL,
    
    -- AI 요청 정보
    feature_type VARCHAR(50) NOT NULL,  -- 'task_analysis', 'process_optimization', etc.
    request_data JSON,
    response_data JSON,
    
    -- 토큰 사용량
    tokens_used INT DEFAULT 0,
    cost DECIMAL(10,4) DEFAULT 0.0000,
    
    -- 요청 결과
    status ENUM('success', 'error', 'timeout') DEFAULT 'success',
    error_message TEXT,
    
    -- 타임스탬프
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- 외래키
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    
    -- 인덱스
    INDEX idx_user (user_id),
    INDEX idx_organization (organization_id),
    INDEX idx_feature_type (feature_type),
    INDEX idx_created (created_at)
);

-- ============================================================================
-- 트리거 설정
-- ============================================================================

-- 조직 생성 시 자동으로 소유자를 멤버로 추가
DELIMITER //
CREATE TRIGGER tr_organization_add_owner 
AFTER INSERT ON organizations
FOR EACH ROW
BEGIN
    INSERT INTO organization_members (organization_id, user_id, role, status, joined_at)
    VALUES (NEW.id, NEW.owner_id, 'owner', 'active', NOW());
END//
DELIMITER ;

-- 사용자 생성 시 자동으로 무료 구독 생성
DELIMITER //
CREATE TRIGGER tr_user_create_subscription 
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_subscriptions (user_id, plan_type, plan_name, status)
    VALUES (NEW.id, 'free', 'Free Plan', 'active');
END//
DELIMITER ;

-- ============================================================================
-- 초기 데이터 삽입을 위한 뷰 생성
-- ============================================================================

-- 조직별 멤버 요약 뷰
CREATE VIEW v_organization_summary AS
SELECT 
    o.id,
    o.name,
    o.slug,
    o.owner_id,
    COUNT(om.id) as member_count,
    o.max_members,
    o.created_at
FROM organizations o
LEFT JOIN organization_members om ON o.id = om.organization_id AND om.status = 'active'
GROUP BY o.id;

-- 사용자별 구독 정보 뷰
CREATE VIEW v_user_subscription_info AS
SELECT 
    u.id as user_id,
    u.email,
    u.full_name,
    us.plan_type,
    us.status as subscription_status,
    us.max_process_maps,
    us.ai_tokens_limit,
    us.ai_tokens_used,
    us.expires_at
FROM users u
LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active';

-- 작업 통계 뷰
CREATE VIEW v_task_statistics AS
SELECT 
    organization_id,
    task_type,
    status,
    COUNT(*) as task_count,
    AVG(processing_time) as avg_processing_time,
    AVG(lead_time) as avg_lead_time,
    AVG(optimization_score) as avg_optimization_score
FROM tasks
GROUP BY organization_id, task_type, status;

-- ============================================================================
-- 인덱스 최적화
-- ============================================================================

-- 복합 인덱스 추가
CREATE INDEX idx_user_org_role ON organization_members(user_id, organization_id, role);
CREATE INDEX idx_org_process_status ON process_maps(organization_id, status, created_at);
CREATE INDEX idx_task_assigned_status ON tasks(assigned_to, status, due_date);
CREATE INDEX idx_ai_usage_date ON ai_usage_log(user_id, created_at, feature_type);

-- ============================================================================
-- 데이터베이스 설정 완료
-- ============================================================================

-- 문자셋 확인
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';

-- 테이블 목록 확인
SHOW TABLES;

SELECT 'BPR Hub Database Schema Created Successfully!' as message;