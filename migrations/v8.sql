CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_key CHAR(64) NOT NULL,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(64) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  last_attempt_at DATETIME NOT NULL,
  locked_until DATETIME NULL,
  UNIQUE KEY uq_login_attempt_key(attempt_key),
  INDEX idx_login_cleanup(last_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  scopes JSON NULL,
  last_used_at DATETIME NULL,
  expires_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_api_token_hash(token_hash),
  INDEX idx_api_token_user(company_id,user_id),
  CONSTRAINT fk_api_token_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_api_token_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS security_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  event_type VARCHAR(80) NOT NULL,
  severity VARCHAR(20) NOT NULL DEFAULT 'info',
  email VARCHAR(190) NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  metadata JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_security_event(company_id,created_at),
  INDEX idx_security_type(event_type,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schema_versions (
  version VARCHAR(32) PRIMARY KEY,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_versions(version) VALUES('8.0.0');
