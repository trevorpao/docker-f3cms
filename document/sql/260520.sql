-- 2026-05-20 daily SQL delivery
-- SMSSystem first implementation slice: Mobile / Phonebook / Campaign schema.

CREATE TABLE IF NOT EXISTS `tbl_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(32) NOT NULL DEFAULT '',
  `status` enum('Active','Invalid','Opt-out') NOT NULL DEFAULT 'Active',
  `last_sent_ts` datetime DEFAULT NULL,
  `last_ts` datetime DEFAULT NULL,
  `last_user` int(11) NOT NULL DEFAULT 0,
  `insert_ts` datetime DEFAULT NULL,
  `insert_user` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_phone_number` (`phone_number`),
  KEY `idx_status` (`status`),
  KEY `idx_last_sent_ts` (`last_sent_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS mobile master';

CREATE TABLE IF NOT EXISTS `tbl_phonebook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(191) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `last_ts` datetime DEFAULT NULL,
  `last_user` int(11) NOT NULL DEFAULT 0,
  `insert_ts` datetime DEFAULT NULL,
  `insert_user` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_member_title` (`member_id`,`title`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS phonebook';

CREATE TABLE IF NOT EXISTS `tbl_phonebook_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phonebook_id` int(11) NOT NULL DEFAULT 0,
  `mobile_id` int(11) NOT NULL DEFAULT 0,
  `insert_ts` datetime DEFAULT NULL,
  `insert_user` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_phonebook_mobile` (`phonebook_id`,`mobile_id`),
  KEY `idx_mobile_id` (`mobile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS phonebook-mobile relation';

CREATE TABLE IF NOT EXISTS `tbl_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT 0,
  `phonebook_id` int(11) NOT NULL DEFAULT 0,
  `provider_policy` varchar(64) NOT NULL DEFAULT 'TW_TO_MITAKE_ELSE_AWS',
  `content` text DEFAULT NULL,
  `scheduled_ts` datetime DEFAULT NULL,
  `status` enum('Draft','Queued','Processing','Completed','PartiallyFailed','Failed') NOT NULL DEFAULT 'Draft',
  `total_targets` int(11) NOT NULL DEFAULT 0,
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `last_ts` datetime DEFAULT NULL,
  `last_user` int(11) NOT NULL DEFAULT 0,
  `insert_ts` datetime DEFAULT NULL,
  `insert_user` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_phonebook_id` (`phonebook_id`),
  KEY `idx_status_scheduled_ts` (`status`,`scheduled_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS campaign';

CREATE TABLE IF NOT EXISTS `tbl_campaign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL DEFAULT 0,
  `member_id` int(11) NOT NULL DEFAULT 0,
  `phonebook_id` int(11) NOT NULL DEFAULT 0,
  `mobile_id` int(11) NOT NULL DEFAULT 0,
  `provider_alias` varchar(32) NOT NULL DEFAULT '',
  `status` enum('Pending','Sent','Failed','Skipped') NOT NULL DEFAULT 'Pending',
  `error_message` varchar(255) DEFAULT NULL,
  `provider_message_id` varchar(191) DEFAULT NULL,
  `scheduled_ts` datetime DEFAULT NULL,
  `sent_ts` datetime DEFAULT NULL,
  `attempt_ts` datetime DEFAULT NULL,
  `last_ts` datetime DEFAULT NULL,
  `last_user` int(11) NOT NULL DEFAULT 0,
  `insert_ts` datetime DEFAULT NULL,
  `insert_user` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_campaign_mobile` (`campaign_id`,`mobile_id`),
  KEY `idx_status_scheduled_ts` (`status`,`scheduled_ts`),
  KEY `idx_mobile_id` (`mobile_id`),
  KEY `idx_mobile_status_sent_ts` (`mobile_id`,`status`,`sent_ts`),
  KEY `idx_provider_alias` (`provider_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS campaign log queue';