-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- 主機： mariadb:3306
-- 產生時間： 2025 年 09 月 21 日 12:38
-- 伺服器版本： 10.4.6-MariaDB-1:10.4.6+maria~bionic
-- PHP 版本： 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `target_db`
--

-- --------------------------------------------------------

--
-- 資料表結構 `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(255) NOT NULL,
  `data` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agent` varchar(300) DEFAULT NULL,
  `stamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_adv`
--

DROP TABLE IF EXISTS `tbl_adv`;
CREATE TABLE IF NOT EXISTS `tbl_adv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL,
  `counter` int(11) NOT NULL,
  `exposure` int(11) NOT NULL DEFAULT 0,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Disabled',
  `weight` int(11) NOT NULL DEFAULT 0,
  `theme` varchar(10) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `uri` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `background` varchar(255) NOT NULL,
  `last_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`position_id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_adv_lang`
--

DROP TABLE IF EXISTS `tbl_adv_lang`;
CREATE TABLE IF NOT EXISTS `tbl_adv_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_adv_meta`
--

DROP TABLE IF EXISTS `tbl_adv_meta`;
CREATE TABLE IF NOT EXISTS `tbl_adv_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `k` varchar(50) DEFAULT NULL,
  `v` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_meta_press_idx` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_author`
--

DROP TABLE IF EXISTS `tbl_author`;
CREATE TABLE IF NOT EXISTS `tbl_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `slug` varchar(255) NOT NULL,
  `online_date` date DEFAULT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  `cover` varchar(255) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_author`
--

INSERT INTO `tbl_author` (`id`, `status`, `slug`, `online_date`, `sorter`, `cover`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(2, 'Enabled', 'editor', '2019-04-05', 0, '', '2025-05-26 06:40:47', 1, '2019-04-04 21:02:04', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_author_lang`
--

DROP TABLE IF EXISTS `tbl_author_lang`;
CREATE TABLE IF NOT EXISTS `tbl_author_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `jobtitle` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `slogan` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `summary` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- 傾印資料表的資料 `tbl_author_lang`
--

INSERT INTO `tbl_author_lang` (`id`, `lang`, `parent_id`, `title`, `jobtitle`, `slogan`, `summary`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'tw', 2, 'farm tyc', '編輯', '', NULL, '', '2025-05-26 06:40:47', 1, '2019-04-04 21:02:04', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_author_tag`
--

