-- Adminer 4.8.1 MySQL 10.6.16-MariaDB-0ubuntu0.22.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `benutzer`;
CREATE TABLE `benutzer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `role` enum('Mitglied','Supporter','Admin','Owner') NOT NULL DEFAULT 'Mitglied',
  `password` varchar(255) DEFAULT NULL,
  `letzte_ip` varchar(45) DEFAULT NULL,
  `kontonummer` int(11) DEFAULT NULL,
  `gesperrt` tinyint(1) DEFAULT NULL,
  `ticket_sperre` tinyint(4) NOT NULL DEFAULT 0,
  `sperrgrund` varchar(100) DEFAULT NULL,
  `last_username_change` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `totp_secret` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kundennummer` (`kontonummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `email_delivered`;
CREATE TABLE `email_delivered` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` enum('delivered','error') DEFAULT NULL,
  `requested` datetime NOT NULL DEFAULT current_timestamp(),
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `email_verify`;
CREATE TABLE `email_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `user` int(11) NOT NULL,
  `verify_code` varchar(255) NOT NULL,
  `request_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `size` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('ELS','URLS') NOT NULL,
  `released` enum('No','Yes','Only Verfied','disabled') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `files` (`id`, `name`, `size`, `date`, `category`, `released`) VALUES
(1,	'ELS-v1.5-Beta.zip',	'2.26 MB',	'2023-10-30 05:48:33',	'ELS',	'Yes'),
(2,	'ELS-v1.6-Beta.zip',	'2.25 MB',	'2023-10-31 18:39:06',	'ELS',	'Yes'),
(3,	'ELS-v1.7-Beta.zip',	'2.38 MB',	'2023-11-02 11:37:02',	'ELS',	'Yes'),
(4,	'ELS-v1.8-Beta.zip',	'2.45 MB',	'2023-12-09 08:08:02',	'ELS',	'Yes'),
(5,	'ELS-v1.9-Beta.zip',	'2.44 MB',	'2024-01-04 12:16:50',	'ELS',	'Yes'),
(6,	'URLS-v1.0-Beta.zip',	'6.60 MB',	'2024-03-21 09:26:00',	'URLS',	'disabled');

DROP TABLE IF EXISTS `holydays`;
CREATE TABLE `holydays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `holydays` (`id`, `name`, `date_from`, `date_to`) VALUES
(1,	'christmas',	'2024-12-01',	'2024-12-26'),
(2,	'easter',	'2024-04-01',	'2024-04-03');

DROP TABLE IF EXISTS `hoster_list`;
CREATE TABLE `hoster_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hoster_url` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Online','Offline','error','maintenance','checking') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hoster_list` (`id`, `hoster_url`, `location`, `status`) VALUES
(3,	'https://de-fsn-1.cdn.mcslot.net',	'DE, Falkenstein',	'Online');

DROP TABLE IF EXISTS `ip_bans`;
CREATE TABLE `ip_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `ban_reason` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `ban_time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `login_status` varchar(20) NOT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `login_attempts` (`id`, `ip_address`, `login_status`, `timestamp`) VALUES


DROP TABLE IF EXISTS `login_history`;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `login_status` enum('erfolgreich','abgelehnt') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `login_history` (`id`, `user_id`, `login_time`, `ip_address`, `login_status`) VALUES

DROP TABLE IF EXISTS `password_reset`;
CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `verify_key` varchar(255) NOT NULL,
  `requested` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `user_id` int(11) NOT NULL,
  `creation_date` timestamp NULL DEFAULT current_timestamp(),
  `deletion_date` timestamp NULL DEFAULT NULL,
  `complete` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `queue` (`user_id`, `creation_date`, `deletion_date`, `complete`) VALUES


DROP TABLE IF EXISTS `queue_last_execution`;
CREATE TABLE `queue_last_execution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `last_execution_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `queue_last_execution` (`id`, `type`, `last_execution_datetime`) VALUES
(1,	'Account delete',	'2024-04-30 19:30:01'),
(2,	'Status CDN',	'2024-04-30 19:48:17');

DROP TABLE IF EXISTS `registration_codes`;
CREATE TABLE `registration_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `remember_me_tokens`;
CREATE TABLE `remember_me_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expiry` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `remember_me_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `benutzer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` (`setting_id`, `setting_name`, `setting_value`) VALUES
(1,	'register',	'0'),
(2,	'login',	'1'),
(4,	'maintenance',	'0'),
(5,	'website_name',	'McSlot'),
(6,	'cron_key',	'919273619926192334'),
(7,	'lockdown',	'0'),
(8,	'2fa_requirement_team',	'1');

DROP TABLE IF EXISTS `settings_users`;
CREATE TABLE `settings_users` (
  `user_id` int(11) NOT NULL,
  `effects` tinyint(1) DEFAULT 1,
  `night_mode` enum('on','off','auto','device') NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `settings_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `benutzer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings_users` (`user_id`, `effects`, `night_mode`) VALUES


DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('offen','bearbeitung','geschlossen') DEFAULT 'offen',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `benutzer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tickets` (`id`, `user_id`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES

DROP TABLE IF EXISTS `ticket_replies`;
CREATE TABLE `ticket_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `team_reply` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `benutzer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `created_at`, `team_reply`) VALUES

DROP TABLE IF EXISTS `two_factor`;
CREATE TABLE `two_factor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(255) NOT NULL,
  `login_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `two_factor` (`id`, `user`, `login_key`) VALUES

-- 2024-04-30 17:49:49
