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
-- CONTRACTS
-- =========================
INSERT INTO contracts (client_id, contract_number, value, start_date, end_date, status, approved_by, approved_at) VALUES
((SELECT id FROM clients WHERE company_name = 'PT Weenie Hut Juniors'), 'CTR-WHJ-2026-01', 50000000.00, '2026-01-01', '2026-12-31', 'approved', (SELECT id FROM accounts WHERE email = 'manajemen@gmail.com'), NOW()),
((SELECT id FROM clients WHERE company_name = 'PT Squidward Music School'), 'CTR-SMS-2026-01', 25000000.00, '2026-06-01', '2026-11-30', 'approved', (SELECT id FROM accounts WHERE email = 'manajemen@gmail.com'), NOW());

-- =========================
-- CAMPAIGNS
-- =========================
INSERT INTO campaigns (contract_id, name, description, start_date, end_date) VALUES
((SELECT id FROM contracts WHERE contract_number = 'CTR-WHJ-2026-01'), 'Kemerdekaan & Akhir Tahun WHJ', 'Campaign promo Kemerdekaan dan Akhir Tahun 2026', '2026-08-01', '2026-12-31'),
((SELECT id FROM contracts WHERE contract_number = 'CTR-SMS-2026-01'), 'Music School Launch', 'Campaign launching kurikulum baru', '2026-06-01', '2026-11-30');

-- =========================
-- AD CONTENTS
-- =========================
INSERT INTO ad_contents (title, campaign_id, platform, content_identifier, published_at, created_at) VALUES
('Kemerdekaan Promo - Facebook Ads', (SELECT id FROM campaigns WHERE name = 'Kemerdekaan & Akhir Tahun WHJ'), 'facebook', 'fb-promo-kemerdekaan-01', '2026-08-01 10:00:00', NOW()),
('Akhir Tahun Sale - Instagram Reel', (SELECT id FROM campaigns WHERE name = 'Kemerdekaan & Akhir Tahun WHJ'), 'instagram', 'ig-akhir-tahun-02', '2026-12-01 12:00:00', NOW()),
('Launching Product X - YouTube Pre-roll', (SELECT id FROM campaigns WHERE name = 'Music School Launch'), 'youtube', 'yt-launch-product-03', '2026-07-15 08:30:00', NOW()),
('Banner Ads - Google Ad Manager', (SELECT id FROM campaigns WHERE name = 'Music School Launch'), 'gam', 'gam-banner-04', '2026-09-10 09:00:00', NOW()),
('Article Feature - GA4', NULL, 'ga4', 'ga4-article-05', '2026-10-05 14:00:00', NOW());

-- =========================
-- PRODUCTS
-- =========================
INSERT INTO products (category, name, price_model, price, is_active) VALUES
('content_marketing', 'Premium Content Article', 'fixed', 5000000.00, 1),
('banner_ads', 'Homepage Banner - Top', 'cpm', 25000.00, 1),
('social_media', 'Instagram Reel Endorsement', 'per_day', 1000000.00, 1),
('social_media', 'Facebook Post Ads', 'fixed', 2000000.00, 1);

-- =========================
-- CONTRACT ITEMS
-- =========================
INSERT INTO contract_items (contract_id, product_id, quantity, price, subtotal) VALUES
((SELECT id FROM contracts WHERE contract_number = 'CTR-WHJ-2026-01'), (SELECT id FROM products WHERE name = 'Instagram Reel Endorsement'), 5, 1000000.00, 5000000.00),
((SELECT id FROM contracts WHERE contract_number = 'CTR-WHJ-2026-01'), (SELECT id FROM products WHERE name = 'Facebook Post Ads'), 2, 2000000.00, 4000000.00),
((SELECT id FROM contracts WHERE contract_number = 'CTR-SMS-2026-01'), (SELECT id FROM products WHERE name = 'Homepage Banner - Top'), 1000, 25000.00, 25000000.00);

-- =========================
-- AD METRICS
-- =========================
-- Facebook metrics
INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value) VALUES
((SELECT id FROM ad_contents WHERE content_identifier = 'fb-promo-kemerdekaan-01'), 'post_impressions', 15000.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'fb-promo-kemerdekaan-01'), 'post_clicks', 320.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'fb-promo-kemerdekaan-01'), 'post_engaged_users', 450.0);

-- Instagram metrics
INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value) VALUES
((SELECT id FROM ad_contents WHERE content_identifier = 'ig-akhir-tahun-02'), 'likes', 1250.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'ig-akhir-tahun-02'), 'comments', 85.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'ig-akhir-tahun-02'), 'views', 22000.0);

-- YouTube metrics
INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value) VALUES
((SELECT id FROM ad_contents WHERE content_identifier = 'yt-launch-product-03'), 'viewCount', 50000.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'yt-launch-product-03'), 'likeCount', 4200.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'yt-launch-product-03'), 'averageViewDuration', 185.5);

-- GAM metrics
INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value) VALUES
((SELECT id FROM ad_contents WHERE content_identifier = 'gam-banner-04'), 'ad_server_impressions', 85000.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'gam-banner-04'), 'ad_server_clicks', 425.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'gam-banner-04'), 'ad_server_ctr', 0.005);

-- GA4 metrics
INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value) VALUES
((SELECT id FROM ad_contents WHERE content_identifier = 'ga4-article-05'), 'totalUsers', 12000.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'ga4-article-05'), 'sessions', 15400.0),
((SELECT id FROM ad_contents WHERE content_identifier = 'ga4-article-05'), 'averageSessionDuration', 124.2);
