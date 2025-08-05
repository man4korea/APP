-- 006_create_file_security_tables.sql
-- Create at 2508051300 Ver1.00

-- 파일 업로드 로그 테이블
CREATE TABLE IF NOT EXISTS file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(100),
    mime_type VARCHAR(100),
    status ENUM('success', 'failed', 'quarantined') NOT NULL DEFAULT 'success',
    upload_path VARCHAR(500),
    security_scan_result TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- 보안 사고 로그 테이블
CREATE TABLE IF NOT EXISTS security_incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_type ENUM('malicious_file', 'suspicious_upload', 'virus_detected', 'invalid_signature') NOT NULL,
    filename VARCHAR(255),
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    reason TEXT NOT NULL,
    quarantine_path VARCHAR(500),
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    INDEX idx_incident_type (incident_type),
    INDEX idx_severity (severity),
    INDEX idx_resolved (resolved),
    INDEX idx_created_at (created_at)
);

-- 허용된 파일 타입 설정 테이블
CREATE TABLE IF NOT EXISTS allowed_file_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    extension VARCHAR(20) NOT NULL UNIQUE,
    mime_types JSON NOT NULL,
    max_size_mb INT NOT NULL DEFAULT 50,
    description TEXT,
    security_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_extension (extension),
    INDEX idx_enabled (enabled)
);

-- 기본 허용 파일 타입 데이터 삽입
INSERT INTO allowed_file_types (extension, mime_types, max_size_mb, description, security_level) VALUES
('zip', '["application/zip", "application/x-zip-compressed"]', 50, 'ZIP archive files', 'medium'),
('rar', '["application/vnd.rar", "application/x-rar-compressed"]', 50, 'RAR archive files', 'medium'),
('tar.gz', '["application/gzip", "application/x-gzip", "application/x-tar"]', 100, 'Compressed tar archives', 'medium'),
('7z', '["application/x-7z-compressed"]', 75, '7-Zip archive files', 'medium')
ON DUPLICATE KEY UPDATE 
    mime_types = VALUES(mime_types),
    max_size_mb = VALUES(max_size_mb),
    description = VALUES(description);

-- 시스템 설정 테이블 (보안 설정용)
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_category (category)
);

-- 기본 보안 설정 삽입
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('max_upload_size', '52428800', 'integer', 'Maximum file upload size in bytes (50MB)', 'security'),
('virus_scan_enabled', 'false', 'boolean', 'Enable virus scanning for uploaded files', 'security'),
('quarantine_enabled', 'true', 'boolean', 'Enable quarantine for suspicious files', 'security'),
('upload_rate_limit', '10', 'integer', 'Maximum uploads per user per hour', 'security'),
('allowed_upload_times', '{"start": "00:00", "end": "23:59"}', 'json', 'Allowed upload time window', 'security')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- 업로드 제한 추적 테이블
CREATE TABLE IF NOT EXISTS upload_rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    upload_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_window (user_id, window_start),
    INDEX idx_ip_address (ip_address),
    INDEX idx_blocked_until (blocked_until)
);