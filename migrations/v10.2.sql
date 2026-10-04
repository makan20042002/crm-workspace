CREATE TABLE IF NOT EXISTS v10_ai_calls (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  feature VARCHAR(40) NOT NULL,
  entity_type VARCHAR(64) NULL,
  entity_id BIGINT UNSIGNED NULL,
  provider VARCHAR(30) NOT NULL,
  model VARCHAR(190) NOT NULL,
  tokens_in INT UNSIGNED NOT NULL DEFAULT 0,
  tokens_out INT UNSIGNED NOT NULL DEFAULT 0,
  duration_ms INT UNSIGNED NOT NULL DEFAULT 0,
  success TINYINT(1) NOT NULL DEFAULT 0,
  error_code VARCHAR(80) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_ai_company_day(company_id,created_at),
  KEY ix_ai_user_day(company_id,user_id,created_at),
  KEY ix_ai_record(company_id,entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_versions(version) VALUES('10.2.0');
