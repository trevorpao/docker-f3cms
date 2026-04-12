-- 2026-04-12 daily SQL delivery
-- Add schema changes and DBA-executed full-table SQL for this date in this file.

-- EventRuleEngine minimal owning-module baseline

CREATE TABLE IF NOT EXISTS `tbl_member` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
	`display_name` varchar(255) NOT NULL DEFAULT '',
	`last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`last_user` int(11) DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member baseline entity';

CREATE TABLE IF NOT EXISTS `tbl_duty` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`slug` varchar(255) NOT NULL DEFAULT '',
	`claim` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
	`factor` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
	`next` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
	`status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
	`last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`last_user` int(11) DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_slug` (`slug`),
	KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Event rule duty definition';

CREATE TABLE IF NOT EXISTS `tbl_task` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`duty_id` int(11) NOT NULL DEFAULT 0,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`status` enum('New','Claimed','Done','Invalid') NOT NULL DEFAULT 'New',
	`last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`last_user` int(11) DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_duty_member` (`duty_id`,`member_id`),
	KEY `idx_member_status` (`member_id`,`status`),
	KEY `idx_duty_status` (`duty_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member duty runtime state';

CREATE TABLE IF NOT EXISTS `tbl_member_seen` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`target` varchar(32) NOT NULL DEFAULT '',
	`row_id` int(11) NOT NULL DEFAULT 0,
	`source` varchar(32) NOT NULL DEFAULT '',
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_member_target_row` (`member_id`,`target`,`row_id`),
	KEY `idx_target_row_member` (`target`,`row_id`,`member_id`),
	KEY `idx_member_insert_ts` (`member_id`,`insert_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member-owned first-hit seen truth';

CREATE TABLE IF NOT EXISTS `tbl_task_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`parent_id` int(11) NOT NULL DEFAULT 0,
	`action_code` varchar(64) NOT NULL DEFAULT '',
	`old_state_code` varchar(64) DEFAULT NULL,
	`new_state_code` varchar(64) DEFAULT NULL,
	`remark` text CHARACTER SET utf8mb4 DEFAULT NULL,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_parent_ts` (`parent_id`,`insert_ts`),
	KEY `idx_action_ts` (`action_code`,`insert_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Task audit trail';

CREATE TABLE IF NOT EXISTS `tbl_heraldry` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`slug` varchar(255) NOT NULL DEFAULT '',
	`status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
	`last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`last_user` int(11) DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_slug` (`slug`),
	KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Heraldry definition';

CREATE TABLE IF NOT EXISTS `tbl_member_heraldry` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`heraldry_id` int(11) NOT NULL DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_member_heraldry` (`member_id`,`heraldry_id`),
	KEY `idx_heraldry_member` (`heraldry_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member heraldry relation';

CREATE TABLE IF NOT EXISTS `tbl_manaccount` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`balance` int(11) NOT NULL DEFAULT 0,
	`status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
	`last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`last_user` int(11) DEFAULT 0,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member account current state';

CREATE TABLE IF NOT EXISTS `tbl_manaccount_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`parent_id` int(11) NOT NULL DEFAULT 0,
	`action_code` varchar(64) NOT NULL DEFAULT '',
	`delta_point` int(11) NOT NULL DEFAULT 0,
	`old_balance` int(11) NOT NULL DEFAULT 0,
	`new_balance` int(11) NOT NULL DEFAULT 0,
	`remark` text CHARACTER SET utf8mb4 DEFAULT NULL,
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_parent_ts` (`parent_id`,`insert_ts`),
	KEY `idx_action_ts` (`action_code`,`insert_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Member account audit trail';