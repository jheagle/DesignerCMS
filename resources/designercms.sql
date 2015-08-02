CREATE DATABASE IF NOT EXISTS `designercms`;

USE `designercms`;

CREATE TABLE IF NOT EXISTS `system_gate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(125),
  `access_levels` int(11) NOT NULL DEFAULT 1,
  `pattern` varchar(255),
  `access_names` text NOT NULL,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `access_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `access_key` blob NOT NULL,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `access_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_type_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100),
  `access_key` blob NOT NULL,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`access_type_id`) REFERENCES `access_type`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(65) NOT NULL,
  `password` varchar(255) NOT NULL,
  `short_name` varchar(45) DEFAULT NULL,
  `preferred_name` varchar(120) DEFAULT NULL,
  `full_name` varchar(250) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `email` varchar(191) NOT NULL DEFAULT '',
  `main` tinyint(4) NOT NULL DEFAULT '0',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `access_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `access_level_id` int(11) NOT NULL DEFAULT 0,
  `access_email_id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(65) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access_email` varchar(191) NOT NULL,
  `activation_date` date NOT NULL,
  `logged_in` tinyint(4) DEFAULT '0',
  `last_login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `access_email` (`access_email`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`),
  FOREIGN KEY (`access_level_id`) REFERENCES `access_level`(`id`),
  FOREIGN KEY (`access_email_id`) REFERENCES `email`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `street1` blob NOT NULL,
  `street2` varchar(255) NOT NULL DEFAULT '',
  `municipality` varchar(125) NOT NULL DEFAULT '',
  `region` varchar(80) NOT NULL DEFAULT '',
  `country` varchar(80) NOT NULL DEFAULT '',
  `code` varchar(7) NOT NULL DEFAULT '',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `phone_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(40) NOT NULL DEFAULT '',
  `sms` tinyint(4) NOT NULL DEFAULT '0',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) NOT NULL DEFAULT 0,
  `phone` varchar(255) NOT NULL DEFAULT '',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`),
  FOREIGN KEY (`type_id`) REFERENCES `phone_type`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `flag` varchar(255) NOT NULL DEFAULT '',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

CREATE TABLE IF NOT EXISTS `translation` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `table` varchar(60) NOT NULL DEFAULT '',
  `column` varchar(60) NOT NULL DEFAULT '',
  `table_id` int(11) NOT NULL DEFAULT 0,
  `translation` blob NOT NULL,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`language_id`, `table`, `column`, `table_id`),
  FOREIGN KEY (`language_id`) REFERENCES `language`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 