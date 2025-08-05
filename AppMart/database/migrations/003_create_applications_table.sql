-- AppMart Applications Table Migration
-- File: 003_create_applications_table.sql
-- Create at 2508041600 Ver1.00

CREATE TABLE IF NOT EXISTS applications (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500) DEFAULT NULL,
    version VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    tech_stack JSON DEFAULT NULL,
    database_type VARCHAR(50) DEFAULT NULL,
    demo_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    documentation_url VARCHAR(255) DEFAULT NULL,
    
    -- File information
    file_path VARCHAR(255) NOT NULL,
    file_size INT(11) UNSIGNED DEFAULT NULL,
    file_hash VARCHAR(64) DEFAULT NULL,
    
    -- Media
    thumbnail VARCHAR(255) DEFAULT NULL,
    screenshots JSON DEFAULT NULL,
    
    -- Pricing and status
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'draft',
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Metadata
    download_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT NULL,
    rating_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
    
    -- Relationships
    owner_id INT(11) UNSIGNED NOT NULL,
    category_id INT(11) UNSIGNED DEFAULT NULL,
    
    -- Tags (stored as JSON for flexibility)
    tags JSON DEFAULT NULL,
    
    -- Timestamps
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_slug (slug),
    INDEX idx_owner_id (owner_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    INDEX idx_rating (rating_average),
    INDEX idx_download_count (download_count),
    INDEX idx_published_at (published_at),
    
    CONSTRAINT fk_applications_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;