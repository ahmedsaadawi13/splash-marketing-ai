-- FILE: /database.sql
-- SplashMarketingAI Database Schema
-- Compatible with MySQL 5.7+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `splashmarketingai` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `splashmarketingai`;

-- ============================================
-- TENANTS & USERS
-- ============================================

CREATE TABLE `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `domain` VARCHAR(255) NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'marketer', 'analyst', 'viewer') NOT NULL DEFAULT 'marketer',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUBSCRIPTION & BILLING
-- ============================================

CREATE TABLE `plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly',
  `max_contacts` INT NOT NULL DEFAULT 1000,
  `max_sends_per_month` INT NOT NULL DEFAULT 10000,
  `max_workflows` INT NOT NULL DEFAULT 5,
  `max_users` INT NOT NULL DEFAULT 3,
  `max_ai_tokens_per_month` INT NOT NULL DEFAULT 50000,
  `max_api_calls_per_month` INT NOT NULL DEFAULT 10000,
  `features_json` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `status` ENUM('trialing', 'active', 'past_due', 'canceled') NOT NULL DEFAULT 'active',
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `renewal_date` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_usage` (
  `tenant_id` INT UNSIGNED NOT NULL,
  `contacts_count` INT NOT NULL DEFAULT 0,
  `sends_this_month` INT NOT NULL DEFAULT 0,
  `ai_tokens_used_this_month` INT NOT NULL DEFAULT 0,
  `api_calls_this_month` INT NOT NULL DEFAULT 0,
  `storage_bytes_used` BIGINT NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `due_date` DATE NOT NULL,
  `status` ENUM('unpaid', 'paid', 'overdue') NOT NULL DEFAULT 'unpaid',
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `paid_at` TIMESTAMP NOT NULL,
  `method` VARCHAR(50) NOT NULL,
  `transaction_reference` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CONTACTS & LISTS
-- ============================================

CREATE TABLE `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `full_name` VARCHAR(200) NULL,
  `country` VARCHAR(2) NULL,
  `timezone` VARCHAR(50) NULL,
  `status` ENUM('subscribed', 'unsubscribed', 'bounced', 'complaint') NOT NULL DEFAULT 'subscribed',
  `tags` TEXT NULL,
  `attributes_json` TEXT NULL,
  `last_open_at` TIMESTAMP NULL,
  `last_click_at` TIMESTAMP NULL,
  `last_engagement_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lists` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `list_contacts` (
  `list_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED NOT NULL,
  `tenant_id` INT UNSIGNED NOT NULL,
  `subscribed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`list_id`, `contact_id`),
  INDEX `idx_contact_id` (`contact_id`),
  INDEX `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `segments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `segment_type` ENUM('dynamic', 'static') NOT NULL DEFAULT 'dynamic',
  `filter_json` TEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEMPLATES & CHANNELS
-- ============================================

CREATE TABLE `templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `channel` ENUM('email', 'sms', 'social') NOT NULL,
  `subject` VARCHAR(255) NULL,
  `body_html` LONGTEXT NULL,
  `body_text` TEXT NULL,
  `variables_json` TEXT NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `channels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `type` ENUM('email', 'sms', 'social') NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `config_json` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CAMPAIGNS
-- ============================================

CREATE TABLE `campaigns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `channel` ENUM('email', 'sms', 'social') NOT NULL,
  `template_id` INT UNSIGNED NULL,
  `list_id` INT UNSIGNED NULL,
  `segment_id` INT UNSIGNED NULL,
  `from_name` VARCHAR(255) NULL,
  `from_email` VARCHAR(255) NULL,
  `from_phone` VARCHAR(20) NULL,
  `social_account_id` INT UNSIGNED NULL,
  `subject_line` VARCHAR(255) NULL,
  `status` ENUM('draft', 'scheduled', 'sending', 'sent', 'canceled') NOT NULL DEFAULT 'draft',
  `scheduled_at` TIMESTAMP NULL,
  `sent_at` TIMESTAMP NULL,
  `total_recipients` INT NOT NULL DEFAULT 0,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_channel` (`channel`),
  INDEX `idx_scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `campaign_recipients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `campaign_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `channel` ENUM('email', 'sms', 'social') NOT NULL,
  `status` ENUM('pending', 'queued', 'sent', 'bounced', 'complained', 'opened', 'clicked') NOT NULL DEFAULT 'pending',
  `open_count` INT NOT NULL DEFAULT 0,
  `click_count` INT NOT NULL DEFAULT 0,
  `last_event_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_campaign_id` (`campaign_id`),
  INDEX `idx_contact_id` (`contact_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AUTOMATION FLOWS
