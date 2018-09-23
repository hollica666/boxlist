-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2018 at 11:54 AM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>admins`
--

DROP TABLE IF EXISTS `<<prefix>>admins`;
CREATE TABLE IF NOT EXISTS `<<prefix>>admins` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password_hash` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default_user.png',
  `permission` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `<<prefix>>admins`
--

TRUNCATE TABLE `<<prefix>>admins`;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>adsense`
--

DROP TABLE IF EXISTS `<<prefix>>adsense`;
CREATE TABLE IF NOT EXISTS `<<prefix>>adsense` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` text,
  `provider_name` varchar(255) DEFAULT NULL,
  `large_track_code` text,
  `tablet_track_code` text,
  `phone_track_code` text,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `<<prefix>>adsense`
--

TRUNCATE TABLE `<<prefix>>adsense`;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>balance`
--

DROP TABLE IF EXISTS `<<prefix>>balance`;
CREATE TABLE IF NOT EXISTS `<<prefix>>balance` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `current_balance` double(9,2) NOT NULL DEFAULT '0.00',
  `total_earning` double(9,2) NOT NULL DEFAULT '0.00',
  `total_withdrawal` double(9,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `<<prefix>>balance`
--

TRUNCATE TABLE `<<prefix>>balance`;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>catagory_main`
--

DROP TABLE IF EXISTS `<<prefix>>catagory_main`;
CREATE TABLE IF NOT EXISTS `<<prefix>>catagory_main` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_order` int(10) DEFAULT NULL,
  `cat_name` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(300) NOT NULL DEFAULT 'fa-usd',
  `picture` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `<<prefix>>catagory_main`
--

TRUNCATE TABLE `<<prefix>>catagory_main`;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>catagory_sub`
--

