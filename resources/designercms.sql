CREATE DATABASE IF NOT EXISTS `designercms`;

USE `designercms`;

CREATE TABLE IF NOT EXISTS `access_level` (
  `id` int(11) NOT NULL,
  `title` varchar(100)
  `username` varchar(65) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `short_name` varchar(45) DEFAULT NULL,
  `preferred_name` varchar(120) DEFAULT NULL,
  `full_name` varchar(250) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `activation_date` date NOT NULL,
  `logged_in` tinyint(4) DEFAULT '0',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL,
  `username` varchar(65) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access_level_id` int(11) NOT NULL DEFAULT 0,
  `short_name` varchar(45) DEFAULT NULL,
  `preferred_name` varchar(120) DEFAULT NULL,
  `full_name` varchar(250) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `activation_date` date NOT NULL,
  `logged_in` tinyint(4) DEFAULT '0',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_by` varchar(65) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;