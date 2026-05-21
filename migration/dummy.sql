INSERT INTO filter_keywords (platform, type, keyword, is_active, created_at) VALUES
('facebook', 'keyword', 'Content partnership with', 1, NOW()),
('facebook', 'keyword', '#kilas', 1, NOW()),
('instagram', 'keyword', 'Content partnership with', 1, NOW()),
('ga4', 'html', '<a href="https://grahajktskripsi.blogspot.com/search/label/Ekonomi" rel="tag">Ekonomi</a>', 1, NOW());

INSERT INTO accounts (name, email, password, role, is_active, created_at) VALUES
('admin', 'admin@gmail.com', 'admin', 'superadmin', 1, NOW());

INSERT INTO clients (company_name, pic_name, ae_id, is_active, created_at) VALUES
('PT Weenie Hut Juniors', 'Firdaus', NULL, 1, NOW()),
('PT Squidward Music School', 'Hendra Wira', NULL, 1, NOW()),
('PT Salty Spitoon', 'Budi Irawan', NULL, 1, NOW());
