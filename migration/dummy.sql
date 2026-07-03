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

-- =========================
-- AD CONTENTS
-- =========================
INSERT INTO ad_contents (title, campaign_id, platform, content_identifier, published_at, created_at) VALUES
('Kemerdekaan Promo - Facebook Ads', NULL, 'facebook', 'fb-promo-kemerdekaan-01', '2026-08-01 10:00:00', NOW()),
('Akhir Tahun Sale - Instagram Reel', NULL, 'instagram', 'ig-akhir-tahun-02', '2026-12-01 12:00:00', NOW()),
('Launching Product X - YouTube Pre-roll', NULL, 'youtube', 'yt-launch-product-03', '2026-07-15 08:30:00', NOW()),
('Banner Ads - Google Ad Manager', NULL, 'gam', 'gam-banner-04', '2026-09-10 09:00:00', NOW()),
('Article Feature - GA4', NULL, 'ga4', 'ga4-article-05', '2026-10-05 14:00:00', NOW());
