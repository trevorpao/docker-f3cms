-- 2026-03-24 conversation mapping

DROP TABLE IF EXISTS `tbl_conversation`;
CREATE TABLE IF NOT EXISTS `tbl_conversation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) NOT NULL DEFAULT '' COMMENT '會員識別碼 (如 user_U0a4...)',
  `thread_id` varchar(96) NOT NULL DEFAULT '' COMMENT 'OpenAI conversation/thread ID',
  `model` varchar(64) NOT NULL DEFAULT '' COMMENT '最後使用的模型',
  `status` enum('Active','Archived') NOT NULL DEFAULT 'Active',
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '最後互動時間',
  `insert_ts` timestamp NULL DEFAULT current_timestamp() COMMENT '建立時間',
  `insert_user` int(11) DEFAULT 0,
  `last_user` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_conversation_user` (`user_id`),
  UNIQUE KEY `uniq_conversation_thread` (`thread_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='會員 ID 與 OpenAI conversation 對應表';