-- ============================================

CREATE TABLE `automation_flows` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `trigger_type` ENUM('on_subscribe', 'on_tag_added', 'on_campaign_interaction', 'date_based', 'segment_entry', 'event') NOT NULL,
  `trigger_config_json` TEXT NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `automation_steps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `flow_id` INT UNSIGNED NOT NULL,
  `step_order` INT NOT NULL,
  `step_type` ENUM('delay', 'send_email', 'send_sms', 'send_social', 'add_tag', 'remove_tag', 'branch', 'exit_flow') NOT NULL,
  `config_json` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_flow_id` (`flow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `automation_executions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `flow_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active', 'completed', 'canceled') NOT NULL DEFAULT 'active',
  `current_step_order` INT NOT NULL DEFAULT 0,
  `last_step_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_flow_id` (`flow_id`),
  INDEX `idx_contact_id` (`contact_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `automation_step_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `execution_id` INT UNSIGNED NOT NULL,
  `step_id` INT UNSIGNED NOT NULL,
  `step_type` VARCHAR(50) NOT NULL,
  `status` ENUM('pending', 'executed', 'skipped', 'failed') NOT NULL,
  `details_json` TEXT NULL,
  `executed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_execution_id` (`execution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- API & ACTIVITY
-- ============================================

CREATE TABLE `tenant_api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `api_key` VARCHAR(255) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_api_key` (`api_key`),
  INDEX `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NULL,
  `entity_type` VARCHAR(50) NULL,
  `entity_id` INT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_entity` (`entity_type`, `entity_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

-- Insert plans
INSERT INTO `plans` (`name`, `price`, `billing_cycle`, `max_contacts`, `max_sends_per_month`, `max_workflows`, `max_users`, `max_ai_tokens_per_month`, `max_api_calls_per_month`, `features_json`, `is_active`) VALUES
('Starter', 29.00, 'monthly', 1000, 10000, 3, 2, 50000, 5000, '{"sms_enabled":false,"social_enabled":false,"advanced_automation":false,"a_b_testing":false,"webhooks":false,"api_access":true}', 1),
('Professional', 79.00, 'monthly', 10000, 100000, 10, 5, 200000, 25000, '{"sms_enabled":true,"social_enabled":true,"advanced_automation":true,"a_b_testing":true,"webhooks":true,"api_access":true}', 1),
('Enterprise', 199.00, 'monthly', 100000, 1000000, 50, 20, 1000000, 100000, '{"sms_enabled":true,"social_enabled":true,"advanced_automation":true,"a_b_testing":true,"webhooks":true,"api_access":true}', 1);

-- Insert platform admin user (password: admin123)
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`) VALUES
(NULL, 'admin@splashmarketingai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform', 'Admin', 'platform_admin', 1);

-- Insert demo tenant
INSERT INTO `tenants` (`name`, `domain`, `status`) VALUES
('Demo Company', 'demo-company', 'active');

SET @demo_tenant_id = LAST_INSERT_ID();

-- Insert demo tenant admin (password: password)
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`) VALUES
(@demo_tenant_id, 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'User', 'tenant_admin', 1);

-- Insert demo marketer (password: password)
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`) VALUES
(@demo_tenant_id, 'marketer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marketing', 'Specialist', 'marketer', 1);

-- Set up demo tenant subscription (Professional plan)
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `start_date`, `renewal_date`) VALUES
(@demo_tenant_id, 2, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH));

-- Initialize demo tenant usage
INSERT INTO `tenant_usage` (`tenant_id`, `contacts_count`, `sends_this_month`, `ai_tokens_used_this_month`, `api_calls_this_month`, `storage_bytes_used`) VALUES
(@demo_tenant_id, 0, 0, 0, 0, 0);

-- Insert demo lists
INSERT INTO `lists` (`tenant_id`, `name`, `description`, `is_default`) VALUES
(@demo_tenant_id, 'All Subscribers', 'Default subscriber list', 1),
(@demo_tenant_id, 'Newsletter Subscribers', 'Users subscribed to newsletter', 0);

