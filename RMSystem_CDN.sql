-- Adminer 4.8.1 MySQL 10.6.16-MariaDB-0ubuntu0.22.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `files_access`;
CREATE TABLE `files_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `files_access` (`id`, `key`, `user`, `file`) VALUES
(107,	'0e41cb4480c450bc7b40ba4a79fef0ef',	1,	'ELS-v1.9-Beta.zip');

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `files_history`;
CREATE TABLE `files_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `download_key` varchar(255) DEFAULT NULL,
  `cdn` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `files_history` (`id`, `user`, `download_key`, `cdn`, `file`, `date`) VALUES


-- 2024-04-30 17:48:39
