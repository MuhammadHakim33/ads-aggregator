-- =========================
-- ACCOUNTS
-- =========================
CREATE TABLE accounts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','ae') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================
-- CLIENTS
-- =========================
CREATE TABLE clients (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    pic_name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -- =========================
-- -- AD CONTENTS
-- -- =========================
-- CREATE TABLE ad_contents (
--     id BIGINT AUTO_INCREMENT PRIMARY KEY,
--     platform VARCHAR(50) NOT NULL,
--     content_identifier VARCHAR(255) NOT NULL,
--     ad_type ENUM('article','banner','video','social') NOT NULL,
--     title VARCHAR(255),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

--     UNIQUE KEY unique_content (platform, content_identifier)
-- ) ENGINE=InnoDB;

-- -- =========================
-- -- AD METRICS
-- -- =========================
-- CREATE TABLE ad_metrics (
--     id BIGINT AUTO_INCREMENT PRIMARY KEY,
--     -- client_id BIGINT NOT NULL,
--     ad_content_id BIGINT NOT NULL,
--     date DATE NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

--     -- FOREIGN KEY (client_id) REFERENCES clients(id),
--     FOREIGN KEY (ad_content_id) REFERENCES ad_contents(id),

--     -- UNIQUE KEY unique_metric (client_id, ad_content_id, date),
--     -- INDEX idx_client_date (client_id, date)
-- ) ENGINE=InnoDB;

-- -- =========================
-- -- AD METRIC VALUES (FLEXIBLE)
-- -- =========================
-- CREATE TABLE ad_metric_values (
--     id BIGINT AUTO_INCREMENT PRIMARY KEY,
--     ad_metrics_id BIGINT NOT NULL,
--     metric_name VARCHAR(100) NOT NULL,
--     metric_value DOUBLE NOT NULL,

--     FOREIGN KEY (ad_metrics_id) REFERENCES ad_metrics(id),

--     INDEX idx_metric (metric_name),
--     INDEX idx_metrics_id (ad_metrics_id)
-- ) ENGINE=InnoDB;

-- =========================
-- PLATFORM CREDENTIALS
-- =========================
CREATE TABLE platform_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform_code ENUM('fb', 'ig', 'gam', 'ga4', 'yt') NOT NULL UNIQUE,
    credential_data LONGTEXT NOT NULL COMMENT 'JSON format credential',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_platform_code (platform_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;