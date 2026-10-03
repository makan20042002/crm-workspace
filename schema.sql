CREATE TABLE IF NOT EXISTS companies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  logo_url VARCHAR(255) NULL,
  primary_color VARCHAR(20) DEFAULT '#184C3B',
  accent_color VARCHAR(20) DEFAULT '#C6A15B',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NULL,
  extension VARCHAR(32) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'employee',
  title VARCHAR(160) NULL,
  lang VARCHAR(5) DEFAULT 'fa',
  active TINYINT(1) DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_email_company(company_id,email),
  CONSTRAINT fk_users_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  type VARCHAR(40) DEFAULT 'company',
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  city VARCHAR(100) NULL,
  address TEXT NULL,
  status VARCHAR(40) DEFAULT 'active',
  owner_id INT UNSIGNED NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id),
  CONSTRAINT fk_customer_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_customer_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  customer_name VARCHAR(180) NULL,
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  source VARCHAR(80) NULL,
  vertical VARCHAR(80) NULL,
  stage VARCHAR(60) DEFAULT 'new',
  value DECIMAL(18,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'IRR',
  owner_id INT UNSIGNED NULL,
  next_followup_at DATETIME NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,stage),
  CONSTRAINT fk_lead_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_lead_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opportunities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  stage VARCHAR(60) DEFAULT 'qualification',
  probability INT DEFAULT 10,
  amount DECIMAL(18,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'IRR',
  owner_id INT UNSIGNED NULL,
  expected_close_date DATE NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,stage),
  CONSTRAINT fk_opp_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_opp_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_opp_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  name VARCHAR(220) NOT NULL,
  code VARCHAR(80) NULL,
  vertical VARCHAR(100) NULL,
  category VARCHAR(100) NULL,
  manager_id INT UNSIGNED NULL,
  status VARCHAR(60) DEFAULT 'planning',
  progress INT DEFAULT 0,
  start_date DATE NULL,
  due_date DATE NULL,
  budget DECIMAL(18,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'IRR',
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,status),
  CONSTRAINT fk_project_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_project_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_project_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NULL,
  assignee_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NULL,
  status VARCHAR(60) DEFAULT 'todo',
  priority VARCHAR(30) DEFAULT 'medium',
  due_date DATETIME NULL,
  completed_at DATETIME NULL,
  estimated_hours DECIMAL(8,2) DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,status),
  CONSTRAINT fk_task_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_assignee FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_task_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS time_entries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  task_id INT UNSIGNED NULL,
  project_id INT UNSIGNED NULL,
  minutes INT UNSIGNED NOT NULL DEFAULT 0,
  note VARCHAR(255) NULL,
  work_date DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_time_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_time_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_time_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE SET NULL,
  CONSTRAINT fk_time_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS procurement_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  supplier VARCHAR(180) NULL,
  amount DECIMAL(18,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'IRR',
  status VARCHAR(50) DEFAULT 'requested',
  requested_by INT UNSIGNED NULL,
  approved_by INT UNSIGNED NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_proc_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_proc_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_proc_req FOREIGN KEY(requested_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_proc_app FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  metadata JSON NULL,
  ip_address VARCHAR(64) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,created_at),
  CONSTRAINT fk_log_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_log_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  body TEXT NULL,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_notif_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  job_title VARCHAR(160) NULL,
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,customer_id),
  CONSTRAINT fk_contact_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_contact_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS followups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  lead_id INT UNSIGNED NULL,
  opportunity_id INT UNSIGNED NULL,
  assigned_to INT UNSIGNED NULL,
  subject VARCHAR(220) NOT NULL,
  type VARCHAR(40) DEFAULT 'call',
  status VARCHAR(40) DEFAULT 'open',
  due_at DATETIME NULL,
  notes TEXT NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,status,due_at),
  CONSTRAINT fk_followup_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_lead FOREIGN KEY(lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_opp FOREIGN KEY(opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_user FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  opportunity_id INT UNSIGNED NULL,
  quote_no VARCHAR(80) NOT NULL,
  title VARCHAR(220) NOT NULL,
  amount DECIMAL(18,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'IRR',
  status VARCHAR(40) DEFAULT 'draft',
  valid_until DATE NULL,
  notes TEXT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_quote_company_no(company_id,quote_no),
  INDEX(company_id,status),
  CONSTRAINT fk_quote_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_quote_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_opp FOREIGN KEY(opportunity_id) REFERENCES opportunities(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  category VARCHAR(120) NULL,
  contact_name VARCHAR(160) NULL,
  phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  status VARCHAR(40) DEFAULT 'active',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,status),
  CONSTRAINT fk_supplier_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS milestones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  status VARCHAR(40) DEFAULT 'pending',
  due_date DATE NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,project_id),
  CONSTRAINT fk_milestone_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_milestone_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  task_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,task_id),
  CONSTRAINT fk_comment_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_comment_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  CONSTRAINT fk_comment_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_setting(company_id,setting_key),
  CONSTRAINT fk_setting_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== V6 extensibility layer =====
CREATE TABLE IF NOT EXISTS pipelines (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  entity_type VARCHAR(50) NOT NULL DEFAULT 'opportunity',
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,entity_type),
  CONSTRAINT fk_pipeline_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pipeline_stages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  pipeline_id INT UNSIGNED NOT NULL,
  code VARCHAR(60) NOT NULL,
  name_fa VARCHAR(120) NOT NULL,
  name_en VARCHAR(120) NOT NULL,
  position INT NOT NULL DEFAULT 0,
  probability INT NOT NULL DEFAULT 0,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  is_won TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pipeline_stage(pipeline_id,code),
  INDEX(company_id,pipeline_id,position),
  CONSTRAINT fk_stage_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_stage_pipeline FOREIGN KEY(pipeline_id) REFERENCES pipelines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_packages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  discipline VARCHAR(100) NULL,
  owner_id INT UNSIGNED NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'planning',
  progress INT NOT NULL DEFAULT 0,
  start_date DATE NULL,
  due_date DATE NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,project_id,status),
  CONSTRAINT fk_wp_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_wp_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_wp_owner FOREIGN KEY(owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS custom_fields (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(60) NOT NULL,
  field_key VARCHAR(80) NOT NULL,
  label_fa VARCHAR(160) NOT NULL,
  label_en VARCHAR(160) NOT NULL,
  field_type VARCHAR(30) NOT NULL DEFAULT 'text',
  options_json JSON NULL,
  required TINYINT(1) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  position INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_custom_field(company_id,entity_type,field_key),
  INDEX(company_id,entity_type,active),
  CONSTRAINT fk_cf_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS custom_field_values (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  custom_field_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  value_text TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_custom_value(custom_field_id,entity_type,entity_id),
  INDEX(company_id,entity_type,entity_id),
  CONSTRAINT fk_cfv_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_cfv_field FOREIGN KEY(custom_field_id) REFERENCES custom_fields(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NULL,
  customer_id INT UNSIGNED NULL,
  task_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  category VARCHAR(80) NULL,
  file_url VARCHAR(500) NULL,
  version_label VARCHAR(40) NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,project_id,customer_id),
  CONSTRAINT fk_doc_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE SET NULL,
  CONSTRAINT fk_doc_user FOREIGN KEY(uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_views (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  entity_type VARCHAR(60) NOT NULL,
  name VARCHAR(160) NOT NULL,
  filters_json JSON NULL,
  is_shared TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,entity_type),
  CONSTRAINT fk_view_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_view_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== V7 Trade & Operations Suite =====
CREATE TABLE IF NOT EXISTS rfqs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  opportunity_id INT UNSIGNED NULL,
  project_id INT UNSIGNED NULL,
  rfq_no VARCHAR(80) NOT NULL,
  title VARCHAR(220) NOT NULL,
  vertical VARCHAR(80) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  due_date DATE NULL,
  incoterm VARCHAR(20) NULL,
  destination VARCHAR(180) NULL,
  notes TEXT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_rfq_company_no(company_id,rfq_no),
  INDEX(company_id,status,vertical),
  CONSTRAINT fk_rfq_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_rfq_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_rfq_opp FOREIGN KEY(opportunity_id) REFERENCES opportunities(id) ON DELETE SET NULL,
  CONSTRAINT fk_rfq_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_rfq_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rfq_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  rfq_id BIGINT UNSIGNED NOT NULL,
  item_code VARCHAR(80) NULL,
  description VARCHAR(500) NOT NULL,
  qty DECIMAL(18,3) NOT NULL DEFAULT 1,
  unit VARCHAR(40) NULL,
  technical_spec JSON NULL,
  target_price DECIMAL(18,4) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,rfq_id),
  CONSTRAINT fk_rfqi_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_rfqi_rfq FOREIGN KEY(rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rfq_suppliers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  rfq_id BIGINT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'sent',
  sent_at DATETIME NULL,
  responded_at DATETIME NULL,
  notes TEXT NULL,
  UNIQUE KEY uq_rfq_supplier(rfq_id,supplier_id),
  INDEX(company_id,status),
  CONSTRAINT fk_rfqs_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_rfqs_rfq FOREIGN KEY(rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
  CONSTRAINT fk_rfqs_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_quotes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  rfq_id BIGINT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  quote_ref VARCHAR(100) NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  exchange_rate DECIMAL(20,8) NOT NULL DEFAULT 1,
  subtotal DECIMAL(18,4) NOT NULL DEFAULT 0,
  freight DECIMAL(18,4) NOT NULL DEFAULT 0,
  other_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  total DECIMAL(18,4) NOT NULL DEFAULT 0,
  lead_time_days INT NULL,
  validity_date DATE NULL,
  payment_terms VARCHAR(255) NULL,
  warranty VARCHAR(160) NULL,
  technical_match DECIMAL(5,2) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'received',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,rfq_id,supplier_id),
  CONSTRAINT fk_sq_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_sq_rfq FOREIGN KEY(rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
  CONSTRAINT fk_sq_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_quote_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  supplier_quote_id BIGINT UNSIGNED NOT NULL,
  rfq_item_id BIGINT UNSIGNED NULL,
  description VARCHAR(500) NOT NULL,
  qty DECIMAL(18,3) NOT NULL DEFAULT 1,
  unit_price DECIMAL(18,4) NOT NULL DEFAULT 0,
  line_total DECIMAL(18,4) NOT NULL DEFAULT 0,
  compliance_status VARCHAR(30) NOT NULL DEFAULT 'compliant',
  notes TEXT NULL,
  INDEX(company_id,supplier_quote_id),
  CONSTRAINT fk_sqi_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_sqi_quote FOREIGN KEY(supplier_quote_id) REFERENCES supplier_quotes(id) ON DELETE CASCADE,
  CONSTRAINT fk_sqi_item FOREIGN KEY(rfq_item_id) REFERENCES rfq_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  quotation_id INT UNSIGNED NOT NULL,
  description VARCHAR(500) NOT NULL,
  qty DECIMAL(18,3) NOT NULL DEFAULT 1,
  unit VARCHAR(40) NULL,
  unit_price DECIMAL(18,4) NOT NULL DEFAULT 0,
  discount_percent DECIMAL(7,3) NOT NULL DEFAULT 0,
  tax_percent DECIMAL(7,3) NOT NULL DEFAULT 0,
  line_total DECIMAL(18,4) NOT NULL DEFAULT 0,
  position INT NOT NULL DEFAULT 0,
  INDEX(company_id,quotation_id),
  CONSTRAINT fk_qi_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_qi_quote FOREIGN KEY(quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_milestones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  project_id INT UNSIGNED NULL,
  opportunity_id INT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  method VARCHAR(40) NOT NULL DEFAULT 'bank_transfer',
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  amount DECIMAL(18,4) NOT NULL DEFAULT 0,
  exchange_rate DECIMAL(20,8) NOT NULL DEFAULT 1,
  due_date DATE NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  reference_no VARCHAR(120) NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(company_id,status,due_date),
  CONSTRAINT fk_pm_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_pm_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_pm_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_pm_opp FOREIGN KEY(opportunity_id) REFERENCES opportunities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lc_records (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  project_id INT UNSIGNED NULL,
  lc_no VARCHAR(120) NOT NULL,
  issuing_bank VARCHAR(220) NULL,
  advising_bank VARCHAR(220) NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  amount DECIMAL(18,4) NOT NULL DEFAULT 0,
  issue_date DATE NULL,
  expiry_date DATE NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'requested',
  latest_shipment_date DATE NULL,
  terms TEXT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_lc_company_no(company_id,lc_no),
  INDEX(company_id,status),
  CONSTRAINT fk_lc_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_lc_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_lc_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NULL,
  project_id INT UNSIGNED NULL,
  rfq_id BIGINT UNSIGNED NULL,
  shipment_no VARCHAR(100) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'booking',
  mode VARCHAR(30) NOT NULL DEFAULT 'sea',
  incoterm VARCHAR(20) NULL,
  container_no VARCHAR(100) NULL,
  bl_no VARCHAR(120) NULL,
  vessel VARCHAR(180) NULL,
  forwarder VARCHAR(180) NULL,
  port_loading VARCHAR(180) NULL,
  port_destination VARCHAR(180) NULL,
  etd DATE NULL,
  eta DATE NULL,
  actual_delivery_date DATE NULL,
  tracking_url VARCHAR(500) NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_shipment_company_no(company_id,shipment_no),
  INDEX(company_id,status,eta),
  CONSTRAINT fk_ship_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_ship_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_ship_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_ship_rfq FOREIGN KEY(rfq_id) REFERENCES rfqs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipment_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  shipment_id BIGINT UNSIGNED NOT NULL,
  document_type VARCHAR(50) NOT NULL,
  title VARCHAR(220) NOT NULL,
  file_url VARCHAR(500) NULL,
  document_no VARCHAR(120) NULL,
  issue_date DATE NULL,
  expiry_date DATE NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(company_id,shipment_id,document_type),
  CONSTRAINT fk_sd_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_shipment FOREIGN KEY(shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vertical_workflows (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  vertical VARCHAR(80) NOT NULL,
  name_fa VARCHAR(160) NOT NULL,
  name_en VARCHAR(160) NOT NULL,
  stages_json JSON NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_vertical_workflow(company_id,vertical),
  CONSTRAINT fk_vw_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  country VARCHAR(100) NULL,
  manufacturer TINYINT(1) NOT NULL DEFAULT 0,
  moq VARCHAR(120) NULL,
  typical_lead_time_days INT NULL,
  certifications TEXT NULL,
  quality_score DECIMAL(5,2) NULL,
  delivery_score DECIMAL(5,2) NULL,
  document_score DECIMAL(5,2) NULL,
  response_score DECIMAL(5,2) NULL,
  preferred TINYINT(1) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_supplier_profile(company_id,supplier_id),
  CONSTRAINT fk_sp_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_sp_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exchange_rates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  rate_date DATE NOT NULL,
  base_currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  quote_currency VARCHAR(8) NOT NULL,
  rate DECIMAL(20,8) NOT NULL,
  source VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fx(company_id,rate_date,base_currency,quote_currency),
  CONSTRAINT fk_fx_company FOREIGN KEY(company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ===== V8 Production Security Layer =====
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

INSERT IGNORE INTO schema_versions(version) VALUES('7.0.0'),('8.0.0');
CREATE TABLE IF NOT EXISTS reminders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(50) NULL,
  entity_id BIGINT UNSIGNED NULL,
  source_key VARCHAR(190) NULL,
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
  UNIQUE KEY uq_reminder_source(company_id,source_key),
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

ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS subtotal DECIMAL(18,2) NOT NULL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS terms TEXT NULL;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS customer_reference VARCHAR(120) NULL;

INSERT IGNORE INTO schema_versions(version) VALUES('9.0.0');

ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(80) NULL;

INSERT IGNORE INTO schema_versions(version) VALUES('9.1.0');
