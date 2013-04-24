-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 10, 2013 at 12:51 AM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `numbers_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `size` int(5) unsigned NOT NULL,
  `start_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `end_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `provider` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `start_number` (`start_number`,`end_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `iso_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `local` tinyint(4) NOT NULL DEFAULT '0',
  `toll_free` varchar(255) NULL,
  `vanity` tinyint(4) NOT NULL DEFAULT '0',
  `prefix` int(5) unsigned NOT NULL,
  `flag_url` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;