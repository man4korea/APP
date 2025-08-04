-- AppMart Database Schema
-- Created for XAMPP Development Environment

CREATE DATABASE IF NOT EXISTS appmart_db;
USE appmart_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id int(11) NOT NULL AUTO_INCREMENT,
    email varchar(255) NOT NULL,
    password_hash text NOT NULL,
    nickname varchar(100) NOT NULL,
    role enum('user','developer','admin') NOT NULL DEFAULT 'user',
    created_at datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Apps table
CREATE TABLE IF NOT EXISTS apps (
    id int(11) NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description text NOT NULL,
    tech_stack text DEFAULT NULL,
    db_type varchar(100) DEFAULT NULL,
    file_url text NOT NULL,
    thumbnail text DEFAULT NULL,
    price int(11) NOT NULL DEFAULT 0,
    status enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    owner_id int(11) NOT NULL,
    created_at datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY owner_id (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchases table
CREATE TABLE IF NOT EXISTS purchases (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    app_id int(11) NOT NULL,
    purchase_date datetime NOT NULL DEFAULT current_timestamp(),
    amount int(11) NOT NULL,
    status enum('completed','pending','cancelled') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY app_id (app_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requests table
CREATE TABLE IF NOT EXISTS requests (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    title varchar(255) NOT NULL,
    description text NOT NULL,
    tech_requirements text DEFAULT NULL,
    budget int(11) DEFAULT NULL,
    deadline date DEFAULT NULL,
    status enum('open','in_progress','completed','cancelled') NOT NULL DEFAULT 'open',
    created_at datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key constraints
ALTER TABLE apps
    ADD CONSTRAINT apps_ibfk_1 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE;

ALTER TABLE purchases
    ADD CONSTRAINT purchases_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    ADD CONSTRAINT purchases_ibfk_2 FOREIGN KEY (app_id) REFERENCES apps (id) ON DELETE CASCADE;

ALTER TABLE requests
    ADD CONSTRAINT requests_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

-- Insert sample data for testing (optional)
INSERT INTO users (email, password_hash, nickname, role) VALUES 
('admin@appmart.com', '$2y$10$example_hash_here', 'Admin', 'admin'),
('dev@appmart.com', '$2y$10$example_hash_here', 'Developer', 'developer'),
('user@appmart.com', '$2y$10$example_hash_here', 'TestUser', 'user');