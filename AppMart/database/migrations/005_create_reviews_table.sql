-- AppMart Reviews Table Migration
-- File: 005_create_reviews_table.sql
-- Create at 2508041600 Ver1.00

CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    
    -- Review content
    rating TINYINT(1) UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    
    -- Moderation
    status ENUM('pending', 'approved', 'rejected', 'hidden') NOT NULL DEFAULT 'pending',
    moderator_id INT(11) UNSIGNED DEFAULT NULL,
    moderation_reason TEXT DEFAULT NULL,
    
    -- Interaction tracking
    helpful_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
    not_helpful_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
    
    -- Developer response
    developer_response TEXT DEFAULT NULL,
    developer_response_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    moderated_at TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_app_review (user_id, application_id),
    INDEX idx_application_id (application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;