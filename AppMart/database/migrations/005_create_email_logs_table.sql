-- 005_create_email_logs_table.sql
-- Create at 2508051130 Ver1.00

CREATE TABLE IF NOT EXISTS email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient VARCHAR(255) NOT NULL,
    email_type ENUM('welcome', 'password_reset', 'app_status', 'purchase', 'notification') NOT NULL,
    status ENUM('sent', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_recipient (recipient),
    INDEX idx_email_type (email_type),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);

-- 사용자 이메일 인증을 위한 테이블
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- 비밀번호 재설정 토큰 테이블  
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- users 테이블에 이메일 인증 상태 컬럼 추가 (이미 존재하지 않는 경우)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER email,
ADD COLUMN IF NOT EXISTS email_verification_sent_at TIMESTAMP NULL AFTER email_verified_at;