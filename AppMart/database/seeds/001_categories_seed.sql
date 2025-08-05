-- AppMart Categories Seed Data
-- File: 001_categories_seed.sql
-- Create at 2508041600 Ver1.00

INSERT IGNORE INTO categories (id, name, slug, description, icon, color, sort_order) VALUES
(1, 'Web Development', 'web-development', 'Full-stack web applications and frameworks', '🌐', '#3B82F6', 1),
(2, 'E-commerce', 'ecommerce', 'Online stores and shopping platforms', '🛒', '#10B981', 2),
(3, 'Business Tools', 'business-tools', 'CRM, project management, and productivity apps', '💼', '#6366F1', 3),
(4, 'Content Management', 'content-management', 'CMS, blogs, and content platforms', '📝', '#8B5CF6', 4),
(5, 'Analytics & Reporting', 'analytics-reporting', 'Data visualization and business intelligence', '📊', '#06B6D4', 5),
(6, 'Social & Community', 'social-community', 'Social networks and community platforms', '👥', '#EC4899', 6),
(7, 'Education & Learning', 'education-learning', 'LMS and educational platforms', '🎓', '#F59E0B', 7),
(8, 'Health & Fitness', 'health-fitness', 'Healthcare and wellness applications', '🏥', '#EF4444', 8),
(9, 'Finance & Accounting', 'finance-accounting', 'Financial management and accounting tools', '💰', '#059669', 9),
(10, 'Real Estate', 'real-estate', 'Property management and real estate platforms', '🏠', '#7C3AED', 10),
(11, 'Food & Restaurant', 'food-restaurant', 'Restaurant management and food delivery', '🍕', '#F97316', 11),
(12, 'Travel & Tourism', 'travel-tourism', 'Booking systems and travel platforms', '✈️', '#0EA5E9', 12);

-- Sub-categories for Web Development
INSERT IGNORE INTO categories (name, slug, description, icon, color, parent_id, sort_order) VALUES
('Frontend Frameworks', 'frontend-frameworks', 'React, Vue, Angular applications', '⚛️', '#61DAFB', 1, 1),
('Backend APIs', 'backend-apis', 'REST and GraphQL API services', '🔗', '#68D391', 1, 2),
('Full-Stack Applications', 'fullstack-applications', 'Complete web applications', '🔧', '#4299E1', 1, 3),
('WordPress Themes', 'wordpress-themes', 'Custom WordPress themes and plugins', '📦', '#21759B', 1, 4);

-- Sub-categories for E-commerce
INSERT IGNORE INTO categories (name, slug, description, icon, color, parent_id, sort_order) VALUES
('Shopping Carts', 'shopping-carts', 'Complete e-commerce solutions', '🛍️', '#10B981', 2, 1),
('Payment Systems', 'payment-systems', 'Payment gateways and processors', '💳', '#059669', 2, 2),
('Inventory Management', 'inventory-management', 'Stock and inventory tracking', '📦', '#0D9488', 2, 3);

-- Sub-categories for Business Tools
INSERT IGNORE INTO categories (name, slug, description, icon, color, parent_id, sort_order) VALUES
('CRM Systems', 'crm-systems', 'Customer relationship management', '👤', '#6366F1', 3, 1),
('Project Management', 'project-management', 'Task and project tracking tools', '📋', '#8B5CF6', 3, 2),
('HR Management', 'hr-management', 'Human resources and payroll', '👥', '#A855F7', 3, 3);