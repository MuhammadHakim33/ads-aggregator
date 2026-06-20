-- =========================
-- MASTER TABEL
-- =========================
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL COMMENT 'superadmin, ae, manajemen, client'
);




-- =========================
-- ACCOUNTS
-- =========================
CREATE TABLE accounts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- =========================
-- CLIENTS
-- =========================
CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(255) NOT NULL,
  pic_name VARCHAR(255),
  ae_id INT NULL,
  account_id INT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (ae_id) REFERENCES accounts(id),
  FOREIGN KEY (account_id) REFERENCES accounts(id)
);

-- =========================
-- CONTRACTS
-- =========================
CREATE TABLE contracts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  client_id INT NOT NULL,
  contract_number VARCHAR(100) UNIQUE NOT NULL,
  value DECIMAL(15,2) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  terminated_at TIMESTAMP NULL,
  termination_reason TEXT NULL,
  document_path VARCHAR(500) NULL,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- =========================
-- CAMPAIGNS
-- =========================
CREATE TABLE campaigns (
  id INT PRIMARY KEY AUTO_INCREMENT,
  contract_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  -- status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
  is_active BOOLEAN DEFAULT TRUE,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (contract_id) REFERENCES contracts(id)
);

-- =========================
-- FILTER KEYWORDS
-- =========================
CREATE TABLE filter_keywords (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform ENUM('facebook', 'instagram', 'gam', 'ga4', 'youtube') NOT NULL,
  type ENUM('html', 'keyword', 'hostname') NOT NULL,
  keyword VARCHAR(255) NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- AD CONTENTS
-- =========================
CREATE TABLE ad_contents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255),
  campaign_id INT NULL,
  platform ENUM('facebook', 'instagram', 'gam', 'ga4', 'youtube') NOT NULL,
  content_identifier VARCHAR(255) UNIQUE NOT NULL,
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
);

-- =========================
-- AD METRICS
-- =========================
CREATE TABLE ad_metrics (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad_content_id INT NOT NULL,
  metric_name VARCHAR(100) NOT NULL,
  metric_value DOUBLE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY unique_metric (ad_content_id, metric_name),
  FOREIGN KEY (ad_content_id) REFERENCES ad_contents(id)
);

-- =========================
-- COMPLAINTS
-- =========================
CREATE TABLE complaints (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ad_content_id INT NOT NULL,
  subject VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('waiting', 'resolved', 'closed', 'in_progress') NOT NULL DEFAULT 'waiting',
  resolution_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (ad_content_id) REFERENCES ad_contents(id)
);

-- =========================
-- PLATFORM CREDENTIALS
-- =========================
CREATE TABLE platform_credentials (
  id INT PRIMARY KEY AUTO_INCREMENT,
  platform ENUM('meta', 'gam', 'ga4', 'youtube') UNIQUE NOT NULL,
  credential_data LONGTEXT NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CRON LOGS
-- =========================
CREATE TABLE cron_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  job_name VARCHAR(100) NOT NULL COMMENT 'fetch, sync',
  platform VARCHAR(50) NOT NULL COMMENT 'facebook, instagram, gam, ga4, youtube',
  status ENUM('success', 'failed', 'partial') NOT NULL,
  rows_affected INT DEFAULT 0 COMMENT 'jumlah contents saved atau metrics upserted',
  duration_ms INT DEFAULT 0 COMMENT 'durasi eksekusi dalam milidetik',
  error_message TEXT NULL COMMENT 'pesan error jika status failed',
  started_at TIMESTAMP NOT NULL,
  finished_at TIMESTAMP NOT NULL,
);
