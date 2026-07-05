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
INSERT INTO accounts (name, email, password, role_id, is_active) VALUES
('Superadmin', 'admin@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE name = 'superadmin'), 1),
('Account Executive', 'ae@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE name = 'ae'), 1),
('Manajemen', 'manajemen@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE name = 'manajemen'), 1),
('PIC Firdaus', 'pic@gmail.com', '$2y$12$WLrG.3kIy2nQH8iMwexlyehoM7uiDI3zaoYMKx6hAI5xtRue30u32', (SELECT id FROM roles WHERE name = 'client'), 1); -- Password untuk semua akun: password123

-- =========================
-- CLIENTS
-- =========================
INSERT INTO clients (company_name, ae_id, is_active) VALUES
('PT Weenie Hut Juniors', (SELECT id FROM accounts WHERE email = 'ae@gmail.com'), 1),
('PT Squidward Music School', NULL, 1),
('PT Salty Spitoon', NULL, 1);

-- =========================
-- CLIENT PICS
-- =========================
INSERT INTO client_pics (client_id, name, position, account_id, is_active) VALUES
((SELECT id FROM clients WHERE company_name = 'PT Weenie Hut Juniors'), 'Firdaus', 'Marketing Manager', (SELECT id FROM accounts WHERE email = 'pic@gmail.com'), 1),
((SELECT id FROM clients WHERE company_name = 'PT Squidward Music School'), 'Hendra Wira', 'Director', NULL, 1),
((SELECT id FROM clients WHERE company_name = 'PT Salty Spitoon'), 'Budi Irawan', 'CEO', NULL, 1);

-- =========================
-- AD CONTENTS
-- =========================
INSERT INTO ad_contents (title, campaign_id, platform, content_identifier, published_at, created_at) VALUES
('Kemerdekaan Promo - Facebook Ads', NULL, 'facebook', 'fb-promo-kemerdekaan-01', '2026-08-01 10:00:00', NOW()),
('Akhir Tahun Sale - Instagram Reel', NULL, 'instagram', 'ig-akhir-tahun-02', '2026-12-01 12:00:00', NOW()),
('Launching Product X - YouTube Pre-roll', NULL, 'youtube', 'yt-launch-product-03', '2026-07-15 08:30:00', NOW()),
('Banner Ads - Google Ad Manager', NULL, 'gam', 'gam-banner-04', '2026-09-10 09:00:00', NOW()),
('Article Feature - GA4', NULL, 'ga4', 'ga4-article-05', '2026-10-05 14:00:00', NOW());
