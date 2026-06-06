-- =========================
-- MASTER DATA
-- =========================
INSERT INTO roles (name) VALUES 
('superadmin'), 
('ae'), 
('manajemen'), 
('client');

-- =========================
-- ACCOUNTS
-- =========================
INSERT INTO accounts (name, email, password, role_id, is_active, created_at) VALUES
('admin', 'admin@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE name = 'superadmin'), 1, NOW()); -- Password: password123

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
INSERT INTO filter_keywords (platform, type, keyword, is_active, created_at) VALUES
('facebook', 'keyword', 'Content partnership with', 1, NOW()),
('ga4', 'html', '<a href="https://grahajktskripsi.blogspot.com/search/label/Ekonomi" rel="tag">Ekonomi</a>', 1, NOW());
