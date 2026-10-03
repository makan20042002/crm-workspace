CREATE TABLE IF NOT EXISTS reminders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(50) NULL,
  entity_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  message TEXT NULL,
  remind_at DATETIME NOT NULL,
  recurrence VARCHAR(30) NOT NULL DEFAULT 'none',
  channels VARCHAR(120) NOT NULL DEFAULT 'in_app',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  last_sent_at DATETIME NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_reminders_due(company_id,status,remind_at),
  INDEX idx_reminders_user(company_id,user_id),
  CONSTRAINT fk_rem_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_rem_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_rem_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS communication_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  customer_id INT UNSIGNED NULL,
  contact_id INT UNSIGNED NULL,
  opportunity_id INT UNSIGNED NULL,
  channel VARCHAR(30) NOT NULL,
  direction VARCHAR(20) NOT NULL DEFAULT 'outbound',
  recipient VARCHAR(255) NULL,
  subject VARCHAR(255) NULL,
  body TEXT NULL,
  provider VARCHAR(80) NULL,
  provider_ref VARCHAR(190) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'logged',
  error_message TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comm_company(company_id,channel,created_at),
  INDEX idx_comm_customer(company_id,customer_id),
  CONSTRAINT fk_comm_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_comm_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_comm_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_comm_contact FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  CONSTRAINT fk_comm_opp FOREIGN KEY(opportunity_id) REFERENCES opportunities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  provider_type VARCHAR(40) NOT NULL,
  provider_name VARCHAR(80) NOT NULL,
  settings_json LONGTEXT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 0,
  updated_by INT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_integration(company_id,provider_type,provider_name),
  CONSTRAINT fk_int_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_int_user FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  module VARCHAR(50) NOT NULL,
  config_json LONGTEXT NOT NULL,
  is_shared TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_reports_company(company_id,module),
  CONSTRAINT fk_report_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_report_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS import_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  total_rows INT NOT NULL DEFAULT 0,
  success_rows INT NOT NULL DEFAULT 0,
  failed_rows INT NOT NULL DEFAULT 0,
  errors_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_import_company(company_id,created_at),
  CONSTRAINT fk_import_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_import_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(80) NULL;

ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS subtotal DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS terms TEXT NULL;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS customer_reference VARCHAR(120) NULL;

INSERT IGNORE INTO schema_versions(version) VALUES('9.0.0');
