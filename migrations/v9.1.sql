ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(80) NULL AFTER email;
ALTER TABLE reminders ADD COLUMN IF NOT EXISTS source_key VARCHAR(190) NULL AFTER entity_id;
ALTER TABLE reminders ADD UNIQUE KEY IF NOT EXISTS uq_reminder_source(company_id,source_key);
INSERT IGNORE INTO schema_versions(version) VALUES('9.1.0');
