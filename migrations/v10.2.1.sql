ALTER TABLE customers ADD COLUMN IF NOT EXISTS national_id VARCHAR(64) NULL AFTER name;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS economic_code VARCHAR(64) NULL AFTER national_id;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS bank_account VARCHAR(120) NULL AFTER economic_code;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS iban VARCHAR(64) NULL AFTER bank_account;
UPDATE customers SET type=CASE WHEN type='person' THEN 'individual' ELSE 'legal' END WHERE type IS NULL OR type='' OR type IN ('person','company');
ALTER TABLE customers MODIFY COLUMN type VARCHAR(40) NOT NULL DEFAULT 'legal';

ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS type VARCHAR(40) NOT NULL DEFAULT 'legal' AFTER company_id;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS national_id VARCHAR(64) NULL AFTER name;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS economic_code VARCHAR(64) NULL AFTER national_id;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS bank_account VARCHAR(120) NULL AFTER economic_code;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS iban VARCHAR(64) NULL AFTER bank_account;

INSERT IGNORE INTO schema_versions(version) VALUES('10.2.1');
