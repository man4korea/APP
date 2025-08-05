-- AppMart Purchases Table Migration
-- File: 004_create_purchases_table.sql
-- Create at 2508041600 Ver1.00

CREATE TABLE IF NOT EXISTS purchases (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT(11) UNSIGNED NOT NULL,
    application_id INT(11) UNSIGNED NOT NULL,
    
    -- Transaction details
    transaction_id VARCHAR(100) UNIQUE DEFAULT NULL,
    payment_method ENUM('stripe', 'paypal', 'free', 'admin') NOT NULL DEFAULT 'free',
    
    -- Pricing at time of purchase
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    
    -- Status and processing
    status ENUM('pending', 'completed', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    
    -- Metadata
    payment_data JSON DEFAULT NULL, -- Store payment gateway response
    refund_reason TEXT DEFAULT NULL,
    
    -- Download tracking
    download_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
    last_download_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Timestamps
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    refunded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_app (user_id, application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_application_id (application_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_purchased_at (purchased_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;