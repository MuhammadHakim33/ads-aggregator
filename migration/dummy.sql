-- =========================
-- MASTER DATA
-- =========================
INSERT INTO roles (name) VALUES 
('superadmin'), 
('ae'), 
('manajemen'), 
('client');

INSERT INTO platforms (name) VALUES 
('facebook'), 
('instagram'), 
('gam'), 
('ga4'), 
('youtube'), 
('meta');

INSERT INTO keyword_types (name) VALUES 
('html'), 
('keyword'), 
('hostname');

-- =========================
-- ACCOUNTS
-- =========================
INSERT INTO accounts (name, email, password, role_id, is_active, created_at) VALUES
('admin', 'admin@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE role = 'superadmin'), 1, NOW()); -- Password: password123

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
((SELECT id FROM platforms WHERE name = 'facebook'), (SELECT id FROM keyword_types WHERE name = 'keyword'), 'Content partnership with', 1, NOW()),
((SELECT id FROM platforms WHERE name = 'facebook'), (SELECT id FROM keyword_types WHERE name = 'keyword'), '#kilas', 1, NOW()),
((SELECT id FROM platforms WHERE name = 'instagram'), (SELECT id FROM keyword_types WHERE name = 'keyword'), 'Content partnership with', 1, NOW()),
((SELECT id FROM platforms WHERE name = 'ga4'), (SELECT id FROM keyword_types WHERE name = 'html'), '<a href="https://grahajktskripsi.blogspot.com/search/label/Ekonomi" rel="tag">Ekonomi</a>', 1, NOW());