SET @demo_list_id = 1 + @demo_tenant_id;

-- Insert demo contacts
INSERT INTO `contacts` (`tenant_id`, `email`, `first_name`, `last_name`, `full_name`, `country`, `status`) VALUES
(@demo_tenant_id, 'john@example.com', 'John', 'Doe', 'John Doe', 'US', 'subscribed'),
(@demo_tenant_id, 'jane@example.com', 'Jane', 'Smith', 'Jane Smith', 'US', 'subscribed'),
(@demo_tenant_id, 'bob@example.com', 'Bob', 'Johnson', 'Bob Johnson', 'CA', 'subscribed');

-- Link contacts to list
INSERT INTO `list_contacts` (`list_id`, `contact_id`, `tenant_id`) VALUES
(@demo_list_id, @demo_tenant_id + 1, @demo_tenant_id),
(@demo_list_id, @demo_tenant_id + 2, @demo_tenant_id),
(@demo_list_id, @demo_tenant_id + 3, @demo_tenant_id);

-- Insert demo email template
INSERT INTO `templates` (`tenant_id`, `name`, `channel`, `subject`, `body_html`, `body_text`, `created_by_user_id`) VALUES
(@demo_tenant_id, 'Welcome Email', 'email', 'Welcome to {{company_name}}!',
'<!DOCTYPE html><html><body><h1>Welcome {{first_name}}!</h1><p>Thank you for subscribing.</p><p><a href="{{unsubscribe_link}}">Unsubscribe</a></p></body></html>',
'Welcome {{first_name}}!\n\nThank you for subscribing.\n\nUnsubscribe: {{unsubscribe_link}}',
NULL);

-- Insert demo segment
INSERT INTO `segments` (`tenant_id`, `name`, `description`, `segment_type`, `filter_json`, `is_active`) VALUES
(@demo_tenant_id, 'Active Subscribers', 'Contacts who opened in last 30 days', 'dynamic',
'{"match":"all","conditions":[{"field":"status","operator":"is","value":"subscribed"},{"field":"last_open","operator":"within_days","value":"30"}]}',
1);

-- Insert demo automation flow
INSERT INTO `automation_flows` (`tenant_id`, `name`, `description`, `is_active`, `trigger_type`, `trigger_config_json`) VALUES
(@demo_tenant_id, 'Welcome Series', 'Automated welcome email series for new subscribers', 1, 'on_subscribe',
'{"list_id":' + CAST(@demo_list_id AS CHAR) + '}');

SET @demo_flow_id = LAST_INSERT_ID();

-- Insert automation steps
INSERT INTO `automation_steps` (`tenant_id`, `flow_id`, `step_order`, `step_type`, `config_json`) VALUES
(@demo_tenant_id, @demo_flow_id, 1, 'send_email', '{"template_id":1,"subject":"Welcome to our community!"}'),
(@demo_tenant_id, @demo_flow_id, 2, 'delay', '{"delay_type":"days","delay_value":2}'),
(@demo_tenant_id, @demo_flow_id, 3, 'send_email', '{"template_id":1,"subject":"Here are some tips to get started"}');

-- Insert demo campaign
INSERT INTO `campaigns` (`tenant_id`, `name`, `channel`, `template_id`, `list_id`, `from_name`, `from_email`, `subject_line`, `status`, `total_recipients`) VALUES
(@demo_tenant_id, 'Monthly Newsletter - January', 'email', 1, @demo_list_id, 'Demo Company', 'noreply@example.com', 'Your Monthly Update', 'sent', 3);

SET @demo_campaign_id = LAST_INSERT_ID();

-- Insert campaign recipients with sample engagement
INSERT INTO `campaign_recipients` (`tenant_id`, `campaign_id`, `contact_id`, `email`, `channel`, `status`, `open_count`, `click_count`) VALUES
(@demo_tenant_id, @demo_campaign_id, @demo_tenant_id + 1, 'john@example.com', 'email', 'opened', 2, 1),
(@demo_tenant_id, @demo_campaign_id, @demo_tenant_id + 2, 'jane@example.com', 'email', 'clicked', 1, 3),
(@demo_tenant_id, @demo_campaign_id, @demo_tenant_id + 3, 'bob@example.com', 'email', 'sent', 0, 0);

-- ============================================
-- COMPLETE
-- ============================================
