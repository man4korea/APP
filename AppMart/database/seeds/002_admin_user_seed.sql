-- AppMart Admin User Seed Data
-- File: 002_admin_user_seed.sql
-- Create at 2508041600 Ver1.00

-- Create default admin user
-- Password: admin123 (hashed with PHP password_hash)
INSERT IGNORE INTO users (
    id, 
    email, 
    password_hash, 
    username, 
    first_name, 
    last_name, 
    role, 
    status, 
    email_verified_at,
    bio,
    created_at
) VALUES (
    1,
    'admin@appmart.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'admin123'
    'admin',
    'System',
    'Administrator',
    'admin',
    'active',
    NOW(),
    'System administrator account for AppMart platform management.',
    NOW()
);

-- Create demo developer user
INSERT IGNORE INTO users (
    id,
    email,
    password_hash,
    username,
    first_name,
    last_name,
    role,
    status,
    email_verified_at,
    bio,
    website,
    github_username,
    created_at
) VALUES (
    2,
    'developer@appmart.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'admin123'
    'developer',
    'Demo',
    'Developer',
    'developer',
    'active',
    NOW(),
    'Demo developer account for testing app submissions and management.',
    'https://github.com/demo-developer',
    'demo-developer',
    NOW()
);

-- Create demo regular user
INSERT IGNORE INTO users (
    id,
    email,
    password_hash,
    username,
    first_name,
    last_name,
    role,
    status,
    email_verified_at,
    bio,
    created_at
) VALUES (
    3,
    'user@appmart.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'admin123'
    'testuser',
    'Test',
    'User',
    'user',
    'active',
    NOW(),
    'Demo user account for testing the platform from a customer perspective.',
    NOW()
);