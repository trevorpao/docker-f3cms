-- 2026-04-25 daily SQL delivery
-- SeenTargetTaskCompletion: first live entity seen table slice for Press.

CREATE TABLE IF NOT EXISTS `tbl_press_seen` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`row_id` int(11) NOT NULL DEFAULT 0,
	`source` varchar(32) NOT NULL DEFAULT '',
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_member_row` (`member_id`,`row_id`),
	KEY `idx_row_member` (`row_id`,`member_id`),
	KEY `idx_member_insert_ts` (`member_id`,`insert_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Press-owned first-hit seen truth';

CREATE TABLE IF NOT EXISTS `tbl_post_seen` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL DEFAULT 0,
	`row_id` int(11) NOT NULL DEFAULT 0,
	`source` varchar(32) NOT NULL DEFAULT '',
	`insert_ts` timestamp NULL DEFAULT current_timestamp(),
	`insert_user` int(11) DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_member_row` (`member_id`,`row_id`),
	KEY `idx_row_member` (`row_id`,`member_id`),
	KEY `idx_member_insert_ts` (`member_id`,`insert_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Post-owned first-hit seen truth';