DROP TABLE IF EXISTS `tbl_author_tag`;
CREATE TABLE IF NOT EXISTS `tbl_author_tag` (
  `author_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`author_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_book`
--

DROP TABLE IF EXISTS `tbl_book`;
CREATE TABLE IF NOT EXISTS `tbl_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `cate_id` int(11) DEFAULT 0,
  `counter` int(11) DEFAULT 0,
  `exposure` int(11) DEFAULT 0,
  `uri` varchar(255) NOT NULL,
  `cover` varchar(100) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_book_lang`
--

DROP TABLE IF EXISTS `tbl_book_lang`;
CREATE TABLE IF NOT EXISTS `tbl_book_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_category`
--

DROP TABLE IF EXISTS `tbl_category`;
CREATE TABLE IF NOT EXISTS `tbl_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `sorter` tinyint(4) NOT NULL DEFAULT 0,
  `group` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `cover` varchar(100) NOT NULL DEFAULT '',
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_category`
--

INSERT INTO `tbl_category` (`id`, `status`, `sorter`, `group`, `slug`, `cover`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Enabled', 0, 'press', 'undefined', '', '2025-02-28 10:36:19', 1, '2025-02-19 07:49:26', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_category_lang`
--

DROP TABLE IF EXISTS `tbl_category_lang`;
CREATE TABLE IF NOT EXISTS `tbl_category_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `info` varchar(700) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- 傾印資料表的資料 `tbl_category_lang`
--

INSERT INTO `tbl_category_lang` (`id`, `lang`, `parent_id`, `title`, `info`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'tw', 1, '雜談', '', '2025-02-28 10:36:19', 1, '2025-02-19 07:49:26', 1),
(2, 'en', 1, 'Undefined', '', '2025-02-28 10:36:19', 1, '2025-02-19 07:49:26', 1),
(9, 'jp', 1, '雑談', '', '2025-02-28 10:36:19', 1, '2025-02-28 10:36:19', 1),
(10, 'ko', 1, '잡담', '', '2025-02-28 10:36:19', 1, '2025-02-28 10:36:19', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_category_tag`
--

DROP TABLE IF EXISTS `tbl_category_tag`;
CREATE TABLE IF NOT EXISTS `tbl_category_tag` (
  `tag_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 99,
  PRIMARY KEY (`category_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_collection`
--

DROP TABLE IF EXISTS `tbl_collection`;
CREATE TABLE IF NOT EXISTS `tbl_collection` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `txt_color` varchar(10) NOT NULL DEFAULT 'dark',
  `txt_algin` varchar(10) NOT NULL DEFAULT 'left',
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_collection_lang`
--

DROP TABLE IF EXISTS `tbl_collection_lang`;
CREATE TABLE IF NOT EXISTS `tbl_collection_lang` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_contact`
--

DROP TABLE IF EXISTS `tbl_contact`;
CREATE TABLE IF NOT EXISTS `tbl_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('New','Process','Done') NOT NULL DEFAULT 'New',
  `type` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `other` text DEFAULT NULL,
  `response` text NOT NULL,
  `last_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_dictionary`
--

DROP TABLE IF EXISTS `tbl_dictionary`;
CREATE TABLE IF NOT EXISTS `tbl_dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `slug` varchar(255) NOT NULL,
  `cover` varchar(100) NOT NULL DEFAULT '',
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_dictionary_lang`
--

DROP TABLE IF EXISTS `tbl_dictionary_lang`;
CREATE TABLE IF NOT EXISTS `tbl_dictionary_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_doorman`
--

DROP TABLE IF EXISTS `tbl_doorman`;
CREATE TABLE IF NOT EXISTS `tbl_doorman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('Member','Staff','Admin') DEFAULT 'Member',
  `status` enum('New','Invalid') NOT NULL DEFAULT 'New',
  `pwd` varchar(100) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_draft`
--

DROP TABLE IF EXISTS `tbl_draft`;
CREATE TABLE IF NOT EXISTS `tbl_draft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `press_id` int(11) NOT NULL DEFAULT 0 COMMENT '新聞稿 ID',
  `owner_id` int(11) NOT NULL DEFAULT 0 COMMENT '擁有者 ID',
  `request_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` enum('New','Waiting','Done','Invalid','Used') CHARACTER SET utf8mb4 DEFAULT 'New' COMMENT '草稿狀態',
  `lang` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tw' COMMENT '語言',
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'LLM 函式',
  `intent` text COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '意圖',
  `guideline` text COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '指導方針/原文',
  `content` mediumtext CHARACTER SET utf8mb4 DEFAULT '' COMMENT '內容',
  `insert_ts` timestamp NULL DEFAULT current_timestamp() COMMENT '插入時間',
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '最後更新時間',
  `insert_user` int(11) DEFAULT 0 COMMENT '新增的使用者 ID',
  `last_user` int(11) DEFAULT 0 COMMENT '最後更新的使用者 ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='草稿清單';

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_flash`
--

DROP TABLE IF EXISTS `tbl_flash`;
CREATE TABLE IF NOT EXISTS `tbl_flash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(32) DEFAULT NULL,
  `press_id` int(11) NOT NULL DEFAULT 0,
  `hit` int(11) NOT NULL DEFAULT 0,
  `exposure` int(11) NOT NULL DEFAULT 0,
  `status` enum('New','Done','Enabled','Disabled') NOT NULL DEFAULT 'New',
  `auto` enum('Yes','No') NOT NULL DEFAULT 'No',
  `genus` int(11) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT 0,
  `reliable` int(11) NOT NULL DEFAULT 0,
  `international` int(11) NOT NULL DEFAULT 0,
  `source` varchar(25) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `online_date` timestamp NULL DEFAULT NULL,
  `filename` varchar(20) NOT NULL DEFAULT '',
  `last_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`) USING BTREE,
  KEY `genus` (`genus`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_flash_lang`
--

DROP TABLE IF EXISTS `tbl_flash_lang`;
CREATE TABLE IF NOT EXISTS `tbl_flash_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_flash_meta`
--

DROP TABLE IF EXISTS `tbl_flash_meta`;
CREATE TABLE IF NOT EXISTS `tbl_flash_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `k` varchar(50) DEFAULT NULL,
  `v` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_meta_flash_idx` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_flash_raw`
--

DROP TABLE IF EXISTS `tbl_flash_raw`;
CREATE TABLE IF NOT EXISTS `tbl_flash_raw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `cover` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_genus`
--

DROP TABLE IF EXISTS `tbl_genus`;
CREATE TABLE IF NOT EXISTS `tbl_genus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `sorter` tinyint(4) NOT NULL DEFAULT 0,
  `group` varchar(50) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_genus`
--

INSERT INTO `tbl_genus` (`id`, `status`, `sorter`, `group`, `name`, `color`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Enabled', 98, 'course', '線上活動', NULL, '', '2021-11-11 17:25:01', 1, '2020-10-30 02:36:34', 1),
(2, 'Enabled', 99, 'course', '實體活動', NULL, '', '2021-11-11 17:25:11', 1, '2020-10-30 02:36:50', 1),
(3, 'Enabled', 2, 'press', '影音文章', '', '不放首圖、文中有影音', '2024-06-06 03:07:56', 1, '2020-10-30 03:00:26', 1),
(4, 'Enabled', 1, 'press', '一般文章', '', '大版位圖片 + 簡介在前', '2024-06-06 03:07:08', 1, '2021-11-11 14:42:01', 1),
(5, 'Enabled', 4, 'adv', '首頁友站連結', '', '小圖並列，最多六則', '2025-06-13 01:13:44', 1, '2023-09-22 05:31:41', 1),
(6, 'Enabled', 1, 'adv', '首頁首屏', '', '大圖輪播，最多三則', '2025-06-13 01:12:56', 1, '2025-03-12 16:15:41', 1),
(7, 'Enabled', 2, 'adv', '首頁特色活動', '', '', '2025-06-13 01:10:52', 1, '2025-03-12 16:16:30', 1),
(8, 'Enabled', 0, 'tag', '一般標籤', '', '', '2025-03-21 00:06:44', 1, '2025-03-21 00:06:44', 1),
(9, 'Enabled', 1, 'tag', '大標籤', '', '', '2025-03-21 00:06:53', 1, '2025-03-21 00:06:53', 1),
(10, 'Enabled', 3, 'adv', '首頁Youtube', '', '每次單則', '2025-06-13 01:12:36', 1, '2025-06-13 01:12:36', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_media`
--

DROP TABLE IF EXISTS `tbl_media`;
CREATE TABLE IF NOT EXISTS `tbl_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target` enum('Normal','Press') NOT NULL DEFAULT 'Normal',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `sorter` int(11) NOT NULL DEFAULT 0,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=485 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_media_lang`
--

DROP TABLE IF EXISTS `tbl_media_lang`;
CREATE TABLE IF NOT EXISTS `tbl_media_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_ai` enum('No','Yes') NOT NULL DEFAULT 'No',
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_media_meta`
--

DROP TABLE IF EXISTS `tbl_media_meta`;
CREATE TABLE IF NOT EXISTS `tbl_media_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `k` varchar(50) DEFAULT NULL,
  `v` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_meta_media_idx` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_media_tag`
--

DROP TABLE IF EXISTS `tbl_media_tag`;
CREATE TABLE IF NOT EXISTS `tbl_media_tag` (
  `media_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`media_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_menu`
--

DROP TABLE IF EXISTS `tbl_menu`;
CREATE TABLE IF NOT EXISTS `tbl_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Disabled',
  `blank` enum('Yes','No') NOT NULL DEFAULT 'No',
  `parent_id` int(11) DEFAULT 0,
  `uri` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `theme` varchar(30) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  `cover` varchar(150) DEFAULT NULL,
  `last_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;

--
-- 傾印資料表的資料 `tbl_menu`
--

INSERT INTO `tbl_menu` (`id`, `status`, `blank`, `parent_id`, `uri`, `theme`, `color`, `icon`, `sorter`, `cover`, `last_ts`, `last_user`, `insert_user`, `insert_ts`) VALUES
(1, 'Enabled', 'No', 0, '/nav', 'Basic', NULL, NULL, 0, '', '2017-01-17 13:09:45', 1, 1, '2021-09-20 01:14:24'),
(2, 'Enabled', 'No', 0, '/sidebar', 'Basic', NULL, NULL, 1, '', '2015-12-08 02:02:02', 1, 1, '2021-09-20 01:14:24'),
(4, 'Enabled', 'No', 2, 'about', 'Basic', 'info', NULL, 0, NULL, '2018-08-15 10:58:14', 1, 1, '2018-08-15 10:58:14'),
(5, 'Enabled', 'No', 2, '/s/privacy', 'Basic', 'info', '', 1, NULL, '2025-02-28 10:50:23', 1, 1, '2018-08-15 10:58:14'),
(9, 'Enabled', 'No', 2, '/contact', 'Basic', 'info', '', 2, NULL, '2025-02-28 10:50:46', 1, 1, '2018-08-17 12:02:05'),
(16, 'Enabled', 'No', 0, 'Backend', 'Basic', 'info', NULL, 3, NULL, '2021-05-15 10:45:43', 1, 1, '2021-05-15 10:45:43'),
(17, 'Enabled', 'No', 16, 'cms', 'Basic', 'info', NULL, 1, NULL, '2021-05-15 10:46:29', 1, 1, '2021-05-15 10:46:29'),
(18, 'Enabled', 'No', 16, 'crm', 'Basic', 'info', NULL, 2, NULL, '2021-05-15 10:47:10', 1, 1, '2021-05-15 10:47:10'),
(19, 'Enabled', 'No', 16, 'site', 'Basic', 'info', NULL, 3, NULL, '2021-05-15 10:47:47', 1, 1, '2021-05-15 10:47:47'),
(21, 'Enabled', 'No', 19, 'menu/simple', 'Basic', 'info', 'sitemap', 1, NULL, '2023-06-12 21:01:05', 1, 1, '2021-05-16 11:18:31'),
(22, 'Enabled', 'No', 19, 'post/list', 'Basic', 'info', 'file-text-o', 0, NULL, '2021-05-16 11:19:11', 1, 1, '2021-05-16 11:19:11'),
(23, 'Enabled', 'No', 19, 'staff/simple', 'Basic', 'info', 'users', 2, NULL, '2023-06-12 21:01:05', 1, 1, '2021-05-16 11:20:14'),
(25, 'Enabled', 'No', 18, 'contact/simple', 'Basic', 'info', 'phone', 1, NULL, '2023-06-13 14:13:48', 1, 1, '2021-05-16 11:24:01'),
(27, 'Enabled', 'No', 17, 'press/list', 'Basic', 'info', 'rss', 0, NULL, '2021-09-22 08:37:49', 1, 1, '2021-05-16 11:26:33'),
(29, 'Disabled', 'No', 17, 'dashboard/collections', 'Basic', 'info', 'cogs', 5, NULL, '2025-07-02 08:13:27', 1, 1, '2021-05-16 11:27:52'),
(31, 'Enabled', 'No', 18, 'adv/list', 'Basic', 'info', 'newspaper-o', 0, NULL, '2023-06-12 21:01:05', 1, 1, '2021-05-16 11:28:46'),
(35, 'Enabled', 'No', 18, 'stream/simple', 'Basic', 'info', 'stack-overflow', 2, NULL, '2023-06-13 14:13:42', 1, 1, '2021-05-22 00:18:52'),
(36, 'Enabled', 'No', 19, 'dashboard/advanced', 'Basic', 'info', 'cogs', 5, NULL, '2023-06-12 21:08:11', 1, 1, '2021-05-22 00:22:40'),
(43, 'Disabled', 'No', 17, 'dictionary/list', '', NULL, 'wikipedia-w', 2, NULL, '2025-07-02 13:00:22', 1, 1, '2025-02-19 07:46:12'),
(48, 'Enabled', 'No', 17, 'draft/list', '', NULL, 'pencil', 1, NULL, '2025-07-02 13:00:53', 1, 1, '2025-02-27 19:04:04'),
(77, 'Enabled', 'No', 16, 'board', '', NULL, '', 0, NULL, '2025-07-21 04:01:11', 1, 1, '2025-05-30 02:20:02'),
(78, 'Enabled', 'No', 77, 'stats/simple', '', NULL, 'dashboard', 0, NULL, '2025-05-30 02:21:36', 1, 1, '2025-05-30 02:21:36'),
(79, 'Enabled', 'No', 17, 'tag/simple', '', NULL, 'tags', 3, NULL, '2025-07-02 08:14:08', 1, 1, '2025-07-02 08:14:08'),
(80, 'Enabled', 'No', 17, 'author/list', '', NULL, 'users', 4, NULL, '2025-07-02 08:15:14', 1, 1, '2025-07-02 08:15:14'),
(81, 'Enabled', 'No', 17, 'category/simple', '', NULL, 'folder', 7, NULL, '2025-07-02 08:16:32', 1, 1, '2025-07-02 08:16:32');

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_menu_lang`
--

DROP TABLE IF EXISTS `tbl_menu_lang`;
CREATE TABLE IF NOT EXISTS `tbl_menu_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `badge` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=latin1;

--
-- 傾印資料表的資料 `tbl_menu_lang`
--

INSERT INTO `tbl_menu_lang` (`id`, `lang`, `parent_id`, `title`, `badge`, `info`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'tw', 1, '上方導覽', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(2, 'en', 1, 'Nav', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(3, 'en', 2, 'Sidebar', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(4, 'tw', 2, '側邊欄', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(5, 'tw', 4, '關於我們', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(6, 'en', 4, 'About', NULL, NULL, '2018-08-15 09:36:49', 1, '2018-08-15 09:36:49', 1),
(7, 'tw', 5, '隱私權政策', '', '', '2025-02-28 10:50:23', 1, '2018-08-15 09:36:49', 1),
(8, 'en', 5, 'Privacy', '', '', '2025-02-28 10:50:23', 1, '2018-08-15 09:36:49', 1),
(9, 'tw', 9, '聯絡我們', '', '', '2025-02-28 10:50:46', 1, '2018-08-17 12:02:05', 1),
(10, 'en', 9, 'Contact us', '', '', '2025-02-28 10:50:46', 1, '2018-08-17 12:02:05', 1),
(11, 'tw', 10, '關於我們', '', '', '2025-02-28 10:51:00', 1, '2018-09-27 03:52:10', 1),
(12, 'en', 10, 'About us', '', '', '2025-02-28 10:51:00', 1, '2018-09-27 03:52:10', 1),
(21, 'tw', 15, '聯絡我們', '', '', '2025-02-28 10:46:42', 1, '2018-09-27 04:54:09', 1),
(22, 'en', 15, 'Contact us', '', '', '2025-02-28 10:46:42', 1, '2018-09-27 04:54:09', 1),
(23, 'tw', 16, '後台選單', '', '', '2021-05-15 10:45:43', 1, '2021-05-15 10:45:43', 1),
(25, 'tw', 17, '內容管理', '', '', '2021-05-15 10:46:29', 1, '2021-05-15 10:46:29', 1),
(27, 'tw', 18, '客戶管理', '', '', '2021-05-15 10:47:10', 1, '2021-05-15 10:47:10', 1),
(29, 'tw', 19, '網站管理', '', '', '2021-05-15 10:47:47', 1, '2021-05-15 10:47:47', 1),
(33, 'tw', 21, '選單', '', '', '2021-05-16 11:18:31', 1, '2021-05-16 11:18:31', 1),
(35, 'tw', 22, '固定單頁', '', '', '2021-05-16 11:19:11', 1, '2021-05-16 11:19:11', 1),
(37, 'tw', 23, '管理員', '', '', '2021-05-22 00:20:27', 1, '2021-05-16 11:20:14', 1),
(41, 'tw', 25, '聯絡我們', '', '', '2021-05-16 11:24:01', 1, '2021-05-16 11:24:01', 1),
(45, 'tw', 27, '文章', '', '', '2021-05-16 11:26:33', 1, '2021-05-16 11:26:33', 1),
(47, 'tw', 28, '標籤', '', '', '2021-05-16 11:27:10', 1, '2021-05-16 11:27:10', 1),
(49, 'tw', 29, '集合管理', '', '', '2025-07-02 08:13:27', 1, '2021-05-16 11:27:52', 1),
(53, 'tw', 31, '廣告', '', '', '2021-05-16 11:28:46', 1, '2021-05-16 11:28:46', 1),
(61, 'tw', 35, '服務歷程', '', '', '2023-06-12 21:00:12', 1, '2021-05-22 00:18:52', 1),
(63, 'tw', 36, '進階', '', '', '2023-06-12 20:52:08', 1, '2021-05-22 00:22:40', 1),
(69, 'tw', 39, '使用者', '', '', '2024-02-13 17:34:48', 1, '2021-05-22 00:34:27', 1),
(71, 'tw', 40, '客戶', '', '', '2024-02-13 17:35:41', 1, '2024-02-13 17:34:36', 1),
(73, 'tw', 41, '專案', '', '', '2025-02-19 07:48:19', 1, '2024-02-13 17:37:03', 1),
(75, 'tw', 42, '發票', '', '', '2024-02-13 17:40:12', 1, '2024-02-13 17:40:12', 1),
(77, 'tw', 43, '詞彙表', '', '', '2025-07-02 13:00:22', 1, '2025-02-19 07:46:12', 1),
(79, 'tw', 44, '推薦書目', '', '', '2025-02-19 07:47:08', 1, '2025-02-19 07:47:08', 1),
(81, 'tw', 45, '開拓行者', '', '', '2025-02-28 10:45:15', 1, '2025-02-19 08:39:04', 1),
(82, 'en', 45, 'Junior Wayfarer', '', '', '2025-02-28 10:45:15', 1, '2025-02-19 08:39:04', 1),
(83, 'tw', 46, '歷練行者', '', '', '2025-02-28 10:46:20', 1, '2025-02-21 08:16:57', 1),
(84, 'en', 46, 'Senior Wayfarer', '', '', '2025-02-28 10:46:20', 1, '2025-02-21 08:16:57', 1),
(85, 'tw', 47, '遊戲假說', '', '', '2025-02-28 10:44:22', 1, '2025-02-21 19:05:27', 1),
(86, 'en', 47, 'Game Hypothesis', '', '', '2025-02-28 10:44:22', 1, '2025-02-21 19:05:27', 1),
(87, 'tw', 48, 'AI 小編', '', '', '2025-07-02 13:00:53', 1, '2025-02-27 19:04:04', 1),
(89, 'tw', 49, '外站文章', '', '', '2025-02-27 19:04:45', 1, '2025-02-27 19:04:45', 1),
(134, 'tw', 77, '總覽', '', '', '2025-07-21 04:01:11', 1, '2025-05-30 02:20:02', 1),
(138, 'tw', 78, '常用數據', '', '', '2025-05-30 02:21:36', 1, '2025-05-30 02:21:36', 1),
(147, 'tw', 79, '標籤', '', '', '2025-07-02 08:14:08', 1, '2025-07-02 08:14:08', 1),
(151, 'tw', 80, '作者', '', '', '2025-07-02 08:15:14', 1, '2025-07-02 08:15:14', 1),
(155, 'tw', 81, '分類', '', '', '2025-07-02 08:16:32', 1, '2025-07-02 08:16:32', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_menu_tag`
--

DROP TABLE IF EXISTS `tbl_menu_tag`;
CREATE TABLE IF NOT EXISTS `tbl_menu_tag` (
  `menu_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`menu_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_meta`
--

DROP TABLE IF EXISTS `tbl_meta`;
CREATE TABLE IF NOT EXISTS `tbl_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `fence` varchar(50) DEFAULT NULL,
  `label` varchar(150) DEFAULT NULL,
  `preset` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `input` varchar(20) DEFAULT NULL,
  `option` varchar(20) DEFAULT NULL,
  `sorter` tinyint(4) NOT NULL DEFAULT 10,
  `ps` varchar(250) DEFAULT NULL,
  `last_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_meta`
--

INSERT INTO `tbl_meta` (`id`, `status`, `fence`, `label`, `preset`, `type`, `input`, `option`, `sorter`, `ps`, `last_ts`, `last_user`, `insert_user`, `insert_ts`) VALUES
(5, 'Enabled', 'seo_desc', 'SEO 描述', NULL, 'text', 'paragraph', NULL, 3, '', '2025-07-13 00:50:17', 1, 1, '2021-07-04 06:20:11'),
(6, 'Enabled', 'seo_keyword', 'SEO 關鍵字', NULL, 'text', 'paragraph', NULL, 4, '以英文逗號( , )間隔 ', '2022-04-14 09:37:31', 1, 1, '2021-07-04 06:26:12'),
(11, 'Enabled', 'btn_txt', 'CTA 文字', NULL, 'text', 'text', NULL, 10, '簡潔有力', '2025-07-13 00:47:24', 1, 1, '2022-08-05 15:52:44');

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_meta_tag`
--

DROP TABLE IF EXISTS `tbl_meta_tag`;
CREATE TABLE IF NOT EXISTS `tbl_meta_tag` (
  `meta_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`meta_id`,`tag_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_meta_tag`
--

INSERT INTO `tbl_meta_tag` (`meta_id`, `tag_id`, `sorter`) VALUES
(5, 4, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_option`
--

DROP TABLE IF EXISTS `tbl_option`;
CREATE TABLE IF NOT EXISTS `tbl_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `loader` enum('Preload','Demand') NOT NULL DEFAULT 'Demand',
  `group` varchar(50) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_option`
--

INSERT INTO `tbl_option` (`id`, `status`, `loader`, `group`, `name`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Enabled', 'Demand', 'page', 'title', 'Demo', '2025-07-16 22:19:38', 1, '2015-12-29 06:43:32', 1),
(2, 'Enabled', 'Demand', 'page', 'keyword', 'Demo', '2018-11-06 03:16:03', 1, '2015-12-29 06:44:11', 1),
(4, 'Enabled', 'Demand', 'page', 'img', 'https://lifetrainee.org/media/social-img', '2018-11-06 03:20:47', 1, '2015-12-29 06:46:44', 1),
(5, 'Enabled', 'Preload', 'social', 'facebook_page', 'https://www.facebook.com/', '2025-07-08 22:35:18', 1, '2015-12-29 10:35:46', 1),
(8, 'Enabled', 'Preload', 'default', 'contact_mail', 'trevor@sense-info.co', '2025-07-08 22:51:02', 1, '2016-02-02 02:08:41', 1),
(12, 'Enabled', 'Demand', 'page', 'ga', 'G-', '2025-03-03 03:11:39', 1, '2016-05-03 23:51:12', 1),
(26, 'Enabled', 'Preload', 'default', 'contact_phone', '02 3224 2399', '2025-07-08 22:50:45', 1, '2016-02-02 02:08:41', 1),
(27, 'Enabled', 'Preload', 'default', 'contact_address', '林路3段9號', '2025-07-08 22:50:26', 1, '2016-02-02 02:08:41', 1),
(28, 'Enabled', 'Preload', 'social', 'gmap_page', 'https://www.google.com/maps/', '2025-07-08 22:37:41', 1, '2015-12-29 10:35:46', 1),
(29, 'Enabled', 'Preload', 'social', 'line_page', '@', '2025-07-08 22:47:08', 1, '2015-12-29 10:35:46', 1),
(30, 'Enabled', 'Demand', 'page', 'desc', 'Demo', '2018-11-06 03:15:26', 1, '2018-11-06 03:15:26', 1),
(31, 'Enabled', 'Preload', 'default', 'color_name', 'color-theme-3', '2025-03-03 03:55:48', 1, '2016-02-02 02:08:41', 1),
(32, 'Enabled', 'Demand', 'page', 'subtitle', 'Demo', '2025-07-16 22:20:18', 1, '2025-07-16 22:20:18', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post`
--

DROP TABLE IF EXISTS `tbl_post`;
CREATE TABLE IF NOT EXISTS `tbl_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `slug` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `layout` varchar(20) DEFAULT 'normal',
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=379 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_post`
--

INSERT INTO `tbl_post` (`id`, `status`, `slug`, `cover`, `layout`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(376, 'Enabled', 'contact', '', 'contact', '2025-07-11 04:07:20', 1, '2025-07-06 14:24:39', 1),
(378, 'Enabled', 'about', '', 'normal', '2025-08-26 01:11:07', 1, '2019-06-21 02:03:07', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post_lang`
--

DROP TABLE IF EXISTS `tbl_post_lang`;
CREATE TABLE IF NOT EXISTS `tbl_post_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_ai` enum('No','Yes') NOT NULL DEFAULT 'No',
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- 傾印資料表的資料 `tbl_post_lang`
--

INSERT INTO `tbl_post_lang` (`id`, `from_ai`, `lang`, `parent_id`, `title`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(12, 'No', 'tw', 376, '聯絡我們', '<p>麻煩您留下基本資訊與洽詢內容，待收到您的回饋後，我們將指派專人以最快的速度與您聯繫！</p><p>如果想發信給我們也歡迎寄信到 hello@sense-info.co。個資安全相關的問題也可以寄信到 pims@sense-info.co。</p>', '2025-07-11 04:07:20', 1, '2025-07-06 14:24:39', 1),
(13, 'No', 'en', 376, 'Contact Us', '<p>Please leave your basic information and inquiry content. Once we receive your feedback, we will assign a dedicated person to contact you at the earliest possible time!</p><p>If you wish to send an email to us, feel free to write to hello@sense-info.co. For issues related to personal data security, you can also email pims@sense-info.co.</p>', '2025-07-11 04:07:20', 1, '2025-07-06 14:24:39', 1),
(14, 'No', 'jp', 376, 'お問い合わせ', '<p>恐れ入りますが、基本情報とご相談内容をお知らせください。ご連絡をいただき次第、担当者が迅速に対応いたします！</p><p>また、メールでのご連絡をご希望の場合は、 hello@sense-info.co までお送りください。個人情報保護に関するご質問は、 pims@sense-info.co へもご連絡いただけます。</p>', '2025-07-11 04:07:20', 1, '2025-07-06 14:24:39', 1),
(15, 'No', 'ko', 376, '연락처', '<p>귀하의 기본 정보와 문의 내용을 남겨 주시면, 귀하의 피드백을 받은 후 최대한 신속하게 담당자가 연락드리겠습니다!</p><p>저희에게 이메일을 보내고 싶다면 hello@sense-info.co 로 보내 주세요. 개인정보 보안 관련 문의는 pims@sense-info.co 로도 보내실 수 있습니다.</p>', '2025-07-11 04:07:20', 1, '2025-07-06 14:24:39', 1),
(17, 'No', 'tw', 378, '關於本區', '', '2025-08-26 01:11:07', 1, '2025-08-26 01:11:07', 1),
(17, 'No', 'en', 378, '關於本區', '', '2025-08-26 01:11:07', 1, '2025-08-26 01:11:07', 1),
(18, 'No', 'ja', 378, '關於本區', '', '2025-08-26 01:11:07', 1, '2025-08-26 01:11:07', 1),
(19, 'No', 'ko', 378, '關於本區', '', '2025-08-26 01:11:07', 1, '2025-08-26 01:11:07', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post_meta`
--

DROP TABLE IF EXISTS `tbl_post_meta`;
CREATE TABLE IF NOT EXISTS `tbl_post_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `k` varchar(50) DEFAULT NULL,
  `v` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_meta_press_idx` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post_tag`
--

DROP TABLE IF EXISTS `tbl_post_tag`;
CREATE TABLE IF NOT EXISTS `tbl_post_tag` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press`
--

DROP TABLE IF EXISTS `tbl_press`;
CREATE TABLE IF NOT EXISTS `tbl_press` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL DEFAULT 1,
  `layout` int(11) NOT NULL DEFAULT 1,
  `status` enum('Draft','Published','Scheduled','Changed','Offlined') DEFAULT 'Draft',
  `mode` enum('Article','Slide') NOT NULL DEFAULT 'Article',
  `on_homepage` enum('Yes','No') NOT NULL DEFAULT 'No',
  `on_top` enum('Yes','No') NOT NULL DEFAULT 'No',
  `slug` varchar(255) NOT NULL,
  `online_date` datetime DEFAULT current_timestamp(),
  `sorter` int(11) NOT NULL DEFAULT 99,
  `cover` varchar(255) NOT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=450 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_author`
--

DROP TABLE IF EXISTS `tbl_press_author`;
CREATE TABLE IF NOT EXISTS `tbl_press_author` (
  `press_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`press_id`,`author_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_book`
--

DROP TABLE IF EXISTS `tbl_press_book`;
CREATE TABLE IF NOT EXISTS `tbl_press_book` (
  `press_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_lang`
--

DROP TABLE IF EXISTS `tbl_press_lang`;
CREATE TABLE IF NOT EXISTS `tbl_press_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_ai` enum('No','Yes') NOT NULL DEFAULT 'No',
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `info` varchar(700) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_meta`
--

DROP TABLE IF EXISTS `tbl_press_meta`;
CREATE TABLE IF NOT EXISTS `tbl_press_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `k` varchar(50) DEFAULT NULL,
  `v` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_meta_press_idx` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_related`
--

DROP TABLE IF EXISTS `tbl_press_related`;
CREATE TABLE IF NOT EXISTS `tbl_press_related` (
  `press_id` int(10) UNSIGNED NOT NULL,
  `related_id` int(10) UNSIGNED NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`related_id`,`press_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_tag`
--

DROP TABLE IF EXISTS `tbl_press_tag`;
CREATE TABLE IF NOT EXISTS `tbl_press_tag` (
  `press_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`press_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_term`
--

DROP TABLE IF EXISTS `tbl_press_term`;
CREATE TABLE IF NOT EXISTS `tbl_press_term` (
  `press_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`,`press_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_trace`
--

DROP TABLE IF EXISTS `tbl_press_trace`;
CREATE TABLE IF NOT EXISTS `tbl_press_trace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `press_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `insert_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `press_id` (`press_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_role`
--

DROP TABLE IF EXISTS `tbl_role`;
CREATE TABLE IF NOT EXISTS `tbl_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `menu_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `priv` int(11) DEFAULT 0,
  `info` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_role`
--

INSERT INTO `tbl_role` (`id`, `status`, `menu_id`, `title`, `priv`, `info`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Enabled', 16, 'Administrator', 31, '基本管理&#13;&#10;進階管理', '2025-07-15 09:51:18', 1, '2018-01-13 17:58:43', 1),
(2, 'Enabled', 83, '編輯', 5, '基本管理', '2025-07-15 09:51:03', 1, '2018-01-17 03:37:15', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_search`
--

DROP TABLE IF EXISTS `tbl_search`;
CREATE TABLE IF NOT EXISTS `tbl_search` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `status` enum('Disabled','Enabled') NOT NULL DEFAULT 'Disabled',
  `site_id` int(11) DEFAULT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_search_press`
--

DROP TABLE IF EXISTS `tbl_search_press`;
CREATE TABLE IF NOT EXISTS `tbl_search_press` (
  `press_id` int(11) NOT NULL,
  `search_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_shorten`
--

DROP TABLE IF EXISTS `tbl_shorten`;
CREATE TABLE IF NOT EXISTS `tbl_shorten` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Shorten ID',
  `cap` int(11) NOT NULL DEFAULT 9999,
  `hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `finished` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('Disabled','Enabled') NOT NULL DEFAULT 'Disabled' COMMENT '狀態',
  `origin` varchar(255) NOT NULL COMMENT '原始網址',
  `token` varchar(255) NOT NULL COMMENT '短網址 key',
  `note` varchar(300) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_uni` (`token`),
  KEY `origin_uni` (`origin`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_staff`
--

DROP TABLE IF EXISTS `tbl_staff`;
CREATE TABLE IF NOT EXISTS `tbl_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('New','Verified','Freeze') DEFAULT 'New',
  `needReset` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` int(11) NOT NULL,
  `account` varchar(45) DEFAULT NULL,
  `pwd` varchar(72) DEFAULT NULL,
  `verify_code` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(250) DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `tbl_staff`
--

INSERT INTO `tbl_staff` (`id`, `status`, `needReset`, `role_id`, `account`, `pwd`, `verify_code`, `email`, `note`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Verified', 0, 1, 'trevor', '$2y$10$6zGcEf6T8Mz9iNofPcCTuO8YVR.6UuAMNDcp7It.8seHxHJlp/jga', 'JAGRSTLTLLXN9HNTDN7QYLWFK8FXDLF5', 'trevor@sense-info.co', '', '2025-07-07 08:59:20', 1, '2015-08-04 12:41:20', 1),
(2, 'Verified', 0, 2, 'editor', '$2y$10$jfL3NNv9EIX7115ExL8osea8WT/yyOchUwNmhQxoEeVK/b9IYlADa', 'XYX95QGMM6F89NST6MQ8C8XG5FFDW8N9', 'shuaib25@gmail.com', '', '2025-07-15 09:45:30', 2, '2025-07-15 09:06:30', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_staff_footmark`
--

DROP TABLE IF EXISTS `tbl_staff_footmark`;
CREATE TABLE IF NOT EXISTS `tbl_staff_footmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `pwd` varchar(100) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- 傾印資料表的資料 `tbl_staff_footmark`
--

INSERT INTO `tbl_staff_footmark` (`id`, `parent_id`, `pwd`, `insert_ts`, `insert_user`) VALUES
(1, 2, '$2y$10$WkTwHPRrO6gpWo6lywoGW.68I1PT4GnnVokGyH1US2DLobWD2THqK', '2025-07-15 09:08:32', 2);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_staff_sudo`
--

DROP TABLE IF EXISTS `tbl_staff_sudo`;
CREATE TABLE IF NOT EXISTS `tbl_staff_sudo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_stream`
--

DROP TABLE IF EXISTS `tbl_stream`;
CREATE TABLE IF NOT EXISTS `tbl_stream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target` enum('Task','Sudo','StaffLogin') NOT NULL DEFAULT 'Sudo',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `content` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_subscription`
--

DROP TABLE IF EXISTS `tbl_subscription`;
CREATE TABLE IF NOT EXISTS `tbl_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('Disabled','Enabled') NOT NULL DEFAULT 'Enabled',
  `lancode` varchar(10) DEFAULT 'tw',
  `name` varchar(255) DEFAULT '',
  `phone` varchar(50) DEFAULT '',
  `email` varchar(255) NOT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_user` int(11) DEFAULT 0,
  `insert_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `insert_user` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_tag`
--

DROP TABLE IF EXISTS `tbl_tag`;
CREATE TABLE IF NOT EXISTS `tbl_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `counter` int(11) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_tag_lang`
--

DROP TABLE IF EXISTS `tbl_tag_lang`;
CREATE TABLE IF NOT EXISTS `tbl_tag_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT current_timestamp(),
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_pid` (`lang`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_tag_related`
--

DROP TABLE IF EXISTS `tbl_tag_related`;
CREATE TABLE IF NOT EXISTS `tbl_tag_related` (
  `related_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
