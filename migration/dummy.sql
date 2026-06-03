-- =========================
-- MASTER DATA
-- =========================
INSERT INTO roles (role_name) VALUES 
('superadmin'), 
('ae'), 
('manajemen'), 
('client');

INSERT INTO platforms (platform_name) VALUES 
('facebook'), 
('instagram'), 
('gam'), 
('ga4'), 
('youtube'), 
('meta');

INSERT INTO keyword_types (type_name) VALUES 
('html'), 
('keyword'), 
('hostname');

-- =========================
-- ACCOUNTS
-- =========================
INSERT INTO accounts (name, email, password, role_id, is_active, created_at) VALUES
('admin', 'admin@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE role_name = 'superadmin'), 1, NOW()); -- Password: password123

-- =========================
-- CLIENTS
-- =========================
INSERT INTO clients (company_name, pic_name, ae_id, account_id, is_active, created_at) VALUES
('PT Weenie Hut Juniors', 'Firdaus', NULL, NULL, 1, NOW()),
('PT Squidward Music School', 'Hendra Wira', NULL, NULL, 1, NOW()),
('PT Salty Spitoon', 'Budi Irawan', NULL, NULL, 1, NOW());

-- =========================
-- FILTER KEYWORDS
-- =========================
INSERT INTO filter_keywords (platform_id, type_id, keyword, is_active, created_at) VALUES
((SELECT id FROM platforms WHERE platform_name = 'facebook'), (SELECT id FROM keyword_types WHERE type_name = 'keyword'), 'Content partnership with', 1, NOW()),
((SELECT id FROM platforms WHERE platform_name = 'facebook'), (SELECT id FROM keyword_types WHERE type_name = 'keyword'), '#kilas', 1, NOW()),
((SELECT id FROM platforms WHERE platform_name = 'instagram'), (SELECT id FROM keyword_types WHERE type_name = 'keyword'), 'Content partnership with', 1, NOW()),
((SELECT id FROM platforms WHERE platform_name = 'ga4'), (SELECT id FROM keyword_types WHERE type_name = 'html'), '<a href="https://grahajktskripsi.blogspot.com/search/label/Ekonomi" rel="tag">Ekonomi</a>', 1, NOW());
