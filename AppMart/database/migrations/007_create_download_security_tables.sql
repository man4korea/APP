-- 007_create_download_security_tables.sql
-- Create at 2508051330 Ver1.00

-- 보안 다운로드 토큰 테이블
CREATE TABLE IF NOT EXISTS download_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    token VARCHAR(64) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_used_at (used_at)
);

-- 다운로드 활동 로그 테이블
CREATE TABLE IF NOT EXISTS download_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    download_size BIGINT DEFAULT 0,
    download_duration INT DEFAULT 0,
    status ENUM('started', 'completed', 'failed', 'interrupted') DEFAULT 'started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_application_id (application_id),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
);

-- 파일 접근 제어 테이블
CREATE TABLE IF NOT EXISTS file_access_controls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_path VARCHAR(500) NOT NULL UNIQUE,
    access_level ENUM('public', 'authenticated', 'purchased', 'admin') DEFAULT 'authenticated',
    allowed_user_roles JSON,
    allowed_user_ids JSON,
    access_conditions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_file_path (file_path),
    INDEX idx_access_level (access_level),
    INDEX idx_is_active (is_active)
);

-- 파일 무결성 검증 로그 테이블
CREATE TABLE IF NOT EXISTS file_integrity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_path VARCHAR(500) NOT NULL,
    expected_hash VARCHAR(64) NOT NULL,
    actual_hash VARCHAR(64) NOT NULL,
    verification_status ENUM('passed', 'failed', 'error') NOT NULL,
    verification_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_file_path (file_path),
    INDEX idx_verification_status (verification_status),
    INDEX idx_created_at (created_at)
);

-- 업로드된 파일의 기본 접근 제어 설정
INSERT INTO file_access_controls 
(file_path, access_level, allowed_user_roles, access_conditions) VALUES
('/uploads/apps/', 'purchased', '["user", "developer", "admin"]', '{"requires_purchase": true, "app_status": "approved"}'),
('/uploads/screenshots/', 'public', '["user", "developer", "admin"]', '{}'),
('/uploads/thumbnails/', 'public', '["user", "developer", "admin"]', '{}')
ON DUPLICATE KEY UPDATE 
    access_level = VALUES(access_level),
    allowed_user_roles = VALUES(allowed_user_roles);

-- upload_rate_limits 테이블에 필요한 컬럼 추가
ALTER TABLE upload_rate_limits 
ADD COLUMN IF NOT EXISTS file_size BIGINT DEFAULT 0 AFTER upload_count,
ADD COLUMN IF NOT EXISTS file_name VARCHAR(255) DEFAULT '' AFTER file_size;