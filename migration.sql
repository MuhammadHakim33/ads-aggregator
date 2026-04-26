-- =========================
-- ACCOUNTS
-- =========================
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    pic_name VARCHAR(255),
    ae_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ae_id) REFERENCES accounts(id),
    INDEX idx_company_name (company_name)
) ENGINE=InnoDB;

-- =========================
-- FILTER KEYWORDS
-- =========================
CREATE TABLE filter_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('meta', 'gam', 'ga4', 'yt') NOT NULL,
    keyword VARCHAR(255) NOT NULL COMMENT 'Keyword umum: Content partnership with, #kilas, dll',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_platform (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- CLIENT IDENTIFIERS
-- =========================
CREATE TABLE client_identifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    platform ENUM('meta', 'gam', 'ga4', 'yt') NOT NULL,
    identifier VARCHAR(255) NOT NULL COMMENT '@account_klien, /klien-path/, [KLIEN]%, dll',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client_platform (client_id, platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- AD CONTENTS
-- =========================
CREATE TABLE ad_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    content_identifier VARCHAR(255) NOT NULL,
    ad_type ENUM('article','banner','video','social') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_content (client_id, platform, content_identifier),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client_platform (client_id, platform)
) ENGINE=InnoDB;

-- =========================
-- AD METRICS
-- =========================
CREATE TABLE ad_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_content_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DOUBLE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (ad_content_id) REFERENCES ad_contents(id),

    INDEX idx_metric (metric_name),
    INDEX idx_ad_content_id (ad_content_id),
    UNIQUE KEY unique_metric (ad_content_id, metric_name)
) ENGINE=InnoDB;

-- -- =========================
-- -- PLATFORM CREDENTIALS
-- -- =========================
-- CREATE TABLE platform_credentials (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     platform_code ENUM('fb', 'ig', 'gam', 'ga4', 'yt') NOT NULL UNIQUE,
--     credential_data LONGTEXT NOT NULL COMMENT 'JSON format credential',
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
--     INDEX idx_platform_code (platform_code)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;