DROP TABLE IF EXISTS `<<prefix>>catagory_sub`;
CREATE TABLE IF NOT EXISTS `<<prefix>>catagory_sub` (
  `sub_cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `main_cat_id` int(11) DEFAULT NULL,
  `sub_cat_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cat_order` mediumint(8) DEFAULT NULL,
  `photo_show` enum('0','1') NOT NULL DEFAULT '1',
  `price_show` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`sub_cat_id`),
  UNIQUE KEY `id` (`sub_cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>category_translation`
--

DROP TABLE IF EXISTS `<<prefix>>category_translation`;
CREATE TABLE IF NOT EXISTS `<<prefix>>category_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_id` int(1) DEFAULT NULL,
  `lang_code` varchar(10) DEFAULT NULL,
  `category_type` varchar(22) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>cities`
--

DROP TABLE IF EXISTS `<<prefix>>cities`;
CREATE TABLE IF NOT EXISTS `<<prefix>>cities` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'ISO-3166 2-letter country code, 2 characters',
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'name of geographical point (utf8) varchar(200)',
  `asciiname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'name of geographical point in plain ascii characters, varchar(200)',
  `longitude` float DEFAULT NULL COMMENT 'longitude in decimal degrees (wgs84)',
  `latitude` float DEFAULT NULL COMMENT 'latitude in decimal degrees (wgs84)',
  `feature_class` char(1) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'see http://www.geonames.org/export/codes.html, char(1)',
  `feature_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'see http://www.geonames.org/export/codes.html, varchar(10)',
  `subadmin1_code` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)',
  `subadmin2_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80)',
  `population` bigint(20) DEFAULT NULL COMMENT 'bigint (4 byte int) ',
  `time_zone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the timezone id (see file timeZone.txt)',
  `active` tinyint(3) UNSIGNED DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>countries`
--

DROP TABLE IF EXISTS `<<prefix>>countries`;
CREATE TABLE IF NOT EXISTS `<<prefix>>countries` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `iso3` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iso_numeric` int(10) UNSIGNED DEFAULT NULL,
  `fips` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asciiname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `capital` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area` int(10) UNSIGNED DEFAULT NULL,
  `population` int(10) UNSIGNED DEFAULT NULL,
  `continent_code` char(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tld` char(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency_code` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code_format` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code_regex` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `languages` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `neighbours` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `equivalent_fips_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>currencies`
--

DROP TABLE IF EXISTS `<<prefix>>currencies`;
CREATE TABLE IF NOT EXISTS `<<prefix>>currencies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html_entity` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'From Github : An array of currency symbols as HTML entities',
  `font_arial` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `font_code2000` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unicode_decimal` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unicode_hex` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `in_left` tinyint(1) DEFAULT '0',
  `decimal_places` int(10) UNSIGNED DEFAULT '2' COMMENT 'Currency Decimal Places - ISO 4217',
  `decimal_separator` varchar(10) COLLATE utf8_unicode_ci DEFAULT '.',
  `thousand_separator` varchar(10) COLLATE utf8_unicode_ci DEFAULT ',',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>custom_data`
--

DROP TABLE IF EXISTS `<<prefix>>custom_data`;
CREATE TABLE IF NOT EXISTS `<<prefix>>custom_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `field_id` int(11) DEFAULT NULL,
  `field_type` varchar(20) DEFAULT NULL,
  `field_data` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>custom_fields`
--

DROP TABLE IF EXISTS `<<prefix>>custom_fields`;
CREATE TABLE IF NOT EXISTS `<<prefix>>custom_fields` (
  `custom_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `custom_order` int(10) DEFAULT NULL,
  `translation_lang` longtext COLLATE utf8_unicode_ci,
  `translation_name` text COLLATE utf8_unicode_ci,
  `custom_anycat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_catid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_subcatid` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_type` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_options` longtext COLLATE utf8_unicode_ci,
  `custom_required` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `custom_name` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `custom_default` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_min` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `custom_max` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`custom_id`),
  KEY `custom_order` (`custom_order`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>custom_options`
--

DROP TABLE IF EXISTS `<<prefix>>custom_options`;
CREATE TABLE IF NOT EXISTS `<<prefix>>custom_options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>faq_entries`
--

DROP TABLE IF EXISTS `<<prefix>>faq_entries`;
CREATE TABLE IF NOT EXISTS `<<prefix>>faq_entries` (
  `faq_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `translation_lang` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `translation_of` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `faq_pid` smallint(4) NOT NULL DEFAULT '0',
  `faq_weight` mediumint(6) NOT NULL DEFAULT '0',
  `faq_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `faq_content` mediumtext COLLATE utf8_unicode_ci,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`faq_id`),
  KEY `translation_lang` (`translation_lang`,`translation_of`,`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>favads`
--

DROP TABLE IF EXISTS `<<prefix>>favads`;
CREATE TABLE IF NOT EXISTS `<<prefix>>favads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>languages`
--

DROP TABLE IF EXISTS `<<prefix>>languages`;
CREATE TABLE IF NOT EXISTS `<<prefix>>languages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direction` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbr` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>login_attempts`
--

DROP TABLE IF EXISTS `<<prefix>>login_attempts`;
CREATE TABLE IF NOT EXISTS `<<prefix>>login_attempts` (
  `user_id` int(11) NOT NULL,
  `time` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>messages`
--

DROP TABLE IF EXISTS `<<prefix>>messages`;
CREATE TABLE IF NOT EXISTS `<<prefix>>messages` (
  `message_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_id` varchar(40) DEFAULT NULL,
  `to_id` varchar(50) DEFAULT NULL,
  `from_uname` varchar(225) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_uname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `message_content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `message_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recd` tinyint(1) NOT NULL DEFAULT '0',
  `seen` enum('0','1') NOT NULL DEFAULT '0',
  `message_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>options`
--

DROP TABLE IF EXISTS `<<prefix>>options`;
CREATE TABLE IF NOT EXISTS `<<prefix>>options` (
  `option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_value` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>pages`
--

DROP TABLE IF EXISTS `<<prefix>>pages`;
CREATE TABLE IF NOT EXISTS `<<prefix>>pages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `translation_lang` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `translation_of` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_lang` (`translation_lang`),
  KEY `translation_of` (`translation_of`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `<<prefix>>pages`
--

TRUNCATE TABLE `<<prefix>>pages`;
-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>payments`
--

DROP TABLE IF EXISTS `<<prefix>>payments`;
CREATE TABLE IF NOT EXISTS `<<prefix>>payments` (
  `payment_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_install` enum('0','1') NOT NULL DEFAULT '0',
  `payment_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_folder` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>product`
--

DROP TABLE IF EXISTS `<<prefix>>product`;
CREATE TABLE IF NOT EXISTS `<<prefix>>product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` enum('pending','active','rejected','softreject','hide') NOT NULL DEFAULT 'pending',
  `user_id` int(20) DEFAULT NULL,
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `urgent` enum('0','1') NOT NULL DEFAULT '0',
  `highlight` enum('0','1') NOT NULL DEFAULT '0',
  `product_name` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `category` int(11) DEFAULT NULL,
  `sub_category` int(11) DEFAULT NULL,
  `price` int(10) NOT NULL DEFAULT '0',
  `negotiable` enum('0','1') NOT NULL DEFAULT '0',
  `phone` varchar(50) DEFAULT NULL,
  `hide_phone` enum('0','1') NOT NULL DEFAULT '0',
  `location` text,
  `city` char(50) DEFAULT NULL,
  `state` char(50) DEFAULT NULL,
  `country` char(50) DEFAULT NULL,
  `latlong` varchar(255) DEFAULT NULL,
  `screen_shot` text,
  `tag` varchar(225) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `view` int(11) NOT NULL DEFAULT '1',
  `custom_fields` longtext,
  `custom_types` longtext,
  `custom_values` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` int(11) DEFAULT NULL,
  `contact_phone` enum('0','1') NOT NULL DEFAULT '0',
  `contact_email` enum('0','1') NOT NULL DEFAULT '0',
  `contact_chat` enum('0','1') NOT NULL DEFAULT '0',
  `admin_seen` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>product_resubmit`
--

DROP TABLE IF EXISTS `<<prefix>>product_resubmit`;
CREATE TABLE IF NOT EXISTS `<<prefix>>product_resubmit` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(20) DEFAULT NULL,
  `featured` enum('0','1') NOT NULL DEFAULT '0',
  `urgent` enum('0','1') NOT NULL DEFAULT '0',
  `highlight` enum('0','1') NOT NULL DEFAULT '0',
  `product_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `category` int(11) DEFAULT NULL,
  `sub_category` int(11) DEFAULT NULL,
  `price` int(10) DEFAULT NULL,
  `negotiable` enum('0','1') NOT NULL DEFAULT '0',
  `phone` varchar(50) DEFAULT NULL,
  `hide_phone` enum('0','1') DEFAULT '0',
  `location` text,
  `city` char(50) DEFAULT NULL,
  `state` char(50) DEFAULT NULL,
  `country` char(50) DEFAULT NULL,
  `latlong` varchar(255) DEFAULT NULL,
  `screen_shot` text,
  `tag` varchar(225) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','active','rejected','softreject') NOT NULL DEFAULT 'pending',
  `view` int(11) NOT NULL DEFAULT '1',
  `custom_fields` longtext,
  `custom_types` longtext,
  `custom_values` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` int(11) DEFAULT NULL,
  `contact_phone` enum('0','1') NOT NULL DEFAULT '0',
  `contact_email` enum('0','1') NOT NULL DEFAULT '0',
  `contact_chat` enum('0','1') NOT NULL DEFAULT '0',
  `comments` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `admin_seen` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>reviews`
--

DROP TABLE IF EXISTS `<<prefix>>reviews`;
CREATE TABLE IF NOT EXISTS `<<prefix>>reviews` (
  `reviewID` int(21) NOT NULL AUTO_INCREMENT,
  `productID` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` double DEFAULT NULL,
  `comments` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `date` date DEFAULT NULL,
  `publish` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reviewID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>subadmin1`
--

DROP TABLE IF EXISTS `<<prefix>>subadmin1`;
CREATE TABLE IF NOT EXISTS `<<prefix>>subadmin1` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `asciiname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `country_code` (`country_code`),
  KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>`
--

DROP TABLE IF EXISTS `<<prefix>>subadmin2`;
CREATE TABLE IF NOT EXISTS `<<prefix>>subadmin2` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subadmin1_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asciiname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `country_code` (`country_code`),
  KEY `subadmin1_code` (`subadmin1_code`),
  KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>time_zones`
--

DROP TABLE IF EXISTS `<<prefix>>time_zones`;
CREATE TABLE IF NOT EXISTS `<<prefix>>time_zones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `time_zone_id` varchar(40) COLLATE utf8_unicode_ci DEFAULT '',
  `gmt` float DEFAULT NULL,
  `dst` float DEFAULT NULL,
  `raw` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `time_zone_id` (`time_zone_id`),
  KEY `country_code` (`country_code`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>transaction`
--

DROP TABLE IF EXISTS `<<prefix>>transaction`;
CREATE TABLE IF NOT EXISTS `<<prefix>>transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(225) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `amount` double(9,2) DEFAULT NULL,
  `featured` enum('0','1') DEFAULT '0',
  `urgent` enum('0','1') DEFAULT '0',
  `highlight` enum('0','1') DEFAULT '0',
  `transaction_time` int(11) DEFAULT NULL,
  `status` enum('pending','success','failed','cancel') DEFAULT NULL,
  `transaction_gatway` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_ip` varchar(15) DEFAULT NULL,
  `transaction_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_method` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<prefix>>user`
--

DROP TABLE IF EXISTS `<<prefix>>user`;
CREATE TABLE IF NOT EXISTS `<<prefix>>user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_type` enum('user','seller') DEFAULT 'user',
  `password_hash` varchar(255) DEFAULT NULL,
  `forgot` varchar(255) DEFAULT NULL,
  `confirm` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('0','1','2') DEFAULT '0',
  `view` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `name` varchar(225) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `sex` enum('Male','Female','Other') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Male',
  `phone` varchar(255) DEFAULT NULL,
  `postcode` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(225) DEFAULT NULL,
  `image` varchar(225) NOT NULL DEFAULT 'default_user.png',
  `lastactive` datetime DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `googleplus` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `oauth_provider` enum('','facebook','google','twitter') DEFAULT NULL,
  `oauth_uid` varchar(100) DEFAULT NULL,
  `oauth_link` varchar(255) DEFAULT NULL,
  `online` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=738 DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
