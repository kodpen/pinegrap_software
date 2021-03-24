-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: d1    Database: starter
-- ------------------------------------------------------
-- Server version	5.1.73

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aclfolder`
--

DROP TABLE IF EXISTS `aclfolder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aclfolder` (
  `aclfolder_user` int(10) unsigned DEFAULT NULL,
  `aclfolder_folder` int(10) unsigned DEFAULT NULL,
  `aclfolder_rights` int(1) DEFAULT NULL,
  `expiration_date` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aclfolder`
--

LOCK TABLES `aclfolder` WRITE;
/*!40000 ALTER TABLE `aclfolder` DISABLE KEYS */;
/*!40000 ALTER TABLE `aclfolder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_regions`
--

DROP TABLE IF EXISTS `ad_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `display_type` enum('static','dynamic') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'static',
  `transition_duration` smallint(6) NOT NULL DEFAULT '0',
  `slideshow` tinyint(4) NOT NULL DEFAULT '0',
  `slideshow_interval` smallint(6) NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `transition_type` enum('slide','fade') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'slide',
  `slideshow_continuous` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_regions`
--

LOCK TABLES `ad_regions` WRITE;
/*!40000 ALTER TABLE `ad_regions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address_book`
--

DROP TABLE IF EXISTS `address_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_book` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_to_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `salutation` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `company` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_1` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_2` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_type` enum('','residential','business') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_book`
--

LOCK TABLES `address_book` WRITE;
/*!40000 ALTER TABLE `address_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads`
--

DROP TABLE IF EXISTS `ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ad_region_id` int(10) unsigned NOT NULL DEFAULT '0',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `caption` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ad_region_id` (`ad_region_id`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ads`
--

LOCK TABLES `ads` WRITE;
/*!40000 ALTER TABLE `ads` DISABLE KEYS */;
/*!40000 ALTER TABLE `ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_sign_up_form_pages`
--

DROP TABLE IF EXISTS `affiliate_sign_up_form_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `affiliate_sign_up_form_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `terms_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_sign_up_form_pages`
--

LOCK TABLES `affiliate_sign_up_form_pages` WRITE;
/*!40000 ALTER TABLE `affiliate_sign_up_form_pages` DISABLE KEYS */;
INSERT INTO `affiliate_sign_up_form_pages` VALUES (9,169,182,'Apply Now',180);
/*!40000 ALTER TABLE `affiliate_sign_up_form_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `allow_new_comments_for_items`
--

DROP TABLE IF EXISTS `allow_new_comments_for_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allow_new_comments_for_items` (
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_type` enum('','submitted_form','calendar_event','product_group','product') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `allow_new_comments` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `page_id` (`page_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allow_new_comments_for_items`
--

LOCK TABLES `allow_new_comments_for_items` WRITE;
/*!40000 ALTER TABLE `allow_new_comments_for_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `allow_new_comments_for_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applied_gift_cards`
--

DROP TABLE IF EXISTS `applied_gift_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applied_gift_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `old_balance` int(10) unsigned NOT NULL DEFAULT '0',
  `new_balance` int(10) unsigned NOT NULL DEFAULT '0',
  `authorization_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gift_card_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `givex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `gift_card_id` (`gift_card_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applied_gift_cards`
--

LOCK TABLES `applied_gift_cards` WRITE;
/*!40000 ALTER TABLE `applied_gift_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `applied_gift_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arrival_dates`
--

DROP TABLE IF EXISTS `arrival_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arrival_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `arrival_date` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('enabled','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'enabled',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `default_selected` tinyint(4) NOT NULL DEFAULT '0',
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_maximum_arrival_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arrival_dates`
--

LOCK TABLES `arrival_dates` WRITE;
/*!40000 ALTER TABLE `arrival_dates` DISABLE KEYS */;
INSERT INTO `arrival_dates` VALUES (1,'At Once','We will ship your items as soon as possible.','AT-ONCE','0000-00-00','enabled','2019-01-01','2099-01-01',0,1,0,2,1537472973,'0000-00-00'),(2,'Requested Arrival Date','Select a date if this shipment must arrive by a specific date (e.g. birthday)','SDATE','0000-00-00','enabled','2019-01-01','2099-12-31',0,99,1,2,1537472973,'0000-00-00');
/*!40000 ALTER TABLE `arrival_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_dialogs`
--

DROP TABLE IF EXISTS `auto_dialogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_dialogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `width` int(10) unsigned NOT NULL DEFAULT '0',
  `height` int(10) unsigned NOT NULL DEFAULT '0',
  `delay` int(10) unsigned NOT NULL DEFAULT '0',
  `frequency` int(10) unsigned NOT NULL DEFAULT '0',
  `page` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_dialogs`
--

LOCK TABLES `auto_dialogs` WRITE;
/*!40000 ALTER TABLE `auto_dialogs` DISABLE KEYS */;
INSERT INTO `auto_dialogs` VALUES (2,'EBook Offer',0,'/ebook-offer-dialog',450,550,15,24,'',2,1537472973,40,1548277403),(3,'Ticket Discount',0,'/ticket-discount-dialog',400,600,15,24,'',2,1537472973,40,1548277410);
/*!40000 ALTER TABLE `auto_dialogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banned_ip_addresses`
--

DROP TABLE IF EXISTS `banned_ip_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banned_ip_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banned_ip_addresses`
--

LOCK TABLES `banned_ip_addresses` WRITE;
/*!40000 ALTER TABLE `banned_ip_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `banned_ip_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_information_pages`
--

DROP TABLE IF EXISTS `billing_information_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_information_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `po_number` tinyint(4) NOT NULL DEFAULT '0',
  `custom_field_1_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_1_required` tinyint(4) NOT NULL DEFAULT '0',
  `custom_field_2_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_2_required` tinyint(4) NOT NULL DEFAULT '0',
  `form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_label_column_width` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_information_pages`
--

LOCK TABLES `billing_information_pages` WRITE;
/*!40000 ALTER TABLE `billing_information_pages` DISABLE KEYS */;
INSERT INTO `billing_information_pages` VALUES (3,179,'Continue Checkout',101,1,'',0,'',0,0,'',''),(6,1076,'Continue to Payment',1077,1,'',0,'',0,0,'','');
/*!40000 ALTER TABLE `billing_information_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_exceptions`
--

DROP TABLE IF EXISTS `calendar_event_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_exceptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendar_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `recurrence_number` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `calendar_event_id` (`calendar_event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_exceptions`
--

LOCK TABLES `calendar_event_exceptions` WRITE;
/*!40000 ALTER TABLE `calendar_event_exceptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_event_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_locations`
--

DROP TABLE IF EXISTS `calendar_event_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_locations`
--

LOCK TABLES `calendar_event_locations` WRITE;
/*!40000 ALTER TABLE `calendar_event_locations` DISABLE KEYS */;
INSERT INTO `calendar_event_locations` VALUES (1,'Conference Room',2,1537472973,1,1537472973),(2,'Stadium',2,1537472973,1,1537472973),(3,'Party Room',2,1537472973,1,1537472973),(4,'Outdoor Facility',2,1537472973,1,1537472973),(5,'Auditorium',2,1537472973,1,1537472973);
/*!40000 ALTER TABLE `calendar_event_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_view_pages`
--

DROP TABLE IF EXISTS `calendar_event_view_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_view_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `back_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_view_pages`
--

LOCK TABLES `calendar_event_view_pages` WRITE;
/*!40000 ALTER TABLE `calendar_event_view_pages` DISABLE KEYS */;
INSERT INTO `calendar_event_view_pages` VALUES (1,191,'Back to Calendar',0),(2,286,'Back to Members Calendar',0),(3,298,'Back to Staff Calendar',1),(4,480,'Back to Calendar',0),(5,482,'Back to Schedule Training',0),(6,484,'Back to Classes',1);
/*!40000 ALTER TABLE `calendar_event_view_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_views_calendars_xref`
--

DROP TABLE IF EXISTS `calendar_event_views_calendars_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_views_calendars_xref` (
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `page_id` (`page_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_views_calendars_xref`
--

LOCK TABLES `calendar_event_views_calendars_xref` WRITE;
/*!40000 ALTER TABLE `calendar_event_views_calendars_xref` DISABLE KEYS */;
INSERT INTO `calendar_event_views_calendars_xref` VALUES (191,5),(286,1),(298,2),(191,2),(191,1),(286,3),(480,7),(191,3),(191,6),(482,5),(484,6),(298,1),(298,3),(298,6),(584,2),(584,1),(584,3);
/*!40000 ALTER TABLE `calendar_event_views_calendars_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `short_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `full_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recurrence_number` int(10) unsigned NOT NULL DEFAULT '0',
  `recurrence_type` enum('','day','week','month','year') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `location` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` longtext COLLATE utf8_unicode_ci NOT NULL,
  `reservations` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `separate_reservations` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `limit_reservations` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `number_of_initial_spots` int(10) unsigned NOT NULL DEFAULT '0',
  `reserve_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `no_remaining_spots_message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `all_day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_month_type` enum('day_of_the_month','day_of_the_week') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'day_of_the_month',
  `show_start_time` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `show_end_time` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_sun` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_mon` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_tue` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_wed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_thu` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_fri` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurrence_day_sat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `unpublish_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `published` (`published`),
  KEY `unpublish_days` (`unpublish_days`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events_calendar_event_locations_xref`
--

DROP TABLE IF EXISTS `calendar_events_calendar_event_locations_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events_calendar_event_locations_xref` (
  `calendar_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_event_location_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `calendar_event_id` (`calendar_event_id`),
  KEY `calendar_event_location_id` (`calendar_event_location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events_calendar_event_locations_xref`
--

LOCK TABLES `calendar_events_calendar_event_locations_xref` WRITE;
/*!40000 ALTER TABLE `calendar_events_calendar_event_locations_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events_calendar_event_locations_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events_calendars_xref`
--

DROP TABLE IF EXISTS `calendar_events_calendars_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events_calendars_xref` (
  `calendar_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `calendar_event_id` (`calendar_event_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events_calendars_xref`
--

LOCK TABLES `calendar_events_calendars_xref` WRITE;
/*!40000 ALTER TABLE `calendar_events_calendars_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events_calendars_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_view_pages`
--

DROP TABLE IF EXISTS `calendar_view_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_view_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `default_view` enum('monthly','weekly','upcoming') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'monthly',
  `calendar_event_view_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_upcoming_events` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_view_pages`
--

LOCK TABLES `calendar_view_pages` WRITE;
/*!40000 ALTER TABLE `calendar_view_pages` DISABLE KEYS */;
INSERT INTO `calendar_view_pages` VALUES (4,285,'monthly',286,0),(21,200,'monthly',191,5),(5,297,'monthly',298,0),(26,1045,'upcoming',191,4),(27,1063,'upcoming',480,4),(18,479,'upcoming',480,25),(19,481,'monthly',482,0),(20,483,'monthly',484,0);
/*!40000 ALTER TABLE `calendar_view_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_views_calendars_xref`
--

DROP TABLE IF EXISTS `calendar_views_calendars_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_views_calendars_xref` (
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `page_id` (`page_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_views_calendars_xref`
--

LOCK TABLES `calendar_views_calendars_xref` WRITE;
/*!40000 ALTER TABLE `calendar_views_calendars_xref` DISABLE KEYS */;
INSERT INTO `calendar_views_calendars_xref` VALUES (191,1),(483,6),(1063,7),(297,6),(1045,1),(200,5),(285,3),(481,5),(200,2),(200,3),(479,7),(297,3),(297,1),(297,2),(200,1),(200,6);
/*!40000 ALTER TABLE `calendar_views_calendars_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendars`
--

DROP TABLE IF EXISTS `calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendars`
--

LOCK TABLES `calendars` WRITE;
/*!40000 ALTER TABLE `calendars` DISABLE KEYS */;
INSERT INTO `calendars` VALUES (1,'Main Calendar',56,1537472973,40,1545090406),(2,'Staff Calendar',56,1537472973,2,1537472973),(3,'Members Calendar',1,1537472973,2,1537472973),(5,'Training Calendar',2,1537472973,40,1545081351),(6,'Class Calendar',2,1537472973,2,1537472973),(7,'Ticket Calendar',40,1545090424,40,1545090424);
/*!40000 ALTER TABLE `calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_detail_pages`
--

DROP TABLE IF EXISTS `catalog_detail_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_detail_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `add_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `back_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `allow_customer_to_add_product_to_order` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_detail_pages`
--

LOCK TABLES `catalog_detail_pages` WRITE;
/*!40000 ALTER TABLE `catalog_detail_pages` DISABLE KEYS */;
INSERT INTO `catalog_detail_pages` VALUES (6,986,'Add to Cart','Continue Shopping',248,1),(8,998,'Add to Cart','Back',248,1),(9,1002,'Add to Cart','Back',248,1);
/*!40000 ALTER TABLE `catalog_detail_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_pages`
--

DROP TABLE IF EXISTS `catalog_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_columns` int(10) unsigned NOT NULL DEFAULT '0',
  `image_width` int(10) unsigned NOT NULL DEFAULT '0',
  `image_height` int(10) unsigned NOT NULL DEFAULT '0',
  `back_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `catalog_detail_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `menu` tinyint(4) NOT NULL DEFAULT '0',
  `search` tinyint(4) NOT NULL DEFAULT '0',
  `number_of_featured_items` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `number_of_new_items` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_pages`
--

LOCK TABLES `catalog_pages` WRITE;
/*!40000 ALTER TABLE `catalog_pages` DISABLE KEYS */;
INSERT INTO `catalog_pages` VALUES (14,985,37,1,0,0,'',986,0,0,0,0),(19,999,37,1,0,0,'',1002,0,0,0,0),(18,997,37,1,0,0,'',998,0,0,0,0),(20,1000,37,1,0,0,'',1002,0,0,0,0),(21,1001,37,1,0,0,'',1002,0,0,0,0);
/*!40000 ALTER TABLE `catalog_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_type` enum('','submitted_form','calendar_event','product_group','product') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `publish_date_and_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_cancel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `item_id` (`item_id`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`),
  KEY `file_id` (`file_id`),
  KEY `published` (`published`),
  KEY `publish_date_and_time` (`publish_date_and_time`),
  KEY `publish_cancel` (`publish_cancel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('pending','payable','ineligible','paid') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `recurring_commission_profile_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  KEY `affiliate_code` (`affiliate_code`),
  KEY `order_id` (`order_id`),
  KEY `recurring_commission_profile_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commissions`
--

LOCK TABLES `commissions` WRITE;
/*!40000 ALTER TABLE `commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `url_scheme` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `forgot_password_link` tinyint(4) DEFAULT NULL,
  `organization_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_address_1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_address_2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_state` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_zip_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registration_email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_payment_gateway` enum('','Authorize.Net','ClearCommerce','First Data Global Gateway','PayPal Payflow Pro','PayPal Payments Pro','Sage','Stripe') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payflow_pro_merchant_login` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payflow_pro_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `debug` tinyint(4) DEFAULT NULL,
  `ecommerce_payment_gateway_mode` enum('test','live') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'live',
  `page_editor_font` tinyint(4) NOT NULL DEFAULT '1',
  `page_editor_font_size` tinyint(4) NOT NULL DEFAULT '1',
  `page_editor_font_style` tinyint(4) NOT NULL DEFAULT '1',
  `page_editor_font_color` tinyint(4) NOT NULL DEFAULT '1',
  `page_editor_background_color` tinyint(4) NOT NULL DEFAULT '1',
  `membership_email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_id_label` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'Member ID',
  `ecommerce_shipping` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_recipient_mode` enum('single recipient','multi-recipient') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'single recipient',
  `ecommerce_tax` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_tax_exempt` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_product_restriction_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_end_of_day_time` time NOT NULL DEFAULT '17:00:00',
  `ecommerce_no_shipping_methods_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `opt_in_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_tax_exempt_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Tax-Exempt?',
  `ecommerce_american_express` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_diners_club` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_discover_card` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_mastercard` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_visa` tinyint(4) NOT NULL DEFAULT '1',
  `affiliate_program` tinyint(4) NOT NULL DEFAULT '1',
  `affiliate_default_commission_rate` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `affiliate_automatic_approval` tinyint(4) NOT NULL DEFAULT '1',
  `affiliate_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce` tinyint(4) NOT NULL DEFAULT '1',
  `forms` tinyint(4) NOT NULL DEFAULT '1',
  `visitor_tracking` tinyint(4) NOT NULL DEFAULT '1',
  `pay_per_click_flag` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registration_contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `membership_contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `affiliate_contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_paypal_payflow_pro_partner` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payflow_pro_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `calendars` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_authorizenet_api_login_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_authorizenet_transaction_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `proxy_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password_hint` tinyint(4) NOT NULL DEFAULT '0',
  `mass_deletion` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_multicurrency` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_clearcommerce_client_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_clearcommerce_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_clearcommerce_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_payment_gateway_transaction_type` enum('Authorize','Authorize & Capture') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Authorize & Capture',
  `ecommerce_credit_debit_card` tinyint(4) NOT NULL DEFAULT '1',
  `ecommerce_paypal_express_checkout` tinyint(4) NOT NULL DEFAULT '0',
  `ecommerce_paypal_express_checkout_transaction_type` enum('Authorize','Authorize & Capture') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Authorize & Capture',
  `ecommerce_paypal_express_checkout_mode` enum('sandbox','live') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'live',
  `ecommerce_paypal_express_checkout_api_username` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_express_checkout_api_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_express_checkout_api_signature` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_first_data_global_gateway_store_number` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_first_data_global_gateway_pem_file_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_retrieve_order_next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `membership_expiration_warning_email` tinyint(4) NOT NULL DEFAULT '0',
  `membership_expiration_warning_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `membership_expiration_warning_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `membership_expiration_warning_email_days_before_expiration` smallint(6) NOT NULL DEFAULT '0',
  `hostname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payments_pro_api_username` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payments_pro_api_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_paypal_payments_pro_api_signature` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `remember_me` tinyint(4) NOT NULL DEFAULT '0',
  `strong_password` tinyint(4) NOT NULL DEFAULT '0',
  `ecommerce_address_verification` tinyint(4) NOT NULL DEFAULT '0',
  `usps_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_gift_card` tinyint(4) NOT NULL DEFAULT '0',
  `ecommerce_givex_primary_hostname` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_givex_secondary_hostname` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_givex_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_givex_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number_of_queries` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_offline_payment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_offline_payment_only_specific_orders` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `captcha` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subscription_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `powered_by_link` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `private_label` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_software_update_check_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `software_update_available` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `page_editor_version` enum('latest','previous') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'latest',
  `social_networking_facebook` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `social_networking_twitter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `social_networking_addthis` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `special_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_email_recipients` int(10) unsigned NOT NULL DEFAULT '0',
  `google_analytics` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `google_analytics_web_property_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `whos_online` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `whos_online_server_url` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `whos_online_group_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `whos_online_chat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `whos_online_last_chat_check_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `whos_online_chat_button_online_file_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `whos_online_chat_button_offline_file_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `badge_label` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_sitemap_check_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_sitemap_check_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `additional_sitemap_content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `additional_robots_content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `affiliate_group_offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_recurring_commission_check_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program_points` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program_membership` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program_membership_days` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_reward_program_email_bcc_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_reward_program_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_reward_program_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ecommerce_sage_merchant_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_sage_merchant_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `social_networking_plusone` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `social_networking_linkedin` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `social_networking` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `social_networking_type` enum('simple','advanced') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'simple',
  `social_networking_code` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `installer` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_custom_product_field_1_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_custom_product_field_2_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `search_type` enum('simple','advanced') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'simple',
  `ecommerce_custom_product_field_3_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_custom_product_field_4_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `plain_text_email_campaign_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ecommerce_address_verification_enforcement_type` enum('warning','error') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'warning',
  `mobile` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_format` enum('month_day','day_month') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'month_day',
  `ecommerce_givex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_surcharge_percentage` decimal(6,3) unsigned NOT NULL DEFAULT '0.000',
  `ads` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_gift_card_validity_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `auto_dialogs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ecommerce_stripe_api_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ecommerce_private_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `unpublish_event_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `ups_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ups_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ups_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ups_account` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `license_check_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `tracking_code_duration` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ups` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `fedex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `fedex_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fedex_password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fedex_account` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fedex_meter` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mailchimp` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mailchimp_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mailchimp_list_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mailchimp_store_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mailchimp_sync_running` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mailchimp_sync_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mailchimp_sync_limit` int(10) unsigned NOT NULL DEFAULT '0',
  `mailchimp_automation` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('http://','example@example.com','http://www.google.com/analytics','liveSite','This is the default website description that is set in the Site Settings.','default, keywords, from, site settings',1,'Our Organization','1234 Any Street','','Anytown','Any State','99999','USA','example@example.com','','','','',1,'live',0,0,1,0,0,'','Member ID',1,'multi-recipient',1,1,'We\'re sorry.  The item you have selected cannot be shipped to the desination you selected.  You will need to remove the item to complete your order.','17:00:00','We\'re sorry.  We currently do not have a way to ship this item to the destination you requested.  You will need to remove the item to complete your order.','Send me offers','Check this box if you are exempt from sales tax.',1,1,1,1,1,1,'30.00',0,'example@example.com',1,1,1,'ppc_',1,3,4,'','',1,'admin','00admin5','',1,0,0,'','','','Authorize & Capture',1,0,'Authorize & Capture','sandbox','','','','','',256,'2019.1',0,'Your membership will expire on',0,7,'www.example.com','','','',1,0,0,'',1,'','','','',0,1,1,1,'',0,0,0,0,'latest',0,0,0,0,0,0,'',0,'','',0,0,'default','default','VIP',0,'','','',7,0,0,0,0,0,0,'','',0,'','',0,0,'/',1,'advanced','<!-- register for a addthis.com account and change the pubid -->\r\n<script type=\"text/javascript\" src=\"//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-577d4f644c7cc90c\"></script>\r\n\r\n','','Inventory Location','','advanced','','','','warning',0,'US/Central','month_day',0,'0.000',0,0,1,'',284,0,'','','','',0,0,0,30,0,0,'','','','',0,'','','',0,1095,200,0);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_groups`
--

DROP TABLE IF EXISTS `contact_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email_subscription` tinyint(4) NOT NULL DEFAULT '0',
  `email_subscription_type` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_groups`
--

LOCK TABLES `contact_groups` WRITE;
/*!40000 ALTER TABLE `contact_groups` DISABLE KEYS */;
INSERT INTO `contact_groups` VALUES (1,'Registered Guests','Anyone who successfully registers as a guest will be added to this contact group automatically.',0,'closed',0,1537472973,2,1537472973),(3,'Registered Members','Anyone that register successfully as a member is added to this contact group automatically.',0,'closed',0,1537472973,2,1537472973),(4,'Affiliate Application','Anoyone that is approved as an Affiliate will be automatically added to this contact group.',0,'closed',0,1537472973,2,1537472973),(13,'Survey','Anyone that completes the Survey Form will be added to this contact group automatically.',0,'closed',1,1537472973,2,1537472973),(6,'Registration - Participant','Anyone that signs up for the example event will be added to this group automatically.',0,'closed',0,1537472973,2,1537472973),(38,'Ice Time','',0,'open',2,1537472973,2,1537472973),(39,'Marketing 101 Class','',0,'open',2,1537472973,2,1537472973),(40,'Registration - Exhibitor','',0,'open',2,1537472973,2,1537472973),(41,'Service Plan - Annually','',0,'open',2,1537472973,2,1537472973),(8,'Topic 2','I would like to receive all e-mails sent to this mail list.',1,'open',56,1537472973,1,1537472973),(12,'Topic 4','I would like to receive all e-mails sent to this mail list.',1,'open',1,1537472973,1,1537472973),(9,'Topic 3','I would like to receive all e-mails sent to this mail list.',1,'open',56,1537472973,1,1537472973),(11,'Topic 1','I would like to receive all e-mails sent to this mail list.',1,'open',56,1537472973,1,1537472973),(37,'Blog','',0,'open',2,1537472973,2,1537472973),(15,'Member Directory','When a member submits the member directory entry form, they are automatically added to this contact group.',0,'closed',1,1537472973,2,1537472973),(16,'Monthly Donor','Anyone who purchases the recurring monthly donation will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(42,'Client','',0,'open',2,1537472973,2,1537472973),(43,'Membership Dues','',0,'open',2,1537472973,2,1537472973),(18,'One-time Donor','Anyone who makes a non-recurring one-time donation will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(44,'Classified Ad','',0,'open',2,1537472973,2,1537472973),(45,'Staff Directory','',0,'open',2,1537472973,2,1537472973),(20,'Membership Access','Anyone who purchases membership access will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(21,'Orders','',0,'open',0,1537472973,0,1537472973),(22,'Software','Anyone who orders the download #1 will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(23,'eBook','Anyone who orders the download #2 will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(24,'Service Plan - Monthly','Anyone who purchases one of the recurring service plans will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(25,'Account Payment','Anyone that makes a payment on their account will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(26,'Free Product','Anyone that orders the free product is added to this contact group.',0,'closed',1,1537472973,2,1537472973),(27,'Exam','When a user submits the certification quiz form successfully, they are automatically added to this contact group.',0,'closed',1,1537472973,40,1541186018),(28,'Office Chair','Anyone who purchases an office chair will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(29,'Leather Pen Case','Anyone who recievesd a Leather Pen Case (free or purchased) will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(30,'Chocolate Basket','Anyone who purchases a Chocolate Basket will be added to this contact group.',0,'closed',1,1537472973,2,1537472973),(34,'Concert Ticket','',0,'open',2,1537472973,2,1537472973),(32,'Forum','',0,'open',2,1537472973,2,1537472973),(33,'Support Ticket','',0,'open',2,1537472973,2,1537472973),(35,'Mailing List','This is our general mailing list.',1,'open',2,1537472973,2,1537472973),(46,'Contact Form','',0,'open',2,1537472973,2,1537472973),(47,'Membership Trial','',0,'open',2,1537472973,2,1537472973),(48,'Event Ticket Wait List','',0,'open',2,1537472973,2,1537472973),(49,'Services Project','',0,'open',40,1542672705,40,1542672705),(50,'Coming Soon','',0,'open',40,1546295355,40,1546295387);
/*!40000 ALTER TABLE `contact_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_groups_email_campaigns_xref`
--

DROP TABLE IF EXISTS `contact_groups_email_campaigns_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_groups_email_campaigns_xref` (
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email_campaign_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('included','excluded') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'included',
  KEY `contact_group_id` (`contact_group_id`),
  KEY `email_campaign_id` (`email_campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_groups_email_campaigns_xref`
--

LOCK TABLES `contact_groups_email_campaigns_xref` WRITE;
/*!40000 ALTER TABLE `contact_groups_email_campaigns_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_groups_email_campaigns_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nickname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `office_location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_address_1` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_address_2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_zip_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_address_1` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_address_2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_zip_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lead_source` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `member_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expiration_date` date NOT NULL DEFAULT '0000-00-00',
  `salutation` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `suffix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `affiliate_approved` tinyint(4) NOT NULL DEFAULT '0',
  `affiliate_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_commission_rate` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `opt_in` tinyint(4) NOT NULL DEFAULT '1',
  `warning_expiration_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `affiliate_code` (`affiliate_code`),
  KEY `email_address` (`email_address`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_contact_groups_xref`
--

DROP TABLE IF EXISTS `contacts_contact_groups_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_contact_groups_xref` (
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `contact_id` (`contact_id`),
  KEY `contact_group_id` (`contact_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_contact_groups_xref`
--

LOCK TABLES `contacts_contact_groups_xref` WRITE;
/*!40000 ALTER TABLE `contacts_contact_groups_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_contact_groups_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `containers`
--

DROP TABLE IF EXISTS `containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `containers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `length` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `width` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `height` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `weight` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `cost` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `containers`
--

LOCK TABLES `containers` WRITE;
/*!40000 ALTER TABLE `containers` DISABLE KEYS */;
/*!40000 ALTER TABLE `containers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cookies`
--

DROP TABLE IF EXISTS `cookies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cookies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lsid` char(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_source` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_medium` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_campaign` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_term` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lsid` (`lsid`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `modified_timestamp` (`modified_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookies`
--

LOCK TABLES `cookies` WRITE;
/*!40000 ALTER TABLE `cookies` DISABLE KEYS */;
/*!40000 ALTER TABLE `cookies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `transit_adjustment_days` int(10) unsigned NOT NULL DEFAULT '0',
  `default_selected` tinyint(4) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `zip_code_required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `default_selected` (`default_selected`)
) ENGINE=MyISAM AUTO_INCREMENT=293 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (5,'Afghanistan','AF',95,0,48,1537472973,0),(6,'Albania','AL',85,0,48,1537472973,0),(7,'Algeria','DZ',95,0,48,1537472973,1),(8,'Andorra','AD',75,0,48,1537472973,0),(9,'Angola','AO',85,0,48,1537472973,0),(10,'Anguilla','AI',65,0,48,1537472973,0),(11,'Antigua','AG',45,0,48,1537472973,0),(12,'Argentina','AR',95,0,48,1537472973,1),(13,'Armenia','AM',65,0,48,1537472973,1),(14,'Aruba','AW',45,0,48,1537472973,0),(285,'San Marino','SM',55,0,48,1537472973,0),(16,'Australia','AU',75,0,48,1537472973,1),(17,'Austria','AT',85,0,48,1537472973,1),(18,'Azerbaijan','AZ',65,0,48,1537472973,1),(284,'Samoa','WS',55,0,48,1537472973,0),(20,'Bahamas','BS',45,0,48,1537472973,0),(21,'Bahrain','BH',95,0,48,1537472973,0),(22,'Bangladesh','BD',95,0,48,1537472973,1),(23,'Barbados','BB',45,0,48,1537472973,0),(25,'Belgium','BE',65,0,48,1537472973,1),(26,'Belize','BZ',65,0,48,1537472973,0),(27,'Benin','BJ',85,0,48,1537472973,0),(28,'Bermuda','BM',45,0,48,1537472973,0),(29,'Bhutan','BT',95,0,48,1537472973,0),(30,'Bolivia','BO',85,0,48,1537472973,0),(31,'Bosnia-Herzegovina','BA',85,0,48,1537472973,1),(32,'Botswana','BW',95,0,48,1537472973,0),(33,'Brazil','BR',95,0,48,1537472973,1),(34,'Brunei Darussalam','BN',95,0,48,1537472973,1),(35,'Bulgaria','BG',75,0,48,1537472973,1),(36,'Burkina Faso','BF',95,0,48,1537472973,0),(283,'Puerto Rico','PR',55,0,48,1537472973,1),(38,'Burundi','BI',95,0,48,1537472973,0),(39,'Belarus','BY',65,0,48,1537472973,1),(271,'Guam','GU',55,0,48,1537472973,1),(41,'Cameroon','CM',95,0,48,1537472973,0),(42,'Canada','CA',35,0,48,1537472973,1),(44,'Cape Verde Islands','CV',75,0,48,1537472973,0),(45,'Cayman Islands','KY',45,0,48,1537472973,0),(46,'Central African Rep','CF',45,0,48,1537472973,0),(282,'Palestinian Territory (PS)','PS',55,0,2,1537472973,0),(48,'Chile','CL',85,0,48,1537472973,0),(49,'China','CN',75,0,48,1537472973,1),(50,'Colombia','CO',95,0,48,1537472973,0),(51,'Comoros','KM',0,0,48,1537472973,0),(52,'Congo','CG',95,0,48,1537472973,0),(281,'Palau','PW',55,0,48,1537472973,0),(55,'Costa Rica','CR',65,0,48,1537472973,0),(56,'Cote d\'Ivoire','CI',0,0,48,1537472973,0),(57,'Croatia','HR',85,0,48,1537472973,1),(59,'Cyprus','CY',75,0,48,1537472973,1),(60,'Czech Republic','CZ',75,0,48,1537472973,1),(279,'Norfolk Island','NF',55,0,48,1537472973,0),(278,'Niue','NU',55,0,48,1537472973,0),(63,'Denmark','DK',65,0,48,1537472973,1),(65,'Djibouti','DJ',95,0,48,1537472973,0),(66,'Dominica','DM',65,0,48,1537472973,0),(67,'Dominican Republic','DO',45,0,48,1537472973,0),(68,'Ecuador','EC',85,0,48,1537472973,0),(69,'Egypt','EG',95,0,48,1537472973,0),(71,'El Salvador','SV',65,0,48,1537472973,0),(276,'Mayotte','YT',55,0,48,1537472973,1),(277,'Micronesia (FM)','FM',55,0,2,1537472973,0),(74,'Eritrea','ER',95,0,48,1537472973,0),(75,'Estonia','EE',75,0,48,1537472973,1),(76,'Ethiopia','ET',95,0,48,1537472973,0),(77,'Falkland Islands','FK',75,0,48,1537472973,0),(78,'Faroe Islands','FO',85,0,48,1537472973,1),(79,'Fiji','FJ',75,0,48,1537472973,0),(80,'Finland','FI',65,0,48,1537472973,1),(280,'Northern Marina Islands','MP',55,0,48,1537472973,0),(82,'France','FR',75,0,48,1537472973,1),(275,'Marshall Islands','MH',55,0,48,1537472973,1),(84,'French Guiana','GF',75,0,48,1537472973,0),(85,'French Polynesia','PF',75,0,48,1537472973,0),(86,'Gabon','GA',95,0,48,1537472973,0),(87,'Gambia','GM',75,0,48,1537472973,0),(88,'Georgia','GE',65,0,48,1537472973,1),(89,'Germany','DE',55,0,48,1537472973,1),(90,'Ghana','GH',95,0,48,1537472973,0),(91,'Gibraltar','GI',65,0,48,1537472973,0),(270,'French Territories (TF)','TF',55,0,2,1537472973,0),(93,'Greece','GR',95,0,48,1537472973,1),(94,'Greenland','GL',35,0,48,1537472973,1),(95,'Grenada','GD',65,0,48,1537472973,0),(97,'Guadeloupe','GP',75,0,48,1537472973,0),(98,'Guatemala','GT',65,0,48,1537472973,0),(99,'Guinea-Bissau','GW',75,0,48,1537472973,0),(100,'Guinea','GN',75,0,48,1537472973,0),(101,'Guyana','GY',75,0,48,1537472973,0),(102,'Haiti','HT',45,0,48,1537472973,0),(104,'Honduras','HN',65,0,48,1537472973,0),(105,'Hong Kong','HK',55,0,48,1537472973,0),(106,'Hungary','HU',75,0,48,1537472973,1),(107,'Iceland','IS',45,0,48,1537472973,0),(108,'India','IN',95,0,48,1537472973,1),(109,'Indonesia','ID',85,0,48,1537472973,1),(110,'Iran','IR',95,0,48,1537472973,0),(111,'Iraq','IQ',95,0,48,1537472973,0),(112,'Ireland','IE',65,0,48,1537472973,0),(113,'Israel','IL',95,0,48,1537472973,1),(114,'Italy','IT',85,0,48,1537472973,1),(116,'Jamaica','JM',45,0,48,1537472973,0),(117,'Japan','JP',45,0,48,1537472973,1),(118,'Jordan','JO',95,0,48,1537472973,0),(119,'Kazakhstan','KZ',65,0,48,1537472973,1),(120,'Kenya','KE',99,0,48,1537472973,0),(121,'Kiribati','KI',75,0,48,1537472973,0),(122,'Korea, South','KR',75,0,2,1537472973,1),(123,'Kuwait','KW',95,0,48,1537472973,0),(124,'Kyrgystan','KG',65,0,48,1537472973,1),(125,'Lao','LA',75,0,2,1537472973,0),(126,'Latvia','LV',75,0,48,1537472973,1),(127,'Lebanon','LB',75,0,48,1537472973,0),(274,'Korea, North','KP',55,0,2,1537472973,0),(269,'Equatorial Guinea','GQ',55,0,48,1537472973,0),(130,'Lesotho','LS',95,0,48,1537472973,0),(131,'Liberia','LR',95,0,48,1537472973,0),(132,'Libyan Arab Jamahiriya','LY',95,0,48,1537472973,0),(133,'Liechtenstein','LI',55,0,48,1537472973,1),(134,'Lithuania','LT',75,0,48,1537472973,1),(135,'Luxembourg','LU',55,0,48,1537472973,1),(136,'Macao','MO',75,0,48,1537472973,0),(137,'Macedonia','MK',85,0,2,1537472973,1),(138,'Madagascar','MG',95,0,48,1537472973,1),(272,'Heard McDonald Is. (HM)','HM',55,0,2,1537472973,0),(140,'Malawi','MW',95,0,48,1537472973,0),(141,'Malaysia','MY',85,0,48,1537472973,1),(142,'Maldives','MV',75,0,48,1537472973,0),(143,'Mali','ML',95,0,48,1537472973,0),(144,'Malta','MT',75,0,48,1537472973,0),(145,'Martinique','MQ',65,0,48,1537472973,1),(146,'Mauritania','MR',75,0,48,1537472973,0),(147,'Mauritius','MU',75,0,48,1537472973,0),(148,'Mexico','MX',40,0,48,1537472973,1),(150,'Moldova, Republic of','MD',65,0,48,1537472973,0),(151,'Monaco','MC',75,0,48,1537472973,0),(152,'Mongolia','MN',99,0,48,1537472973,1),(273,'Holy See (Vatican City)','VA',55,0,2,1537472973,1),(154,'Montserrat','MS',65,0,48,1537472973,0),(155,'Morocco','MA',75,0,48,1537472973,0),(156,'Mozambique','MZ',95,0,48,1537472973,0),(157,'Myanmar','MM',75,0,48,1537472973,0),(158,'Namibia','NA',85,0,48,1537472973,0),(159,'Nauru','NR',95,0,48,1537472973,0),(160,'Nepal','NP',95,0,48,1537472973,0),(161,'Netherlands Antilles','AN',65,0,48,1537472973,0),(163,'Netherlands','NL',65,0,48,1537472973,1),(268,'Cuba','CU',55,0,48,1537472973,0),(165,'New Caledonia','NC',85,0,48,1537472973,0),(166,'New Zealand','NZ',75,0,48,1537472973,1),(167,'Nicaragua','NI',65,0,48,1537472973,0),(168,'Niger','NE',95,0,48,1537472973,0),(169,'Nigeria','NG',85,0,48,1537472973,0),(170,'Norway','NO',65,0,48,1537472973,1),(171,'Oman','OM',95,0,48,1537472973,0),(172,'Pakistan','PK',95,0,48,1537472973,1),(173,'Panama','PA',55,0,48,1537472973,0),(174,'Papua New Guinea','PG',95,0,48,1537472973,0),(175,'Paraguay','PY',75,0,48,1537472973,0),(176,'Peru','PE',75,0,48,1537472973,0),(177,'Philippines','PH',95,0,48,1537472973,1),(178,'Pitcairn','PN',75,0,48,1537472973,0),(179,'Poland','PL',75,0,48,1537472973,1),(180,'Portugal','PT',85,0,48,1537472973,1),(267,'Cook Islands','CK',55,0,48,1537472973,0),(183,'Qatar','QA',85,0,48,1537472973,0),(184,'Reunion','RE',75,0,48,1537472973,1),(185,'Romania','RO',95,0,48,1537472973,0),(186,'Russian Federation','RU',65,0,48,1537472973,1),(187,'Rwanda','RW',95,0,48,1537472973,0),(266,'Congo (CD)','CD',55,0,2,1537472973,0),(190,'Sao Tome and Principe','ST',95,0,48,1537472973,0),(191,'Saudi Arabia','SA',95,0,48,1537472973,1),(265,'Cocos Islands (CC)','CC',55,0,2,1537472973,0),(193,'Senegal','SN',95,0,48,1537472973,0),(194,'Serbia and Montenegro','CS',85,0,48,1537472973,1),(195,'Seychelles','SC',95,0,48,1537472973,0),(196,'Sierra Leone','SL',85,0,48,1537472973,0),(197,'Singapore','SG',75,0,48,1537472973,1),(198,'Slovakia','SK',75,0,48,1537472973,1),(199,'Slovenia','SI',85,0,48,1537472973,1),(200,'Solomon Islands','SB',65,0,48,1537472973,0),(201,'Somalia','SO',95,0,48,1537472973,0),(264,'Christmas Island','CX',55,0,48,1537472973,0),(203,'South Africa','ZA',85,0,48,1537472973,1),(204,'Spain','ES',95,0,48,1537472973,1),(263,'Chad','TD',55,0,48,1537472973,0),(206,'Sri Lanka','LK',95,0,48,1537472973,1),(262,'Cambodia','KH',55,0,48,1537472973,0),(208,'Saint Helena','SH',65,0,48,1537472973,0),(209,'Saint Lucia','LC',65,0,48,1537472973,0),(261,'British Territory (IO)','IO',55,0,2,1537472973,0),(212,'Saint Pierre Miquelon','PM',35,0,2,1537472973,0),(213,'Saint Vincent (VC)','VC',65,0,2,1537472973,0),(214,'Saint Kitts and Nevis','KN',65,0,48,1537472973,0),(215,'Sudan','SD',95,0,48,1537472973,0),(216,'Suriname','SR',85,0,48,1537472973,0),(217,'Swaziland','SZ',95,0,48,1537472973,0),(218,'Sweden','SE',75,0,48,1537472973,1),(219,'Switzerland','CH',55,0,48,1537472973,1),(220,'Syrian Arab Republic','SY',95,0,48,1537472973,0),(222,'Taiwan','TW',75,0,2,1537472973,1),(223,'Tajikistan','TJ',65,0,48,1537472973,1),(224,'Tanzania','TZ',75,0,2,1537472973,0),(225,'Thailand','TH',75,0,48,1537472973,1),(260,'Bouvet Island','BV',55,0,48,1537472973,0),(227,'Togo','TG',85,0,48,1537472973,0),(228,'Tonga','TO',65,0,48,1537472973,0),(229,'Trinidad and Tobago','TT',55,0,48,1537472973,0),(259,'Antarctica','AQ',55,0,48,1537472973,0),(232,'Tunisia','TN',95,0,48,1537472973,1),(233,'Turkey','TR',95,0,48,1537472973,1),(234,'Turkmenistan','TM',65,0,48,1537472973,1),(235,'Turks and Calicos Is.','TC',55,0,2,1537472973,0),(236,'Tuvalu','TV',85,0,48,1537472973,0),(237,'Uganda','UG',95,0,48,1537472973,0),(238,'Ukraine','UA',65,0,48,1537472973,1),(239,'United Arab Emirates','AE',95,0,48,1537472973,0),(240,'United Kingdom','GB',55,0,48,1537472973,1),(241,'United States','US',0,1,48,1537472973,1),(258,'American Samoa','AS',55,0,48,1537472973,0),(243,'Uruguay','UY',85,0,48,1537472973,1),(244,'Uzbekistan','UZ',65,0,48,1537472973,1),(245,'Vanuatu','VU',85,0,48,1537472973,0),(246,'Vatican City','VA',75,0,48,1537472973,1),(247,'Venezuela','VE',85,0,48,1537472973,0),(248,'Vietnam','VN',75,0,48,1537472973,1),(249,'Virgin Islands, British','VG',45,0,48,1537472973,0),(250,'Western Samoa','EH',65,0,48,1537472973,0),(252,'Yemen','YE',95,0,48,1537472973,0),(257,'Aland Islands','AX',55,0,48,1537472973,0),(254,'Zaire','CD',95,0,48,1537472973,0),(255,'Zambia','ZM',95,0,48,1537472973,0),(256,'Zimbabwe','ZW',75,0,48,1537472973,0),(286,'South Georgia (GS)','GS',55,0,2,1537472973,0),(287,'Svalbard and Jan Mayen','SJ',55,0,48,1537472973,0),(288,'Timor-Leste','TL',55,0,48,1537472973,0),(289,'Tokelau','TK',55,0,48,1537472973,0),(290,'United States Minor (UM)','UM',55,0,2,1537472973,0),(291,'Virgin Islands, U.S.','VI',55,0,48,1537472973,1),(292,'Wallis and Futuna','WF',55,0,48,1537472973,0);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cregion`
--

DROP TABLE IF EXISTS `cregion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cregion` (
  `cregion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cregion_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cregion_content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `cregion_designer_type` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `cregion_user` int(10) unsigned NOT NULL DEFAULT '0',
  `cregion_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cregion_id`),
  KEY `cregion_name` (`cregion_name`),
  KEY `cregion_timestamp` (`cregion_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=377 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cregion`
--

LOCK TABLES `cregion` WRITE;
/*!40000 ALTER TABLE `cregion` DISABLE KEYS */;
INSERT INTO `cregion` VALUES (219,'progress-bar-1','<div class=\"progress-bars\">\r\n    <div class=\"progress progress-1\">\r\n        <div class=\"progress-bar\" data-progress=\"90\">\r\n            <span class=\"title\">Schools & Churches</span>\r\n        </div>\r\n    </div>\r\n    <div class=\"progress progress-1\">\r\n        <div class=\"progress-bar\" data-progress=\"70\">\r\n            <span class=\"title\">Non-Profit Organizations</span>\r\n        </div>\r\n    </div>\r\n    <div class=\"progress progress-1\">\r\n        <div class=\"progress-bar\" data-progress=\"50\">\r\n            <span class=\"title\">Professional Services</span>\r\n        </div>\r\n    </div>\r\n    <div class=\"progress progress-1\">\r\n        <div class=\"progress-bar\" data-progress=\"40\">\r\n            <span class=\"title\">e-Commerce Stores</span>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1548294842),(77,'staff-sidebar','<div class=\"widget\">\n<h6 class=\"title\">Staff Links</h6>\n\n<hr />\n<ul class=\"link-list\">\n	<li><a href=\"{path}staff-latest-activity\">Latest Site Activity</a></li>\n	<li><a href=\"{path}staff-calendar\">Staff Calendar</a></li>\n	<li><a href=\"{path}all-staff-directory\">All Staff Directory</a></li>\n	<li><a href=\"{path}all-conversations\">All Conversations</a></li>\n	<li><a href=\"{path}all-support-tickets\">All Support Tickets</a></li>\n	<li><a href=\"{path}all-services-projects\">All Service Projects</a></li>\n</ul>\n\n<hr />\n<ul class=\"link-list\">\n	<li><a href=\"{path}new-conversation?connect_to_contact=false\" title=\"This customized link allows you to submit the form on behalf of someone else.\">New Conversation (for another)</a></li>\n	<li><a href=\"{path}new-support-ticket?connect_to_contact=false\" title=\"This customized link allows you to submit the form on behalf of someone else.\">New Support Ticket (for another)</a></li>\n	<li><a href=\"{path}new-services-project?connect_to_contact=false\" title=\"This customized link allows you to submit the form on behalf of someone else.\">New Services Project (for another)</a></li>\n	<li><a href=\"{path}livesite/view_calendars.php\">Add Calendar Event</a></li>\n	<li><a href=\"{path}staff-directory-form\">Add Staff Directory</a></li>\n	<li><a href=\"{path}blog-form\">Add Blog Post</a></li>\n	<li><a href=\"{path}video-gallery-form\">Add Video</a></li>\n	<li><a href=\"{path}news-1-select\">Send News</a></li>\n	<li><a href=\"{path}staff-create-quote\">Create Quote</a></li>\n</ul>\n<a class=\"btn btn-primary\" href=\"livesite/do.php?action=reset_order&amp;url=/staff-create-quote\">New Cart</a></div>','no',40,1548294080),(207,'more-account-links','<ul class=\"link-list\">\n	<li><a href=\"{path}contact-my-conversations\">My Conversations</a></li>\n	<li>Take Survey</li>\n</ul>','no',40,1542814200),(218,'button-tabs-vertical','<div class=\"tabbed-content button-tabs vertical\">\r\n    <ul class=\"tabs\">\r\n        <li class=\"active\">\r\n            <div class=\"tab-title\">\r\n                <span>History</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <h5 class=\"uppercase\">Let\'s Talk Tabs</h5>\r\n                <hr>\r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.\r\n                </p>\r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Approach</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <h5 class=\"uppercase\">Cool Tabs</h5>\r\n                <hr>\r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Culture</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <h5 class=\"uppercase\">Shorter Tabs</h5>\r\n                <hr>\r\n                <p>\r\n                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Method</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <h5 class=\"uppercase\">Longer Tabs</h5>\r\n                <hr>\r\n                <p>\r\n                    Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n                <p>\r\n                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est.\r\n                </p>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1545935236),(216,'icon-tabs','<div class=\"tabbed-content icon-tabs\">\r\n    <ul class=\"tabs\">\r\n        <li class=\"active\">\r\n            <div class=\"tab-title\">\r\n                <i class=\"ti-layers icon\"></i>\r\n                <span>History</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <i class=\"ti-package icon\"></i>\r\n                <span>Approach</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <i class=\"ti-stats-up icon\"></i>\r\n                <span>Culture</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <i class=\"ti-layout-media-center-alt icon\"></i>\r\n                <span>Method</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.\r\n                </p>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1545935015),(217,'text-tabs','<div class=\"tabbed-content text-tabs\">\r\n    <ul class=\"tabs\">\r\n        <li class=\"active\">\r\n            <div class=\"tab-title\">\r\n                <span>History</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Approach</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Culture</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Method</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.\r\n                </p>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n</div>\r\n<!--end of text tabs-->','yes',40,1545935103),(297,'home-3-image-right-strip-content','<h3>Speed &amp; Security</h3>\n\n<p class=\"mb0\">liveSite is designed around data security from the ground up, so not only are your transactions encrypted, but your content and login transmissions are too.</p>\n\n<p class=\"mb0\">&nbsp;</p>\n\n<p class=\"mb0\">And if you need reliable and fast liveSite hosting, we have developed our own server-level caching technology that optimizes liveSite software execution and database calls, resulting in super-fast performance.<a href=\"https://livesite.com\"> Check us out</a>.</p>','no',40,1548292385),(275,'home-3-image-right-strip','<section class=\"image-square right bg-secondary pb-sm-64\">\n    <div class=\"col-md-6 image\">\n        <div class=\"background-image-holder\">\n            <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-1-photo-5.jpg\" />\n        </div>\n    </div>\n    <div class=\"col-md-6 content\">\n		<cregion>home-3-image-right-strip-content</cregion>\n    </div>\n</section>','yes',40,1546471014),(277,'home-4-intro','<section class=\"pt240 pb240 parallax image-bg overlay bg-light\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover24.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <img alt=\"Pic\" src=\"{path}logo-dark.png\" class=\"image-small\" />\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class=\"align-bottom text-center\">\r\n        <ul class=\"list-inline social-list mb24\">\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-twitter-alt\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-facebook\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-youtube\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-vimeo-alt\"></i>\r\n                </a>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n</section>','yes',40,1546448552),(278,'home-4-masonry-tiles','<section class=\"pb8\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-8 col-md-offset-2 col-sm-12 text-center\">\n				<cregion>home-4-masonry-tiles-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>\n<section class=\"projects\">\n    <div class=\"container\">\n        <div class=\"masonry-loader\">\n            <div class=\"col-sm-12 text-center\">\n                <div class=\"spinner\"></div>\n            </div>\n        </div>\n        <div class=\"row masonry masonryFlyIn\">\n            <div class=\"col-sm-6 masonry-item project\" data-filter=\"Category\">\n                <div class=\"image-tile hover-tile text-center\">\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-2-photo-5.jpg\" />\n                    <div class=\"hover-state\">\n                        <a href=\"#\">\n                            <h3 class=\"uppercase mb8\">Photo Title</h3>\n                            <h6 class=\"uppercase\">Photo Description</h6>\n                        </a>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-sm-6 masonry-item project\" data-filter=\"Category\">\n                <div class=\"image-tile hover-tile text-center\">\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-2-photo-1.jpg\" />\n                    <div class=\"hover-state\">\n                        <a href=\"#\">\n                            <h3 class=\"uppercase mb8\">Photo Title</h3>\n                            <h6 class=\"uppercase\">Photo Description</h6>\n                        </a>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-sm-6 masonry-item project\" data-filter=\"Category\">\n                <div class=\"image-tile hover-tile text-center\">\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-2-photo-3.jpg\" />\n                    <div class=\"hover-state\">\n                        <a href=\"#\">\n                            <h3 class=\"uppercase mb8\">Photo Title</h3>\n                            <h6 class=\"uppercase\">Photo Description</h6>\n                        </a>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-sm-6 masonry-item project\" data-filter=\"Category\">\n                <div class=\"image-tile hover-tile text-center\">\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-2-photo-2.jpg\" />\n                    <div class=\"hover-state\">\n                        <a href=\"#\">\n                            <h3 class=\"uppercase mb8\">Photo Title</h3>\n                            <h6 class=\"uppercase\">Photo Description</h6>\n                        </a>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546973556),(276,'home-3-testimonial','<section>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-md-10 col-md-offset-1 col-sm-12 text-center\">\n                <h3>What designers are saying...</h3>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-sm-4 text-center\">\n                <div class=\"feature boxed cast-shadow-light\">\n                    <img alt=\"Pic\" class=\"image-small inline-block mb24\" src=\"{path}avatar-1.png\" />\n                    <h4>\"Amazing!\"</h4>\n                    <p>\n                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n                    </p>\n                    <span><strong>Ginny Lin</strong></span>\n                </div>\n            </div>\n            <div class=\"col-sm-4 text-center\">\n                <div class=\"feature boxed cast-shadow-light\">\n                    <img alt=\"Pic\" class=\"image-small inline-block mb24\" src=\"{path}avatar-2.png\" />\n                    <h4>\"Incredible!\"</h4>\n                    <p>\n                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n                    </p>\n					<span><strong>Patrick Peterson</strong></span>\n                </div>\n            </div>\n            <div class=\"col-sm-4 text-center\">\n                <div class=\"feature boxed cast-shadow-light\">\n                    <img alt=\"Pic\" class=\"image-small inline-block mb24\" src=\"{path}avatar-3.png\" />\n                    <h4>\"Wonderful!\"</h4>\n                    <p>\n                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n                    </p>\n					<span><strong>Jordan Varro</strong></span>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546971910),(320,'home-6-intro','<section class=\"fullscreen cover parallax image-bg overlay\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover25.jpg\" />\n    </div>\n    <div class=\"container-fluid v-align-transform\">\n        <div class=\"row\">\n            <div class=\"text-center\" style=\"background-color: rgba(0, 0, 0, 0.5); padding: 1em 0\">\n                <div>\n                    <cregion>home-6-intro-content</cregion>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548269239),(281,'home-5-intro','<section class=\"cover image-bg fullscreen overlay overlay-heavy bg-dark vid-bg pt-xs-120\">\n    <div class=\"player\" data-video-id=\"dmgomCutGqc\" data-start-at=\"22\"></div>\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover27.jpg\" />\n    </div>\n    <div class=\"masonry-loader\">\n        <div class=\"spinner\">\n        </div>\n    </div>\n    <div class=\"container v-align-transform\">\n        <div class=\"row\">\n            <div class=\"col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1\">\n				<cregion>home-5-intro-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546471744),(280,'home-4-staff-left','<section class=\"bg-secondary\">\r\n    <div class=\"container\">\r\n        <div class=\"row v-align-children\">\r\n            <div class=\"col-md-7 col-sm-6 text-center mb-xs-24 overflow-hidden\">\r\n                <div>\r\n                    <img class=\"col-xs-6 p0\" alt=\"Pic\" src=\"{path}staff-01.jpg\" />\r\n                    <img class=\"col-xs-6 p0\" alt=\"Pic\" src=\"{path}staff-02.jpg\" />\r\n                    <img class=\"col-xs-6 p0\" alt=\"Pic\" src=\"{path}staff-03.jpg\" />\r\n                    <img class=\"col-xs-6 p0\" alt=\"Pic\" src=\"{path}staff-04.jpg\" />\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-md-offset-1 col-sm-5 col-sm-offset-1 text-center\">\r\n                <h3>Here to serve you.</h3>\r\n                <p>\r\n                    We are a team of web designers and developers that create clear and purposeful website workflows for everyone connected to your organization.\r\n                </p>\r\n                <a class=\"btn\" href=\"{path}staff-directory\">Our Staff</a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548294736),(301,'megamenu-centered','<!-- wrap <div class=\"nav-utility\"> with <nav> or <nav class=\"bg-dark\"> or <nav class=\"absolute transparent\">-->\n<div class=\"nav-utility\">\n    <div class=\"module right\">\n        <span class=\"sub\"><cart></cart></span>\n    </div>\n    <div class=\"module right\">\n        <span class=\"sub\"><login>site-login</login></span>\n    </div>        \n    <if view-access folder-id=\"199\">\n        <div class=\"module right\">\n            <a href=\"{path}my-conversations\"><i class=\"ti-comments\">&nbsp;</i></a>\n            <span class=\"sub\"><a href=\"{path}my-conversations\">conversations</a></span>\n        </div>\n    </if>\n</div>\n\n<div class=\"text-center\">\n     <a href=\"{path}\">\n        <img class=\"logo logo-light\" alt=\"Logo\" src=\"{path}logo-light.png\" />\n        <img class=\"logo logo-dark\" alt=\"Logo\" src=\"{path}logo-dark.png\" />\n    </a>\n</div>\n\n<div class=\"nav-bar text-center\">\n    <div class=\"module widget-handle mobile-toggle right visible-sm visible-xs\">\n        <i class=\"ti-menu\"></i>\n    </div>\n    <div class=\"module-group text-left\">      \n        <div class=\"module left\">\n            <menu>site-menu</menu>\n        </div>\n        <div class=\"module widget-handle search-widget-handle left\">\n            <div class=\"search\">\n                <a href=\"{path}site-search\"><i class=\"ti-search\"></i></a>\n                <span class=\"title\">Search Site</span>\n            </div>\n            <div class=\"function\">\n                <form class=\"search-form\" action=\"{path}site-search\">\n                    <input type=\"text\" value=\"\" name=\"query\" placeholder=\"Search Site\" />\n                </form>\n            </div>\n        </div>\n    </div>\n</div>','yes',40,1547760725),(282,'home-5-image-edge-right','<section class=\"image-edge pb0\">\r\n    <div class=\"col-md-6 col-sm-4 p0 col-md-push-6 col-sm-push-8\">\r\n        <img alt=\"Screenshot\" class=\"mb-xs-24\" src=\"{path}photo-gallery-album-1-photo-1.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"col-md-5 col-md-pull-0 col-sm-7 col-sm-pull-4 v-align-transform\">\r\n            <h1 class=\"large mb64 mb-xs-24\">Clean Design</h1>\r\n            <div class=\"feature feature-3\">\r\n                <div class=\"left\">\r\n                    <i class=\"ti-notepad icon-sm\"></i>\r\n                </div>\r\n                <div class=\"right\">\r\n                    <h5 class=\"uppercase mb16\">Consistent</h5>\r\n                    <p>\r\n                        Creative Style Guides are welcome here. Add your own shortcodes and restrict the use of fonts and colors to enforce a consistent “look and feel” across all pages.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"feature feature-3\">\r\n                <div class=\"left\">\r\n                    <i class=\"ti-shield icon-sm\"></i>\r\n                </div>\r\n                <div class=\"right\">\r\n                    <h5 class=\"uppercase mb16\">Protected</h5>\r\n                    <p>\r\n                        User access control includes a “Designer” role that protects design files and front-end code from getting messed up by non-designer users. Yeah, it can happen.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548195128),(283,'home-5-image-edge-left','<section class=\"image-edge\">\r\n    <div class=\"col-md-6 col-sm-4 p0\">\r\n        <img alt=\"Pic\" class=\"mb-xs-24\" src=\"{path}photo-gallery-album-1-photo-3.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"col-md-5 col-md-offset-1 col-sm-7 col-sm-offset-1 v-align-transform right\">\r\n            <h1 class=\"large mb64 mb-xs-24\">User Control</h1>\r\n            <p>\r\n				Delegate and restrict any aspect of site building, site design, site content, and site management to others using flexible access control.\r\n				There are infinite ways to delegate user access to any feature or section of your website securely. Users can be given trial access to areas and have their access expire after any period of time. Users can also manage their own account profiles including time zones, contact information, mailing lists, and passwords.\r\n            </p>\r\n            <a class=\"btn btn-lg\" href=\"https://livesite.com/community\">Get liveSite</a>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548292752),(284,'home-5-testimonial-cta','<section class=\"bg-secondary\">\r\n    <div class=\"container\">\r\n        <div class=\"row mb64 mb-xs-24\">\r\n            <div class=\"col-sm-12 text-center spread-children-large\">\r\n                <img alt=\"Pic\" src=\"{path}avatar-1.png\" />\r\n                <img alt=\"Pic\" src=\"{path}avatar-2.png\" />\r\n                <img alt=\"Pic\" src=\"{path}avatar-3.png\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center\">\r\n                <h2>Built by professionals, for professionals.</h2>\r\n                <p class=\"mb40 mb-xs-24\">\r\n					The integrated contact database is populated and updated automatically whenever a site visitor or user posts content, submits a form, or makes a payment of any kind. Powerful email campaigns features turn this contact data into segmented mailing lists for your staff automatically and manage the opt in/out process for you.\r\n                </p>\r\n                <a class=\"mb0 btn btn-lg btn-filled\" href=\"https://livesite.com/community\">Learn More</a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548292791),(285,'home-5-parallax-icon-boxes','<section class=\"image-bg overlay parallax\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"Background\" class=\"background-image\" src=\"{path}cover20.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h2 class=\"mb16\">There\'s so much to love</h2>\r\n                <p class=\"lead mb64\">\r\n                    A remarkably powerful website platform packed with features.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-1 boxed\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-shopping-cart icon\"></i>\r\n                        <h5 class=\"uppercase\">Customers</h5>\r\n                    </div>\r\n                    <p>\r\n                        Built-in customer portal features provide sales communication tools, online ordering, and support tickets to create a friendly ecosystem for smart commerce - no matter what you\'re selling.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-1 boxed\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-medall icon\"></i>\r\n                        <h5 class=\"uppercase\">Members</h5>\r\n                    </div>\r\n                    <p>\r\n                        Built-in members portal features allow you to sell access to protected members-only areas to publish directories, calendars, reservations for events, and even share classfied ads.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-1 boxed\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-lock icon\"></i>\r\n                        <h5 class=\"uppercase\">Staff</h5>\r\n                    </div>\r\n                    <p>\r\n                        Built-in staff portal provides a secure place to go for your staff to post events, blogs, videos, download forms and documents, and communicate with customers and each other.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548195329),(286,'home-5-pricing-table','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h3 class=\"mb16\">Pricing</h3>\r\n                <p class=\"lead mb64\">\r\n                    Here is pricing table to explain your plans\r\n                    <br /> in a straight-forward fashion.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <cregion>pricing-table</cregion>\r\n    </div>\r\n</section>','yes',40,1548195375),(375,'full-height-modal-auto','<!-- add data-cookie like below to prevent reappearance of auto modal\n<div class=\"site_modal text-center fullscreen image-bg\" data-time-delay=\"3000\" data-cookie=\"dismissed-auto-modal\">\n-->\n<div class=\"site_modal text-center fullscreen image-bg\" data-time-delay=\"3000\">\n    <div class=\"background-image-holder\">\n        <img alt=\"Background\" class=\"background-image\" src=\"{path}cover13.jpg\" />\n    </div>\n    <div style=\"display: table; height: 100%\">\n        <div style=\"display: table-cell; vertical-align: middle; height: auto; padding: 0 2em;\">\n            <h4>Full Height Modal (Auto)</h4>\n            <hr>\n            <p>\n                This modal opens automatically 3 seconds after the page has been loaded in the visitor\'s browser, but only if the visitor has not already seen it by \n            	setting a cookie in the visitor\'s browser called \'dismissed-auto-modal\'.\n            </p>\n        	<p>\n            	To prevent this modal from reappearing to visitors, add a \'data-cookie\' attribute.\n        	</p>\n        	<p>\n           		Then, to test your modal, delete the cookie from your browser and go into \"Edit Mode\" before accessing this page. \n            	\"Edit Mode\" will ignore the fact that you have seen the modal already.\n        	</p>\n            <hr>\n        </div>\n    </div>\n</div>','yes',40,1548269686),(289,'home-2-text-strip-content','<h3>The hassle-free website platform for all.</h3>\n\n<p class=\"lead\">liveSite is more than a design template. It&#39;s a complete website solution. No other software tools are required to update your design, edit your content, create apps, and delegate user tasks.</p>','no',40,1548124476),(290,'home-2-parallax-text-strip-content','<h3>liveSite is the best cloud-based website platform<br />\nfor multi-user organizations</h3>\n\n<p>&nbsp;<i class=\"ti-cloud icon inline-block mb16\">&nbsp;</i></p>\n\n<p><a class=\"btn btn-lg btn-primary\" href=\"https://livesite.com\" target=\"_blank\">Find out why</a></p>','no',40,1548184095),(291,'home-2-centered-image-button-tabs','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row mb80 mb-xs-24\">\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-12 text-center\">\r\n                <h3>A team of friendly geeks combining<br />\r\ndesign and development.</h3>\r\n                <p class=\"mb0\">Camelback Web Architects is a Dallas-based software development company focused on making simply innovative website platform to power multi-user organizations.</p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row mb64 mb-xs-24\">\r\n            <div class=\"col-md-10 col-md-offset-1\">\r\n                <img alt=\"Pic\" class=\"cast-shadow\" src=\"{path}photo-gallery-album-1-photo-3.jpg\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-12 text-center\">\r\n                <cregion>button-tabs</cregion>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548184354),(292,'home-3-intro-content','<h1 class=\"large\">Meet liveSite.</h1>\n\n<h2 class=\"large\">Your Web Design&#39;s New Best Friend.</h2>\n\n<p class=\"lead uppercase mb48\">Carefully-crafted functionality at your fingertips</p>','no',40,1548191145),(295,'home-3-text-strip-content-2','<h1 class=\"large\">Software</h1>\n\n<p>We provide a complete website platform to any type of business or non-profit.</p>','no',40,1548292257),(296,'home-3-text-strip-content-3','<h1 class=\"large\">Hosting</h1>\n\n<p>We also offer liveSite website hosting and support services so we have your back when you need it.</p>','no',40,1548292257),(299,'home-4-parallax-strip','<section class=\"pt240 pb240 parallax image-bg overlay\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover25.jpg\" />\r\n    </div>\r\n</section>','yes',40,1546471420),(298,'home-4-masonry-tiles-content','<h6 class=\"uppercase\">You Design it. We Power it.</h6>\n\n<h3 class=\"mb0\">liveSite is the ultimate back-end solution for modern web designers</h3>','no',40,1548191763),(300,'home-5-intro-content','<h1 class=\"mb16\">Build beautiful websites with exceptional features including video backgrounds.</h1>\n\n<h6 class=\"uppercase mb32\">liveSite: A Powerful Website Back-end Solution</h6>','no',40,1548192083),(302,'home-gifts-intro-content','<h1 class=\"large\" style=\"text-align: center;\"><strong>Gift Shop</strong></h1>\n\n<h4 style=\"text-align: center;\"><strong><em>&ldquo;The place to purchase gifts for every occasion.<br />\nArrival date and freshness guaranteed!&rdquo;</em></strong></h4>\n\n<p style=\"text-align: center;\"><strong>Donald Fagan - Happy Customer</strong></p>','no',40,1546901090),(303,'home-giftshop-intro','<section class=\"image-bg overlay fullscreen cover parallax\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-6.jpg\" />\r\n    </div>\r\n    <div class=\"container v-align-transform\">\r\n        <div class=\"row mt80\">\r\n            <div class=\"col-sm-12\">\r\n                <cregion>home-gifts-intro-content</cregion>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class=\"align-bottom text-center\">\r\n        <ul class=\"list-inline social-list mb24\">\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-twitter-alt\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-facebook\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-pinterest\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-vimeo-alt\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a href=\"#\">\r\n                    <i class=\"ti-youtube\"></i>\r\n                </a>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n</section>\r\n','yes',40,1546900758),(304,'home-giftshop-featured','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-8 col-sm-offset-2 text-center overflow-hidden mb-xs-48\">\r\n                <div class=\"text-center\">\r\n                    <h2>Featured Gifts</h2>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-4 text-center overflow-hidden mb-xs-48\">\r\n                <div class=\"col-sm-8 col-sm-offset-2 col-xs-6 col-xs-offset-3\">\r\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-album-3-photo-1.jpg\" />\r\n                </div>\r\n                <br class=\"mb48\">\r\n                <div class=\"text-center\">\r\n                    <h4>Gourmet&nbsp;Chocolates</h4>\r\n                    <h5>Our delicious chocolate treats packaged cold and guaranteed to be fresh upon arrival.</h5>\r\n                    <a class=\"btn btn-filled mb0\" href=\"{path}shop-sidebar\">Visit Shop</a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4 text-center overflow-hidden mb-xs-48\">\r\n                <div class=\"col-sm-8 col-sm-offset-2 col-xs-6 col-xs-offset-3\">\r\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-album-3-photo-3.jpg\" />\r\n                </div>\r\n                <br class=\"mb48\">\r\n                <div class=\"text-center\">\r\n                    <h4>Gourmet&nbsp;Coffee</h4>\r\n                    <h5>Roasted to perfection. Select ground or whole bean and add a personalized gift message.</h5>\r\n                    <a class=\"btn btn-filled mb0\" href=\"{path}shop-sidebar\">Visit Shop</a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4 text-center overflow-hidden mb-xs-48\">\r\n                <div class=\"col-sm-8 col-sm-offset-2 col-xs-6 col-xs-offset-3\">\r\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-album-3-photo-4.jpg\" />\r\n                </div>\r\n                <br class=\"mb48\">\r\n                <div class=\"text-center\">\r\n                    <h4>Gourmet&nbsp;Cookies</h4>\r\n                    <h5>Baked from our original family recipes, our cookies are irresistable. Sugar-free options.</h5>\r\n                    <a class=\"btn btn-filled mb0\" href=\"{path}shop-sidebar\">Visit Shop</a>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546901304),(305,'home-giftshop-parallax-strip','<section class=\"cover fullscreen image-bg parallax overlay\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-5.jpg\" />\r\n    </div>\r\n    <div class=\"container v-align-transform\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-8 col-sm-offset-2 text-center\">\r\n                <h3>Creating gourmet treats for over 100 years</h3>\r\n                <p class=\"lead\">\r\n                    Handmade from the original family recipes and passed down from generation to generation.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546901371),(306,'home-giftshop-image-square-right','<section class=\"image-square right\">\r\n    <div class=\"col-md-6 image\">\r\n        <div class=\"background-image-holder\">\r\n            <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-3.jpg\" />\r\n        </div>\r\n    </div>\r\n    <div class=\"col-md-6 content\">\r\n        <div class=\"text-center\">\r\n            <h5 class=\"uppercase fade-half mb-xs-24\">&#8226; Est. 1906 &#8226;</h5>\r\n            <h4>We roast our own coffee beans to the highest standards</h4>\r\n            <p class=\"lead\">\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.\r\n            </p>\r\n            <img alt=\"signature\" src=\"{path}signature.png\" class=\"image-small\" />\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546901424),(307,'home-giftshop-image-square-left','<section class=\"image-square left bg-secondary\">\r\n    <div class=\"col-md-6 image\">\r\n        <div class=\"background-image-holder\">\r\n            <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-2.jpg\" />\r\n        </div>\r\n    </div>\r\n    <div class=\"col-md-6 col-md-offset-1 content\">\r\n        <h4>Join us for coffee &amp; dessert</h4>\r\n        <p class=\"lead\">\r\n            Each Friday afternoon at 2pm, we welcome our customers sit with us and enjoy a $5 coffee and dessert.\r\n        </p>\r\n        <div class=\"modal-container\">\r\n            <a class=\"btn btn-primary btn-modal\" href=\"#\">Reserve My Seat</a>\r\n            <div class=\"site_modal\">\r\n                <h4>Reserve My Seat</h4>\r\n                <hr>\r\n                <p>\r\n                    Site Designer,\r\n                </p>\r\n                <p>\r\n                    We did not build-out an example of a reservation for this button but you could do that easily by creating a product and a calendar event and enabling reservations for the event. An example of this can be found <a href=\"{path}buy-tickets\">here</a>.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <hr>\r\n        <p class=\"lead\">\r\n            1234 Main Street<br />\r\n            Anytown, TX 55555<br />\r\n        </p>\r\n        <ul>\r\n            <li>(555) 555-5555</li>\r\n            <li>example@mydomain.com</li>\r\n        </ul>\r\n    </div>\r\n</section>','yes',40,1546901464),(308,'home-agency-2-intro','<section class=\"kenburns cover fullscreen image-slider slider-arrow-controls controls-inside\">\n    <ul class=\"slides\">\n        <li class=\"image-bg pt-xs-240 pb-xs-240\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover33.jpg\" />\n            </div>\n            <div class=\"align-bottom\" style=\"background-color: rgba(0, 0, 0, 0.5)\">\n                <div class=\"row mt24\">\n                    <div class=\"col-md-3 col-sm-6 col-xs-12 text-center-xs mb-xs-24\">\n						<cregion>home-agency-2-content-1a</cregion>\n                    </div>\n                    <div class=\"col-md-4 hidden-sm hidden-xs\">\n						<cregion>home-agency-2-content-1b</cregion>\n                    </div>\n                    <div class=\"col-md-5 col-sm-6 col-xs-12 text-right text-center-xs\">\n                        <cregion>home-agency-2-content-1c</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n        <li class=\"image-bg pt-xs-240 pb-xs-240\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover34.jpg\" />\n            </div>\n            <div class=\"align-bottom\" style=\"background-color: rgba(0, 0, 0, 0.5)\">\n                <div class=\"row mt24\">\n                    <div class=\"col-md-3 col-sm-6 col-xs-12 text-center-xs mb-xs-24\">\n						<cregion>home-agency-2-content-2a</cregion>\n                    </div>\n                    <div class=\"col-md-4 hidden-sm hidden-xs\">\n						<cregion>home-agency-2-content-2b</cregion>\n                    </div>\n                    <div class=\"col-md-5 col-sm-6 col-xs-12 text-right text-center-xs\">\n                        <cregion>home-agency-2-content-2c</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n            <li class=\"image-bg pt-xs-240 pb-xs-240\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover35.jpg\" />\n            </div>\n            <div class=\"align-bottom\" style=\"background-color: rgba(0, 0, 0, 0.5)\">\n                <div class=\"row mt24\">\n                    <div class=\"col-md-3 col-sm-6 col-xs-12 text-center-xs mb-xs-24\">\n						<cregion>home-agency-2-content-3a</cregion>\n                    </div>\n                    <div class=\"col-md-4 hidden-sm hidden-xs\">\n						<cregion>home-agency-2-content-3b</cregion>\n                    </div>\n                    <div class=\"col-md-5 col-sm-6 col-xs-12 text-right text-center-xs\">\n                        <cregion>home-agency-2-content-3c</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n    </ul>\n</section>','yes',40,1546968626),(312,'home-agency-2-content-2a','<h4 class=\"uppercase mb0\">Client B</h4>\r\n\r\n<p class=\"mb0\">Product Design</p>\r\n','no',40,1546968712),(313,'home-agency-2-content-3a','<h4 class=\"uppercase mb0\">Client C</h4>\r\n\r\n<p class=\"mb0\">Brand Marketing</p>\r\n','no',40,1546968706),(314,'home-agency-2-content-2b','<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>\r\n','no',40,1546968739),(315,'home-agency-2-content-3b','<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>\r\n','no',40,1546968732),(311,'home-agency-2-content-1c','<p class=\"mb0\"><a class=\"btn btn-lg btn-white\" href=\"{path}case-study\">Case Study</a></p>','no',40,1546968491),(310,'home-agency-2-content-1b','<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>','no',40,1546968554),(309,'home-agency-2-content-1a','<h4 class=\"uppercase mb0\">Client A</h4>\r\n\r\n<p class=\"mb0\">Retail Space Design</p>\r\n','no',40,1546968075),(316,'home-agency-2-content-2c','<p class=\"mb0\"><a class=\"btn btn-lg btn-white\" href=\"{path}case-study\">Case Study</a></p>\r\n','no',40,1546968761),(317,'home-agency-2-content-3c','<p class=\"mb0\"><a class=\"btn btn-lg btn-white\" href=\"{path}case-study\">Case Study</a></p>\r\n','no',40,1546968754),(318,'home-agency-intro-content','<h3>Build beautiful, contemporary websites with exceptional features using liveSite.</h3>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><a class=\"btn btn-lg btn-white\" href=\"https://livesite.com/community\" target=\"_blank\">Learn More</a></p>\r\n','no',40,1548294634),(319,'home-giftshop-icon-boxes','<section class=\"pt40 pb40\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 overflow-hidden\">\r\n                <div class=\"col-sm-3 p0\">\r\n                    <a href=\"#\">\r\n                        <div class=\"bg-secondary pt96 pb96 text-center fade-on-hover\">\r\n                            <i class=\"ti-shopping-cart-full icon icon-sm mb8\"></i>\r\n                            <h6 class=\"uppercase mb0\">Shop Range</h6>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n                <div class=\"col-sm-3 p0\">\r\n                    <a href=\"#\">\r\n                        <div class=\"bg-secondary pt96 pb96 text-center fade-on-hover\">\r\n                            <i class=\"ti-package icon icon-sm mb8\"></i>\r\n                            <h6 class=\"uppercase mb0\">Shipping Info</h6>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n                <div class=\"col-sm-3 p0\">\r\n                    <a href=\"#\">\r\n                        <div class=\"bg-secondary pt96 pb96 text-center fade-on-hover\">\r\n                            <i class=\"ti-help-alt icon icon-sm mb8\"></i>\r\n                            <h6 class=\"uppercase mb0\">FAQ</h6>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n                <div class=\"col-sm-3 p0\">\r\n                    <a href=\"#\">\r\n                        <div class=\"bg-secondary pt96 pb96 text-center fade-on-hover\">\r\n                            <i class=\"ti-receipt icon icon-sm mb8\"></i>\r\n                            <h6 class=\"uppercase mb0\">Returns Policy</h6>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>\r\n','yes',40,1546971480),(321,'home-6-intro-content','<h2 class=\"mb16\">Import Your Existing Website into liveSite in seconds.</h2>\r\n\r\n<h6 class=\"uppercase mb32\">liveSite: A Powerful Website Back-end Solution</h6>\r\n\r\n<p><a class=\"btn btn-lg btn-white\" href=\"https://livesite.com/community\" target=\"_blank\">Get liveSite</a></p>\r\n','no',40,1548294656),(322,'home-training-intro','<section class=\"image-bg overlay pt240 pb240 pt-xs-180 pb-xs-180\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover16.jpg\" />\n    </div>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-10 col-sm-offset-1\">\n                <cregion>home-training-intro-content</cregion>\n\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546993394),(197,'breadcrumb-1','                            <ol class=\"breadcrumb breadcrumb-2\">\n                                <li>\n                                    <a href=\"{path}home\">Home</a>\n                                </li>\n                                <li>\n                                    <a href=\"{path}catalog\">Catalog</a>\n                                </li>\n                                <!--<li class=\"active\">Grid Structure</li>-->\n                                <li class=\"active\"><pregion></pregion></li>\n                            </ol>','yes',40,1537918019),(294,'home-3-text-strip-content-1','<h3>Hassle-free Website Platform</h3>\n\n<p class=\"lead mb0\">No plugins to connect and manage. Add any design and launch.</p>','no',40,1548292134),(274,'home-3-text-strip','<section>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-md-10 col-md-offset-1 col-sm-12 text-center\">\n				<cregion>home-3-text-strip-content-1</cregion>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-4 col-md-offset-2 col-sm-5 col-sm-offset-1 mb-xs-24\">\n				<cregion>home-3-text-strip-content-2</cregion>\n            </div>\n            <div class=\"col-md-4 col-sm-5 mb-xs-24\">\n				<cregion>home-3-text-strip-content-3</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546470936),(198,'footer-1','<!-- call using \n	 <footer class=\"footer-1 bg-dark\">\n  or <footer class=\"footer-1 bg-secondary\"> \n  or <footer class=\"footer-1 bg-primary\">\n-->\n<div class=\"container\">\n    <div class=\"row\">\n        <div class=\"col-md-3 col-sm-6\">\n            <img alt=\"Logo\" class=\"logo image-xs logo-light\" src=\"{path}logo-light.png\" />\n            <img alt=\"Logo\" class=\"logo image-xs logo-dark\" src=\"{path}logo-dark.png\" />\n            <hr style=\"margin-top:6px;margin-bottom:12px\">\n            <system>mailing-list-widget</system>\n        </div>\n        <div class=\"col-md-3 col-sm-6\">\n            <system>blog-widget</system>\n        </div>\n        <div class=\"col-md-3 col-sm-6\">\n            <cregion>twitter-feed</cregion>\n        </div>\n        <div class=\"col-md-3 col-sm-6\">\n            <div class=\"widget\">\n                <h6 class=\"title\">Upcoming Events</h6>\n                <hr>\n                <system>calendar-widget</system>\n            </div>\n        </div>\n    </div>\n    <div class=\"row mb24\">\n        <div class=\"col-xs-4 text-center-sm\">\n            &copy; My Organization\n        </div>\n        <div class=\"col-xs-4 text-center\">\n            <div class=\"modal-container\" style=\"display:inline-block\">\n				<a class=\"btn-modal\" style=\"opacity:.3;cursor:pointer;font-weight:300\" href=\"#\">liveSite + Foundry</a>\n				<div class=\"site_modal\">\n					<h4>liveSite + Foundry</h4>\n					<hr>\n					<p>\n						This site theme was created using <a href=\"https://livesite.com/community\" target=\"_blank\">liveSite</a> which manages all the back-end functionality and workflow for the site.  \n                        liveSite can be \"skinned\" with any front-end design or template you desire, so we selected the Foundry HTML Template \n                        as the basis for the front-end design we created for this site. Foundry is a solid front-end design and our conversion of it \n                        into a liveSite theme is provided as a great resource for you as you decide how you want to design your own liveSite front-end.\n                    </p>\n                    <p>\n                        IMPORTANT: The Foundry Template is not free, so if you decide to use it as a basis for your own liveSite, you will \n                        need to purchase a license from the author at \n                        <a href=\"//themeforest.net/item/foundry-multipurpose-html-variant-page-builder/11562108?ref=camelbackwebarchitects\" target=\"_blank\">Envato Market</a>.\n                    </p>\n                    <p>\n						Enjoy!<br />\n                        Camelback Web Architects<br />\n						simply innovative.\n					</p>\n				</div>\n			</div>\n        </div>\n        <div class=\"col-xs-4 text-right text-center-sm\">\n            <ul class=\"list-inline social-list\">\n                <li>\n                    <a href=\"#\">\n                        <i class=\"ti-twitter-alt\"></i>\n                    </a>\n                </li>\n                <li>\n                    <a href=\"#\">\n                        <i class=\"ti-facebook\"></i>\n                    </a>\n                </li>\n                <li>\n                    <a href=\"#\">\n                        <i class=\"ti-pinterest-alt\"></i>\n                    </a>\n                </li>\n                <li>\n                    <a href=\"#\">\n                        <i class=\"ti-linkedin\"></i>\n                    </a>\n                </li>\n                <li>\n                    <a href=\"#\">\n                        <i class=\"ti-youtube\"></i>\n                    </a>\n                </li>\n            </ul>\n        </div>\n    </div>\n</div>','yes',40,1548291828),(246,'scrolling-carousel','<div class=\"logo-carousel\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l1.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l2.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l3.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l4.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l1.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l2.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l3.png\" />\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" src=\"{path}l4.png\" />\r\n            </a>\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1547659594),(214,'image-slider','<div class=\"image-slider slider-all-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover14.jpg\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover15.jpg\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover16.jpg\" />\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1546364760),(215,'button-tabs','<div class=\"tabbed-content button-tabs\">\r\n    <ul class=\"tabs\">\r\n        <li class=\"active\">\r\n            <div class=\"tab-title\">\r\n                <span>History</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Approach</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Culture</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est.\r\n                </p>\r\n            </div>\r\n        </li>\r\n        <li>\r\n            <div class=\"tab-title\">\r\n                <span>Method</span>\r\n            </div>\r\n            <div class=\"tab-content\">\r\n                <p>\r\n                    Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.\r\n                </p>\r\n            </div>\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1545934851),(199,'footer-2','<!-- call using \r\n	 <footer class=\"footer-1 bg-dark\">\r\n  or <footer class=\"footer-1 bg-secondary\"> \r\n  or <footer class=\"footer-1 bg-primary\">\r\n-->\r\n<div class=\"container\">\r\n    <div class=\"row\">\r\n        <div class=\"col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center\">\r\n            <a href=\"#\">\r\n                <img alt=\"Logo\" class=\"image-xs mb32 fade-on-hover\" src=\"{path}logo-light.png\" />\r\n            </a>\r\n            <h5 class=\"fade-1-4\"> &copy; My Organization <br />\r\n                \r\n                <div class=\"modal-container\" style=\"display:inline-block\">\r\n    				<a class=\"btn-modal\" style=\"opacity:.3;cursor:pointer;font-weight:300\" href=\"#\">liveSite + Foundry</a>\r\n    				<div class=\"site_modal\">\r\n    					<h4>liveSite + Foundry</h4>\r\n    					<hr>\r\n    					<p>\r\n    						This site was created using <a href=\"https://livesite.com/community\" target=\"_blank\">liveSite</a> which manages all the back-end functionality and workflow for the site.  \r\n                            liveSite can be \"skinned\" with any front-end design or template you desire, so we selected the Foundry HTML Template \r\n                            as the basis for the front-end design we created for this site. Foundry is a solid front-end design and our conversion of it \r\n                            into a liveSite theme is provided as a great resource for you as you decide how you want to design your own liveSite front-end.\r\n                        </p>\r\n                        <p>\r\n                        	IMPORTANT: The Foundry Template is not free, so if you decide to use it as a basis for your own liveSite, you will \r\n                            need to purchase a license from \r\n                            <a href=\"//themeforest.net/item/foundry-multipurpose-html-variant-page-builder/11562108?ref=camelbackwebarchitects\" target=\"_blank\">Envato Market</a>.\r\n    					</p>\r\n                        <p>\r\n    						Enjoy!<br />\r\n                            Camelback Web Architects<br />\r\n    						simply innovative.\r\n    					</p>\r\n    				</div>\r\n    			</div>                \r\n            </h5>\r\n            <ul class=\"list-inline social-list mb0\">\r\n                <li><a href=\"#\"><i class=\"ti-twitter-alt\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-facebook\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-dribbble\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-vimeo-alt\"></i></a></li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1548292728),(293,'home-3-image-left-strip-content','<h3>Create new designs and easily test them across your existing pages.</h3>\n\n<p class=\"mb0\">liveSite&#39;s Theme Preview Mode allows you to test your modified designs and new designs across your production pages so there is no need to maintain a development website! Imagine testing a new website design right on top of your current storefront so you know it will work before you flip the switch and make it live!</p>','no',40,1548185363),(272,'home-3-icon-boxes','<section class=\"pb64 pb-xs-40\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-2 filled text-center\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-layout-grid4 icon-sm\"></i>\r\n                        <h5 class=\"uppercase\">Unlimited Pages</h5>\r\n                    </div>\r\n                    <p>\r\n                        Upload all your digital assets, photos, and code files using drag-n-drop or by importing them in a ZIP file. Edit code files and images in place on the server without the need for FTP or any other desktop design software.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-2 filled text-center\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-id-badge icon-sm\"></i>\r\n                        <h5 class=\"uppercase\">Unlimited Users</h5>\r\n                    </div>\r\n                    <p>\r\n						Unlike most enterprise website platforms, liveSite allows for an unlimited number of Users so you can grow your site without growing your cost. Delegate and restrict any aspect of site building, site design, site content, and site management to others using flexible access control.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-4\">\r\n                <div class=\"feature feature-2 filled text-center\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-panel icon-sm\"></i>\r\n                        <h5 class=\"uppercase\">Unlimited Apps</h5>\r\n                    </div>\r\n                    <p>\r\n                        Create custom apps to collect information from any group of users on your site and create secure data views to display the information. Sync with your contact database and trigger autoresponders, notifications, and personalized email drip campaigns.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548185071),(273,'home-3-image-left-strip','<section class=\"image-square left bg-secondary pb-sm-64\">\r\n    <div class=\"col-md-6 image\">\r\n        <div class=\"background-image-holder\">\r\n            <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-1-photo-2.jpg\" />\r\n        </div>\r\n    </div>\r\n    <div class=\"col-md-6 col-md-offset-1 content\">\r\n		<cregion>home-3-image-left-strip-content</cregion>\r\n    </div>\r\n</section>','yes',40,1546470703),(358,'home-firm-twitter-feed','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12\">\r\n                <h6 class=\"uppercase\">Updates & Insights</h6>\r\n                <hr class=\"mb160 mb-xs-24\">\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-10\">\r\n                <a class=\"h1 thin color-primary inline-block mb24\" href=\"#\">@ilovelivesite</a>\r\n            </div>\r\n        </div>\r\n        <div class=\"row mb160 mb-xs-0\">\r\n            <div class=\"col-md-6 col-sm-8\">\r\n                <p class=\"lead\">\r\n                    Engagement and sharing knowledge are the cornerstones of our success. Follow us for SV related insights.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"twitter-feed thirds\">\r\n                <div class=\"tweets-feed\" data-widget-id=\"714599501978214400\">\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547239918),(359,'home-firm-intro-content','<h1 class=\"thin\">A San Francisco based professional services firm focused on your success.</h1>\n\n<p>&nbsp;</p>\n\n<p style=\"text-align: center;\"><a class=\"btn btn-lg btn-white\" href=\"{path}contact-us\">Contact Us</a></p>','no',40,1547240442),(360,'home-software-intro','<section class=\"bg-primary pb0\">\n    <div class=\"container pt80\">\n        <div class=\"row mb24 mb-xs-0\">\n            <div class=\"col-sm-10 col-sm-offset-1 text-center\">\n                <cregion>home-software-intro-content</cregion>\n            </div>\n        </div>\n        <div class=\"row\">\n            <img alt=\"image\" src=\"{path}software-1.png\" />\n        </div>\n    </div>\n</section>','yes',40,1547583300),(367,'home-product-benefits','<section>\n    <div class=\"container\">\n        <div class=\"row v-align-children\">\n            <div class=\"col-sm-4 col-md-offset-1 mb-xs-24\">\n                <h2 class=\"mb64 mb-xs-32\">Product Name</h2>\n                <div class=\"mb40 mb-xs-24\">\n					<h5 class=\"uppercase bold mb16\">Benefit 1</h5>\n					<p class=\"fade-1-4\">\n						Nulla accumsan mauris eget urna commodo, a placerat felis interdum. Aliquam maximus dui neque, sed accumsan eros scelerisque sed. Nunc at facilisis neque, a mollis erat.\n					</p>\n				</div>\n				<div class=\"mb40 mb-xs-24\">\n					<h5 class=\"uppercase bold mb16\">Benefit 2</h5>\n					<p class=\"fade-1-4\">\n						Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris rutrum sollicitudin sagittis. Nunc in blandit lectus. Pellentesque mattis, felis eget lacinia consequat, lectus libero maximus quam, nec lacinia dui erat sed urna.\n					</p>\n				</div>\n            </div>\n            <div class=\"col-sm-5 col-sm-6 col-sm-offset-1 text-center\">\n                <img alt=\"Screenshot\" src=\"{path}product-2.png\" />\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547586428),(361,'home-software-features','<section class=\"bg-dark pt120 pb120 pt-xs-40 pb-xs-40\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-6 col-md-4 col-md-offset-2\">\r\n                <div class=\"feature feature-3 mb64 mb-xs-24\">\r\n                    <div class=\"left\">\r\n                        <i class=\"ti-signal icon-sm\"></i>\r\n                    </div>\r\n                    <div class=\"right\">\r\n                        <h4 class=\"mb16\">Feature 1</h4>\r\n                        <p>\r\n                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                        </p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-6 col-md-4\">\r\n                <div class=\"feature feature-3 mb64 mb-xs-24\">\r\n                    <div class=\"left\">\r\n                        <i class=\"ti-ruler-alt icon-sm\"></i>\r\n                    </div>\r\n                    <div class=\"right\">\r\n                        <h4 class=\"mb16\">Feature 2</h4>\r\n                        <p>\r\n                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                        </p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-6 col-md-4 col-md-offset-2\">\r\n                <div class=\"feature feature-3 mb-xs-24\">\r\n                    <div class=\"left\">\r\n                        <i class=\"ti-layers icon-sm\"></i>\r\n                    </div>\r\n                    <div class=\"right\">\r\n                        <h4 class=\"mb16\">Feature 3</h4>\r\n                        <p>\r\n                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                        </p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-6 col-md-4\">\r\n                <div class=\"feature feature-3\">\r\n                    <div class=\"left\">\r\n                        <i class=\"ti-package icon-sm\"></i>\r\n                    </div>\r\n                    <div class=\"right\">\r\n                        <h4 class=\"mb16\">Feature 4</h4>\r\n                        <p>\r\n                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                        </p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547579224),(362,'home-software-video','<section class=\"bg-primary\">\r\n    <div class=\"container\">\r\n        <div class=\"row mb64 mb-xs-24\">\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center\">\r\n                <h3 class=\"mb40 mb-xs-24\">Video Tour</h3>\r\n                <p class=\"lead\">\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row mb48 mb-xs-24\">\r\n            <div class=\"col-sm-8 col-sm-offset-2\">\r\n                <cregion>iframe-video</cregion>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center spread-children-large\">\r\n                <img alt=\"pic\" class=\"image-xxs mb-xs-8 fade-half\" src=\"{path}c1.png\" />\r\n                <img alt=\"pic\" class=\"image-xxs mb-xs-8 fade-half\" src=\"{path}c2.png\" />\r\n                <img alt=\"pic\" class=\"image-xxs mb-xs-8 fade-half\" src=\"{path}c3.png\" />\r\n                <img alt=\"pic\" class=\"image-xxs mb-xs-8 fade-half\" src=\"{path}c4.png\" />\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547579275),(363,'home-software-screenshots','<section class=\"image-edge pt120 pb120 pt-xs-40 pb-xs-40\">\r\n    <div class=\"col-md-6 col-sm-4 p0 col-md-push-6 col-sm-push-8\">\r\n        <img alt=\"Screenshot\" class=\"cast-shadow mb-xs-24\" src=\"{path}software-2.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"col-md-5 col-md-pull-0 col-sm-7 col-sm-pull-4 v-align-transform\">\r\n            <h3 class=\"mb40 mb-xs-16\">The coolest features</h3>\r\n            <p class=\"lead mb40\">\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n            </p>\r\n            <div class=\"feature boxed\">\r\n                <p>\r\n                    \"We have saved tons of money and time using a platform to provide services to all our stakeholders.\"\r\n                </p>\r\n                <div class=\"spread-children\">\r\n                    <img alt=\"Pic\" class=\"image-xs\" src=\"{path}avatar-1.png\" />\r\n                    <span>Ginny Lin</span>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>\r\n\r\n<section class=\"image-edge pt120 pb120 pt-xs-40 pb-xs-40\">\r\n    <div class=\"col-md-6 col-sm-4 p0\">\r\n        <img alt=\"Screenshot\" class=\"cast-shadow mb-xs-24\" src=\"{path}software-3.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"col-md-5 col-md-offset-1 col-sm-7 col-sm-offset-1 v-align-transform right\">\r\n            <h3 class=\"mb40 mb-xs-16\">Powerfully flexible</h3>\r\n            <p class=\"lead mb40\">\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n            </p>\r\n            <div class=\"feature feature-3\">\r\n                <div class=\"left\">\r\n                    <i class=\"ti-user icon-sm\"></i>\r\n                </div>\r\n                <div class=\"right\">\r\n                    <h4 class=\"mb16\">Personalized</h4>\r\n                     <p>\r\n                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"feature feature-3\">\r\n                <div class=\"left\">\r\n                    <i class=\"ti-gift icon-sm\"></i>\r\n                </div>\r\n                <div class=\"right\">\r\n                    <h4 class=\"mb16\">A gift of pure website productivity</h4>\r\n                     <p>\r\n                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547583513),(364,'home-software-benefits-strip','<section class=\"bg-primary\">\n    <div class=\"container\">\n        <div class=\"row mb40 mb-xs-0\">\n            <div class=\"col-sm-12 text-center\">\n                <h4 class=\"uppercase\">Take your website to the next level</h4>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-sm-6 col-md-5 text-right text-center-xs\">\n                <h1 class=\"large mb8\">4,000+</h1>\n                <h6 class=\"uppercase\">Enterprises Trust liveSite</h6>\n            </div>\n            <div class=\"col-md-2 text-center hidden-sm hidden-xs\">\n                <i class=\"ti-infinite icon icon-lg mt8 mt-xs-0\" style=\"color: #fff; opacity: .7\"></i>\n            </div>\n            <div class=\"col-sm-6 col-md-5 text-center-xs\">\n                <h1 class=\"large mb8\">Limitless</h1>\n                <h6 class=\"uppercase\">Designs & Features</h6>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548196387),(365,'home-software-intro-content','<h1 class=\"large\">Sell Your Software</h1>\n\n<p class=\"lead\">Showcase your applications and take orders for software downloads &amp; hosting.<br />\n&nbsp;</p>\n\n<p><a class=\"btn btn-lg btn-white\" href=\"shop-product-fullwidth/Software\">Order Now</a></p>','no',40,1548270381),(368,'home-product-parallax','<section class=\"image-bg overlay parallax pt180 pb180 pt-xs-80 pb-xs-80\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}product-4.jpg\" />\n    </div>          \n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-5 col-sm-6 col-md-push-7 col-sm-push-6\">\n                <h2>Amazing features at your fingertips</h2>\n                <p class=\"lead mb48 mb-xs-32\">\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam.\n                </p>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547586455),(369,'home-product-feature-slider','<section>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-6 col-md-push-3 text-center\">\n                <div class=\"image-slider slider-paging-controls controls-outside\">\n                    <ul class=\"slides\">\n                        <li class=\"mb32\">\n                            <img alt=\"App\" src=\"{path}product-3.png\" />\n                        </li>\n                        <li class=\"mb32\">\n                            <img alt=\"App\" src=\"{path}product-3.png\" />\n                        </li>\n                        <li class=\"mb32\">\n                            <img alt=\"App\" src=\"{path}product-3.png\" />\n                        </li>\n                    </ul>\n                </div>\n            </div>      \n            <div class=\"col-md-3 col-md-pull-6\">\n                <div class=\"mt80 mt-xs-80 text-right text-left-xs\">\n                    <h5 class=\"uppercase bold mb16\">Feature 1</h5>\n                    <p class=\"fade-1-4\">\n                        ivamus porta neque ac sollicitudin posuere. Curabitur in nibh cursus dui pharetra iaculis at id sem. Donec feugiat felis eu lacus facilisis tempor.\n                    </p>\n                </div>\n                \n                <div class=\"mt80 mt-xs-0 text-right text-left-xs\">\n                    <h5 class=\"uppercase bold mb16\">Feature 2</h5>\n                    <p class=\"fade-1-4\">\n                        ivamus porta neque ac sollicitudin posuere. Curabitur in nibh cursus dui pharetra iaculis at id sem. Donec feugiat felis eu lacus facilisis tempor.\n                    </p>\n                </div>\n            </div>        \n            <div class=\"col-md-3\">\n                <div class=\"mt80 mt-xs-0\">\n                    <h5 class=\"uppercase bold mb16\">Feature 3</h5>\n                    <p class=\"fade-1-4\">\n                        ivamus porta neque ac sollicitudin posuere. Curabitur in nibh cursus dui pharetra iaculis at id sem. Donec feugiat felis eu lacus facilisis tempor.\n                    </p>\n                </div>\n                \n                <div class=\"mt80 mt-xs-0\">\n                    <h5 class=\"uppercase bold mb16\">Feature 4</h5>\n                    <p class=\"fade-1-4\">\n                        ivamus porta neque ac sollicitudin posuere. Curabitur in nibh cursus dui pharetra iaculis at id sem. Donec feugiat felis eu lacus facilisis tempor.\n                    </p>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547586599),(370,'home-product-cta','<section class=\"bg-dark pb0\">\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-32\">\n            <div class=\"col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center btn-rounded\">\n                <h1 class=\"large\">Limited Supply</h1>\n                <p class=\"lead mb48 mb-xs-32 fade-1-4\"> \n                    We only made so many so don\'t wait. Get yours today. \n                </p>\n                <a class=\"btn btn-lg btn-filled\" href=\"{path}shop-product-fullwidth/Office_Chair\">Order Now</a>\n            </div>\n        </div>\n        \n        <div class=\"row\">\n            <div class=\"col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center\">\n                <img alt=\"App\" src=\"{path}product-5.png\" />\n            </div>  \n        </div>\n    </div>\n</section>','yes',40,1547585482),(371,'home-product-intro-content','<h1>Incredible product.<br />\nAmazing price.</h1>\n\n<p class=\"lead\">Hands-down the best in it&#39;s class.</p>','no',40,1547586149),(372,'contained-tiles','<div class=\"row\">\r\n   <div class=\"col-md-8 col-sm-10 col-sm-offset-1 col-md-offset-2\">\r\n        <div class=\"horizontal-tile\">\r\n            <div class=\"tile-left\">\r\n                <a href=\"#\">\r\n                    <div class=\"background-image-holder\">\r\n                        <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-5.jpg\" />\r\n                    </div>\r\n                </a>\r\n            </div>\r\n            <div class=\"tile-right bg-secondary\">\r\n                <div class=\"description\">\r\n                    <h4 class=\"mb8\">Tile Heading</h4>\r\n                    <h6 class=\"uppercase\">\r\n                        Subheading\r\n                    </h6>\r\n                    <p>\r\n                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        <div class=\"horizontal-tile\">\r\n            <div class=\"tile-left\">\r\n                <a href=\"#\">\r\n                    <div class=\"background-image-holder\">\r\n                        <img alt=\"image\" class=\"background-image\" src=\"{path}photo-gallery-album-3-photo-6.jpg\" />\r\n                    </div>\r\n                </a>\r\n            </div>\r\n            <div class=\"tile-right bg-secondary\">\r\n                <div class=\"description\">\r\n                    <h4 class=\"mb8\">Tile Heading</h4>\r\n                    <h6 class=\"uppercase\">\r\n                        Subheading\r\n                    </h6>\r\n                    <p>\r\n                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. \r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n   </div>\r\n</div>','yes',40,1547658703),(373,'captioned-images','<div class=\"row mb64 mb-xs-32\">\r\n    <div class=\"col-sm-6\">\r\n        <div class=\"image-caption cast-shadow mb-xs-32\">\r\n            <img alt=\"Captioned Image\" src=\"{path}photo-gallery-album-2-photo-6.jpg\" />\r\n            <div class=\"caption\">\r\n                <p>\r\n                    Here is an image caption\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class=\"col-sm-6\">\r\n        <div class=\"image-caption cast-shadow hover-caption\">\r\n            <img alt=\"Captioned Image\" src=\"{path}photo-gallery-album-2-photo-6.jpg\" />\r\n            <div class=\"caption\">\r\n                <p>\r\n                    Here is a hoverable image caption\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1547659187),(374,'bulleted-lists','<div class=\"row mb64 mb-xs-0\">\r\n    <div class=\"col-sm-6\">\r\n        <ul class=\"lead\" data-bullet=\"ti-arrow-right\">\r\n            <li>Here\'s bullet point number one</li>\r\n            <li>Now for the second point</li>\r\n            <li>Here comes the third</li>\r\n            <li>And the fourth bullet point</li>\r\n        </ul>\r\n    </div>\r\n    <div class=\"col-sm-6\">\r\n        <ul class=\"lead\" data-bullet=\"ti-check-box\">\r\n            <li>Here\'s bullet point number one</li>\r\n            <li>Now for the second point</li>\r\n            <li>Here comes the third</li>\r\n            <li>And the fourth bullet point</li>\r\n        </ul>\r\n    </div>\r\n</div>\r\n<div class=\"row\">\r\n    <div class=\"col-sm-6\">\r\n        <ul class=\"lead\" data-bullet=\"ti-plus\">\r\n            <li>Here\'s bullet point number one</li>\r\n            <li>Now for the second point</li>\r\n            <li>Here comes the third</li>\r\n            <li>And the fourth bullet point</li>\r\n        </ul>\r\n    </div>\r\n    <div class=\"col-sm-6\">\r\n        <ul class=\"lead\" data-bullet=\"ti-heart\">\r\n            <li>Here\'s bullet point number one</li>\r\n            <li>Now for the second point</li>\r\n            <li>Here comes the third</li>\r\n            <li>And the fourth bullet point</li>\r\n        </ul>\r\n    </div>\r\n</div>','yes',40,1548293821),(265,'home-2-intro','<section class=\"fullscreen cover parallax image-bg overlay\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover4.jpg\" />\n    </div>\n    <div class=\"container v-align-transform\">\n        <div class=\"row\">\n            <div class=\"col-sm-12 text-center\">\n                <cregion>home-2-intro-content</cregion>\n                <div class=\"modal-container mb0\">\n                    <div class=\"play-button inline large btn-modal\"></div>\n                    <div class=\"site_modal no-bg\">\n                        <iframe data-provider=\"youtube\" data-video-id=\"RxQfQThRDw0\" data-autoplay=\"1\"></iframe>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548124153),(200,'footer-3','<!-- call using \r\n	 <footer class=\"footer-1 bg-dark\">\r\n  or <footer class=\"footer-1 bg-secondary\"> \r\n  or <footer class=\"footer-1 bg-primary\">\r\n-->\r\n<div class=\"container\">\r\n    <div class=\"row\">\r\n        <hr class=\"mt0 mb40\" />\r\n    </div>\r\n    <div class=\"row\">\r\n        <div class=\"col-sm-4 text-center-sm\">\r\n            <a href=\"{path}\"><img class=\"image-xxs fade-half\" alt=\"Logo\" src=\"{path}logo-light.png\"></a>\r\n        </div>\r\n        <div class=\"col-sm-4 text-center text-center-sm\">\r\n            <span class=\"fade-half\">\r\n                &copy; My Organization<br>\r\n                <div class=\"modal-container\" style=\"display:inline-block\">\r\n    				<a class=\"btn-modal\" style=\"opacity:.3;cursor:pointer;font-weight:300\" href=\"#\">liveSite + Foundry</a>\r\n    				<div class=\"site_modal\">\r\n    					<h4>liveSite + Foundry</h4>\r\n    					<hr>\r\n    					<p>\r\n    						This site was created using <a href=\"https://livesite.com/community\" target=\"_blank\">liveSite</a> which manages all the back-end functionality and workflow for the site.  \r\n                            liveSite can be \"skinned\" with any front-end design or template you desire, so we selected the Foundry HTML Template \r\n                            as the basis for the front-end design we created for this site. Foundry is a solid front-end design and our conversion of it \r\n                            into a liveSite theme is provided as a great resource for you as you decide how you want to design your own liveSite front-end.\r\n                        </p>\r\n                        <p>\r\n                        	IMPORTANT: The Foundry Template is not free, so if you decide to use it as a basis for your own liveSite, you will \r\n                            need to purchase a license from \r\n                            <a href=\"//themeforest.net/item/foundry-multipurpose-html-variant-page-builder/11562108?ref=camelbackwebarchitects\" target=\"_blank\">Envato Market</a>.\r\n    					</p>\r\n                        <p>\r\n    						Enjoy!<br />\r\n                            Camelback Web Architects<br />\r\n    						simply innovative.\r\n    					</p>\r\n    				</div>\r\n    			</div>                \r\n            </span>\r\n        </div>\r\n        <div class=\"col-sm-4 text-right text-center-sm\">\r\n            <ul class=\"list-inline social-list\">\r\n                <li><a href=\"#\"><i class=\"ti-twitter-alt\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-facebook\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-youtube\"></i></a></li>\r\n                <li><a href=\"#\"><i class=\"ti-vimeo-alt\"></i></a></li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1548292701),(323,'home-training-bg-primary','<section class=\"bg-primary\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-4 col-md-offset-1 text-right text-left-xs col-sm-5\">\n                <cregion>home-training-bg-primary-content-left</cregion>\n            </div>\n            <div class=\"col-md-5 col-sm-7\">\n				<cregion>home-training-bg-primary-content-right</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546993929),(332,'home-training-image-square-right-content','<h3 class=\"uppercase\"><strong>Exams</strong></h3>\n\n<p>Get certified so everyone will know your level of skill!<br />\nOnce you have completed your classes you are ready for the exam.</p>\n\n<h6 class=\"uppercase\">Exam available anytime<br />\nMulti-exam discounts<br />\nCertification Award</h6>\n\n<p><a class=\"btn btn-lg\" href=\"{path}order-exam\">Order Exam</a></p>','no',40,1546994569),(334,'home-event-intro','<section class=\"pt120 pb120 image-bg overlay\">\n    <div class=\"background-image-holder\">\n        <img alt=\"Background\" class=\"background-image\" src=\"{path}cover1.jpg\" />\n    </div>\n    <div class=\"container\">\n        <div class=\"row v-align-children\">\n            <div class=\"col-sm-8 mb-xs-80\">\n                <cregion>home-event-intro-content</cregion>\n            </div>\n            <div class=\"col-sm-4 text-center text-left-xs\">\n                <div class=\"modal-container\">\n                    <div class=\"play-button btn-modal large inline\"></div>\n                    <div class=\"site_modal no-bg\">\n                        <iframe data-provider=\"vimeo\" data-video-id=\"88883554\" data-autoplay=\"1\"></iframe>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547066800),(333,'home-training-testimonial-content','<h4 class=\"uppercase\"><strong>&ldquo;Training Pros has allowed me to learn at my own pace and it&#39;s really paid off.&rdquo;</strong></h4>\n\n<h6 class=\"uppercase\">Troy Hamlin</h6>','no',40,1546994947),(324,'home-training-image-square-left','<section class=\"image-square left\">\n    <div class=\"col-md-6 image\">\n        <div class=\"background-image-holder\">\n            <img alt=\"image\" class=\"background-image\" src=\"{path}cover12.jpg\" />\n        </div>\n    </div>\n    <div class=\"col-md-6 col-md-offset-1 content\">\n        <div>\n            <cregion>home-training-image-square-left-content</cregion>\n        </div>\n    </div>\n</section>','yes',40,1546994672),(341,'home-event-intro-content','<h1 class=\"uppercase mb0\">LiveSite</h1>\n\n<h2 class=\"uppercase\">Conference</h2>\n\n<h2>Future trends<br />\nin web design using liveSite</h2>\n\n<h6 class=\"uppercase\">September 23 / San Fransisco</h6>\n\n<p><a class=\"btn btn-lg btn-primary\" href=\"{path}event-registration\">Register Now</a></p>','no',40,1547066773),(335,'home-event-text-panels','<section>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-4\">\n                <cregion>home-event-text-panels-content-1</cregion>\n            </div>\n            <div class=\"col-sm-4\">\n                <cregion>home-event-text-panels-content-2</cregion>\n            </div>\n            <div class=\"col-sm-4\">\n                <cregion>home-event-text-panels-content-3</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547067219),(325,'home-training-image-square-right','<section class=\"image-square right bg-secondary\">\n    <div class=\"col-md-6 image\">\n        <div class=\"background-image-holder\">\n            <img alt=\"image\" class=\"background-image\" src=\"{path}cover11.jpg\" />\n        </div>\n    </div>\n    <div class=\"col-md-6 content\">\n        <div class=\"text-right text-left-xs\">\n            <cregion>home-training-image-square-right-content</cregion>\n        </div>\n    </div>\n</section>','yes',40,1546994497),(326,'home-training-testimonial','<section class=\"image-bg overlay pt180 pb180 pt-xs-96 pb-xs-96\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover28.jpg\" />\n    </div>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-5 col-md-offset-1 col-sm-6\">\n                <cregion>home-training-testimonial-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546994932),(327,'auto-modal','<!-- add data-cookie like below to prevent reappearance of auto modal\n<div class=\"site_modal text-center image-bg overlay\" data-time-delay=\"3000\" data-cookie=\"auto-modal-has-been-seen\">\n-->\n<div class=\"site_modal text-center image-bg overlay\" data-time-delay=\"3000\">\n    <div class=\"background-image-holder\">\n        <img alt=\"Background\" class=\"background-image\" src=\"{path}cover28.jpg\" />\n    </div>\n    <div class=\"col-sm-12\">\n    	<h3 class=\"uppercase bold italic\">Auto Modal</h3>\n    	<hr class=\"mt24 mb24\">\n        <p>\n        	This is an auto modal that you can customize to appear once for each new site visitor after a period of time by editing the \'data-time-delay\' attribute.\n    	</p>\n        <p>\n            To prevent this modal from reappearing to visitors, add a \'data-cookie\' attribute.\n        </p>\n        <p>\n           	Then, to test your modal, delete the cookie from your browser and go into \"Edit Mode\" before accessing this page. \n            \"Edit Mode\" will ignore the fact that you have seen the modal already.\n        </p>\n    </div>\n</div>','yes',40,1548269669),(328,'home-training-intro-content','<h1 class=\"uppercase\"><span class=\"dark-opaque\">Training Pros</span></h1>\n\n<h4><span class=\"dark-opaque\">Online Training Center</span></h4>\n\n<h5 class=\"uppercase\" style=\"text-align: center;\"><span class=\"dark-opaque\">Skills for the next step in your career</span></h5>','no',40,1546993500),(329,'home-training-bg-primary-content-left','<h1 class=\"uppercase\"><strong>Skills For Life</strong></h1>\n\n<h5 class=\"uppercase\">&ldquo;Knowledge is power<br />\nto elEVate people&rdquo;</h5>','no',40,1546993984),(331,'home-training-image-square-left-content','<h2 class=\"uppercase\"><strong>Classes</strong></h2>\n\n<p>Classes can be attended from the comfort of your home or office.<br />\nWe will get your ready for your exam so you can be certified with your skills.</p>\n\n<h6 class=\"uppercase\">Virtual classroom<br />\nLearn at your own pace<br />\nMulti-session discounts<br />\nWeekly progress tracking</h6>\n\n<p><a class=\"btn btn-lg\" href=\"{path}class-registration\">Register</a></p>','no',40,1546994391),(330,'home-training-bg-primary-content-right','                <p>\r\n                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.\r\n                </p>\r\n                \r\n                <p>\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.\r\n                </p>\r\n                \r\n                <p>\r\n                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur\r\n                </p>','no',40,1546993617),(346,'home-event-testimonial-slider','<section class=\"image-bg overlay parallax\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover27.jpg\" />\n    </div>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"text-slider slider-paging-controls controls-outside relative\">\n                <ul class=\"slides\">\n                    <li>\n                        <div class=\"col-md-8 col-md-offset-2\">\n                            <div class=\"feature bordered text-center\">\n                                <h3>\"I attended liveSiteCon last year and the event was amazing. I learned so much and was able to come back and implement many features I really didn\'t even know existed.\"</h3>\n                                <h6 class=\"uppercase\">Brian Orr - Agency One</h6>\n                            </div>\n                        </div>\n                    </li>\n                    <li>\n                        <div class=\"col-md-8 col-md-offset-2\">\n                            <div class=\"feature bordered text-center\">\n                                <h3>\"The liveSite Conference is one of my professional highlights each year. It\'s incredible the power anf flexibility and to see it become a standard for web design and management is truly deserved.\"</h3>\n                                <h6 class=\"uppercase\">Jillian Goodman - Design NY</h6>\n                            </div>\n                        </div>\n                    </li>\n                </ul>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547069609),(336,'home-event-speakers','<section class=\"bg-secondary\">\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-sm-12 text-center\">\n                <h3>Meet our delightful panel of speakers</h3>\n                <p class=\"lead\">\n                    A collective of the web\'s brightest minds gathered in one place to discuss emerging trends.\n                </p>\n            </div>\n        </div>\n        <div class=\"row mb80 mb-xs-24\">\n            <div class=\"col-md-10 col-md-offset-1 p0\">\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Founder\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-01.jpg\"></a>\n                    <h5 class=\"mb0\">Bill Aiser</h5>\n                    <h6 class=\"uppercase\">Founder</h6>\n                </div>\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Managing-Director\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-02.jpg\"></a>\n                    <h5 class=\"mb0\">Jill Jacobs</h5>\n                    <h6 class=\"uppercase\">Managing Director</h6>\n                </div>\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Business-Manager\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-03.jpg\"></a>\n                    <h5 class=\"mb0\">Jake Von</h5>\n                    <h6 class=\"uppercase\">Business Manager</h6>\n                </div>\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Director-of-Marketing\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-04.jpg\"></a>\n                    <h5 class=\"mb0\">Marty McMillian</h5>\n                    <h6 class=\"uppercase\">Director of Marketing</h6>\n                </div>\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Director-of-Communications\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-05.jpg\"></a>\n                    <h5 class=\"mb0\">Niev Wiezel</h5>\n                    <h6 class=\"uppercase\">Director of Communications</h6>\n                </div>\n                <div class=\"col-sm-4 text-center mb40 mb-xs-24\">\n                    <a href=\"{path}staff-directory/Director-of-Technology\"><img alt=\"Pic\" class=\"mb24\" src=\"{path}staff-08.jpg\"></a>\n                    <h5 class=\"mb0\">Rick Easter</h5>\n                    <h6 class=\"uppercase\">Director of Technology</h6>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-sm-12 col-md-10 col-md-offset-1 text-center\">\n                <h3>Interested in speaking at liveSiteCon?</h3>\n                <p class=\"mb40 mb-xs-24\">\n                    We\'re always looking for talented and passionate speakers to contribute to the liveSiteCon experience.\n                </p>\n                <a class=\"btn btn-lg btn-filled\" href=\"{path}contact-us\">Contact Us</a>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547067648),(337,'home-event-pricing','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row v-align-children\">\r\n            <div class=\"col-sm-5\">\r\n                <h3>Join us for a day of\r\n                    <br /> ideas &amp; discussion.</h3>\r\n                <p class=\"lead mb40\">\r\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.\r\n                </p>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6\">\r\n                <div class=\"pricing-table pt-1 text-center emphasis\">\r\n                    <H5 class=\"uppercase\">Limited Seating</H5>\r\n                    <span class=\"price\">$129</span>\r\n                    <p class=\"lead\">Per Participant</p>\r\n                    <a class=\"btn btn-white\" href=\"{path}event-registration\">Register Now</a>\r\n                    <p><a href=\"{path}event-registration\">Exhibitors Welcome!</a></p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547065892),(338,'home-event-agenda','<section>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-sm-12 text-center\">\n                <h3>Strap yourself in for ideas</h3>\n                <p class=\"lead\">\n                    Prepare for a full day of discussion from some of the web\'s best and brightest.\n                </p>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-8 col-md-offset-2\">\n                <div class=\"tabbed-content button-tabs\">\n                    <ul class=\"tabs thirds mb64 mb-xs-24\">\n                        <li class=\"active\">\n                            <div class=\"tab-title\">\n                                <span>Morning</span>\n                            </div>\n                            <div class=\"tab-content text-left\">\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-1.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">9:30am - 10:30am</h6>\n                                            <h4>Ginny Lin - E-Commerce &amp; You</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-2.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">11:00am - 12:00pm</h6>\n                                            <h4>Patrick Peterson - Sell Anything Online</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                            </div>\n                        </li>\n                        <li>\n                            <div class=\"tab-title\">\n                                <span>Afternoon</span>\n                            </div>\n                            <div class=\"tab-content text-left\">\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-3.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">1:30pm - 02:30pm</h6>\n                                            <h4>Jordan Varro - Marketing 101</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda.\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-1.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">3:00pm - 4:00pm</h6>\n                                            <h4>Ginny Lin - Online Sales Conversations</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                            </div>\n                        </li>\n                        <li>\n                            <div class=\"tab-title\">\n                                <span>Evening</span>\n                            </div>\n                            <div class=\"tab-content text-left\">\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-2.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">5:30pm - 06:30pm</h6>\n                                            <h4>Patrick Peterson - Frontend Frameworks</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                                <div>\n                                    <div class=\"overflow-hidden\">\n                                        <img alt=\"Pic\" class=\"mb24 pull-left\" src=\"{path}avatar-3.png\" />\n                                        <div class=\"pull-left p32 p0-xs pt24\">\n                                            <h6 class=\"uppercase mb8 number\">7:00pm - 08:00pm</h6>\n                                            <h4>Jordan Varro - Auto Campaigns</h4>\n                                        </div>\n                                    </div>\n                                    <p>\n                                        At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda.\n                                    </p>\n                                    <hr class=\"mt40 mb40 mt-xs-0 mb-xs-24\">\n                                </div>\n                            </div>\n                        </li>\n                    </ul>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547069306),(347,'home-music-intro','<section class=\"fullscreen cover parallax image-bg overlay\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover29.jpg\" />\n    </div>\n    <div class=\"container v-align-transform\">\n        <div class=\"row\">\n            <div class=\"col-sm-12 text-center\">\n                <div class=\"mt-xs-64\">\n                	<cregion>home-music-intro-content</cregion>\n                </div>\n                <div class=\"modal-container mb0\">\n                    <div class=\"play-button btn-modal inline large mt-xs-0\"></div>\n                    <div class=\"site_modal no-bg\">\n                        <iframe data-provider=\"youtube\" data-video-id=\"okSpoz6ZUOU\" data-autoplay=\"1\"></iframe>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n    <div class=\"align-bottom text-center hidden-xs\">\n        <a class=\"btn btn-white mb32\" href=\"{path}buy-tickets\">Schedule &amp; Tickets</a>\n        <ul class=\"list-inline social-list mb24\">\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-instagram\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-facebook\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-soundcloud\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-youtube\"></i>\n                </a>\n            </li>\n        </ul>\n    </div>\n</section>','yes',40,1547155183),(339,'home-event-twitter-strip','<section class=\"bg-dark\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-10 col-md-offset-1 text-center\">\r\n                <i class=\"ti-twitter-alt icon icon color-primary mb40 mb-xs-24\"></i>\r\n                <div class=\"twitter-feed tweets-slider large\">\r\n                    <div class=\"tweets-feed\" data-widget-id=\"714599501978214400\">\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>\r\n<section class=\"bg-secondary\">\r\n    <div class=\"container\">\r\n        <div class=\"row mb32 mb-xs-24\">\r\n            <div class=\"col-md-8 col-md-offset-2 text-center\">\r\n                <h2 class=\"uppercase mt8 mb16\">#liveSitecon</h2>\r\n                <h6 class=\"uppercase mb0\">Follow Us For Updates</h6>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547066056),(340,'home-event-signup-strip','<section class=\"bg-primary\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-12 text-center\">\n                <h3 class=\"mb16 mb16-xs inline-block p32 p0-xs\" style=\"padding-right: 1em\">Early-Bird Registration Ends Soon!</h3>\n                <a class=\"btn btn-lg btn-white mb8\" href=\"{path}event-registration\">Register Today</a>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547068676),(343,'home-event-text-panels-content-1','<h4 class=\"uppercase\"><span class=\"primary-color\">Front-End Design</span></h4>\n\n<h5>Efficient Frameworks</h5>\n\n<h5>JavaScript Components</h5>\n\n<h5>Mobile-First Design</h5>','no',40,1547067545),(344,'home-event-text-panels-content-2','<h4 class=\"uppercase\"><span class=\"primary-color\">User Interface</span></h4>\n\n<h5>UI/UX Concepts</h5>\n\n<h5>Purposeful Workflow</h5>\n\n<h5>Just-in-Time Data Gathering</h5>','no',40,1547067583),(357,'home-firm-testimonial','<section class=\"image-bg bg-light parallax overlay pt160 pb160 pt-xs-80 pb-xs-80\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover2.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-2 mt32 mb32\">\r\n                <i class=\"ti-rocket icon-lg mt16\"></i>\r\n            </div>\r\n            <div class=\"col-sm-6\">\r\n                <h3 class=\"mb32 light-opaque\">\"Our business took off like a rocket when we implemented a solid technology platform for our online organization.\"</h3>\r\n                <p class=\"light-opaque\">\r\n                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547239823),(345,'home-event-text-panels-content-3','<h4 class=\"uppercase\"><span class=\"primary-color\">Web Experience</span></h4>\n\n<h5>Better Calls to Action</h5>\n\n<h5>Buyer Behaviors</h5>\n\n<h5>Improving Conversions</h5>','no',40,1547067583),(348,'home-music-text-parallax','<section class=\"bg-primary pt160 pb160 pt-xs-80 pb-xs-80\">\n    <div class=\"container\">\n        <div class=\"row text-center\">\n            <div class=\"col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2\">\n                <cregion>home-music-text-parallax-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>\n<section class=\"pt240 pb240 pt-xs-80 pb-xs-80 parallax\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover30.jpg\" />\n    </div>\n</section>','yes',40,1547154318),(352,'home-music-intro-content','<h1 class=\"large uppercase\">0rchestra</h1>\n\n<h5 class=\"uppercase\">Live in Concert</h5>','no',40,1547154645),(353,'about-us-staff-leadership','<section>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-12\">\n                <h4 class=\"uppercase text-center mb40\">Our Leadership</h4>\n            </div>\n            <div class=\"col-md-4 col-sm-6\">\n                <div class=\"image-tile outer-title text-center\">\n                    <a href=\"{path}staff-directory-item?r=B7LQ9FFXFS\"><img alt=\"Pic\" src=\"{path}staff-01.jpg\" /></a>\n                    <div class=\"title mb16\">\n                        <h5 class=\"uppercase mb0\">Bill Aiser</h5>\n                        <span>Founder</span>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-4 col-sm-6\">\n                <div class=\"image-tile outer-title text-center\">\n                    <a href=\"{path}staff-directory-item?r=1WLELY5GTL\"><img alt=\"Pic\" src=\"{path}staff-02.jpg\" /></a>\n                    <div class=\"title mb16\">\n                        <h5 class=\"uppercase mb0\">Jill Jacobs</h5>\n                        <span>Managing Director</span>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-4 col-sm-6\">\n                <div class=\"image-tile outer-title text-center\">  \n                    <a href=\"{path}staff-directory-item?r=QMYMQ99TC2\"><img alt=\"Pic\" src=\"{path}staff-03.jpg\" /></a>\n                    <div class=\"title mb16\">\n                        <h5 class=\"uppercase mb0\">Jake Von</h5>\n                        <span>Business Manager</span>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-sm-12 text-center\">\n                <a class=\"btn btn-primary btn-lg\" href=\"{path}staff-directory\">View Staff Directory</a>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547236464),(349,'home-music-see-hear-follow','<section class=\"bg-primary pt120 pb120 pt-xs-80 pb-xs-80\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-6 col-sm-10 col-sm-offset-1 col-md-offset-0\">\r\n                <h2 class=\"uppercase mb40 mb-xs-24 text-center\">See Us</h2>\r\n                <hr class=\"mb40 mb-xs-24 fade-half\">\r\n                <div class=\"upcoming-concerts-widget\">\r\n                    <system>upcoming-concerts-widget</system>\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"col-md-6 col-sm-10 col-sm-offset-1 col-md-offset-0\">\r\n                <h2 class=\"uppercase mb40 mb-xs-24 text-center\">Hear Us</h2>\r\n                <hr class=\"mb40 mb-xs-24 fade-half\">\r\n                <div class=\"embed-holder\">\r\n                    <iframe width=\"100%\" height=\"450\" scrolling=\"no\" frameborder=\"no\" src=\"//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/playlists/223850335&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true\"></iframe>\r\n                </div>\r\n                \r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>\r\n<section class=\"pt0 pb120 pt-xs-0 pb-xs80 bg-primary\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h2 class=\"uppercase large mb80 mb-xs-24\">Follow Us</h2>\r\n                <ul class=\"list-inline social-list mb24 spread-children-large\">\r\n                    <li class=\"fade-on-hover\">\r\n                        <a href=\"#\">\r\n                            <i class=\"icon icon-lg ti-instagram\"></i>\r\n                        </a>\r\n                    </li>\r\n                    <li class=\"fade-on-hover\">\r\n                        <a href=\"#\">\r\n                            <i class=\"icon icon-lg ti-facebook\"></i>\r\n                        </a>\r\n                    </li>\r\n                    <li class=\"fade-on-hover\">\r\n                        <a href=\"#\">\r\n                            <i class=\"icon icon-lg ti-soundcloud\"></i>\r\n                        </a>\r\n                    </li>\r\n                    <li class=\"fade-on-hover\">\r\n                        <a href=\"#\">\r\n                            <i class=\"icon icon-lg ti-vimeo-alt\"></i>\r\n                        </a>\r\n                    </li>\r\n                </ul>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547153645),(351,'home-music-text-parallax-content','<h2 class=\"uppercase\"><strong>One</strong> Orchestra</h2>\r\n\r\n<h3 class=\"uppercase\"><strong>One</strong> Stage</h3>\r\n\r\n<h4 class=\"uppercase\"><strong>One</strong> Special Evening</h4>\r\n\r\n<p class=\"mb64 mb-xs-24\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>\r\n','no',40,1547154340),(354,'home-firm-intro','<section class=\"fullscreen image-bg parallax background-multiply\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover5.jpg\" />\n    </div>\n    <div class=\"container v-align-transform\">\n        <div class=\"row\">\n            <div class=\"col-sm-12\">\n                <cregion>home-firm-intro-content</cregion>\n            </div>\n        </div>\n    </div>\n    <div class=\"align-bottom text-center\">\n        <ul class=\"list-inline social-list mb24\">\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-twitter-alt\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-facebook\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-linkedin\"></i>\n                </a>\n            </li>\n            <li>\n                <a href=\"#\">\n                    <i class=\"ti-vimeo-alt\"></i>\n                </a>\n            </li>\n        </ul>\n    </div>\n</section>','yes',40,1547240367),(355,'home-firm-about-us','<section>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-12\">\n                <h6 class=\"uppercase\">About Us</h6>\n                <hr class=\"mb160 mb-xs-24\">\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-10\">\n                <h1 class=\"thin\">Providing technology consulting since \'96</h1>\n            </div>\n        </div>\n        <div class=\"row mb-xs-0\">\n            <div class=\"col-md-6 col-sm-8\">\n                <p class=\"lead\">\n                    We partner with passionate entrepreneurs and startups to create relevence and profits.\n                </p>\n            </div>\n        </div>\n        <div class=\"row mb160\">\n            <div class=\"col-sm-12\">\n                <a class=\"btn btn-filled mb0\" href=\"{path}about-us\">Find Out More</a>\n            </div>\n        </div>        \n        <div class=\"row\">\n            <div class=\"col-sm-4 mb-xs-24\">\n                <h1 class=\"large color-primary mb0\">1400+</h1>\n                <h5 class=\"color-primary mb0\">Satisfied Customers</h5>\n            </div>\n            <div class=\"col-sm-4 mb-xs-24\">\n                <h1 class=\"large color-primary mb0\">$1.2b+</h1>\n                <h5 class=\"color-primary mb0\">Customer Growth</h5>\n            </div>\n            <div class=\"col-sm-4\">\n                <h1 class=\"large color-primary mb0\">90%</h1>\n                <h5 class=\"color-primary mb0\">Customer Retention</h5>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548196893),(356,'home-firm-industries','<section class=\"bg-dark\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12\">\r\n                <h6 class=\"uppercase\">Industries Served</h6>\r\n                <hr class=\"mb160 mb-xs-24\">\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-10\">\r\n                <h1 class=\"thin\">Focussed, Diverse, Disruptive.</h1>\r\n            </div>\r\n        </div>\r\n        <div class=\"row mb160 mb-xs-0\">\r\n            <div class=\"col-md-6 col-sm-8\">\r\n                <p class=\"lead\">\r\n                    liveSite maintains a portfolio spanning multiple sectors. Disruptive technology is our unifying theme.\r\n                </p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-3 col-sm-6 mb-xs-24\">\r\n                <i class=\"ti-pulse icon mb32\"></i>\r\n                <h6 class=\"uppercase\">Healthcare</h6>\r\n                <ul>\r\n                    <li>Metro Sync</li>\r\n                    <li>Health Global</li>\r\n                    <li>H&amp; R Hospitals</li>\r\n                    <li>MediCorp</li>\r\n                    <li>Heart Infusion</li>\r\n                    <li>Doctors Stat</li>\r\n                </ul>\r\n            </div>\r\n            <div class=\"col-md-3 col-sm-6 mb-xs-24\">\r\n                <i class=\"ti-gift icon mb32\"></i>\r\n                <h6 class=\"uppercase\">Retail</h6>\r\n                <ul>\r\n                    <li>Big Box Gifts</li>\r\n                    <li>National Chocolates</li>\r\n                    <li>Best Orchard</li>\r\n                    <li>Zoom Shoes</li>\r\n                    <li>Stereo-rama</li>\r\n                    <li>Cookie Kingdom</li>\r\n                </ul>\r\n            </div>\r\n            <div class=\"col-md-3 col-sm-6 mb-xs-24\">\r\n                <i class=\"ti-world icon mb32\"></i>\r\n                <h6 class=\"uppercase\">Non Profit</h6>\r\n                <ul>\r\n                    <li>Saving Partners</li>\r\n                    <li>Giving International</li>\r\n                    <li>Donate To Cause</li>\r\n                    <li>Max Foundation</li>\r\n                    <li>Feed The World</li>\r\n                    <li>Pass It On Charity</li>\r\n                </ul>\r\n            </div>\r\n            <div class=\"col-md-3 col-sm-6 mb-xs-24\">\r\n                <i class=\"ti-harddrives icon mb32\"></i>\r\n                <h6 class=\"uppercase\">Cloud Services</h6>\r\n                <ul>\r\n                    <li>Mega Host</li>\r\n                    <li>WebOne Inc.</li>\r\n                    <li>Clouds R Us</li>\r\n                    <li>GrandeHost</li>\r\n                    <li>Hosting+ Corp.</li>\r\n                </ul>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547239778),(193,'megamenu','<!-- wrap <div class=\"nav-utility\"> with <nav> or <nav class=\"bg-dark\"> or <nav class=\"absolute transparent\">-->\r\n<div class=\"nav-utility\">\r\n    <div class=\"module right\">\r\n        <span class=\"sub\"><cart></cart></span>\r\n    </div>\r\n    <div class=\"module right\">\r\n        <span class=\"sub\"><login>site-login</login></span>\r\n    </div>        \r\n    <if view-access folder-id=\"199\">\r\n        <div class=\"module right\">\r\n            <a href=\"{path}my-conversations\"><i class=\"ti-comments\">&nbsp;</i></a>\r\n            <span class=\"sub\"><a href=\"{path}my-conversations\">conversations</a></span>\r\n        </div>\r\n    </if>\r\n</div>\r\n<div class=\"nav-bar\">\r\n    <div class=\"module left\">\r\n        <a href=\"{path}\">\r\n            <img class=\"logo logo-light\" alt=\"logo\" src=\"{path}logo-light.png\" />\r\n            <img class=\"logo logo-dark\" alt=\"logo\" src=\"{path}logo-dark.png\" />\r\n        </a>\r\n    </div>\r\n    <div class=\"module widget-handle mobile-toggle right visible-sm visible-xs\">\r\n        <i class=\"ti-menu\"></i>\r\n    </div>\r\n    <div class=\"module-group right\">      \r\n        <div class=\"module left\">\r\n            <menu>site-menu</menu>\r\n        </div>\r\n        <div class=\"module widget-handle search-widget-handle left\">\r\n            <div class=\"search\">\r\n                <a href=\"{path}site-search\"><i class=\"ti-search\"></i></a>\r\n                <span class=\"title\">Search Site</span>\r\n            </div>\r\n            <div class=\"function\">\r\n                <form class=\"search-form\" action=\"{path}site-search\">\r\n                    <input type=\"text\" value=\"\" name=\"query\" placeholder=\"Search Site\" />\r\n                </form>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1548294014),(247,'flickr-feed','<ul class=\"flickr-feed masonry masonryFlyIn\" data-user-id=\"8826848@N03\" data-album-id=\"72157633398893288\"></ul>','yes',40,1546364917),(248,'home-1-intro-slide-1','<h1 class=\"large\">The enterprise website platform without the enterprise price tag.</h1>\n\n<h6 class=\"uppercase\">A Powerful Website Back-end Solution You Won&#39;t Outgrow.</h6>\n\n<p><a class=\"btn btn-primary btn-lg\" href=\"https://livesite.com/community\" target=\"_blank\">Learn More</a></p>','no',40,1548294408),(249,'home-1-intro-slide-2','<h1 class=\"large\">We take the stress out of managing your enterprise website.</h1>\n\n<h6 class=\"uppercase\">Everything you need is included. No PlugIn HASSLES.</h6>\n\n<p><a class=\"btn btn-primary btn-lg\" href=\"https://livesite.com/community\" target=\"_blank\">Learn More</a></p>','no',40,1548291684),(205,'twitter-feed','<div class=\"widget\">\r\n    <h6 class=\"title\">Latest Updates</h6>\r\n    <hr>\r\n    <a href=\"//twitter.com/ilovelivesite\" target=\"_blank\">\r\n    <div class=\"twitter-feed\">\r\n        <div class=\"tweets-feed\" data-widget-id=\"714599501978214400\"></div>\r\n    </div>\r\n    </a>\r\n</div>','yes',40,1546028209),(250,'home-agency-canvas-map','<section class=\"pt120 pb120 image-bg\">\r\n    <div class=\"map-canvas\" data-map-zoom=\"6\" data-address=\"lucia,californio[nomarker];san francisco;los angeles\" data-maps-api-key=\"AIzaSyCfo_V3gmpPm1WzJEC9p_sRbgvyVbiO83M\" data-maps-api-key-2=\"AIzaSyAVsRr4i3ovR45biSx0DoWRswL1kfdO9ZU\"></div>\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-6 overflow-hidden\">\r\n                <h3 class=\"mb64 mb-xs-32\">Map</h3>\r\n                <h4 class=\"mb16\">example@exampledomain.com</h4>\r\n                <ul class=\"list-inline social-list mb40 mb-xs-24\">\r\n                    <li><a href=\"#\"><i class=\"ti-twitter-alt\"></i></a></li>\r\n                    <li><a href=\"#\"><i class=\"ti-facebook\"></i></a></li>\r\n                    <li><a href=\"#\"><i class=\"ti-dribbble\"></i></a></li>\r\n                    <li><a href=\"#\"><i class=\"ti-vimeo-alt\"></i></a></li>\r\n                </ul>\r\n                <div class=\"col-sm-6 p0\">\r\n                    <h6 class=\"uppercase mb0\">San Francisco</h6>\r\n                    <p>\r\n                        1111 Any Street<br />\r\n                        Anytown, State, Zip<br />\r\n                        (555) 555-5555\r\n                    </p>\r\n                </div>\r\n                <div class=\"col-sm-6 p0\">\r\n                    <h6 class=\"uppercase mb0\">Los Angeles</h6>\r\n                    <p>\r\n                        2222 Any Street<br />\r\n                        Anytown, State, Zip<br />\r\n                        (555) 555-5555\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1547587803),(251,'home-product-image-zoom','<section class=\"pt0 pb0 pt-xs-80 bg-primary image-zoom\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-5 col-sm-6 mt104 mt-sm-80 mt-xs-0 text-center-xs mb-xs-40\">\n                <h2>Product Zoom</h2>\n                <h5 class=\"mb160 mb-xs-80\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h5>\n                <a class=\"btn btn-lg\" href=\"{path}case-study\">View Case Study</a>\n            </div>\n            <div class=\"col-md-4 col-md-push-2 col-sm-6\">\n                <img alt=\"Pic\" src=\"{path}product-6.png\" />\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547587673),(252,'home-agency-pullup','<section class=\"portfolio-pullup\">\r\n    <div class=\"container\">\r\n        <div class=\"row row-gapless masonry masonryFlyIn\">\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-1.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-2.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-3.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-4.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-5.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\r\n                <div class=\"image-tile inner-title hover-reveal text-center\">\r\n                    <a href=\"#\">\r\n                        <img alt=\"Pic\" src=\"{path}photo-gallery-album-1-photo-6.jpg\" />\r\n                        <div class=\"title\">\r\n                            <h5 class=\"uppercase mb0\">Photo Title</h5>\r\n                            <span>Photo Description</span>\r\n                        </div>\r\n                    </a>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546970502),(253,'home-agency-intro','<section class=\"bg-primary background-multiply pt240 pb240 pt-xs-120 pb-xs-120 overlay image-bg parallax\">\n    <div class=\"background-image-holder\">\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover27.jpg\" />\n    </div>\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center\">\n                <p><img alt=\"Logo\" class=\"image-small mb40 mb-xs-0\" src=\"{path}logo-light.png\" /></p>\n				<cregion>home-agency-intro-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1546970623),(254,'home-agency-testimonial','<section class=\"pt0 pb180 pt-xs-80 pb-xs-80\">\r\n<div class=\"container\">\r\n    <div class=\"row\">\r\n        <div class=\"col-md-6 col-md-offset-3 text-center\">\r\n            <h3 class=\"mb48 mb-xs-32\">What our clients say...</h3>\r\n            <div class=\"text-slider slider-paging-controls text-center relative\">\r\n                <ul class=\"slides\">\r\n                    <li>\r\n                        <h5>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</h5>\r\n                        <div class=\"quote-author\">\r\n                            <img alt=\"Author\" class=\"image-xs mb16\" src=\"{path}avatar-1.png\" />\r\n                            <h6 class=\"uppercase mb0\">Ginny Lin</h6>\r\n                            <span>Client</span>\r\n                        </div>\r\n                    </li>\r\n                    <li>\r\n                        <h5>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</h5>\r\n                        <div class=\"quote-author\">\r\n                            <img alt=\"Author\" class=\"image-xs mb16\" src=\"{path}avatar-2.png\" />\r\n                            <h6 class=\"uppercase mb0\">Patrick Petterson</h6>\r\n                            <span>Client</span>\r\n                        </div>\r\n                    </li>\r\n                    <li>\r\n                        <h5>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</h5>\r\n                        <div class=\"quote-author\">\r\n                            <img alt=\"Author\" class=\"image-xs mb16\" src=\"{path}avatar-3.png\" />\r\n                            <h6 class=\"uppercase mb0\">Jordan Varro</h6>\r\n                            <span>Client</span>\r\n                        </div>\r\n                    </li>\r\n                </ul>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>\r\n</section>','yes',40,1546970450),(255,'home-agency-cta-strip','<section class=\"bg-dark pt64 pb64\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h2 class=\"mb8\">Start a Services Project</h2>\r\n                <p class=\"lead mb40 mb-xs-24\">\r\n                    Our services professional are ready to start working for you today.\r\n                </p>\r\n                <a class=\"btn btn-filled btn-lg mb0\" href=\"{path}order-services\">Order Services</a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546970445),(256,'home-1-intro','<section class=\"cover fullscreen image-slider slider-all-controls controls-inside parallax\">\n    <ul class=\"slides\">\n    	<li class=\"overlay image-bg\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover6.jpg\" />\n            </div>\n            <div class=\"container v-align-transform\">\n                <div class=\"row\">\n                    <div class=\"col-sm-10 col-sm-offset-1 text-center\">\n                        <cregion>home-1-intro-slide-1</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n        <li class=\"overlay image-bg\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover8.jpg\" />\n            </div>\n            <div class=\"container v-align-transform\">\n                <div class=\"row\">\n                    <div class=\"col-sm-offset-1 text-center col-sm-10\">\n                        <cregion>home-1-intro-slide-2</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n    </ul>\n</section>','yes',40,1546379520),(257,'home-1-screenshot','<section>\r\n    <div class=\"container\">\r\n        <div class=\"row mb80 mb-xs-0\">\r\n            <div class=\"col-md-8 col-md-offset-2 text-center\">\r\n                <h1>Sleek, Powerful &amp; Stylish</h1>\r\n\r\n                <p>liveSite is a complete website platform offering all the back-end features you will ever need. Add any front-end design and power any online business, non-profit, school, or professional services agency.</p>\r\n            </div>\r\n        </div>\r\n        <div class=\"row v-align-children\">\r\n            <div class=\"col-md-7 col-sm-6 text-center mb-xs-24\">\r\n                <img class=\"cast-shadow\" alt=\"Screenshot\" src=\"{path}screenshot.jpg\" />\r\n            </div>\r\n            <div class=\"col-md-4 col-md-offset-1 col-sm-5 col-sm-offset-1\">\r\n                <h3>Deploy a slick, modern enterprise site</h3>\r\n                <p>\r\n                    liveSite is an all-in-one solution so you can focus on your online business and not your website technology. Customize the front-end and the back-end seperately for the ultimate flexibility!\r\n                </p>\r\n                <a class=\"btn btn-lg\" href=\"https://livesite.com/community\" target=\"_blank\">Get liveSite</a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548262004),(258,'home-1-icon-boxes','<section class=\"bg-secondary\">\r\n    <div class=\"container\">\r\n        <div class=\"row mb64 mb-xs-24\">\r\n            <div class=\"col-sm-12 col-md-10 col-md-offset-1 text-center\">\r\n                <h3>liveSite comes complete with dozens of customizable website applications for any type of enterprise.</h3>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-6 text-center\">\r\n                <div class=\"feature\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-layers-alt icon-lg mb40 mb-xs-24 inline-block color-primary\"></i>\r\n                        <h5 class=\"uppercase\">Page-based Features</h5>\r\n                    </div>\r\n                    <p>\r\n                        liveSite includes 40 different Page Types \r\n                        <br /> that can be combined together create unique and powerful\r\n                        <br /> website app workflows without any database coding.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-sm-6 text-center\">\r\n                <div class=\"feature\">\r\n                    <div class=\"text-center\">\r\n                        <i class=\"ti-heart icon-lg mb40 mb-xs-24 inline-block color-primary\"></i>\r\n                        <h5 class=\"uppercase\">Built for Designers</h5>\r\n                    </div>\r\n                    <p>\r\n                        Front-end Designer can import any HTML pages\r\n                        <br />or full templates into liveSite, use any responsive platform,\r\n                        <br />and customize and preview code changes in real-time.\r\n                    </p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548119251),(259,'home-1-image-edge','<section class=\"image-edge pt120 pb120 pt-xs-40 pb-xs-40\">\r\n    <div class=\"col-md-6 col-sm-4 p0 col-md-push-6 col-sm-push-8\">\r\n        <img alt=\"Screenshot\" class=\"cast-shadow mb-xs-24\" src=\"{path}screenshot-2.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"col-md-5 col-md-pull-0 col-sm-7 col-sm-pull-4 v-align-transform\">\r\n            <h3 class=\"mb40 mb-xs-16\">Get started fast with this unique, pre-built template.</h3>\r\n            <p class=\"lead mb40\">\r\n                Each liveSite comes complete with this entire site, including over 100 pages of carefully thought out features for almost any organization. Also included is over 100 free stock photos.\r\n            </p>\r\n            <div class=\"feature boxed\">\r\n                <p>\r\n                    &ldquo;Flexible design, incredible features, and prompt support make this an outstanding product. Well done Camelback Web Architects!&rdquo;\r\n                </p>\r\n                <div class=\"spread-children\">\r\n                    <img alt=\"Pic\" class=\"image-xs\" src=\"{path}avatar-2.png\" />\r\n                    <span>Patrick Peterson</span>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548119647),(260,'home-1-parallax-slider','<section class=\"image-bg overlay parallax pt120 pb120 pt-xs-40 pb-xs-40\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover13.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"row mb40 mb-xs-24\">\r\n            <div class=\"col-sm-12 text-center spread-children\">\r\n                <img class=\"image-xs\" alt=\"Pic\" src=\"{path}avatar-1.png\" />\r\n                <img class=\"image-xs\" alt=\"Pic\" src=\"{path}avatar-2.png\" />\r\n                <img class=\"image-xs\" alt=\"Pic\" src=\"{path}avatar-3.png\" />\r\n            </div>\r\n        </div>\r\n        <div class=\"row mb16 mb-xs-0\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h3>People just like you are already loving liveSite</h3>\r\n            </div>\r\n        </div>\r\n        <div class=\"row\">\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1\">\r\n                <div class=\"text-slider slider-arrow-controls text-center relative\">\r\n                    <ul class=\"slides\">\r\n                        <li>\r\n                            <p class=\"lead\">Mauris rutrum sollicitudin sagittis. Nunc in blandit lectus. Pellentesque mattis, felis eget lacinia consequat, lectus libero maximus quam, nec lacinia dui erat sed urna.</p>\r\n                            <div class=\"quote-author\">\r\n                                <h6 class=\"uppercase mb0\">Ginny Lin</h6>\r\n                                <span>ABC Companies</span>\r\n                            </div>\r\n                        </li>\r\n                        <li>\r\n                            <p class=\"lead\">Vivamus porta neque ac sollicitudin posuere. Curabitur in nibh cursus dui pharetra iaculis at id sem. Donec feugiat felis eu lacus facilisis tempor.</p>\r\n                            <div class=\"quote-author\">\r\n                                <h6 class=\"uppercase mb0\">Patrick Peterson</h6>\r\n                                <span>Non Profit Organization</span>\r\n                            </div>\r\n                        </li>\r\n                        <li>\r\n                            <p class=\"lead\">Nulla accumsan mauris eget urna commodo, a placerat felis interdum. Aliquam maximus dui neque, sed accumsan eros scelerisque sed. Nunc at facilisis neque, a mollis erat.</p>\r\n                            <div class=\"quote-author\">\r\n                                <h6 class=\"uppercase mb0\">Jordan Varro</h6>\r\n                                <span>Commerce Guru</span>\r\n                            </div>\r\n                        </li>\r\n                    </ul>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548119712),(366,'home-product-intro','<section class=\"image-bg parallax pt240 pb180 pt-xs-80 pb-xs-80\">\n	<div class=\"background-image-holder\">\n		<img alt=\"image\" class=\"background-image\" src=\"{path}product-1.jpg\" />\n	</div>			\n	<div class=\"container\">\n        <div class=\"row mt-xs-80\">\n            <div class=\"col-md-8 col-sm-12 mt-40\">\n                <cregion>home-product-intro-content</cregion>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547586276),(261,'home-1-icon-tabs','<section class=\"bg-secondary pb0\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-12 text-center\">\r\n                <h2 class=\"mb64 mb-xs-24\">You can do it all with liveSite</h2>\r\n            </div>\r\n            <div class=\"col-md-8 col-md-offset-2 col-sm-12 text-center\">\r\n                <div class=\"tabbed-content icon-tabs\">\r\n                    <ul class=\"tabs\">\r\n                        <li class=\"active\">\r\n                            <div class=\"tab-title\">\r\n                                <i class=\"ti-palette icon\"></i>\r\n                                <span>Design</span>\r\n                            </div>\r\n                            <div class=\"tab-content\">\r\n                                <p>\r\n                                    You can import your entire existing website and any custom responsive HTML design, \r\n                                    so you’ll never have to compromise the look of your website for the sake of your website platform again. \r\n                                    And with front-end designers in mind, you can preview any design changes across all your production site pages \r\n                                    without the need for a second development site.\r\n                                </p>\r\n                            </div>\r\n                        </li>\r\n                        <li>\r\n                            <div class=\"tab-title\">\r\n                                <i class=\"ti-user icon\"></i>\r\n                                <span>User Delegation</span>\r\n                            </div>\r\n                            <div class=\"tab-content\">\r\n                                <p>\r\n									There are infinite ways to delegate user access to any feature or section of your website securely. \r\n                                    Users can be given trial access to areas and have their access expire after any period of time. \r\n                                    Users can also manage their own account profiles including time zones, contact information, mailing lists, and passwords.\r\n                                </p>\r\n                            </div>\r\n                        </li>\r\n                        <li>\r\n                            <div class=\"tab-title\">\r\n                                <i class=\"ti-settings icon\"></i>\r\n                                <span>App Workflow</span>\r\n                            </div>\r\n                            <div class=\"tab-content\">\r\n                                <p>\r\n									Create custom database forms to collect information from any group of users on your site and create custom and \r\n                                    secure data views to display the information collected to privileged users. Syncs with your contact database and \r\n                                    trigger autoresponders, notifications, and personalized email drip campaigns based on actions taken by any user.\r\n                                </p>\r\n                            </div>\r\n                        </li>\r\n                        <li>\r\n                            <div class=\"tab-title\">\r\n                                <i class=\"ti-cloud icon\"></i>\r\n                                <span>Hosted Software</span>\r\n                            </div>\r\n                            <div class=\"tab-content\">\r\n                                <p>\r\n                                    We didn\'t invent the concept of “versionless software” but we know you don\'t care what version you are running as long as it\'s the most current!\r\n									We host everything so you don\'t have to worry about managing software, security, pci compliance, SSL certificates, email campaigns sending, \r\n                                    site monitoring, backups, and servers.\r\n                                </p>\r\n                            </div>\r\n                        </li>\r\n                    </ul>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548120861),(262,'home-1-video-strip','<section class=\"bg-secondary\">\r\n    <div class=\"container\">\r\n        <div class=\"row v-align-children\">\r\n            <div class=\"col-md-7 col-sm-6 text-center mb-xs-24\">\r\n                <div class=\"local-video-container\">\r\n                    <div class=\"background-image-holder\">\r\n                        <img alt=\"Background Image\" class=\"background-image\" src=\"{path}cover20.jpg\">\r\n                    </div>\r\n                    <video controls=\"\">\r\n                        <source src=\"{path}video.webm\" type=\"video/webm\">\r\n                            <source src=\"{path}video.mp4\" type=\"video/mp4\">\r\n                                <source src=\"{path}video.ogv\" type=\"video/ogg\">\r\n                    </video>\r\n                    <div class=\"play-button\"></div>\r\n                </div>\r\n            </div>\r\n            <div class=\"col-md-4 col-md-offset-1 col-sm-5 col-sm-offset-1\">\r\n                <h3>Design that looks less \'theme\' and more you.</h3>\r\n                <p>\r\n                    liveSite will work seamlessly with any front-end design you create or purchase so you are never limited to a particular look or style.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1548120991),(263,'home-1-masonry-boxes','<section>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-sm-12 col-md-10 col-md-offset-1 text-center\">\n                <h3>Sign up for your own liveSite website today.</h3>\n            </div>\n        </div>\n        <div class=\"row masonry masonryFlyIn\">\n            <div class=\"col-md-4 col-sm-12 masonry-item mb30\">\n                <div class=\"feature boxed cast-shadow-light mb0\">\n                    <h2 class=\" color-primary mb0\">175+</h2>\n                    <h6 class=\"uppercase color-primary\">Page Templates</h6>\n                    <p>\n                        Pre-made HTML page templates including fully-functional shop, forum, blog, support tickets, sales conversations, \n                        membership and staff portals.\n                    </p>\n                </div>\n            </div>\n            <div class=\"col-md-4 masonry-item mb30\">\n                <div class=\"boxed feature cast-shadow-light mb0\">\n                    <!--<i class=\"icon ti-infinite color-primary inline-block mb0\"></i>-->\n                    <h2 class=\"color-primary mb0\">Infinite</h2>\n                    <h6 class=\"uppercase color-primary\">Layout Possibilities</h6>\n                    <p>\n                        With tons of purpose-built content blocks and animated features, there\'s a mind-boggling number of \n                        design and feature combinations you can create.\n                    </p>\n                </div>\n            </div>\n            <div class=\"col-md-4 masonry-item mb30\">\n                <div class=\"boxed feature cast-shadow-light mb0\">\n                    <h2 class=\"color-primary mb0\">15</h2>\n                    <h6 class=\"uppercase color-primary\">Home Page Concepts</h6>\n                    <p>\n                        Fresh and unique home page concepts are included so you can get started quickly, \n                        or mix and match home page blocks to create your own.\n                    </p>\n                </div>\n            </div>\n            <div class=\"col-md-4 masonry-item mb30\">\n                <div class=\"boxed feature cast-shadow-light mb0\">\n                    <h2 class=\"color-primary mb0\">100+</h2>\n                    <h6 class=\"uppercase color-primary\">Stock Photos</h6>\n                    <p>\n                        We packed liveSite with over 100 high quality royalty-free stock photos carefully sized and compressed. \n                        Mix and match them to create a stylish backdrop for your content.\n                    </p>\n                </div>\n            </div>\n            <div class=\"col-md-4 masonry-item mb30\">\n                <div class=\"boxed feature cast-shadow-light mb0\">\n                    <h2 class=\"color-primary mb0\">Responsive</h2>\n                    <h6 class=\"uppercase color-primary\">Design</h6>\n                    <p>\n                        All pages are designed to support all modern browsers and all devices. Use any front-end responsive design \n                        framwework like Bootstrap, Foundation, or your own.\n                    </p>\n                </div>\n            </div>\n            <div class=\"col-md-4 masonry-item mb30\">\n                <div class=\"boxed feature cast-shadow-light mb0\">\n                    <h2 class=\"color-primary mb0\">Designer</h2>\n                    <h6 class=\"uppercase color-primary\">Friendly</h6>\n                    <p>\n                        liveSite includes a powerful Page Designer that allows you to edit your liveSite from front-end to \n                        back-end and preview your changes across all devices sizes with ease.\n                    </p>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548262803),(376,'system-region','<!-- collapsable and responsive primary system region -->\n<section class=\"m0 p0\">\n    <div class=\"container m0\">\n        <div class=\"row m0\">\n            <div class=\"col-sm-12 m0\" style=\"min-height: 0px\">\n                <style> .software_social_networking {margin-top:0 !important; margin-bottom:0 !important} </style>\n                <system></system>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1547674068),(288,'home-2-intro-content','<h1 class=\"mb16\">Build beautiful websites with exceptional features using any front-end design</h1>\n\n<h6 class=\"uppercase mb32\">A Powerful Website Back-end Solution</h6>','no',40,1548123858),(271,'home-3-intro','<section class=\"cover fullscreen image-slider slider-all-controls controls-inside parallax\">\n    <ul class=\"slides\">\n        <li class=\"overlay image-bg\">\n            <div class=\"background-image-holder\">\n                <img alt=\"image\" class=\"background-image\" src=\"{path}cover19.jpg\" />\n            </div>\n            <div class=\"container v-align-transform\">\n                <div class=\"row\">\n                    <div class=\"col-md-10 col-md-offset-1 col-sm-12 text-center\">\n						<cregion>home-3-intro-content</cregion>\n                    </div>\n                </div>\n            </div>\n        </li>\n    </ul>\n</section>','yes',40,1546470620),(266,'home-2-text-strip','<section class=\"pt120 pb120 pt-xs-80 pb-xs-80\">\r\n    <div class=\"container\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-10 col-md-offset-1 col-sm-12 text-center\">\r\n                <cregion>home-2-text-strip-content</cregion>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546470013),(267,'home-2-masonry-strip','<section class=\"projects p0 bg-dark\">\n    <div class=\"row masonry-loader\">\n        <div class=\"col-sm-12 text-center\">\n            <div class=\"spinner\"></div>\n        </div>\n    </div>\n    <div class=\"row masonry masonryFlyIn\">\n        <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\n            <div class=\"image-tile inner-title hover-reveal text-center\">\n                <a href=\"#\">\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-photo-1.jpg\" />\n                    <div class=\"title\">\n                        <h5 class=\"uppercase mb0\">Photo Title</h5>\n                        <span>Sometimes pictures are better than words.</span>\n                    </div>\n                </a>\n            </div>\n        </div>\n        <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\n            <div class=\"image-tile inner-title hover-reveal text-center\">\n                <a href=\"#\">\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-album-3-photo-3.jpg\" />\n                    <div class=\"title\">\n                        <h5 class=\"uppercase mb0\">Photo Title</h5>\n                        <span>Sometimes pictures are better than words.</span>\n                    </div>\n                </a>\n            </div>\n        </div>\n        <div class=\"col-md-4 col-sm-6 masonry-item project\" data-filter=\"Category\">\n            <div class=\"image-tile inner-title hover-reveal text-center\">\n                <a href=\"#\">\n                    <img alt=\"Pic\" src=\"{path}photo-gallery-photo-2.jpg\" />\n                    <div class=\"title\">\n                        <h5 class=\"uppercase mb0\">Photo Title</h5>\n                        <span>Sometimes pictures are better than words.</span>\n                    </div>\n                </a>\n            </div>\n        </div>        \n    </div>\n</section>','yes',40,1548124623),(268,'home-2-icon-boxes','<section>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-md-10 col-md-offset-1 col-sm-12 text-center\">\n                <h3>Our Expertise</h3>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-5 col-md-offset-1 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-ruler-pencil icon inline-block mb16 fade-3-4\"></i>\n                <h4>Planning</h4>\n                <p>\n                    Each client brings their own set of unique skills to the table. In the planning phase, we identify all the players, assess the skills required, determine what services are needed, and come up with an estimate and plan to successfully complete the website project.\n                </p>\n            </div>\n            <div class=\"col-md-5 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-archive icon inline-block mb16 fade-3-4\"></i>\n                <h4>Migration</h4>\n                <p>\n                    Since most of our clients come to liveSite after struggling with their current website platform, we have excellent built-in tools to help you move your existing web content into liveSite, even if you don’t have access to the web host. \n 				</p>\n            </div>\n            <div class=\"col-md-5 col-md-offset-1 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-layers-alt icon inline-block mb16 fade-3-4\"></i>\n                <h4>Build Out</h4>\n                <p>\n					Now that you have a backup of your old website, you can begin editing your new site menu, configuring your liveSite apps and workflow, and refactoring your migrated content along with new content into your new liveSite pages. No application or database programming is required.\n                </p>\n            </div>\n            <div class=\"col-md-5 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-settings icon inline-block mb16 fade-3-4\"></i>\n                <h4>Customization</h4>\n                <p>\n					liveSite’s amazing list of features is usually all most organizations need. But for some, they have very specialized features or need liveSite to integrate with other systems. To meet these needs, we offer a full range of application, database, and middleware programming.\n                </p>\n            </div>\n            <div class=\"col-md-5 col-md-offset-1 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-palette icon inline-block mb16 fade-3-4\"></i>\n                <h4>Design</h4>\n                <p>\n					The Design phase is what most clients associate with a new website. Everyone gets excited about the “look and feel”. It is a very important aspect of the website. This is the phase that most clients want to do first. But over the years, we have come to realize that until you have built out your site with content, navigation, and have enabled and tested the functionality and user flows through your site, you really aren’t ready to dress it up.\n                </p>\n            </div>\n            <div class=\"col-md-5 col-sm-6 mb40 mb-xs-24\">\n                <i class=\"ti-book icon inline-block mb16 fade-3-4\"></i>\n                <h4>Training</h4>\n                <p>\n					Some clients are equipped to tweak and grow their liveSite website themselves with the assistance our basic Support. But for many clients, especially in the beginning, additional and more in-depth training and guidance may be required to help them manage their liveSite. We offer custom and tailored video tutorials for all services we provide which we have found to be a very cost-effective solution.\n                </p>\n            </div> \n        </div>\n    </div>\n</section>','yes',40,1548291972),(269,'home-2-parallax-text-strip','<section class=\"image-bg parallax overlay\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"image\" class=\"background-image\" src=\"{path}cover22.jpg\" />\r\n    </div>\r\n    <div class=\"container\">\r\n        <div class=\"row mb40 mb-xs-24\">\r\n            <div class=\"col-sm-10 col-sm-offset-1 text-center\">\r\n                <cregion>home-2-parallax-text-strip-content</cregion>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>','yes',40,1546470235),(270,'home-parallax-cta','<section class=\"image-bg parallax overlay\">\n    <div class=\"background-image-holder\">\n        <img alt=\"Background Image\" class=\"background-image\" src=\"{path}photo-gallery-album-1-photo-6.jpg\">\n    </div>\n    <div class=\"container\">\n        <div class=\"row mb64 mb-xs-24\">\n            <div class=\"col-sm-6 col-md-5 text-right text-center-xs\">\n                <h1 class=\"large mb8\">4,000+</h1>\n                <h6 class=\"uppercase\">Enterprises Use liveSite</h6>\n            </div>\n            <div class=\"col-md-2 text-center hidden-sm hidden-xs\">\n                <i class=\"ti-infinite icon icon-lg mt8 mt-xs-0\"></i>\n            </div>\n            <div class=\"col-sm-6 col-md-5 text-center-xs\">\n                <h1 class=\"large mb8\">Limitless</h1>\n                <h6 class=\"uppercase\">Designs & Features</h6>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-sm-12 text-center\">\n                <h3 class=\"mb40 mb-xs-24\">liveSite\'s purpose-driven UI design and powerful\n                    <br /> features will help propel your online presence\n                    <br /> to the next level.</h3>\n                <a class=\"btn btn-lg btn-filled\" href=\"https://livesite.com/community\">Learn More</a>\n            </div>\n        </div>\n    </div>\n</section>','yes',40,1548195010),(209,'emailer-footer','<p style=\"text-align: center;\">&copy; Copyright</p>','no',40,1546026017),(241,'thumbnail-image-slider','<div class=\"image-slider slider-thumb-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover12.jpg\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover15.jpg\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover16.jpg\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}cover14.jpg\" />\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1546364773),(213,'members-sidebar','<div class=\"widget\">\n<h6 class=\"title\">Member Links</h6>\n\n<hr />\n<ul class=\"link-list\">\n	<li><a href=\"{path}members-calendar\">Members Calendar</a></li>\n	<li><a href=\"{path}members-directory\">Members Directory</a></li>\n	<li><a href=\"{path}classified-ads\">Classified Ads</a></li>\n	<li><a href=\"{path}members-access\">Membership &amp; Renewal</a></li>\n</ul>\n</div>','no',40,1547831081),(210,'support-ticket-button-access','<!-- for customers only (any order will result in user getting access to folder id 284) -->\r\n<if view-access folder-id=\"284\">\r\n    <p style=\"text-align: center;\"><a class=\"btn\" href=\"/new-support-ticket\">New Support Ticket</a></p>\r\n</if>\r\n<else>\r\n    <p style=\"text-align: center;\"><strong>Once you purchase any product or service you will have instant access to create new support tickets.</strong></p>\r\n    <p style=\"text-align: center;\"><a class=\"btn disabled\" style=\"opacity: .3\" href=\"#\">New Support Ticket</a></p>\r\n</else>','yes',40,1543944020),(220,'progress-bar-2','<div class=\"progress-bars\">\n    <div class=\"progress progress-2\">\n        <span class=\"title\">Schools & Churches</span>\n        <div class=\"bar-holder\">\n            <div class=\"progress-bar\" data-progress=\"90\"></div>\n        </div>\n    </div>\n    <div class=\"progress progress-2\">\n        <span class=\"title\">Non-Profit Organizations</span>\n        <div class=\"bar-holder\">\n            <div class=\"progress-bar\" data-progress=\"70\"></div>\n        </div>\n    </div>\n    <div class=\"progress progress-2\">\n        <span class=\"title\">e-Commerce Stores</span>\n        <div class=\"bar-holder\">\n            <div class=\"progress-bar\" data-progress=\"50\"></div>\n        </div>\n    </div>\n    <div class=\"progress progress-2\">\n        <span class=\"title\">Professional Services</span>\n        <div class=\"bar-holder\">\n            <div class=\"progress-bar\" data-progress=\"40\"></div>\n        </div>\n    </div>\n</div>','yes',40,1546022984),(221,'accordion-1-open','<ul class=\"accordion accordion-1 one-open\">\r\n    <li class=\"active\">\r\n        <div class=\"title\">\r\n            <span>Simple Panels</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Toggle Information</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Nice Touch</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n</ul>','yes',40,1545940534),(222,'accordion-1','<ul class=\"accordion accordion-1\">\r\n    <li class=\"active\">\r\n        <div class=\"title\">\r\n            <span>Simple Panels</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Toggle Information</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Nice Touch</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n</ul>','yes',40,1545940553),(223,'accordion-2','<ul class=\"accordion accordion-2\">\r\n    <li class=\"active\">\r\n        <div class=\"title\">\r\n            <span>Simple Panels</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Toggle Information</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Nice Touch</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n</ul>','yes',40,1545940780),(224,'accordion-2-open','<ul class=\"accordion accordion-2 one-open\">\r\n    <li class=\"active\">\r\n        <div class=\"title\">\r\n            <span>Simple Panels</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Toggle Information</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n    <li>\r\n        <div class=\"title\">\r\n            <span>Nice Touch</span>\r\n        </div>\r\n        <div class=\"content\">\r\n            <p>\r\n                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n            </p>\r\n        </div>\r\n    </li>\r\n</ul>','yes',40,1545940790),(225,'countdown-timer',' <div class=\"countdown\" data-date=\"01/01/2020\"></div>','yes',40,1548293349),(226,'basic-modal','<div class=\"modal-container\">\n    <a class=\"btn btn-lg btn-modal\" href=\"#\">Basic Modal</a>\n    <div class=\"site_modal\">\n        <h4>Basic Modal</h4>\n        <hr>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n	</div>\n</div> ','yes',40,1545949997),(245,'support-icon-boxes-on-image','<div class=\"row\">\r\n<div class=\"col-sm-4\">\r\n    <div class=\"feature feature-1 boxed\">\r\n        <div class=\"text-center\">\r\n            <i class=\"ti-agenda icon\"></i>\r\n            <h5 class=\"uppercase mb16\">Research & Ideate</h5>\r\n        </div>\r\n        <p>\r\n            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n        </p>\r\n    </div>\r\n</div>\r\n<div class=\"col-sm-4\">\r\n    <div class=\"feature feature-1 boxed\">\r\n        <div class=\"text-center\">\r\n            <i class=\"ti-pencil-alt2 icon\"></i>\r\n            <h5 class=\"uppercase mb16\">Design & Iterate</h5>\r\n        </div>\r\n        <p>\r\n            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n        </p>\r\n    </div>\r\n</div>\r\n<div class=\"col-sm-4\">\r\n    <div class=\"feature feature-1 boxed\">\r\n        <div class=\"text-center\">\r\n            <i class=\"ti-package icon\"></i>\r\n            <h5 class=\"uppercase mb16\">Ship & Support</h5>\r\n        </div>\r\n        <p>\r\n            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n        </p>\r\n    </div>\r\n</div>','yes',40,1546285624),(227,'scrolling-modal','<div class=\"modal-container\">\n    <a class=\"btn btn-lg btn-modal\" href=\"#\">Scrolling Modal</a>\n    <div class=\"site_modal\">\n        <h4>Scrolling Modal</h4>\n        <hr>\n        <p>\n            When the content of a modal extends beyond the height of the window, the modal automatically becomes scrollable.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n        </p>\n        <p>\n            This is the end of the long modal content.\n        </p>\n    </div>\n</div>','yes',40,1545950009),(228,'image-modal','<div class=\"modal-container\">\r\n    <a class=\"btn btn-lg btn-modal\" href=\"#\">Image Modal</a>\r\n    <div class=\"site_modal text-center image-bg overlay\">\r\n        <div class=\"background-image-holder\">\r\n            <img alt=\"Background\" class=\"background-image\" src=\"{path}cover28.jpg\" />\r\n        </div>\r\n        <h4>Image Modal</h4>\r\n        <hr>\r\n        <p>\r\n            Convergence unicorn thinker-maker-doer ideate thinker-maker-doer pitch deck piverate food-truck long shadow disrupt. Sticky note engaging latte integrate driven convergence food-truck pitch deck. Quantitative vs. qualitative disrupt sticky note piverate 360 campaign co-working bootstrapping long shadow actionable insight agile latte. Thinker-maker-doer bootstrapping integrate personas long shadow Steve Jobs entrepreneur sticky note ship it grok sticky note.\r\n        </p>\r\n    </div>\r\n</div>','yes',40,1547602940),(229,'full-height-modal','<div class=\"modal-container\">\r\n    <a class=\"btn btn-lg btn-modal\" href=\"#\">Full Height Modal</a>\r\n    <div class=\"site_modal text-center image-bg overlay fullscreen\">\r\n        <div class=\"background-image-holder\">\r\n            <img alt=\"Background\" class=\"background-image\" src=\"{path}cover28.jpg\" />\r\n        </div>\r\n        <div style=\"display: table; height: 100%\">\r\n            <div style=\"display: table-cell; vertical-align: middle; height: auto\">\r\n        		<h4>Full Height Modal</h4>\r\n        		<hr>\r\n        		<p>\r\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n        		</p>\r\n        		<p>\r\n			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.	Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n        		</p>\r\n                <hr>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1547660417),(230,'iframe-modal','<div class=\"modal-container\">\n    <a class=\"btn btn-lg btn-modal\" href=\"#\">Iframe Modal</a>\n    <div class=\"site_modal text-center\">\n        <iframe src=\"//www.youtube.com/embed/RxQfQThRDw0\" frameborder=\"0\" allowfullscreen></iframe>\n    </div>\n</div>','yes',40,1545950032),(231,'pricing-table','<div class=\"row mb80\">\r\n    <div class=\"col-md-4 col-sm-4\">\r\n        <div class=\"pricing-table pt-2 text-center\">\r\n            <h5 class=\"uppercase\">Plan 1</h5>\r\n            <span class=\"price\">$50</span>\r\n            <p class=\"lead\">Per Month</p>\r\n            <a class=\"btn btn-filled\" href=\"#\">Get Started</a>\r\n            <ul>\r\n                <li>\r\n                    <strong>Unlimited</strong> things make sense\r\n                </li>\r\n                <li>\r\n                    <strong>Fully Secure</strong> is always a plus\r\n                </li>\r\n                <li>\r\n                    <strong>One Year</strong> terms is best\r\n                </li>\r\n                <li>\r\n                    <strong>FREE</strong> complimentary things as well\r\n                </li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n    <div class=\"col-md-4 col-sm-4\">\r\n        <div class=\"pricing-table pt-2 boxed text-center\">\r\n            <h5 class=\"uppercase\">Plan 2</h5>\r\n            <span class=\"price\">$100</span>\r\n            <p class=\"lead\">Per Month</p>\r\n            <a class=\"btn btn-filled\" href=\"#\">Get Started</a>\r\n            <ul>\r\n                <li>\r\n                    <strong>Unlimited</strong> things make sense\r\n                </li>\r\n                <li>\r\n                    <strong>Fully Secure</strong> is always a plus\r\n                </li>\r\n                <li>\r\n                    <strong>One Year</strong> terms is best\r\n                </li>\r\n                <li>\r\n                    <strong>FREE</strong> complimentary things as well\r\n                </li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n    <div class=\"col-md-4 col-sm-4\">\r\n        <div class=\"pricing-table pt-2 emphasis text-center\">\r\n            <h5 class=\"uppercase\">Plan 3</h5>\r\n            <span class=\"price\">$250</span>\r\n            <p class=\"lead\">Per Month</p>\r\n            <a class=\"btn btn-white\" href=\"#\">Get Started</a>\r\n            <ul>\r\n                <li>\r\n                    <strong>Unlimited</strong> things make sense\r\n                </li>\r\n                <li>\r\n                    <strong>Fully Secure</strong> is always a plus\r\n                </li>\r\n                <li>\r\n                    <strong>One Year</strong> terms is best\r\n                </li>\r\n                <li>\r\n                    <strong>FREE</strong> complimentary things as well\r\n                </li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>','yes',40,1548194854),(232,'large-centered-icon-boxes','<div class=\"row mb80\">\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1\">\n            <div class=\"text-center\">\n                <i class=\"ti-panel icon\"></i>\n                <h5 class=\"uppercase\">Powerful Back-end Software</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1 bordered\">\n            <div class=\"text-center\">\n                <i class=\"ti-palette icon\"></i>\n                <h5 class=\"uppercase\">Flexible Front-End Design</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1 boxed\">\n            <div class=\"text-center\">\n                <i class=\"ti-cloud icon\"></i>\n                <h5 class=\"uppercase\">100% Cloud-Based</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n</div>','yes',40,1546018554),(233,'large-circular-icon-boxes','<div class=\"row mb80\">\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-2\">\n            <div class=\"text-center\">\n                <i class=\"ti-panel icon-sm\"></i>\n                <h5 class=\"uppercase\">Powerful Back-End Features</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-2 bordered\">\n            <div class=\"text-center\">\n                <i class=\"ti-palette icon-sm\"></i>\n                <h5 class=\"uppercase\">Flexible Front-End Design</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-2 boxed\">\n            <div class=\"text-center\">\n                <i class=\"ti-cloud icon-sm\"></i>\n                <h5 class=\"uppercase\">100% Cloud-Based</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n</div>','yes',40,1546018562),(234,'small-left-icon-boxes','<div class=\"row mb80\">\n    <div class=\"col-sm-6\">\n        <div class=\"feature feature-3\">\n            <div class=\"left\">\n                <i class=\"ti-panel icon-sm\"></i>\n            </div>\n            <div class=\"right\">\n                <h5 class=\"uppercase mb16\">Powerful Back-End Software</h5>\n                <p>\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n                </p>\n            </div>\n        </div>\n    </div>\n    <div class=\"col-sm-6\">\n        <div class=\"feature feature-3 bordered\">\n            <div class=\"left\">\n                <i class=\"ti-palette icon-sm\"></i>\n            </div>\n            <div class=\"right\">\n                <h5 class=\"uppercase mb16\">Flexible Front-End Design</h5>\n                <p>\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n                </p>\n            </div>\n        </div>\n    </div>\n</div>','yes',40,1546018569),(235,'large-left-icon-boxes','<div class=\"row mb80\">\n    <div class=\"col-sm-6\">\n        <div class=\"feature feature-3 feature-4\">\n            <div class=\"left\">\n                <i class=\"ti-panel icon-lg\"></i>\n            </div>\n            <div class=\"right\">\n                <h5 class=\"uppercase mb16\">Powerful Back-End Software</h5>\n                <p>\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n                </p>\n            </div>\n        </div>\n    </div>\n    <div class=\"col-sm-6\">\n        <div class=\"feature feature-3 feature-4 boxed\">\n            <div class=\"left\">\n                <i class=\"ti-palette icon-lg\"></i>\n            </div>\n            <div class=\"right\">\n                <h5 class=\"uppercase mb16\">Flexible Front-End Design</h5>\n                <p>\n                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n                </p>\n            </div>\n        </div>\n    </div>\n</div>','yes',40,1546018576),(236,'icon-boxes-on-image','<div class=\"row mb80\">\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1\">\n            <div class=\"text-center\">\n                <i class=\"ti-panel icon\"></i>\n                <h5 class=\"uppercase\">Powerful Back-End Software</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1 bordered\">\n            <div class=\"text-center\">\n                <i class=\"ti-palette icon\"></i>\n                <h5 class=\"uppercase\">Flexible Front-End Design</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n    <div class=\"col-sm-4\">\n        <div class=\"feature feature-1 boxed\">\n            <div class=\"text-center\">\n                <i class=\"ti-cloud icon\"></i>\n                <h5 class=\"uppercase\">100% Cloud-Based</h5>\n            </div>\n            <p>\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\n            </p>\n        </div>\n    </div>\n</div>','yes',40,1546018582),(237,'local-video','<div class=\"local-video-container\">\r\n    <div class=\"background-image-holder\">\r\n        <img alt=\"Background Image\" class=\"background-image\" src=\"{path}cover13.jpg\">\r\n    </div>\r\n    <video controls=\"\">\r\n        <source src=\"{path}video.webm\" type=\"video/webm\">\r\n        <source src=\"{path}video.mp4\" type=\"video/mp4\">\r\n        <source src=\"{path}video.ogv\" type=\"video/ogg\">\r\n    </video>\r\n    <div class=\"play-button\"></div>\r\n</div>','yes',40,1547659049),(238,'iframe-video','<div class=\"embed-video-container embed-responsive embed-responsive-16by9\">\r\n    <iframe class=\"embed-responsive-item\" src=\"//www.youtube.com/embed/RxQfQThRDw0\" frameborder=\"0\" allowfullscreen></iframe>\r\n</div>','yes',40,1548195972),(239,'local-video-modal','<div class=\"modal-container mb16\">\r\n    <div class=\"play-button btn-modal large dark inline\"></div>\r\n    <div class=\"site_modal no-bg\">\r\n        <video controls=\"\">\r\n            <source src=\"{path}video.webm\" type=\"video/webm\">\r\n                <source src=\"{path}video.mp4\" type=\"video/mp4\">\r\n                    <source src=\"{path}video.ogv\" type=\"video/ogg\">\r\n        </video>\r\n    </div>\r\n</div>','yes',40,1546016501),(240,'iframe-video-modal','<div class=\"modal-container mb16\">\r\n    <div class=\"play-button btn-modal large dark inline\"></div>\r\n    <div class=\"site_modal no-bg\">\r\n        <iframe data-provider=\"vimeo\" data-video-id=\"25737856\" data-autoplay=\"1\"></iframe>\r\n    </div>\r\n</div>','yes',40,1546016553),(242,'lightbox-grid','<div class=\"lightbox-grid square-thumbs\" data-gallery-title=\"Gallery\">\r\n    <ul>\r\n        <li>\r\n            <a href=\"{path}cover1.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover1.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover2.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover2.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover3.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover3.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover4.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover4.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover5.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover5.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover6.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover6.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover7.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover7.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}cover8.jpg\" data-lightbox=\"true\">\r\n                <div class=\"background-image-holder\">\r\n                    <img alt=\"image\" class=\"background-image\" src=\"{path}cover8.jpg\" />\r\n                </div>\r\n            </a>\r\n        </li>\r\n    </ul>\r\n</div>','yes',40,1547659863),(243,'testimonial-slider','<div class=\"row\">\r\n<div class=\"col-sm-8 col-sm-offset-2 text-center\">\r\n    <h3 class=\"mb64 uppercase\">People &nbsp;<i class=\"ti-heart\"></i>&nbsp; liveSite</h3>\r\n    <div class=\"testimonials text-slider slider-arrow-controls\">\r\n        <ul class=\"slides\">\r\n            <li>\r\n                <p class=\"lead\">\r\n                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n                </p>\r\n                <div class=\"quote-author\">\r\n                    <img alt=\"Avatar\" src=\"{path}avatar-1.png\" />\r\n                    <h6 class=\"uppercase\">Ginny Lin</h6>\r\n                    <span>liveSite Customer</span>\r\n                </div>\r\n            </li>\r\n            <li>\r\n                <p class=\"lead\">\r\n                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n                </p>\r\n                <div class=\"quote-author\">\r\n                    <img alt=\"Avatar\" src=\"{path}avatar-2.png\" />\r\n                    <h6 class=\"uppercase\">Patrick Petterson</h6>\r\n                    <span>liveSite Customer</span>\r\n                </div>\r\n            </li>\r\n            <li>\r\n                <p class=\"lead\">A fine example of atomic design brought to life. \r\n                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\r\n                </p>\r\n                <div class=\"quote-author\">\r\n                    <img alt=\"Avatar\" src=\"{path}avatar-3.png\" />\r\n                    <h6 class=\"uppercase\">Jordan Varro</h6>\r\n                    <span>liveSite Customer</span>\r\n                </div>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n</div>\r\n</div>','yes',40,1547156623),(244,'support-icon-boxes','<div class=\"row\">\r\n<div class=\"col-md-4 col-sm-6\">                            \r\n    <div class=\"feature feature-3 mb-xs-24 mb64\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-star icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">Experts</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"col-md-4 col-sm-6\">\r\n    <div class=\"feature feature-3 mb-xs-24 mb64\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-medall icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">20 Years of Trust</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"col-md-4 col-sm-6\">\r\n    <div class=\"feature feature-3 mb-xs-24 mb64\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-money icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">Pay As You Go</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"col-md-4 col-sm-6\">\r\n    <div class=\"feature feature-3 mb-xs-24 mb64\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-comment-alt icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">Dedicated</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"col-md-4 col-sm-6\">\r\n    <div class=\"feature feature-3 mb-xs-24\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-infinite icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">Unlimited</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"col-md-4 col-sm-6\">\r\n    <div class=\"feature feature-3 mb-xs-24\">\r\n        <div class=\"left\">\r\n            <i class=\"ti-timer icon-sm\"></i>\r\n        </div>\r\n        <div class=\"right\">\r\n            <h5 class=\"uppercase mb16\">Fast &amp; Friendly</h5>\r\n            <p>\r\n                Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.\r\n            </p>\r\n        </div>\r\n    </div>\r\n</div>\r\n</div>','yes',40,1548292049);
/*!40000 ALTER TABLE `cregion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `symbol` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_rate` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `base` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'US Dollar','USD','$','1.00000',0,1537472973,0,1548296703,1),(2,'British pound','GBP','&pound;','0.75510',1,1537472973,0,1548294923,0),(3,'Canadian dollar','CAD','$','1.28690',1,1537472973,0,1548294923,0),(4,'Euro','EUR','&euro;','0.86030',1,1537472973,0,1548291623,0),(5,'Japanese yen','JPY','&yen;','114.12400',1,1537472973,0,1548294924,0),(6,'Australian dollar','AUD','$','1.30340',1,1537472973,0,1548294925,0);
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_form_confirmation_pages`
--

DROP TABLE IF EXISTS `custom_form_confirmation_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_form_confirmation_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `continue_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_form_confirmation_pages`
--

LOCK TABLES `custom_form_confirmation_pages` WRITE;
/*!40000 ALTER TABLE `custom_form_confirmation_pages` DISABLE KEYS */;
INSERT INTO `custom_form_confirmation_pages` VALUES (14,292,'',0),(15,303,'',0),(25,308,'',0);
/*!40000 ALTER TABLE `custom_form_confirmation_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_form_pages`
--

DROP TABLE IF EXISTS `custom_form_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_form_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `confirmation_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `label_column_width` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `submitter_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submitter_email_from_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `submitter_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `administrator_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `administrator_email_to_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `administrator_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quiz` tinyint(4) NOT NULL DEFAULT '0',
  `quiz_pass_percentage` tinyint(4) NOT NULL DEFAULT '0',
  `membership_days` int(10) unsigned NOT NULL DEFAULT '0',
  `administrator_email_bcc_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `membership` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `membership_start_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `watcher_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submitter_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submitter_email_format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `submitter_email_body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `administrator_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `administrator_email_format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `administrator_email_body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `confirmation_type` enum('message','page') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'message',
  `confirmation_message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `confirmation_alternative_page` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `confirmation_alternative_page_contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `confirmation_alternative_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `return_type` enum('custom_form','message','page') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'custom_form',
  `return_message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `return_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `return_alternative_page` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `return_alternative_page_contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `return_alternative_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `pretty_urls` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `private` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `private_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `private_days` int(10) unsigned NOT NULL DEFAULT '0',
  `private_start_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `auto_registration` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `offer` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offer_days` int(10) unsigned NOT NULL DEFAULT '0',
  `offer_eligibility` enum('everyone','new_contacts','existing_contacts') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'everyone',
  `save` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_form_pages`
--

LOCK TABLES `custom_form_pages` WRITE;
/*!40000 ALTER TABLE `custom_form_pages` DISABLE KEYS */;
INSERT INTO `custom_form_pages` VALUES (60,183,'CONVERSATION',1,'Send Now',85,'',222,'example@example.com','^^subject^^',222,'example@example.com','^^subject^^',46,0,0,0,'',0,0,0,1,'html','',1,'html','','page','<h3>Thank you!</h3>\r\n\r\n<p class=\"lead\">We have received your request and will contact you shorty. You can add a reply to this new <a href=\"{path}my-conversations\">conversation</a>&nbsp;at any time.</p>\r\n',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,1,0,0,0,'everyone',0),(84,472,'MAILING-LIST',1,'Send Offer',579,'',656,'example@example.com','Mailing List Confirmation',508,'example@example.com','Mailing List Confirmation',35,0,0,0,'',0,0,0,1,'html','',1,'html','','page','<p><strong>Congratulations!</strong><br />\r\nYou are now on our mailing list! Feel free to manage your <a href=\"{path}my-account-email-preferences\">e-mail preferences</a> at any time.</p>\r\n',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,1,8,30,'new_contacts',0),(65,290,'CLASSIFIED-AD',1,'Post Ad Now',289,'',0,'','',0,'','',44,0,0,0,'',0,0,354,0,'plain_text','',0,'plain_text','','page','',0,0,0,'custom_form','',0,0,0,0,'',1,0,0,0,0,0,0,0,0,'everyone',1),(78,408,'FORUM THREAD',1,'Post to Forum',407,'',412,'example@example.com','^^subject^^',412,'example@example.com','^^subject^^',32,0,0,0,'',0,0,409,1,'html','',1,'html','','page','',0,0,0,'custom_form','',0,0,0,0,'',1,0,0,0,0,0,0,0,0,'everyone',0),(66,296,'STAFF-DIRECTORY',1,'Submit',1046,'',0,'','',0,'','',45,0,0,0,'',0,0,0,0,'plain_text','',0,'plain_text','','page','',0,0,0,'custom_form','',0,0,0,0,'',1,0,0,0,0,0,0,0,0,'everyone',1),(81,417,'MEMBERSHIP TRIAL',1,'Start Membership Trial Now',489,'',0,'','',0,'','',47,0,0,30,'',1,284,0,0,'plain_text','',0,'plain_text','','page','',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,1,0,0,0,'everyone',0),(76,395,'SUPPORT-TICKET',1,'Submit Support Ticket',399,'',396,'example@example.com','Support Ticket (#^^reference_code^^) )^^subject^^',396,'example@example.com','Support Ticket (#^^reference_code^^) )^^subject^^',33,0,0,0,'',0,0,0,1,'html','',1,'html','','page','',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0),(70,291,'EXAM',1,'Score Exam',308,'60',292,'example@example.com','Exam Confirmation',303,'example@example.com','Exam Notification',27,1,100,0,'',0,0,0,1,'html','',1,'html','','page','',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0),(71,288,'MEMBER-DIRECTORY',1,'Add to Members Directory',568,'15',0,'','',0,'','',15,0,0,0,'',0,0,0,0,'plain_text','',0,'plain_text','','page','',0,0,0,'message','<div class=\"alert alert-danger\">\r\n<p>Wait! You have already submitted a Member Directory Entry.</p>\r\n\r\n<p>We only allow one Member Directory Entry for each Member. Please <a href=\"{path}members-directory\">search</a> for your existing Member Directory Entry and click &quot;Edit Mine&quot; to make changes or delete your entry.</p>\r\n</div>\r\n',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0),(72,313,'BLOG',1,'Post to Blog',227,'',0,'','',0,'','',37,0,0,0,'',0,0,365,0,'plain_text','',0,'plain_text','','page','',0,0,0,'custom_form','',0,0,0,0,'',1,0,0,0,0,0,0,0,0,'everyone',0),(88,538,'VIDEO',1,'Post to Video Gallery',539,'',0,'','',0,'','',0,0,0,0,'',0,0,0,0,'plain_text','',0,'plain_text','','page','',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0),(90,584,'TICKET-WAIT-LIST',1,'Add Me to the Wait List',0,'',0,'','',0,'example@example.com','A new person has been added to the ticket wait list',48,0,0,0,'',0,0,0,0,'html','',1,'plain_text','Click on the \"Forms\" tab to view the person\'s wait list request.  They have also been added to the \"Event Ticket Wait List\" Contact Group.','message','<h3>Congratulations!</h3>\r\n\r\n<p class=\"lead\">You have been added to the wait list. If any tickets become available we will contact you.</p>\r\n',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0),(95,1059,'COMING-SOON',1,'Notify Me',1060,'',0,'','',0,'','',50,0,0,0,'',0,0,0,0,'html','',0,'html','','page','<p><strong>Congratulations!</strong><br />\r\nYou are now on our mailing list! Feel free to manage your <a href=\"{path}my-account-email-preferences\">e-mail preferences</a> at any time.</p>\r\n',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'new_contacts',0),(94,1023,'SERVICES-PROJECT',1,'Create Services Project & Notify Client',1032,'',1032,'','^^subject^^',1032,'','^^subject^^',49,0,0,0,'',0,0,0,1,'html','',1,'html','','page','',0,0,0,'custom_form','',0,0,0,0,'',0,0,0,0,0,0,0,0,0,'everyone',0);
/*!40000 ALTER TABLE `custom_form_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dregion`
--

DROP TABLE IF EXISTS `dregion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dregion` (
  `dregion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dregion_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dregion_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `dregion_user` int(10) unsigned NOT NULL DEFAULT '0',
  `dregion_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`dregion_id`),
  KEY `dregion_name` (`dregion_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dregion`
--

LOCK TABLES `dregion` WRITE;
/*!40000 ALTER TABLE `dregion` DISABLE KEYS */;
/*!40000 ALTER TABLE `dregion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_a_friend_pages`
--

DROP TABLE IF EXISTS `email_a_friend_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_a_friend_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_a_friend_pages`
--

LOCK TABLES `email_a_friend_pages` WRITE;
/*!40000 ALTER TABLE `email_a_friend_pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_a_friend_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_campaign_profiles`
--

DROP TABLE IF EXISTS `email_campaign_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_campaign_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `action` enum('calendar_event_reserved','custom_form_submitted','email_campaign_sent','order_abandoned','order_completed','order_shipped','product_ordered') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'calendar_event_reserved',
  `action_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `from_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `from_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bcc_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `schedule_time` time NOT NULL DEFAULT '00:00:00',
  `schedule_length` int(10) unsigned NOT NULL DEFAULT '0',
  `schedule_unit` enum('days','hours') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'days',
  `schedule_period` enum('before','after') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'before',
  `schedule_base` enum('action','calendar_event_start_time') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'action',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `purpose` enum('commercial','transactional') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'commercial',
  PRIMARY KEY (`id`),
  KEY `action_item_id` (`action_item_id`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_campaign_profiles`
--

LOCK TABLES `email_campaign_profiles` WRITE;
/*!40000 ALTER TABLE `email_campaign_profiles` DISABLE KEYS */;
INSERT INTO `email_campaign_profiles` VALUES (8,'Order Assistance',0,'order_abandoned',0,'Thank you for starting an order!','html','',1071,'My Organization','example@example.com','example@example.com','example@example.com','10:00:00',0,'hours','after','action',40,1548292831,40,1548292572,'commercial');
/*!40000 ALTER TABLE `email_campaign_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_campaigns`
--

DROP TABLE IF EXISTS `email_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `from_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reply_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `status` enum('ready','paused','cancelled','complete') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ready',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `bcc_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('manual','automatic') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'manual',
  `email_campaign_profile_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('','calendar_event_reserved','custom_form_submitted','email_campaign_sent','gift_card_ordered','order_abandoned','order_completed','order_shipped','product_ordered') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `action_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_event_recurrence_number` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `purpose` enum('commercial','transactional') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'commercial',
  PRIMARY KEY (`id`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`),
  KEY `email_campaign_profile_id` (`email_campaign_profile_id`),
  KEY `action_item_id` (`action_item_id`),
  KEY `action` (`action`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`),
  KEY `created_timestamp` (`created_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_campaigns`
--

LOCK TABLES `email_campaigns` WRITE;
/*!40000 ALTER TABLE `email_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_recipients`
--

DROP TABLE IF EXISTS `email_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_recipients` (
  `email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_campaign_id` int(10) unsigned NOT NULL DEFAULT '0',
  `complete` tinyint(4) NOT NULL DEFAULT '0',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reference_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('manual','automatic') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'manual',
  PRIMARY KEY (`id`),
  KEY `email_campaign_id` (`email_campaign_id`),
  KEY `reference_code` (`reference_code`),
  KEY `email_address` (`email_address`),
  KEY `type` (`type`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_recipients`
--

LOCK TABLES `email_recipients` WRITE;
/*!40000 ALTER TABLE `email_recipients` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `excluded_transit_dates`
--

DROP TABLE IF EXISTS `excluded_transit_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `excluded_transit_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `shipping_method_id` (`shipping_method_id`)
) ENGINE=MyISAM AUTO_INCREMENT=186 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `excluded_transit_dates`
--

LOCK TABLES `excluded_transit_dates` WRITE;
/*!40000 ALTER TABLE `excluded_transit_dates` DISABLE KEYS */;
INSERT INTO `excluded_transit_dates` VALUES (181,7,'2020-12-25'),(180,7,'2019-12-25'),(141,3,'2020-12-25'),(140,3,'2019-12-25'),(129,1,'2020-12-25'),(128,1,'2019-12-25'),(137,4,'2020-12-25'),(136,4,'2019-12-25'),(149,2,'2020-12-25'),(148,2,'2019-12-25'),(133,5,'2020-12-25'),(132,5,'2019-12-25'),(185,6,'2020-12-25'),(184,6,'2019-12-25');
/*!40000 ALTER TABLE `excluded_transit_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `express_order_pages`
--

DROP TABLE IF EXISTS `express_order_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `express_order_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shopping_cart_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quick_add_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quick_add_product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `special_offer_code_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `special_offer_code_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_1_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_1_required` tinyint(4) NOT NULL DEFAULT '0',
  `custom_field_2_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_2_required` tinyint(4) NOT NULL DEFAULT '0',
  `po_number` tinyint(4) NOT NULL DEFAULT '0',
  `card_verification_number_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `terms_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `update_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `purchase_now_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offline_payment_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_receipt_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `order_receipt_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_receipt_email_format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `order_receipt_email_header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `order_receipt_email_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `order_receipt_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offline_payment_always_allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `product_description_type` enum('full_description','short_description') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full_description',
  `pre_save_hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `post_save_hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_label_column_width` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `auto_registration` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `shipping_form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `express_order_pages`
--

LOCK TABLES `express_order_pages` WRITE;
/*!40000 ALTER TABLE `express_order_pages` DISABLE KEYS */;
INSERT INTO `express_order_pages` VALUES (1,256,'Order','Add to Order',0,'Offer Code','Click \'Update Order\' to apply.','',0,'',0,1,0,98,'Update Order','Purchase Now',269,'Bill Me Later',1,'Order Receipt #','html','Order Receipt','',533,0,'full_description','','',0,'','',1,0),(2,476,'Donation','Quick Add Items',0,'','','',0,'',0,0,0,0,'Update Donation','Submit Donation',477,'Send Statement',1,'Donation Receipt #','html','Order Receipt','',534,0,'full_description','','',0,'','',1,0),(8,497,'Quote','',0,'','','',0,'',0,0,0,0,'Update Quote','',269,'',1,'Order Receipt #','html','','',533,0,'short_description','','',0,'','',1,0);
/*!40000 ALTER TABLE `express_order_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `folder` int(10) unsigned DEFAULT NULL,
  `type` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `design` tinyint(4) NOT NULL DEFAULT '0',
  `attachment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `activated_desktop_theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `activated_mobile_theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `optimized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `activated_desktop_theme` (`activated_desktop_theme`),
  KEY `activated_mobile_theme` (`activated_mobile_theme`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `design` (`design`),
  KEY `theme` (`theme`)
) ENGINE=MyISAM AUTO_INCREMENT=6882 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (2546,'service-plans.jpg',286,'jpg',30350,40,1548293143,'',0,0,0,0,0,1),(6780,'close.png',115,'png',280,40,1548293139,'Bootstrap depends on this file.',1,0,0,0,0,1),(6781,'next.png',115,'png',1350,40,1548293138,'Bootstrap depends on this file.',1,0,0,0,0,1),(6782,'prev.png',115,'png',1360,40,1548293137,'Bootstrap depends on this file.',1,0,0,0,0,1),(1752,'ebook-download-file.txt',135,'txt',254,2,1537472973,'This sample file is only accessible if the ebook download product is ordered by the User.',0,0,0,0,0,0),(1751,'software-download-file.txt',134,'txt',257,2,1537472973,'This sample file is only accessible if the software download product is ordered by the User.',0,0,0,0,0,0),(2528,'membership-cards.jpg',286,'jpg',23551,40,1548293206,'Sample photo used for sample product.',0,0,0,0,0,1),(2367,'favicon.ico',115,'ico',1150,40,1547675184,'Browser tab icon for site. Replace with your own.',1,0,0,0,0,0),(6642,'photo-gallery-album-3-photo-3.jpg',283,'jpg',431294,40,1548293210,'The file description is also the photo caption.',0,0,0,0,0,1),(6641,'photo-gallery-album-3-photo-4.jpg',283,'jpg',446204,40,1548293229,'The file description is also the photo caption.',0,0,0,0,0,1),(6640,'photo-gallery-album-3-photo-5.jpg',283,'jpg',268399,40,1548293240,'The file description is also the photo caption.',0,0,0,0,0,1),(6639,'photo-gallery-album-3-photo-6.jpg',283,'jpg',311435,40,1548293248,'The file description is also the photo caption.',0,0,0,0,0,1),(6638,'photo-gallery-album-2-photo-2.jpg',188,'jpg',327099,40,1548293258,'The file description is also the photo caption.',0,0,0,0,0,1),(6637,'photo-gallery-album-2-photo-1.jpg',188,'jpg',342260,40,1548293265,'The file description is also the photo caption.',0,0,0,0,0,1),(6636,'photo-gallery-album-2-photo-4.jpg',188,'jpg',194270,40,1548293269,'The file description is also the photo caption.',0,0,0,0,0,1),(6635,'photo-gallery-album-2-photo-5.jpg',188,'jpg',316985,40,1548293278,'The file description is also the photo caption.',0,0,0,0,0,1),(6634,'photo-gallery-album-2-photo-6.jpg',188,'jpg',452585,40,1548293296,'The file description is also the photo caption.',0,0,0,0,0,1),(6633,'photo-gallery-album-2-photo-3.jpg',188,'jpg',286094,40,1548293302,'The file description is also the photo caption.',0,0,0,0,0,1),(6632,'photo-gallery-album-1-photo-5.jpg',189,'jpg',173019,40,1548293307,'The file description is also the photo caption.',0,0,0,0,0,1),(6631,'photo-gallery-album-1-photo-4.jpg',189,'jpg',177236,40,1548293311,'The file description is also the photo caption.',0,0,0,0,0,1),(6630,'photo-gallery-album-1-photo-2.jpg',189,'jpg',187562,40,1548293316,'The file description is also the photo caption.',0,0,0,0,0,1),(6629,'photo-gallery-album-1-photo-6.jpg',189,'jpg',425259,40,1548293324,'The file description is also the photo caption.',0,0,0,0,0,1),(6756,'concert-in-the-park.jpg',286,'jpg',146905,40,1548293329,'',0,0,0,0,0,1),(6655,'office-chair-green-4.jpg',294,'jpg',16801,40,1548293333,'',0,0,0,0,0,1),(6656,'office-chair-mocha-1.jpg',286,'jpg',34046,40,1548293127,'',0,0,0,0,0,1),(6657,'office-chair-mocha-2.jpg',286,'jpg',18804,40,1548293334,'',0,0,0,0,0,1),(6658,'office-chair-mocha-3.jpg',286,'jpg',17382,40,1548293336,'',0,0,0,0,0,1),(6659,'office-chair-mocha-4.jpg',286,'jpg',19039,40,1548293337,'',0,0,0,0,0,1),(6660,'office-chair-orange-1.jpg',286,'jpg',41841,40,1548293340,'',0,0,0,0,0,1),(6661,'office-chair-orange-2.jpg',286,'jpg',17896,40,1548293342,'',0,0,0,0,0,1),(6662,'office-chair-orange-3.jpg',286,'jpg',15852,40,1548293343,'',0,0,0,0,0,1),(6663,'office-chair-orange-4.jpg',286,'jpg',17580,40,1548293346,'',0,0,0,0,0,1),(2375,'example-doc.pdf',286,'pdf',16580,40,1547849359,'Example PDF Document file.',0,0,0,0,0,0),(2019,'transparent.png',115,'png',124,40,1548293350,'1x1px transparent image you can resize and use where necessary.',1,0,0,0,0,1),(6783,'glyphicons-halflings-regular.woff2',115,'woff',18028,40,1547852906,'Bootstrap depends on this file.',1,0,0,0,0,0),(6654,'office-chair-green-3.jpg',286,'jpg',14998,40,1548293352,'',0,0,0,0,0,1),(6652,'office-chair-green-1.jpg',286,'jpg',37102,40,1548293354,'',0,0,0,0,0,1),(6653,'office-chair-green-2.jpg',286,'jpg',16397,40,1548293355,'',0,0,0,0,0,1),(6627,'photo-gallery-album-1-photo-3.jpg',189,'jpg',255674,40,1548293360,'The file description is also the photo caption.',0,0,0,0,0,1),(6628,'photo-gallery-album-1-photo-1.jpg',189,'jpg',218748,40,1548293362,'The file description is also the photo caption.',0,0,0,0,0,1),(4032,'bootstrap.css',115,'css',147414,40,1547853739,'Bootstrap depends on this file.',1,0,0,0,0,0),(4033,'glyphicons-halflings-regular.eot',115,'eot',20335,40,1547852931,'Bootstrap depends on this file.',1,0,0,0,0,0),(4034,'glyphicons-halflings-regular.woff',115,'woff',23320,40,1547852933,'Bootstrap depends on this file.',1,0,0,0,0,0),(4035,'glyphicons-halflings-regular.ttf',115,'ttf',41280,40,1547852936,'Bootstrap depends on this file.',1,0,0,0,0,0),(4036,'glyphicons-halflings-regular.svg',115,'svg',62927,40,1547852939,'Bootstrap depends on this file.',1,0,0,0,0,0),(4037,'themify-icons.css',115,'css',17701,40,1548293410,'',1,0,0,0,0,0),(4038,'themify.eot',115,'eot',78748,40,1547675184,'',1,0,0,0,0,0),(4039,'themify.woff',115,'woff',56108,40,1547675184,'',1,0,0,0,0,0),(4040,'themify.ttf',115,'ttf',78584,40,1547675184,'',1,0,0,0,0,0),(4041,'themify.svg',115,'svg',234269,40,1547675184,'',1,0,0,0,0,0),(2522,'ebook.jpg',294,'jpg',27224,40,1548293365,'',0,0,0,0,0,1),(2542,'gift-basket-premium.jpg',294,'jpg',49551,40,1548293368,'',0,0,0,0,0,1),(2541,'gift-basket-platinum.jpg',294,'jpg',31905,40,1548293371,'',0,0,0,0,0,1),(2539,'gift-basket-mega.jpg',294,'jpg',63278,40,1548293374,'',0,0,0,0,0,1),(2545,'pen-case.jpg',294,'jpg',12375,40,1548293375,'',0,0,0,0,0,1),(6679,'staff-01.jpg',286,'jpg',54656,40,1548293381,'',0,1,0,0,0,1),(6674,'icon_bottom.png',115,'png',606,40,1548293386,'',1,0,0,0,0,1),(6675,'icon_top.png',115,'png',593,40,1548293386,'',1,0,0,0,0,1),(6680,'staff-02.jpg',286,'jpg',45127,40,1548293391,'',0,1,0,0,0,1),(6681,'staff-03.jpg',286,'jpg',46795,40,1548293393,'',0,1,0,0,0,1),(6682,'staff-04.jpg',286,'jpg',44599,40,1548293396,'',0,1,0,0,0,1),(6683,'staff-05.jpg',286,'jpg',42329,40,1548293398,'',0,1,0,0,0,1),(6684,'staff-06.jpg',286,'jpg',38405,40,1548293403,'',0,1,0,0,0,1),(6685,'staff-07.jpg',286,'jpg',47378,40,1548293406,'',0,1,0,0,0,1),(6686,'staff-08.jpg',286,'jpg',46612,40,1548293407,'',0,1,0,0,0,1),(6687,'staff-09.jpg',286,'jpg',50595,40,1548293409,'',0,1,0,0,0,1),(6688,'staff-10.jpg',286,'jpg',41765,40,1548293410,'',0,1,0,0,0,1),(6689,'staff-11.jpg',286,'jpg',45692,40,1548293412,'',0,1,0,0,0,1),(6690,'staff-12.jpg',286,'jpg',40917,40,1548293414,'',0,1,0,0,0,1),(4043,'lightbox.min.css',115,'css',2916,40,1547675184,'',1,0,0,0,0,0),(4045,'loading.gif',115,'gif',8476,40,1548293415,'',0,0,0,0,0,1),(4048,'ytplayer.css',115,'css',6302,40,1547675184,'',1,0,0,0,0,0),(4659,'jquery.min.js',115,'js',96381,40,1547675184,'',1,0,0,0,0,0),(4052,'bootstrap.min.js',115,'js',36816,40,1547852921,'Bootstrap depends on this file.',1,0,0,0,0,0),(4053,'flickr.js',115,'js',1266,40,1547675184,'',1,0,0,0,0,0),(4055,'lightbox.min.js',115,'js',7763,40,1547675184,'',1,0,0,0,0,0),(4056,'masonry.min.js',115,'js',26179,40,1547675184,'',1,0,0,0,0,0),(4057,'twitterfetcher.min.js',115,'js',5861,40,1547675184,'',1,0,0,0,0,0),(4058,'spectragram.min.js',115,'js',2973,40,1547675184,'',1,0,0,0,0,0),(4059,'ytplayer.min.js',115,'js',32032,40,1547675184,'',1,0,0,0,0,0),(4060,'countdown.min.js',115,'js',4708,40,1547675184,'',1,0,0,0,0,0),(4061,'smooth-scroll.min.js',115,'js',2647,40,1547675184,'',1,0,0,0,0,0),(4062,'parallax.js',115,'js',4628,40,1547675184,'',1,0,0,0,0,0),(4063,'scripts.js',115,'js',48372,40,1548294971,'',1,0,0,0,0,0),(6746,'mapmarker.png',115,'png',3536,40,1548293417,'',0,0,0,0,0,1),(4065,'video.webm',115,'webm',755337,40,1547675242,'',0,0,0,0,0,0),(4066,'video.mp4',115,'mp4',871875,40,1547675242,'',0,0,0,0,0,0),(4067,'video.ogv',115,'ogv',2817967,40,1547675242,'',0,0,0,0,0,0),(4080,'product-6.png',286,'png',159628,40,1548293429,'',0,0,0,0,0,1),(4084,'font-poppins.css',115,'css',108,40,1547675184,'',1,0,0,0,0,0),(4085,'cover24.jpg',286,'jpg',74294,40,1548293431,'',0,0,0,0,0,1),(4093,'font-roboto.css',115,'css',126,40,1547675184,'',1,0,0,0,0,0),(4096,'c1.png',286,'png',4282,40,1548293437,'',0,0,0,0,0,1),(4097,'c2.png',286,'png',5082,40,1548293442,'',0,0,0,0,0,1),(4098,'c3.png',286,'png',2452,40,1548293450,'',0,0,0,0,0,1),(4099,'c4.png',286,'png',6156,40,1548293457,'',0,0,0,0,0,1),(6772,'software-2.jpg',286,'jpg',84998,40,1548293461,'',0,0,0,0,0,1),(4105,'product-1.jpg',286,'jpg',64172,40,1548293465,'',0,0,0,0,0,1),(4107,'product-2.png',286,'png',214438,40,1548293474,'',0,0,0,0,0,1),(4108,'product-4.jpg',286,'jpg',92349,40,1548293477,'',0,0,0,0,0,1),(4109,'product-3.png',286,'png',57052,40,1548293485,'',0,0,0,0,0,1),(4110,'product-5.png',286,'png',77712,40,1548293495,'',0,0,0,0,0,1),(4111,'cover5.jpg',286,'jpg',88168,40,1548293502,'',0,0,0,0,0,1),(4112,'cover2.jpg',286,'jpg',46656,40,1548293503,'',0,0,0,0,0,1),(6759,'cover31.jpg',286,'jpg',37487,40,1548293505,'',0,0,0,0,0,1),(6760,'software-1.png',286,'png',63726,40,1548293511,'',0,0,0,0,0,1),(4122,'cover32.jpg',286,'jpg',87687,40,1548293513,'',0,0,0,0,0,1),(6752,'cover28.jpg',286,'jpg',111427,40,1548293518,'',0,0,0,0,0,1),(6753,'cover29.jpg',286,'jpg',259454,40,1548293609,'',0,0,0,0,0,1),(6754,'cover30.jpg',286,'jpg',235530,40,1548293617,'',0,0,0,0,0,1),(4149,'font-dosis.css',115,'css',100,40,1547675184,'',1,0,0,0,0,0),(4151,'cover16.jpg',286,'jpg',85681,40,1548293633,'',0,0,0,0,0,1),(4186,'font-georgia.css',115,'css',88,40,1547675184,'',1,0,0,0,0,0),(4209,'cover11.jpg',286,'jpg',57643,40,1548293634,'',0,0,0,0,0,1),(4214,'cover12.jpg',286,'jpg',82562,40,1548293636,'',0,0,0,0,0,1),(4219,'cover22.jpg',286,'jpg',51932,40,1548293638,'',0,0,0,0,0,1),(4220,'cover13.jpg',286,'jpg',32219,40,1548293639,'',0,0,0,0,0,1),(4222,'cover10.jpg',286,'jpg',83597,40,1548293641,'',0,0,0,0,0,1),(4226,'cover7.jpg',286,'jpg',52620,40,1548293660,'',0,0,0,0,0,1),(6744,'software.jpg',294,'jpg',33721,40,1548293662,'',0,0,0,0,0,1),(6740,'gift-card.jpg',286,'jpg',20049,40,1548293664,'',0,0,0,0,0,1),(6739,'cover27.jpg',286,'jpg',206032,40,1548293684,'',0,0,0,0,0,1),(6738,'cover26.jpg',286,'jpg',280081,40,1548293687,'',0,0,0,0,0,1),(6737,'cover25.jpg',286,'jpg',234339,40,1548293691,'',0,0,0,0,0,1),(6748,'cover33.jpg',286,'jpg',306487,40,1548293696,'',0,0,0,0,0,1),(6749,'cover34.jpg',286,'jpg',262723,40,1548293700,'',0,0,0,0,0,1),(6750,'cover35.jpg',286,'jpg',470721,40,1548293706,'',0,0,0,0,0,1),(6770,'software-3.jpg',286,'jpg',76213,40,1548293708,'',0,0,0,0,0,1),(4305,'cover6.jpg',286,'jpg',813676,40,1548293717,'',0,0,0,0,0,1),(6775,'l1.png',286,'png',4500,40,1548293718,'',0,0,0,0,0,1),(6776,'l2.png',286,'png',1636,40,1548293719,'',0,0,0,0,0,1),(6777,'l3.png',286,'png',2798,40,1548293724,'',0,0,0,0,0,1),(6778,'l4.png',286,'png',3425,40,1548293726,'',0,0,0,0,0,1),(6725,'avatar-2.png',286,'png',9515,40,1548293730,'',0,0,0,0,0,1),(6724,'avatar-1.png',286,'png',9850,40,1548293731,'',0,0,0,0,0,1),(6733,'screenshot-1.jpg',286,'jpg',70929,40,1548293736,'',0,0,0,0,0,1),(6734,'screenshot.jpg',286,'jpg',108305,40,1548293740,'',0,0,0,0,0,1),(6732,'cover23.jpg',286,'jpg',164348,40,1548293745,'',0,0,0,0,0,1),(6728,'signature.png',286,'png',268193,40,1548293749,'',0,0,0,0,0,1),(6719,'cover19.jpg',286,'jpg',196198,40,1548293754,'',0,0,0,0,0,1),(6718,'cover18.jpg',286,'jpg',423838,40,1548293757,'',0,0,0,0,0,1),(6717,'cover17.jpg',286,'jpg',199487,40,1548293769,'',0,0,0,0,0,1),(6716,'cover3.jpg',286,'jpg',288828,40,1548293772,'',0,0,0,0,0,1),(6715,'cover4.jpg',286,'jpg',381355,40,1548293780,'',0,0,0,0,0,1),(6709,'staff-photo-not-available.png',286,'png',16930,40,1548293783,'',0,0,0,0,0,1),(6711,'cover20.jpg',286,'jpg',263486,40,1548293786,'',0,0,0,0,0,1),(6643,'photo-gallery-album-3-photo-2.jpg',283,'jpg',215066,40,1548293790,'The file description is also the photo caption.',0,0,0,0,0,1),(6644,'photo-gallery-album-3-photo-1.jpg',283,'jpg',507938,40,1548293795,'The file description is also the photo caption.',0,0,0,0,0,1),(6645,'photo-gallery-photo-3.jpg',87,'jpg',236808,40,1548293798,'The file description is also the photo caption.',0,0,0,0,0,1),(6646,'photo-gallery-photo-2.jpg',87,'jpg',433814,40,1548293803,'The file description is also the photo caption.',0,0,0,0,0,1),(6647,'photo-gallery-photo-1.jpg',87,'jpg',482913,40,1548293808,'The file description is also the photo caption.',0,0,0,0,0,1),(6648,'photo-gallery-photo-4.jpg',87,'jpg',248085,40,1548293811,'The file description is also the photo caption.',0,0,0,0,0,1),(6774,'screenshot-2.jpg',286,'jpg',90166,40,1548293814,'',0,0,0,0,0,1),(4813,'cover15.jpg',286,'jpg',49481,40,1548293817,'',0,0,0,0,0,1),(4816,'cover1.jpg',286,'jpg',105591,40,1548293819,'',0,0,0,0,0,1),(4857,'cover14.jpg',286,'jpg',66050,40,1548293821,'',0,0,0,0,0,1),(5021,'cover8.jpg',286,'jpg',61382,40,1548293822,'',0,0,0,0,0,1),(5006,'video2.webm',115,'webm',769511,40,1547675242,'',0,0,0,0,0,0),(5007,'video2.mp4',115,'mp4',823916,40,1547675242,'',0,0,0,0,0,0),(5577,'cover21.jpg',286,'jpg',118803,40,1548293824,'',0,0,0,0,0,1),(6727,'avatar-3.png',286,'png',9247,40,1548293826,'',0,0,0,0,0,1),(6785,'logo-dark.png',286,'png',6222,40,1548293136,'dark version of \'your logo\' used throughout design',0,0,0,0,0,1),(6786,'logo-light.png',286,'png',5068,40,1548293134,'light version of \'your logo\' used throughout design',0,0,0,0,0,1),(6863,'Salmon.css',115,'css',6329,40,1548294303,'A color definition file used by default theme.',1,0,0,0,0,0),(6862,'RoyalBlue.css',115,'css',6500,40,1548294338,'A color definition file used by default theme.',1,0,0,0,0,0),(6861,'Red.css',115,'css',6158,40,1548294299,'A color definition file used by default theme.',1,0,0,0,0,0),(6860,'RebeccaPurple.css',115,'css',6728,40,1548294295,'A color definition file used by default theme.',1,0,0,0,0,0),(6859,'Purple.css',115,'css',6329,40,1548294307,'A color definition file used by default theme.',1,0,0,0,0,0),(6858,'Pink.css',115,'css',6215,40,1548294350,'A color definition file used by default theme.',1,0,0,0,0,0),(6857,'Orchid.css',115,'css',6329,40,1548294333,'A color definition file used by default theme.',1,0,0,0,0,0),(6856,'OrangeRed.css',115,'css',6500,40,1548294323,'A color definition file used by default theme.',1,0,0,0,0,0),(6855,'Orange.css',115,'css',6329,40,1548294369,'A color definition file used by default theme.',1,0,0,0,0,0),(6854,'Olive.css',115,'css',6272,40,1548294318,'A color definition file used by default theme.',1,0,0,0,0,0),(6853,'Navy.css',115,'css',6215,40,1548294289,'A color definition file used by default theme.',1,0,0,0,0,0),(6852,'Maroon.css',115,'css',6329,40,1548294312,'A color definition file used by default theme.',1,0,0,0,0,0),(6851,'Magenta.css',115,'css',6386,40,1548294284,'A color definition file used by default theme.',1,0,0,0,0,0),(6850,'IndianRed.css',115,'css',6500,40,1548294276,'A color definition file used by default theme.',1,0,0,0,0,0),(6849,'Grey.css',115,'css',6215,40,1548294265,'A color definition file used by default theme.',1,0,0,0,0,0),(6848,'GreenYellow.css',115,'css',6614,40,1548294380,'A color definition file used by default theme.',1,0,0,0,0,0),(6847,'Green.css',115,'css',6272,40,1548294207,'A color definition file used by default theme.',1,0,0,0,0,0),(6846,'Gold.css',115,'css',6215,40,1548294204,'A color definition file used by default theme.',1,0,0,0,0,0),(6843,'FireBrick.css',115,'css',6500,40,1548294211,'A color definition file used by default theme.',1,0,0,0,0,0),(6842,'Crimson.css',115,'css',6386,40,1548294228,'A color definition file used by default theme.',1,0,0,0,0,0),(6841,'CornflowerBlue.css',115,'css',6785,40,1548294225,'A color definition file used by default theme.',1,0,0,0,0,0),(6840,'Coral.css',115,'css',6272,40,1548294236,'A color definition file used by default theme.',1,0,0,0,0,0),(6839,'Chocolate.css',115,'css',6500,40,1548294232,'A color definition file used by default theme.',1,0,0,0,0,0),(6838,'BurlyWood.css',115,'css',6500,40,1548294241,'A color definition file used by default theme.',1,0,0,0,0,0),(6837,'Brown.css',115,'css',6272,40,1548294245,'A color definition file used by default theme.',1,0,0,0,0,0),(6836,'BlueViolet.css',115,'css',6557,40,1548294250,'A color definition file used by default theme.',1,0,0,0,0,0),(6835,'Blue.css',115,'css',6215,40,1548294255,'A color definition file used by default theme.',1,0,0,0,0,0),(6864,'SeaGreen.css',115,'css',6443,40,1548294341,'A color definition file used by default theme.',1,0,0,0,0,0),(6865,'SkyBlue.css',115,'css',6386,40,1548294345,'A color definition file used by default theme.',1,0,0,0,0,0),(6866,'SpringGreen.css',115,'css',6614,40,1548294354,'A color definition file used by default theme.',1,0,0,0,0,0),(6867,'SteelBlue.css',115,'css',6500,40,1548294360,'A color definition file used by default theme.',1,0,0,0,0,0),(6868,'Tan.css',115,'css',6158,40,1548294327,'A color definition file used by default theme.',1,0,0,0,0,0),(6869,'Teal.css',115,'css',6215,40,1548294364,'A color definition file used by default theme.',1,0,0,0,0,0),(6877,'theme-foundrygreen.css',115,'css',134580,40,1548293138,'Default theme. This theme allows you to easily change colors and fonts in one place. Duplicate theme and make your own.',1,0,1,1,1,0),(6878,'FoundryGreen.css',115,'css',6386,40,1548294216,'A color definition file used by default theme.',1,0,0,0,0,0),(6872,'Tomato.css',115,'css',6329,40,1548294373,'A color definition file used by default theme.',1,0,0,0,0,0),(6873,'Turquoise.css',115,'css',6500,40,1548294171,'A color definition file used by default theme.',1,0,0,0,0,0),(6874,'Violet.css',115,'css',6329,40,1548294197,'A color definition file used by default theme.',1,0,0,0,0,0),(6875,'Yellow.css',115,'css',6329,40,1548294194,'A color definition file used by default theme.',1,0,0,0,0,0),(6876,'YellowGreen.css',115,'css',6614,40,1548294200,'A color definition file used by default theme.',1,0,0,0,0,0),(6879,'theme-maroon.css',115,'css',134574,40,1548293226,'This is a duplicate copy of the default theme with a different color and fonts.',1,0,0,0,1,0),(6880,'theme-blue.css',115,'css',134590,40,1548293170,'This is a duplicate copy of the default theme with a different color and fonts.',1,0,0,0,1,0),(6881,'theme-orange.css',115,'css',134580,40,1548293210,'This is a duplicate copy of the default theme with a different color and fonts.',1,0,0,0,1,0);
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folder`
--

DROP TABLE IF EXISTS `folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folder` (
  `folder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folder_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `folder_parent` int(10) unsigned DEFAULT NULL,
  `folder_level` int(2) DEFAULT NULL,
  `folder_style` int(10) unsigned DEFAULT NULL,
  `folder_order` int(2) DEFAULT NULL,
  `folder_user` int(10) unsigned DEFAULT NULL,
  `folder_timestamp` int(10) unsigned DEFAULT NULL,
  `folder_access_control_type` enum('public','guest','private','registration','membership') COLLATE utf8_unicode_ci DEFAULT NULL,
  `folder_archived` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mobile_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`folder_id`),
  KEY `folder_parent` (`folder_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=299 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folder`
--

LOCK TABLES `folder` WRITE;
/*!40000 ALTER TABLE `folder` DISABLE KEYS */;
INSERT INTO `folder` VALUES (1,'All Pages and Files',0,0,718,0,40,1542401714,'public',0,718),(115,'Design Files (keep in a Public Folder)',1,1,0,999,40,1547849468,'public',0,0),(197,'Visitors (no login or register required)',1,1,0,100,40,1540345712,'public',0,0),(104,'Pay for Exam',215,4,0,0,40,1541185860,'private',0,0),(110,'Staff (site management pages)',1,1,0,500,40,1540349888,'private',0,0),(167,'Registered Guest System Pages (one of each page type)',199,2,0,0,40,1540344187,'registration',0,0),(199,'Registered Guests (requires login or register)',1,1,0,200,40,1540345516,'registration',0,0),(87,'Photo Gallery Pages & Photos',197,2,0,3,40,1540398436,'',0,0),(201,'Members (requires member status in user contact)',1,1,0,300,40,1540345529,'membership',0,0),(288,'Staff Pages & Files',110,2,0,0,40,1547675919,'private',0,0),(123,'System Autoresponder Pages',110,2,0,99,40,1547675957,'private',0,0),(125,'Visitor System Pages (one of each page type)',197,2,0,99,40,1540398443,'public',0,0),(134,'Software Product Folder',137,5,0,0,2,1537472973,'private',0,0),(135,'eBook Product Folder',137,5,0,0,2,1537472973,'private',0,0),(137,'Pay for Downloads',215,4,0,0,2,1537472973,'private',0,0),(188,'Album 2',87,3,0,0,40,1538583472,'',0,0),(189,'Album 1',87,3,0,0,40,1538583463,'',0,0),(287,'Member Pages & Files',201,2,0,0,40,1547849409,'membership',0,0),(219,'My Content (Registration Folder above Private Content)',199,2,0,99,40,1540345962,'registration',0,0),(215,'Pay for Access',219,3,0,0,2,1537472973,'private',0,0),(284,'Customers (who have placed an order)',1,1,0,400,40,1540349753,'private',0,0),(291,'Customer Pages & Files',284,2,0,0,40,1547675819,'private',0,0),(294,'Slider Gallery Widget Page & Photos',197,2,0,99,40,1545875727,'public',0,0),(295,'Widget Pages',197,2,0,0,40,1547849317,'public',0,0),(286,'Visitor Pages & Files',197,2,0,0,40,1547849335,'public',0,0),(290,'Registered Guest Pages & Attached Files',199,2,0,0,40,1547675640,'registration',0,0),(283,'Album 3',87,3,0,0,40,1538583482,'',0,0);
/*!40000 ALTER TABLE `folder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folder_view_pages`
--

DROP TABLE IF EXISTS `folder_view_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folder_view_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pages` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `files` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folder_view_pages`
--

LOCK TABLES `folder_view_pages` WRITE;
/*!40000 ALTER TABLE `folder_view_pages` DISABLE KEYS */;
INSERT INTO `folder_view_pages` VALUES (1,558,1,1);
/*!40000 ALTER TABLE `folder_view_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_data`
--

DROP TABLE IF EXISTS `form_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quantity_number` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('standard','date','date and time','html','time') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'standard',
  `ship_to_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `form_field_id` (`form_field_id`),
  KEY `file_id` (`file_id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `ship_to_id` (`ship_to_id`),
  KEY `form_id_form_field_id` (`form_id`,`form_field_id`),
  KEY `data` (`data`(100))
) ENGINE=MyISAM AUTO_INCREMENT=9185 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_data`
--

LOCK TABLES `form_data` WRITE;
/*!40000 ALTER TABLE `form_data` DISABLE KEYS */;
INSERT INTO `form_data` VALUES (9100,593,36,'You can use an embedded audio player in your blog posts',0,0,0,0,'title','standard',0),(9099,593,293,'<p class=\"embed-responsive\"><iframe src=\"https://w.soundcloud.com/player/?url=//api.soundcloud.com/tracks/242059089&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true\"></iframe></p>',0,0,0,0,'media','html',0),(9104,593,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9103,593,257,'music',0,0,0,0,'category','standard',0),(9102,593,266,'<p>This blog posting shows that you can add anything to a blog post, even an embedded audio player.</p>',0,0,0,0,'summary','html',0),(9101,593,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>',0,0,0,0,'details','html',0),(8876,594,293,'<blockquote>\r\n<p>Invention, my dear friends, is 93% perspiration, 6% electricity, 4% evaporation, and 2% butterscotch ripple.</p>\r\n\r\n<p>- Willy Wonka</p>\r\n</blockquote>',0,0,0,0,'media','html',0),(9107,593,197,'Best Regards',0,0,0,0,'signature','standard',0),(9106,593,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9105,593,193,'This blog posting shows that you can add anything to a blog post, even an embedded audio player.',0,0,0,0,'description','standard',0),(8879,594,257,'quotes',0,0,0,0,'category','standard',0),(8798,594,266,'<p>Even a Quote can be added for the blog posting header.</p>',0,0,0,0,'summary','html',0),(8877,594,36,'You can use a block quote in your blog posts',0,0,0,0,'title','standard',0),(8893,565,266,'<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>',0,0,0,0,'summary','html',0),(8894,565,257,'LifeStyle',0,0,0,0,'category','standard',0),(8895,565,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(8896,565,193,'A simple image post for starters',0,0,0,0,'description','standard',0),(8897,565,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(8898,565,197,'Best Regards',0,0,0,0,'signature','standard',0),(8890,565,293,'<p><img alt=\"\" class=\"img-responsive\" src=\"{path}cover26.jpg\" /></p>',0,0,0,0,'media','html',0),(8880,594,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(8801,594,193,'Even a Quote can be added for the blog posting header.',0,0,0,0,'description','standard',0),(8802,594,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9118,595,36,'You can use a (vimeo) video player in your blog posts',0,0,0,0,'title','standard',0),(9119,595,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum. Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</p>\r\n\r\n<p><a href=\"https://vimeo.com/32944253\">The Fundamental Elements of Design</a> from <a href=\"https://vimeo.com/ericagorochow\">Erica Gorochow</a> on <a href=\"https://vimeo.com\">Vimeo</a>.</p>',0,0,0,0,'details','html',0),(9120,595,266,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus...</p>',0,0,0,0,'summary','html',0),(9122,595,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9123,595,193,'Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et.',0,0,0,0,'description','standard',0),(9124,595,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9125,595,197,'Best Regards',0,0,0,0,'signature','standard',0),(9126,597,293,'<p class=\"embed-responsive\"><iframe allowfullscreen=\"\" frameborder=\"0\" src=\"https://www.youtube.com/embed/iwZXZcyr460\"></iframe></p>',0,0,0,0,'media','html',0),(9127,597,36,'Consectetur adipiscing elit.',0,0,0,0,'title','standard',0),(9128,597,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum. Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</p>',0,0,0,0,'details','html',0),(9129,597,266,'<p>Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit...</p>',0,0,0,0,'summary','html',0),(9130,597,257,'Portfolio',0,0,0,0,'category','standard',0),(9131,597,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9132,597,193,'Consectetur adipiscing elit.',0,0,0,0,'description','standard',0),(9133,597,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9134,597,197,'Best Regards, Our Organization',0,0,0,0,'signature','standard',0),(9163,598,36,'Sed lorem diam, semper ut iaculis eu, scelerisque sed neque.',0,0,0,0,'title','standard',0),(9164,598,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum. Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</p>\r\n\r\n<p><a href=\"https://vimeo.com/20534171\">Conan O&#39;Brien Kinetic Typography</a> from <a href=\"https://vimeo.com/jacobgilbreath\">Jacob Gilbreath</a> on <a href=\"https://vimeo.com\">Vimeo</a>.</p>',0,0,0,0,'details','html',0),(9165,598,266,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum...</p>',0,0,0,0,'summary','html',0),(9166,598,257,'Portfolio',0,0,0,0,'category','standard',0),(9167,598,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9168,598,193,'Sed lorem diam, semper ut iaculis eu, scelerisque sed neque.',0,0,0,0,'description','standard',0),(9169,598,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9170,598,197,'Best Regards, Our Organization',0,0,0,0,'signature','standard',0),(9145,599,36,'Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor.',0,0,0,0,'title','standard',0),(9146,599,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum. Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</p>\r\n\r\n<p><a href=\"https://vimeo.com/35616659\">Beautiful Day at the Dog Park</a> from <a href=\"https://vimeo.com/kelseywynns\">Kelsey Wynns</a> on <a href=\"https://vimeo.com\">Vimeo</a>.</p>',0,0,0,0,'details','html',0),(9147,599,266,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus...</p>',0,0,0,0,'summary','html',0),(9148,599,257,'Design',0,0,0,0,'category','standard',0),(9149,599,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9150,599,193,'Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor.',0,0,0,0,'description','standard',0),(9151,599,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9152,599,197,'Best Regards, Our Organization',0,0,0,0,'signature','standard',0),(9154,600,36,'You can use a (youtube) video player in your blog posts',0,0,0,0,'title','standard',0),(9155,600,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum. Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</p>',0,0,0,0,'details','html',0),(9156,600,266,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim. Vivamus dictum ultricies orci nec condimentum...</p>',0,0,0,0,'summary','html',0),(9157,600,257,'Services',0,0,0,0,'category','standard',0),(9158,600,276,'2019-01-01',0,0,0,0,'publish-date','date',0),(9159,600,193,'Lorem Ipsum',0,0,0,0,'description','standard',0),(9160,600,195,'Dear [[^^name^^||Subscriber]],',0,0,0,0,'to','standard',0),(9161,600,197,'Best Regards, Our Organization',0,0,0,0,'signature','standard',0),(9153,600,293,'<p class=\"embed-responsive\"><iframe allowfullscreen=\"\" frameborder=\"0\" src=\"https://www.youtube.com/embed/A3PDXmYoF5U\"></iframe></p>',0,0,0,0,'media','html',0),(9162,598,293,'<p class=\"embed-responsive\"><iframe frameborder=\"0\" src=\"https://player.vimeo.com/video/20534171?title=0&amp;byline=0&amp;color=ffffff\"></iframe></p>',0,0,0,0,'media','html',0),(8878,594,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>',0,0,0,0,'details','html',0),(8968,0,190,'XL',0,454,1057,1,'shirt_size','standard',0),(8966,0,189,'Member',0,454,1057,1,'last_name','standard',0),(8965,0,188,'Manny',0,454,1057,1,'first_name','standard',0),(8967,0,191,'Collegues',0,454,1057,1,'comments','standard',0),(9046,0,289,'',0,464,1069,1,'id','standard',0),(8803,594,197,'Best Regards,\r\n\r\nOur Organization',0,0,0,0,'signature','standard',0),(8238,655,40,'Niev',0,0,0,0,'first_name','standard',0),(8670,651,41,'Aiser',0,0,0,0,'last_name','standard',0),(8671,651,42,'Founder',0,0,0,0,'title','standard',0),(8672,651,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8669,651,40,'Bill',0,0,0,0,'first_name','standard',0),(8673,651,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8002,651,44,'',6679,0,0,0,'photo','standard',0),(8676,651,255,'B',0,0,0,0,'alphabetical','standard',0),(8105,652,40,'Jill',0,0,0,0,'first_name','standard',0),(8106,652,41,'Jacobs',0,0,0,0,'last_name','standard',0),(8107,652,42,'Managing Director',0,0,0,0,'title','standard',0),(8108,652,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8109,652,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8009,652,44,'',6680,0,0,0,'photo','standard',0),(8110,652,255,'J',0,0,0,0,'alphabetical','standard',0),(8245,653,40,'Jake',0,0,0,0,'first_name','standard',0),(8246,653,41,'Von',0,0,0,0,'last_name','standard',0),(8247,653,42,'Business Manager',0,0,0,0,'title','standard',0),(8248,653,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8249,653,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8016,653,44,'',6681,0,0,0,'photo','standard',0),(8250,653,255,'V',0,0,0,0,'alphabetical','standard',0),(8126,654,40,'Marty',0,0,0,0,'first_name','standard',0),(8127,654,41,'McMillian',0,0,0,0,'last_name','standard',0),(8129,654,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8130,654,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8023,654,44,'',6682,0,0,0,'photo','standard',0),(8131,654,255,'A',0,0,0,0,'alphabetical','standard',0),(8239,655,41,'Wiezel',0,0,0,0,'last_name','standard',0),(8240,655,42,'Director of Communications',0,0,0,0,'title','standard',0),(8241,655,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8242,655,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8035,655,44,'',6683,0,0,0,'photo','standard',0),(8243,655,255,'W',0,0,0,0,'alphabetical','standard',0),(8168,656,40,'Steph',0,0,0,0,'first_name','standard',0),(8169,656,41,'Naisman',0,0,0,0,'last_name','standard',0),(8170,656,42,'Director of Personnel',0,0,0,0,'title','standard',0),(8171,656,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8172,656,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8042,656,44,'',6684,0,0,0,'photo','standard',0),(8173,656,255,'N',0,0,0,0,'alphabetical','standard',0),(8175,657,40,'Reggie',0,0,0,0,'first_name','standard',0),(8176,657,41,'Robertson',0,0,0,0,'last_name','standard',0),(8177,657,42,'Social Media Coordinator',0,0,0,0,'title','standard',0),(8178,657,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8179,657,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8049,657,44,'',6685,0,0,0,'photo','standard',0),(8180,657,255,'R',0,0,0,0,'alphabetical','standard',0),(8182,658,40,'Rick',0,0,0,0,'first_name','standard',0),(8183,658,41,'Easter',0,0,0,0,'last_name','standard',0),(8184,658,42,'Director of Technology',0,0,0,0,'title','standard',0),(8185,658,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8186,658,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8056,658,44,'',6686,0,0,0,'photo','standard',0),(8187,658,255,'E',0,0,0,0,'alphabetical','standard',0),(8119,659,40,'Hester',0,0,0,0,'first_name','standard',0),(8120,659,41,'Ravi',0,0,0,0,'last_name','standard',0),(8121,659,42,'Director of Sales',0,0,0,0,'title','standard',0),(8122,659,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8123,659,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8063,659,44,'',6687,0,0,0,'photo','standard',0),(8124,659,255,'R',0,0,0,0,'alphabetical','standard',0),(8530,660,40,'Daniel',0,0,0,0,'first_name','standard',0),(8531,660,41,'Sapatto',0,0,0,0,'last_name','standard',0),(8532,660,42,'Director of Product Development',0,0,0,0,'title','standard',0),(8533,660,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8534,660,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8070,660,44,'',6688,0,0,0,'photo','standard',0),(8535,660,255,'S',0,0,0,0,'alphabetical','standard',0),(8537,661,40,'Nadia',0,0,0,0,'first_name','standard',0),(8538,661,41,'Davenport',0,0,0,0,'last_name','standard',0),(8539,661,42,'Customer Service Director',0,0,0,0,'title','standard',0),(8540,661,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8541,661,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8077,661,44,'',6689,0,0,0,'photo','standard',0),(8542,661,255,'D',0,0,0,0,'alphabetical','standard',0),(8544,662,40,'David',0,0,0,0,'first_name','standard',0),(8545,662,41,'LaCosta',0,0,0,0,'last_name','standard',0),(8546,662,42,'Director of Operations',0,0,0,0,'title','standard',0),(8547,662,47,'555-555-5555',0,0,0,0,'phone','standard',0),(8548,662,43,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin felis eget enim lobortis, et egestas metus malesuada. Nulla congue, sapien sit amet lacinia feugiat, nibh est gravida metus, ut euismod diam felis sed ipsum. Donec quis enim id sem tempor dignissim sed ut metus. Ut euismod enim aliquet tellus aliquet vehicula. Nunc vel viverra lorem. Mauris non orci fermentum, placerat nisl eu, sollicitudin metus. Vestibulum mollis nisl et imperdiet tincidunt. Ut vel ornare purus, et consectetur arcu. Donec placerat enim commodo commodo accumsan. Fusce nec mauris dolor.',0,0,0,0,'bio','standard',0),(8084,662,44,'',6690,0,0,0,'photo','standard',0),(8549,662,255,'L',0,0,0,0,'alphabetical','standard',0),(8128,654,42,'Director of Marketing',0,0,0,0,'title','standard',0),(8677,651,321,'1',0,0,0,0,'sort','standard',0),(8111,652,321,'2',0,0,0,0,'sort','standard',0),(8251,653,321,'3',0,0,0,0,'sort','standard',0),(8125,659,321,'4',0,0,0,0,'sort','standard',0),(8132,654,321,'5',0,0,0,0,'sort','standard',0),(8244,655,321,'6',0,0,0,0,'sort','standard',0),(8174,656,321,'7',0,0,0,0,'sort','standard',0),(8181,657,321,'8',0,0,0,0,'sort','standard',0),(8188,658,321,'9',0,0,0,0,'sort','standard',0),(8536,660,321,'10',0,0,0,0,'sort','standard',0),(8543,661,321,'11',0,0,0,0,'sort','standard',0),(8550,662,321,'12',0,0,0,0,'sort','standard',0),(8295,670,322,'124858722',0,0,0,0,'vimeo-id','standard',0),(8294,670,248,'',0,0,0,0,'youtube-id','standard',0),(8843,669,254,'3',0,0,0,0,'sort','standard',0),(8840,669,248,'MKWWhf8RAV8',0,0,0,0,'youtube-id','standard',0),(8841,669,322,'',0,0,0,0,'vimeo-id','standard',0),(8842,669,249,'Tahiti Surf',0,0,0,0,'title','standard',0),(8313,668,254,'2',0,0,0,0,'sort','standard',0),(8312,668,249,'Avalanche Cliff Jump with Matthias Giraud',0,0,0,0,'title','standard',0),(8310,668,248,'',0,0,0,0,'youtube-id','standard',0),(8311,668,322,'22669590',0,0,0,0,'vimeo-id','standard',0),(8306,667,248,'uwHQEpmRjhw',0,0,0,0,'youtube-id','standard',0),(8307,667,322,'',0,0,0,0,'vimeo-id','standard',0),(8308,667,249,'Sand Dune Jumping with Ronnie Renner',0,0,0,0,'title','standard',0),(8309,667,254,'1',0,0,0,0,'sort','standard',0),(8296,670,249,'Antarctica',0,0,0,0,'title','standard',0),(8297,670,254,'3',0,0,0,0,'sort','standard',0),(8298,671,248,'TsbL9HxK3ns',0,0,0,0,'youtube-id','standard',0),(8299,671,322,'',0,0,0,0,'vimeo-id','standard',0),(8300,671,249,'Champions - Beach Volleyball',0,0,0,0,'title','standard',0),(8301,671,254,'5',0,0,0,0,'sort','standard',0),(8302,672,248,'',0,0,0,0,'youtube-id','standard',0),(8303,672,322,'35616659',0,0,0,0,'vimeo-id','standard',0),(8304,672,249,'Beautiful Day at the Dog Park',0,0,0,0,'title','standard',0),(8305,672,254,'6',0,0,0,0,'sort','standard',0),(9095,0,151,'',0,483,1110,1,'honoree','standard',0),(9096,0,152,'',0,483,1110,1,'from','standard',0),(9097,0,246,'',0,483,0,0,'instructions','standard',344),(9098,0,246,'',0,484,0,0,'instructions','standard',345),(9144,599,293,'<p class=\"embed-responsive\"><iframe allowfullscreen=\"allowfullscreen\" src=\"https://player.vimeo.com/video/35616659?badge=0&amp;title=0&amp;byline=0&amp;title=0\"></iframe></p>',0,0,0,0,'media','html',0),(9173,0,151,'',0,486,1114,1,'honoree','standard',0),(9174,0,152,'',0,486,1114,1,'from','standard',0),(9184,0,246,'',0,494,0,0,'instructions','standard',354),(9084,0,152,'',0,468,1085,1,'from','standard',0),(9083,0,151,'',0,468,1085,1,'honoree','standard',0),(8648,702,324,'',0,0,0,0,'linkedin','standard',0),(8674,651,323,'https://twitter.com/ilovelivesite',0,0,0,0,'twitter','standard',0),(8645,702,47,'',0,0,0,0,'phone','standard',0),(8646,702,43,'',0,0,0,0,'bio','standard',0),(8556,702,44,'',0,0,0,0,'photo','standard',0),(8642,702,40,'Orvill',0,0,0,0,'first_name','standard',0),(8649,702,255,'N',0,0,0,0,'alphabetical','standard',0),(8647,702,323,'',0,0,0,0,'twitter','standard',0),(8650,702,321,'9999',0,0,0,0,'sort','standard',0),(8675,651,324,'https://www.linkedin.com/in/michael-wilson-13056318',0,0,0,0,'linkedin','standard',0),(8643,702,41,'Neisson',0,0,0,0,'last_name','standard',0),(8644,702,42,'Director of Special Projects',0,0,0,0,'title','standard',0),(9117,595,293,'<p class=\"embed-responsive\"><iframe allowfullscreen=\"allowfullscreen\" class=\"embed-responsive-item\" src=\"https://player.vimeo.com/video/32944253?title=0&amp;byline=0&amp;portrait=0&amp;color=78fab7\"></iframe></p>',0,0,0,0,'media','html',0),(9121,595,257,'Design',0,0,0,0,'category','standard',0),(8891,565,36,'You can use a photo in your blog posts',0,0,0,0,'title','standard',0),(8892,565,38,'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>\r\n\r\n<blockquote>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam.</p>\r\n</blockquote>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi rutrum orci ligula, ut vehicula tortor commodo vel. Etiam nunc arcu, sagittis ac lectus sed, cursus condimentum diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras id ante et enim dignissim tincidunt id ut arcu. Vivamus elementum tortor vitae semper feugiat. Aliquam tincidunt efficitur metus. Fusce arcu felis, interdum quis rhoncus ac, tempus id odio. Aliquam auctor velit erat, in rhoncus sapien congue eu. Nam mattis tempus velit, et efficitur lacus sollicitudin non. Pellentesque porta id diam ac sodales. Quisque cursus libero et turpis pulvinar, non auctor augue pellentesque. Cras nibh risus, porta et rhoncus ac, euismod ut quam. Vestibulum vel neque leo.</p>',0,0,0,0,'details','html',0);
/*!40000 ALTER TABLE `form_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_field_options`
--

DROP TABLE IF EXISTS `form_field_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_field_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `default_selected` tinyint(4) NOT NULL DEFAULT '0',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `target_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `upload_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `form_field_id` (`form_field_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1381 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_field_options`
--

LOCK TABLES `form_field_options` WRITE;
/*!40000 ALTER TABLE `form_field_options` DISABLE KEYS */;
INSERT INTO `form_field_options` VALUES (1376,0,216,'Mens Large','Mens Large',0,7,58,'',0,0),(1375,0,216,'Mens Medium','Mens Medium',0,6,58,'',0,0),(1325,0,298,'Green','Green',0,4,70,'',0,0),(467,288,114,'Rev.','Rev.',0,6,0,'',0,0),(466,288,114,'Dr.','Dr.',0,5,0,'',0,0),(465,288,114,'Miss','Miss',0,4,0,'',0,0),(464,288,114,'Mrs.','Mrs.',0,3,0,'',0,0),(463,288,114,'Ms.','Ms.',0,2,0,'',0,0),(462,288,114,'Mr.','Mr.',0,1,0,'',0,0),(460,288,118,'Finishing','Finishing',0,6,0,'',0,0),(459,288,118,'Customizing','Customizing',0,5,0,'',0,0),(458,288,118,'Retrofiting','Retrofiting',0,4,0,'',0,0),(457,288,118,'Building','Building',0,3,0,'',0,0),(456,288,118,'Designing','Designing',0,2,0,'',0,0),(455,288,118,'Planning','Planning',0,1,0,'',0,0),(461,288,118,'Maintainence','Maintainence',0,7,0,'',0,0),(468,291,119,'Mr.','Mr.',0,1,0,'',0,0),(469,291,119,'Ms.','Ms.',0,2,0,'',0,0),(470,291,119,'Mrs.','Mrs.',0,3,0,'',0,0),(471,291,119,'Miss','Miss',0,4,0,'',0,0),(472,291,119,'Dr.','Dr.',0,5,0,'',0,0),(473,291,119,'Rev.','Rev.',0,6,0,'',0,0),(805,291,124,'USC Trojans','USC Trojans',0,2,0,'',0,0),(804,291,124,'Texas Longhorns','Texas Longhorns',0,1,0,'',0,0),(793,291,127,'Nevada','NV',0,11,0,'',0,0),(792,291,127,'Nebraska','NE',0,10,0,'',0,0),(791,291,127,'Lousiana','LA',0,9,0,'',0,0),(790,291,127,'Hawaii','HI',0,8,0,'',0,0),(789,291,127,'Georgia','GA',0,7,0,'',0,0),(788,291,127,'Florida','FL',0,6,0,'',0,0),(787,291,127,'Delaware','DE',0,5,0,'',0,0),(786,291,127,'Colorado','CO',0,4,0,'',0,0),(785,291,127,'California','CA',0,3,0,'',0,0),(784,291,127,'Arkansas','AK',0,2,0,'',0,0),(1085,291,137,'False','False',0,2,0,'',0,0),(803,291,140,'False','False',0,2,0,'',0,0),(783,291,127,'--select one--','',0,1,0,'',0,0),(1093,290,130,'Furniture','Furniture',0,4,0,'',0,0),(1092,290,130,'Equipment','Equipment',0,3,0,'',0,0),(1084,291,137,'True','True',0,1,0,'',0,0),(1087,291,138,'False','False',0,2,0,'',0,0),(1086,291,138,'True','True',0,1,0,'',0,0),(1089,291,139,'False','False',0,2,0,'',0,0),(1088,291,139,'True','True',0,1,0,'',0,0),(802,291,140,'True','True',0,1,0,'',0,0),(640,0,144,'Any','Any',0,1,47,'',0,0),(641,0,144,'Double','Double',0,2,47,'',0,0),(642,0,144,'Queen','Queen',0,3,47,'',0,0),(643,0,144,'King','King',0,4,47,'',0,0),(1324,0,298,'Tan','Tan',0,5,70,'',0,0),(1323,0,298,'White','White',0,6,70,'',0,0),(683,0,190,'XXL','XXL',0,5,35,'',0,0),(682,0,190,'XL','XL',0,4,35,'',0,0),(681,0,190,'L','L',0,3,35,'',0,0),(680,0,190,'M','M',0,2,35,'',0,0),(679,0,190,'S','S',0,1,35,'',0,0),(1374,0,216,'Mens Small','Mens Small',0,5,58,'',0,0),(1373,0,216,'Womens Large','Womens Large',0,4,58,'',0,0),(1372,0,216,'Womens Medium','Womens Medium',0,3,58,'',0,0),(1371,0,216,'Womens Small','Womens Small',0,2,58,'',0,0),(1370,0,216,'','',0,1,58,'',0,0),(1091,290,130,'Autos','Autos',0,2,0,'',0,0),(1380,290,136,'Sold','Sold',0,2,0,'',0,0),(1379,290,136,'Still Available','',0,1,0,'',0,0),(1090,290,130,'--select a category for your item--','',0,1,0,'',0,0),(794,291,127,'New York','NY',0,12,0,'',0,0),(795,291,127,'Texas','TX',0,13,0,'',0,0),(826,506,232,'Mr.','Mr.',0,1,0,'',0,0),(827,506,232,'Ms.','Ms.',0,2,0,'',0,0),(828,506,232,'Mrs.','Mrs.',0,3,0,'',0,0),(829,506,232,'Miss','Miss',0,4,0,'',0,0),(830,506,232,'Dr.','Dr.',0,5,0,'',0,0),(831,506,232,'Rev.','Rev.',0,6,0,'',0,0),(832,506,237,'USC Trojans','USC Trojans',0,2,0,'',0,0),(833,506,237,'Texas Longhorns','Texas Longhorns',0,1,0,'',0,0),(834,506,240,'Nevada','NV',0,11,0,'',0,0),(835,506,240,'Nebraska','NE',0,10,0,'',0,0),(836,506,240,'Lousiana','LA',0,9,0,'',0,0),(837,506,240,'Hawaii','HI',0,8,0,'',0,0),(838,506,240,'Georgia','GA',0,7,0,'',0,0),(839,506,240,'Florida','FL',0,6,0,'',0,0),(840,506,240,'Delaware','DE',0,5,0,'',0,0),(841,506,240,'Colorado','CO',0,4,0,'',0,0),(842,506,240,'California','CA',0,3,0,'',0,0),(843,506,240,'Arkansas','AK',0,2,0,'',0,0),(844,506,240,'--select one--','',0,1,0,'',0,0),(845,506,240,'New York','NY',0,12,0,'',0,0),(846,506,240,'Texas','TX',0,13,0,'',0,0),(847,506,242,'False','False',0,2,0,'',0,0),(848,506,242,'True','True',0,1,0,'',0,0),(849,506,243,'False','False',0,2,0,'',0,0),(850,506,243,'True','True',0,1,0,'',0,0),(851,506,244,'False','False',0,2,0,'',0,0),(852,506,244,'True','True',0,1,0,'',0,0),(853,506,245,'False','False',0,2,0,'',0,0),(854,506,245,'True','True',0,1,0,'',0,0),(1378,0,216,'Mens XXL','Mens XXL',0,9,58,'',0,0),(1377,0,216,'Mens XL','Mens XL',0,8,58,'',0,0),(1194,296,255,'Z','Z',0,26,0,'',0,0),(1094,290,130,'Miscellaneous','Miscellaneous',0,5,0,'',0,0),(1193,296,255,'Y','Y',0,25,0,'',0,0),(1192,296,255,'X','X',0,24,0,'',0,0),(1191,296,255,'W','W',0,23,0,'',0,0),(1190,296,255,'V','V',0,22,0,'',0,0),(1189,296,255,'U','U',0,21,0,'',0,0),(1188,296,255,'T','T',0,20,0,'',0,0),(1187,296,255,'S','S',0,19,0,'',0,0),(1186,296,255,'R','R',0,18,0,'',0,0),(1185,296,255,'Q','Q',0,17,0,'',0,0),(1184,296,255,'P','P',0,16,0,'',0,0),(1183,296,255,'O','O',0,15,0,'',0,0),(1182,296,255,'N','N',0,14,0,'',0,0),(1181,296,255,'M','M',0,13,0,'',0,0),(1180,296,255,'L','L',0,12,0,'',0,0),(1179,296,255,'K','K',0,11,0,'',0,0),(1178,296,255,'J','J',0,10,0,'',0,0),(1177,296,255,'I','I',0,9,0,'',0,0),(1176,296,255,'H','H',0,8,0,'',0,0),(1175,296,255,'G','G',0,7,0,'',0,0),(1174,296,255,'F','F',0,6,0,'',0,0),(1173,296,255,'E','E',0,5,0,'',0,0),(1172,296,255,'D','D',0,4,0,'',0,0),(1171,296,255,'C','C',0,3,0,'',0,0),(1170,296,255,'B','B',0,2,0,'',0,0),(1169,296,255,'A','A',0,1,0,'',0,0),(1326,0,298,'Brown','Brown',0,3,70,'',0,0),(1327,0,298,'Black','Black',0,2,70,'',0,0),(1328,0,298,'','',0,1,70,'',0,0),(1329,0,299,'Green','Green',0,4,71,'',0,0),(1330,0,299,'Tan','Tan',0,5,71,'',0,0),(1331,0,299,'White','White',0,6,71,'',0,0),(1332,0,299,'Brown','Brown',0,3,71,'',0,0),(1333,0,299,'Black','Black',0,2,71,'',0,0),(1334,0,299,'','',0,1,71,'',0,0),(1335,0,300,'Green','Green',0,4,72,'',0,0),(1336,0,300,'Tan','Tan',0,5,72,'',0,0),(1337,0,300,'White','White',0,6,72,'',0,0),(1338,0,300,'Brown','Brown',0,3,72,'',0,0),(1339,0,300,'Black','Black',0,2,72,'',0,0),(1340,0,300,'','',0,1,72,'',0,0);
/*!40000 ALTER TABLE `form_field_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_fields`
--

DROP TABLE IF EXISTS `form_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('text box','text area','pick list','radio button','check box','file upload','date','date and time','email address','information','time') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text box',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `required` tinyint(4) NOT NULL DEFAULT '1',
  `information` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `default_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `maxlength` int(10) unsigned NOT NULL DEFAULT '0',
  `rows` int(10) unsigned NOT NULL DEFAULT '0',
  `cols` int(10) unsigned NOT NULL DEFAULT '0',
  `multiple` tinyint(4) NOT NULL DEFAULT '0',
  `spacing_above` tinyint(4) NOT NULL DEFAULT '0',
  `spacing_below` tinyint(4) NOT NULL DEFAULT '0',
  `contact_field` enum('','salutation','first_name','last_name','suffix','nickname','company','title','department','office_location','business_address_1','business_address_2','business_city','business_state','business_country','business_zip_code','business_phone','business_fax','home_address_1','home_address_2','home_city','home_state','home_country','home_zip_code','home_phone','home_fax','mobile_phone','email_address','website','lead_source','opt_in','description','affiliate_name') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `upload_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `office_use_only` tinyint(4) NOT NULL DEFAULT '0',
  `wysiwyg` tinyint(4) NOT NULL DEFAULT '0',
  `quiz_question` tinyint(4) NOT NULL DEFAULT '0',
  `quiz_answer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rss_field` enum('','category','title','description') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `use_folder_name_for_default_value` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `form_type` enum('','custom','product','shipping','billing') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `product_id` (`product_id`),
  KEY `form_type` (`form_type`)
) ENGINE=MyISAM AUTO_INCREMENT=327 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_fields`
--

LOCK TABLES `form_fields` WRITE;
/*!40000 ALTER TABLE `form_fields` DISABLE KEYS */;
INSERT INTO `form_fields` VALUES (1,183,'first_name','First Name:','text box',1,1,'','',30,0,0,0,0,0,0,'first_name',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(2,183,'last_name','Last Name:','text box',2,0,'','',30,0,0,0,0,0,0,'last_name',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(3,183,'company','Company:','text box',3,0,'','',30,0,0,0,0,0,0,'company',2,1537472973,1,0,0,0,'',0,'description',0,'custom'),(224,489,'company','Company:','text box',3,1,'','',0,0,0,0,0,0,0,'company',2,1537472973,1,0,0,0,'',0,'',0,''),(225,489,'business_phone','Phone:','text box',4,1,'','',0,0,0,0,0,0,0,'business_phone',2,1537472973,1,0,0,0,'',0,'',0,''),(9,183,'business_phone','Phone:','text box',4,0,'','',30,0,0,0,0,0,0,'business_phone',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(10,183,'e-mail','E-mail Address:','email address',5,1,'','',30,0,0,0,0,0,1,'email_address',40,1543545213,1,0,0,0,'',0,'',0,'custom'),(12,183,'details','Details:','text area',7,1,'','',0,0,5,30,0,0,1,'',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(219,290,'info','','information',6,0,'<p><em>Be sure to include your currency symbol in your price. (e.g. $, etc).</em></p>','',0,0,0,0,0,1,0,'',2,1537472973,105,0,0,0,'',0,'',0,'custom'),(44,296,'photo','Photo (All same size. 577px x 422px):','file upload',6,0,'','',0,0,0,0,0,0,1,'',40,1545337030,207,0,0,0,'',0,'',0,'custom'),(43,296,'bio','Bio:','text area',5,0,'','',0,0,4,50,0,0,0,'',40,1545337024,1,0,0,0,'',0,'description',0,'custom'),(42,296,'title','Title:','text box',3,0,'','',23,80,0,0,0,0,0,'',40,1547059518,1,0,0,0,'',0,'title',0,'custom'),(40,296,'first_name','First Name:','text box',1,1,'','',23,0,0,0,0,0,0,'',2,1537472973,112,0,0,0,'',0,'',0,'custom'),(41,296,'last_name','Last Name:','text box',2,1,'','',23,0,0,0,0,0,0,'',2,1537472973,112,0,0,0,'',0,'',0,'custom'),(321,296,'sort','Sort Order:','text box',10,1,'','1',0,0,0,0,0,0,0,'',40,1544173265,0,0,0,0,'',0,'',0,'custom'),(36,313,'title','Title','text box',2,1,'','',91,100,0,0,0,0,1,'',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(38,313,'details','Posting (Details):','text area',3,1,'','',0,0,15,60,0,0,1,'',40,1547760129,1,0,1,0,'',0,'',0,'custom'),(217,0,'info','','information',1,0,'<p>Each student will receive a shirt during the class.&nbsp; Please specify your shirt size.</p>','',0,0,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',58,'',0,'product'),(47,296,'phone','Phone:','text box',4,0,'','',23,0,0,0,0,0,0,'',40,1545337007,112,0,0,0,'',0,'',0,'custom'),(52,183,'notes','Staff Notes:','text area',8,0,'','',0,0,5,30,0,0,0,'',2,1537472973,1,1,0,0,'',0,'',0,'custom'),(208,472,'email','Email:','email address',2,1,'','',21,0,0,0,0,0,0,'email_address',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(215,0,'info','','information',3,0,'<p>We will be serving lunch during the class so please let us know if you have any special dietary requirements.</p>','',0,0,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',58,'',0,'product'),(324,296,'linkedin','LinkedIn Link:','text box',8,0,'','',0,0,0,0,0,0,0,'',40,1545336741,0,0,0,0,'',0,'',0,'custom'),(323,296,'twitter','Twitter Link:','text box',7,0,'','',0,0,0,0,0,0,0,'',40,1545336722,0,0,0,0,'',0,'',0,'custom'),(176,408,'detail','Detail','text area',2,1,'','',0,0,10,70,0,0,0,'',16,1537472973,1,0,0,0,'',0,'description',0,'custom'),(216,0,'shirt-size','Shirt Size:','pick list',2,1,'','',0,0,0,0,0,0,0,'',40,1545078300,1,0,0,0,'',58,'',0,'product'),(152,0,'from','From:','text box',2,0,'','',40,80,0,0,0,0,0,'',2,1537472973,0,0,0,0,'',31,'',0,'product'),(151,0,'honoree','In Loving Memory of:','text box',1,0,'','',40,80,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',31,'',0,'product'),(115,288,'last_name','Last Name:','text box',8,1,'','',0,0,0,0,0,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(114,288,'salutation','Salutation:','pick list',6,1,'','',0,0,0,0,0,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(113,288,'first_name','First Name:','text box',7,1,'','',0,0,0,0,0,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(109,288,'phone','Phone:','text box',9,0,'','',0,0,0,0,0,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(110,288,'email','E-mail Address:','text box',10,0,'','',0,0,0,0,0,0,1,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(111,288,'organization','Organization:','text box',2,1,'','',45,0,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(112,288,'description','Description:','text area',4,0,'','',0,0,10,45,0,0,0,'',2,1537472973,1,0,0,0,'',0,'description',0,'custom'),(116,288,'Contact Heading','','information',5,0,'<p>The following&nbsp;fields are seperate from your&nbsp;\'My Account\' information and will only be updated when you update this member directory entry.</p>\r\n<p>&nbsp;<strong>Contact Information</strong></p>\r\n<hr />','',0,0,0,0,0,1,0,'',2,1537472973,106,0,0,0,'',0,'',0,'custom'),(117,288,'Organization Heading','','information',1,0,'<strong>Organization Information </strong>\r\n<hr />\r\n','',0,0,0,0,0,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(118,288,'services','Services','check box',3,1,'','',0,0,0,0,1,0,0,'',1,1537472973,106,0,0,0,'',0,'',0,'custom'),(119,291,'salutation','Salutation:','pick list',1,1,'','',0,0,0,0,0,0,0,'salutation',1,1537472973,104,0,0,0,'',0,'',0,'custom'),(120,291,'first_name','First Name:','text box',2,1,'','',0,0,0,0,0,0,0,'first_name',1,1537472973,104,0,0,0,'',0,'',0,'custom'),(121,291,'last_name','Last Name:','text box',3,1,'','',0,0,0,0,0,0,0,'last_name',2,1537472973,104,0,0,0,'',0,'title',0,'custom'),(122,291,'email','Email:','email address',4,1,'','',0,0,0,0,0,0,0,'email_address',2,1537472973,104,0,0,0,'',0,'description',0,'custom'),(123,291,'test_game_date','The 2006 Rose Bowl game was played on what date?','date',6,1,'','01/01/2006',12,0,0,0,0,1,1,'',40,1538953872,104,0,0,1,'2006-01-04',0,'',0,'custom'),(124,291,'test_winner','What team won the 2006 Rose Bowl game?','radio button',12,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'Texas Longhorns',0,'',0,'custom'),(125,291,'test info','','information',5,0,'<p class=\"lead\">&nbsp;</p>\r\n\r\n<p class=\"lead\">Please read the following carefully:</p>\r\n\r\n<blockquote>\r\n<p class=\"text-box-primary\"><em>The 2006 Rose Bowl Game was the final game and national championship of the 2005-2006 Bowl Championship Series (BCS) and the 92nd Rose Bowl Game. The game was played on January 4, 2006, at the Rose Bowl Stadium in Pasadena, California.&nbsp;&nbsp;Although the game saw a back-and-forth contest, it was ultimately won by the Texas Longhorns, 41-38. UT&#39;s Vince Young was the game&#39;s MVP. Texas&#39;s Rose Bowl win was the 800th victory in school history. The Longhorns ended the season ranked third in the all-time list of both total wins and winning percentage.</em></p>\r\n</blockquote>\r\n\r\n<p class=\"lead\">Now,&nbsp;answer the&nbsp;following questions to the best of your ability and click &#39;Score Quiz&#39;.</p>\r\n\r\n<p class=\"lead\">&nbsp;</p>\r\n','',0,0,0,0,0,0,0,'',40,1538953791,104,0,0,0,'',0,'',0,'custom'),(130,290,'category','Category:','pick list',1,1,'','--select a category for your item--',0,0,0,0,0,0,0,'',2,1537472973,105,0,0,0,'',0,'',0,'custom'),(293,313,'media','Media (Add Image and use \"img-responsive\" custom format; add iframe or video and use \"embed-responsive\" custom format)','text area',1,1,'','',0,0,10,50,0,0,1,'',40,1547759730,207,0,1,0,'',0,'',0,'custom'),(127,291,'test_location','In what state was the game held?','pick list',7,1,'','--select one--',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'CA',0,'',0,'custom'),(129,291,'test_mvp','Who was the game\'s MVP?','text box',13,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'Vince Young',0,'',0,'custom'),(131,290,'item','Item:','text box',2,1,'','',45,45,0,0,0,0,0,'',2,1537472973,105,0,0,0,'',0,'title',0,'custom'),(132,290,'description','Description:','text area',3,1,'','',0,0,6,60,0,0,1,'',2,1537472973,105,0,0,0,'',0,'description',0,'custom'),(133,290,'price','Price:','text box',7,1,'','',0,0,0,0,0,0,1,'',2,1537472973,105,0,0,0,'',0,'',0,'custom'),(134,290,'seller','Seller:','text area',8,1,'','',0,0,3,30,0,0,1,'',2,1537472973,105,0,0,0,'',0,'',0,'custom'),(136,290,'status','Status:','radio button',9,0,'','',0,0,0,0,0,0,1,'',40,1548294061,105,0,0,0,'',0,'',0,'custom'),(137,291,'burnt_orange','Texas Longhorns colors include burnt orange.','radio button',8,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'True',0,'',0,'custom'),(138,291,'carolina','USC Trojans are from the University of South Carolina.','radio button',9,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'False',0,'',0,'custom'),(139,291,'pasadena','The Rose Bowl is located in the city of Pasadena.','radio button',10,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'True',0,'',0,'custom'),(140,291,'nfl','Both teams now play in the NFL.','radio button',11,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,1,'False',0,'',0,'custom'),(142,0,'first_name','Attendee First Name:','text box',2,1,'','',20,20,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',47,'',0,'product'),(143,0,'last_name','Attendee Last Name:','text box',3,1,'','',20,20,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',47,'',0,'product'),(144,0,'hotel','Hotel Room Preference:','pick list',7,1,'','Any',0,0,0,0,0,0,0,'',2,1537472973,0,0,0,0,'',47,'',0,'product'),(145,0,'phone','Attendee Phone:','text box',4,1,'','',15,15,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',47,'',0,'product'),(146,0,'email','Attendee E-Mail:','email address',5,1,'','',40,80,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',47,'',0,'product'),(147,0,'info','','information',1,0,'<p>Please complete the following information.</p>\r\n','',0,0,0,0,0,0,0,'',40,1547059203,1,0,0,0,'',47,'',0,'product'),(148,0,'hotel heading','','information',6,0,'The conference center hotel is providing complimentary hotel accommodations for all our Exhibitors. Please specify your preference on hotel rooms.&nbsp; All are non-smoking.\r\n','',0,0,0,0,0,1,0,'',2,1537472973,0,0,0,0,'',47,'',0,'product'),(149,0,'needs','Special Needs:','text area',8,0,'','',0,0,3,40,0,0,0,'',2,1537472973,1,0,0,0,'',47,'',0,'product'),(174,408,'subject','Subject','text box',1,1,'','',55,80,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(214,0,'comments','Special Instructions:','text area',4,0,'','',0,0,4,60,0,0,0,'',2,1537472973,1,0,0,0,'',58,'',0,'product'),(166,395,'subject','Subject:','text box',1,1,'','',50,0,0,0,0,0,0,'',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(167,395,'details','Details:','text area',2,1,'','',0,0,10,65,0,0,0,'',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(168,395,'information_01','','information',3,0,'<p>Please enter/update your contact information.</p>','',0,0,0,0,0,1,0,'',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(169,395,'first_name','First Name:','text box',4,1,'','',0,0,0,0,0,0,0,'first_name',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(170,395,'last_name','Last Name:','text box',5,1,'','',0,0,0,0,0,0,0,'last_name',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(171,395,'e-mail','E-mail:','email address',6,1,'','',0,0,0,0,0,0,0,'email_address',2,1537472973,173,0,0,0,'',0,'',0,'custom'),(300,0,'color','Color:','pick list',1,1,'','',0,0,0,0,0,0,0,'',40,1540917300,0,0,0,0,'',72,'',0,'product'),(299,0,'color','Color:','pick list',1,1,'','',0,0,0,0,0,0,0,'',40,1540916852,0,0,0,0,'',71,'',0,'product'),(298,0,'color','Color:','pick list',1,1,'','',0,0,0,0,0,0,0,'',40,1540916031,0,0,0,0,'',70,'',0,'product'),(177,412,'subject','Subject','text box',1,1,'','',65,80,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',0,'',0,''),(178,412,'name','Display Name:','text box',3,1,'','',35,45,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',0,'',0,''),(179,412,'detail','Detail','text area',2,1,'','',0,0,10,50,0,0,1,'',2,1537472973,1,0,0,0,'',0,'',0,''),(180,417,'first_name','First Name:','text box',1,1,'','',30,0,0,0,0,0,0,'first_name',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(181,417,'last_name','Last Name:','text box',2,1,'','',30,0,0,0,0,0,0,'last_name',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(182,417,'company','Company:','text box',3,1,'','',30,0,0,0,0,0,0,'company',2,1537472973,1,0,0,0,'',0,'title',0,'custom'),(183,417,'business_phone','Phone:','text box',4,1,'','',30,0,0,0,0,0,0,'business_phone',2,1537472973,1,0,0,0,'',0,'',0,'custom'),(184,417,'email_address','E-mail Address:','email address',5,1,'','',30,0,0,0,0,0,1,'email_address',2,1537472973,1,0,0,0,'',0,'description',0,'custom'),(188,0,'first_name','Member\'s First Name:','text box',1,1,'','',0,0,0,0,0,0,0,'',2,1537472973,0,0,0,0,'',35,'',0,'product'),(189,0,'last_name','Member\'s Last Name:','text box',2,1,'','',0,0,0,0,0,0,0,'',2,1537472973,0,0,0,0,'',35,'',0,'product'),(190,0,'shirt_size','T-Shirt Size:','pick list',4,1,'','',0,0,0,0,0,0,1,'',2,1537472973,1,0,0,0,'',35,'',0,'product'),(191,0,'comments','How did you hear about us?','text area',3,1,'','',0,0,5,45,0,0,0,'',2,1537472973,1,0,0,0,'',35,'',0,'product'),(193,313,'description','Description:','text box',10,0,'','',80,150,0,0,0,0,0,'',2,1537472973,1,0,0,0,'',0,'description',0,'custom'),(194,313,'to_field','','information',11,0,'<p>If you plan on e-mailing this blog posting, you can enter the  ^^name^^ field to personalize the email. (For example, \"Dear  [[^^name^^||Subscriber]],\"&nbsp;will become \"Dear Johnny Appleseed,\" or \"Dear  Subscriber,\"&nbsp;when the posting is sent as an e-mail campaign.) It will  not be visible to blog visitors.</p>','',0,0,0,0,0,1,0,'',2,1537472973,112,1,0,0,'',0,'',0,'custom'),(195,313,'to','To:','text box',12,0,'','Dear [[^^name^^||Subscriber]],',50,0,0,0,0,0,0,'',2,1537472973,112,1,0,0,'',0,'',0,'custom'),(196,313,'signature_field','','information',13,0,'<p>If you plan on e-mailing this blog posting, you can add a signature area at the bottom of your e-mailed page.</p>','',0,0,0,0,0,1,0,'',2,1537472973,112,1,0,0,'',0,'',0,'custom'),(197,313,'signature','Signature','text area',14,0,'','Best Regards',0,0,10,50,0,0,1,'',2,1537472973,1,1,0,0,'',0,'',0,'custom'),(277,313,'info2','','information',7,0,'<p>Specify this posting\'s publish date.</p>','',0,0,0,0,0,1,0,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(222,489,'first_name','First Name:','text box',1,1,'','',0,0,0,0,0,0,0,'first_name',2,1537472973,1,0,0,0,'',0,'',0,''),(223,489,'last_name','Last Name:','text box',2,1,'','',0,0,0,0,0,0,0,'last_name',2,1537472973,1,0,0,0,'',0,'',0,''),(226,489,'email_address','E-mail Address:','email address',5,1,'','',0,0,0,0,0,0,1,'email_address',2,1537472973,1,0,0,0,'',0,'',0,''),(232,506,'salutation','Salutation:','pick list',2,1,'','',0,0,0,0,0,0,0,'salutation',2,1537472973,104,0,0,0,'',0,'',0,''),(233,506,'first_name','First Name:','text box',3,1,'','',0,0,0,0,0,0,0,'first_name',2,1537472973,104,0,0,0,'',0,'',0,''),(234,506,'last_name','Last Name:','text box',4,1,'','',0,0,0,0,0,0,0,'last_name',2,1537472973,104,0,0,0,'',0,'',0,''),(235,506,'email','Email:','email address',5,1,'','',0,0,0,0,0,0,0,'email_address',2,1537472973,104,0,0,0,'',0,'',0,''),(236,506,'test_game_date','The 2006 Rose Bowl game was played on what date?','date',7,1,'','',12,0,0,0,0,1,1,'',2,1537472973,104,0,0,0,'',0,'',0,''),(237,506,'test_winner','What team won the 2006 Rose Bowl game?','radio button',13,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,0,'',0,'',0,''),(238,506,'test info','','information',6,0,'<p><strong>Please read the following carefully:</strong></p>\r\n<p class=\"text-box-primary\"><em>The 2006 Rose Bowl Game was the final game and national championship of the 2005-2006 Bowl Championship Series (BCS) and the 92nd Rose Bowl Game. The game was played on January 4, 2006, at the Rose Bowl Stadium in Pasadena, California.&nbsp;&nbsp;Although the game saw a back-and-forth contest, it was ultimately won by the Texas Longhorns, 41-38. UT\'s Vince Young was the game\'s MVP. Texas\'s Rose Bowl win was the 800th victory in school history. The Longhorns ended the season ranked third in the all-time list of both total wins and winning percentage.</em></p>\r\n<p>Now,&nbsp;answer the&nbsp;following questions to the best of your ability and click \'Score Exam Now\'.&nbsp; <span style=\"color: #ff0000;\">You must achieve a score of<strong> 100%</strong> in order to pass this exam and retrieve your certificate.</span>&nbsp; You can take the exam&nbsp;as many times as you need too.</p>','',0,0,0,0,0,0,0,'',2,1537472973,104,0,0,0,'',0,'',0,''),(239,506,'tester','','information',1,0,'<p>\r\n<strong>Please update your \'My Account\' information if it has changed:</strong> \r\n</p>\r\n','',0,0,0,0,0,0,0,'',2,1537472973,104,0,0,0,'',0,'',0,''),(240,506,'test_location','In what state was the game held?','pick list',8,1,'','--select one--',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,0,'',0,'',0,''),(241,506,'test_mvp','Who was the game\'s MVP?','text box',14,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,0,'',0,'',0,''),(242,506,'burnt_orange','Texas Longhorns colors include burnt orange.','radio button',9,1,'','',0,0,0,0,0,0,0,'',2,1537472973,104,0,0,0,'',0,'',0,''),(243,506,'carolina','USC Trojans are from the University of South Carolina.','radio button',10,1,'','',0,0,0,0,0,0,0,'',2,1537472973,104,0,0,0,'',0,'',0,''),(244,506,'pasadena','The Rose Bowl is located in the city of Pasadena.','radio button',11,1,'','',0,0,0,0,0,0,0,'',2,1537472973,104,0,0,0,'',0,'',0,''),(245,506,'nfl','Both teams now play in the NFL.','radio button',12,1,'','',0,0,0,0,0,0,1,'',2,1537472973,104,0,0,0,'',0,'',0,''),(246,144,'instructions','Delivery Instructions','text area',1,0,'','',0,0,3,60,0,0,0,'',40,1541702168,0,0,0,0,'',0,'',0,'shipping'),(248,538,'youtube-id','Youtube Video ID:','text box',2,0,'','',15,15,0,0,0,0,0,'',40,1544545530,110,0,0,0,'',0,'',0,'custom'),(249,538,'title','Title:','text box',4,1,'','',70,0,0,0,0,0,0,'',2,1537472973,110,0,0,0,'',0,'title',0,'custom'),(254,538,'sort','Sort Order:','text box',6,1,'','',3,0,0,0,0,0,1,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(253,538,'info','','information',1,0,'<h5>Instructions</h5>\r\n\r\n<p>Paste the <strong>ID</strong> to the youtube.com or vimeo video to add. For example, only paste the bold part of the following video link examples: http://youtu.be/<strong>3L9lz7Zz9QI </strong>or http://player.vimeo.com/video/<strong>167054481</strong></p>\r\n\r\n<p>Leave the other ID field blank.</p>\r\n','',0,0,0,0,0,0,0,'',40,1544545668,110,0,0,0,'',0,'',0,'custom'),(255,296,'alphabetical','Alphabet:','pick list',9,1,'','',1,1,0,0,0,0,1,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(257,313,'category','Category:','text box',6,0,'','',0,0,0,0,0,0,1,'',40,1545759057,110,0,0,0,'',0,'',0,'custom'),(325,1059,'email','Email:','email address',1,1,'','',21,0,0,0,0,0,0,'email_address',40,1548292774,1,0,0,0,'',0,'',0,'custom'),(259,313,'info1','','information',5,0,'<p>Add a tag or topic name for this post.&nbsp; A tag cloud will be formed from all the tagged postings.</p>','',0,0,0,0,0,1,0,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(278,313,'info3','','information',9,0,'<p>Enter a short description for RSS and Advanced Site Search (if either are enabled).</p>','',0,0,0,0,0,1,0,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(266,313,'summary','Summary:','text area',4,1,'','',0,0,15,60,0,0,0,'',2,1537472973,110,0,1,0,'',0,'',0,'custom'),(267,472,'description','','information',1,0,'<p>Get the latest offers direct to your inbox and save $3 on our popular eBook!</p>\r\n','',0,0,0,0,0,0,0,'',40,1539115658,197,0,0,0,'',0,'',0,'custom'),(268,290,'photo','Photo:','file upload',5,0,'','',0,0,0,0,0,0,0,'',40,1547675727,208,0,0,0,'',0,'',0,'custom'),(269,290,'photo info','','information',4,0,'<p>Add a photo for better sales! Your photo should be at least 750px wide for best results.</p>\r\n','',0,0,0,0,0,1,0,'',40,1544809796,201,0,0,0,'',0,'',0,'custom'),(270,584,'first_name','First Name:','text box',1,1,'','',0,0,0,0,0,0,0,'first_name',2,1537472973,197,0,0,0,'',0,'',0,'custom'),(271,584,'last_name','Last Name:','text box',2,1,'','',0,0,0,0,0,0,0,'last_name',2,1537472973,197,0,0,0,'',0,'',0,'custom'),(272,584,'email','E-mail Address:','email address',3,1,'','',0,0,0,0,0,0,0,'email_address',2,1537472973,197,0,0,0,'',0,'',0,'custom'),(273,584,'phone','Phone:','text box',4,0,'','',0,0,0,0,0,0,0,'business_phone',2,1537472973,197,0,0,0,'',0,'',0,'custom'),(274,584,'tickets','# Tickets:','text box',6,1,'','',3,3,0,0,0,0,1,'',40,1545156107,197,0,0,0,'',0,'',0,'custom'),(275,584,'event','Event / Date:','text box',5,1,'','',0,0,5,60,0,1,0,'',40,1545159117,197,0,0,0,'',0,'',0,'custom'),(276,313,'publish-date','Publish Date:','date',8,0,'','',0,0,0,0,0,0,1,'',2,1537472973,110,0,0,0,'',0,'',0,'custom'),(290,0,'info','','information',1,0,'<p>To apply the credit(s) to an existing services project, please enter the Project #.<br />\r\nTo open a new services project, leave the Project # blank (below) and we will create one for you.</p>\r\n','',0,0,0,0,0,0,0,'',40,1541014840,1,0,0,0,'',69,'',0,'product'),(289,0,'id','Project #:','text box',2,0,'','',0,0,0,0,0,0,0,'',40,1541014851,1,0,0,0,'',69,'',0,'product'),(291,183,'subject','Subject:','text box',6,1,'','',30,0,0,0,0,0,0,'',2,1537472973,197,0,0,0,'',0,'',0,'custom'),(322,538,'vimeo-id','(or) Vimeo Video ID:','text box',3,0,'','',15,15,0,0,0,0,0,'',40,1544545687,0,0,0,0,'',0,'',0,'custom'),(319,1023,'e-mail','E-mail:','email address',6,1,'','',0,0,0,0,0,0,0,'email_address',40,1542670997,173,0,0,0,'',0,'',0,'custom'),(320,1023,'credits','Credits:','text box',7,0,'','0',6,0,0,0,0,0,0,'',40,1543335574,199,1,0,0,'',0,'',0,'custom'),(318,1023,'last_name','Last Name:','text box',5,1,'','',0,0,0,0,0,0,0,'last_name',40,1542670997,173,0,0,0,'',0,'',0,'custom'),(317,1023,'first_name','First Name:','text box',4,1,'','',0,0,0,0,0,0,0,'first_name',40,1542670997,173,0,0,0,'',0,'',0,'custom'),(316,1023,'information_01','','information',3,0,'<p>Please enter/update your contact information.</p>','',0,0,0,0,0,1,0,'',40,1542670997,173,0,0,0,'',0,'',0,'custom'),(315,1023,'details','Details:','text area',2,1,'','',0,0,10,65,0,0,0,'',40,1542670997,173,0,0,0,'',0,'',0,'custom'),(314,1023,'subject','Subject:','text box',1,1,'','',50,0,0,0,0,0,0,'',40,1542670997,173,0,0,0,'',0,'',0,'custom');
/*!40000 ALTER TABLE `form_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_item_view_pages`
--

DROP TABLE IF EXISTS `form_item_view_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_item_view_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_form_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `layout` longtext COLLATE utf8_unicode_ci NOT NULL,
  `submitted_form_editable_by_submitter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submitted_form_editable_by_registered_user` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submitter_security` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `collection` enum('a','b') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `page_id_collection` (`page_id`,`collection`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_item_view_pages`
--

LOCK TABLES `form_item_view_pages` WRITE;
/*!40000 ALTER TABLE `form_item_view_pages` DISABLE KEYS */;
INSERT INTO `form_item_view_pages` VALUES (3,316,296,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">\r\n		[[<p><img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" /></p>||<p><img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" /></p>]]\r\n	</div>\r\n	<div class=\"col-sm-6\">\r\n		<h3 style=\"margin-bottom:0\">^^first_name^^ ^^last_name^^</h3>\r\n		<h4>^^title^^</h4>\r\n		<p>^^phone^^</p>\r\n		<p>^^bio^^</p>\r\n		<p style=\"display:none;\">^^alphabetical^^</p>\r\n		<p style=\"display:none;\">^^sort^^</p>\r\n		<div>   \r\n			<ul class=\"list-inline social-list\">\r\n				<li>[[<a href=\"^^twitter^^\" target=\"_blank\"><i class=\"ti-twitter-alt\">&nbsp;</i></a>||<i class=\"ti-twitter-alt\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n                <li>[[<a href=\"^^linkedin^^\" target=\"_blank\"><i class=\"ti-linkedin\">&nbsp;</i></a>||<i class=\"ti-linkedin\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n			</ul>\r\n			<p>&nbsp;</p>\r\n		</div>    \r\n	</div>\r\n</div>',1,0,0,'','a'),(9,330,288,'<h4>^^organization^^</h4>\r\n\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td style=\"width: 20%;\"><strong>Services:</strong></td>\r\n			<td>^^services^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Description:</strong></td>\r\n			<td>^^description^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Contact:</strong></td>\r\n			<td>^^salutation^^ ^^first_name^^ ^^last_name^^<br />\r\n			^^phone^^<br />\r\n			^^email^^</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',1,0,0,'','a'),(13,365,313,'<div class=\"post-snippet mb64\">\r\n    ^^media^^\r\n	<div class=\"post-title\">\r\n    	<span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n    	<a href=\"#\"> </a>\r\n		<h4 class=\"inline-block\">^^title^^</h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^ </span></li>\r\n	</ul>\r\n	<hr />\r\n	^^details^^\r\n</div>',1,0,0,'','a'),(36,1014,313,'<div class=\"post-snippet mb64\">\r\n    ^^media^^\r\n	<div class=\"post-title\">\r\n    	<span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n    	<a href=\"#\"> </a>\r\n		<h4 class=\"inline-block\">^^title^^</h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^ </span></li>\r\n	</ul>\r\n	<hr />\r\n	^^details^^\r\n</div>',1,0,0,'','a'),(12,354,290,'<h4>^^category^^</h4>\r\n\r\n<div>[[<img src=\"{path}^^photo^^\" alt=\"^^item^^\"/>||]]</div>\r\n\r\n<h3>^^item^^</h3>\r\n\r\n<h5>[[<span style=\"color: red; font-weight: bold;\">^^status^^</span> <span style=\"font-decoration: strikethrough;\"><span style=\"text-decoration: line-through;\">^^price^^</span></span>||^^price^^]]</h5>\r\n\r\n<p>^^description^^</p>\r\n\r\n<p><em>^^seller^^</em></p>\r\n',1,0,0,'','a'),(19,402,395,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) &nbsp; ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb32\" style=\"font-size: 125%\">^^details^^</div>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Add your Team</h6>\r\n\r\n<p>Grant access to anyone on your team to participate in this support ticket by including them as a <a href=\"#software_watcher\">watcher</a>.</p>\r\n[[\r\n\r\n<h6 class=\"heading-title\">File Attachments</h6>\r\n\r\n<p>^^comment_attachments^^</p>\r\n||]]</div>\r\n\r\n<div class=\"col-sm-3 mb24\">\r\n<h6 class=\"heading-title\">Support Ticket #^^reference_code^^</h6>\r\n[[\r\n\r\n<p class=\"mt8 mb16\"><i>Latest Reply</i></p>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n<a href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a></div>\r\n||]]</div>\r\n',0,0,1,'','a'),(21,409,408,'<table border=\"0\" style=\"width: 100%; padding: 1em;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>\r\n			<h3>^^subject^^</h3>\r\n\r\n			<p><span class=\"text-fine-print\">Posted by: <strong>^^submitter^^</strong> on ^^submitted_date_and_time^^</span></p>\r\n\r\n			<p>^^detail^^</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'','a'),(24,222,183,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-12 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb24\" style=\"font-size:125%\">^^details^^</div>\r\n\r\n<p><a href=\"{path}my-conversation?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n</div>\r\n</div>\r\n',0,0,0,'','a'),(25,438,313,'<hr>\r\n<p><strong>Enter the ^^name^^ field and any conditionals to personalize the email.</strong> (For example, &quot;Dear [[^^name^^||Subscriber]],&quot; will become &quot;Dear Johnny Appleseed,&quot; or &quot;Dear Subscriber,&quot; when the posting is sent as an e-mail campaign.)</p>\r\n\r\n<p><strong>^^to^^</strong></p>\r\n\r\n<p><strong>Add a signature </strong>area at the bottom of your e-mailed page.</p>\r\n\r\n<p><strong>^^signature^^</strong></p>\r\n<hr>\r\n\r\n',0,0,0,'','a'),(31,540,538,'<div class=\"row\">\r\n<div class=\"col-sm-12 text-center\">[[\r\n<div class=\"embed-video-container embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/^^youtube-id^^?hl=en_US&amp;rel=0\"></iframe></div>\r\n||\r\n\r\n<div class=\"embed-video-container embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"https://player.vimeo.com/video/^^vimeo-id^^?badge=0&amp;title=0&amp;byline=0&amp;title=0\"></iframe></div>\r\n]]\r\n\r\n<h5>^^title^^</h5>\r\n\r\n<p>Sort Order: ^^sort^^</p>\r\n</div>\r\n</div>\r\n',0,0,0,'','a'),(41,85,183,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-12 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb24\" style=\"font-size:125%\">^^details^^</div>\r\n\r\n<p><a href=\"{path}my-conversation?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n</div>\r\n</div>\r\n',0,0,1,'','a'),(34,648,183,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16\" style=\"font-size:125%\">^^details^^</div>\r\n</div>\r\n\r\n<div class=\"col-sm-3\">[[<i class=\"mb16\" style=\"display:block\">Latest Reply</i>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n<a href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||]]</div>\r\n</div>\r\n',0,0,1,'','a'),(37,1029,1023,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) &nbsp; ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb32\" style=\"font-size: 125%\">^^details^^</div>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Add your Team</h6>\r\n\r\n<p>Grant access to anyone on your team to participate in this support ticket by including them as a <a href=\"#software_watcher\">watcher</a>.</p>\r\n[[\r\n\r\n<h6 class=\"heading-title\">File Attachments</h6>\r\n\r\n<p>^^comment_attachments^^</p>\r\n||]]</div>\r\n\r\n<div class=\"col-sm-3 mb24\">\r\n<h6 class=\"heading-title\">Service Project #^^reference_code^^</h6>\r\n\r\n[[<p class=\"mt8 mb16\"><i>Latest Reply</i></p>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n    <a style=\"display:block\" href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a><br />||]]\r\n\r\n<h3 style=\"display:inline\">^^credits^^</h3> <strong>Credits Remaining</strong>\r\n\r\n<p class=\"mt24\"><a class=\"btn mb0\" href=\"{path}{software_directory}/do.php?action=prefill_product_form&amp;field_name=id&amp;field_value=^^reference_code^^&amp;url={path}order-services\">Add Credits</a></p>\r\n    \r\n<p>You can add additional credits to this project at any time when you are ready for our services team to continue working on your project.</p>\r\n</div>\r\n</div>\r\n',0,0,1,'','a'),(39,1032,1023,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Project Summary</h6>\r\n\r\n<p>Project Number: <strong>^^reference_code^^</strong><br />\r\nClient Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nClient Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><strong>^^credits^^ Credits Remaining</strong></p>\r\n\r\n<p><a href=\"{path}my-services-project?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,0,'','a'),(40,396,395,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Support Ticket Summary</h6>\r\n\r\n<p>Support Ticket #:<strong>^^reference_code^^</strong><br />\r\nCustomer Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nCustomer Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><a href=\"{path}my-support-ticket?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,0,'','a'),(42,399,395,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Support Ticket Summary</h6>\r\n\r\n<p>Support Ticket #:<strong>^^reference_code^^</strong><br />\r\nCustomer Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nCustomer Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><a href=\"{path}my-support-ticket?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n',0,0,1,'','a'),(43,1024,1023,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Project Summary</h6>\r\n\r\n<p>Project Number: <strong>^^reference_code^^</strong><br />\r\nClient Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nClient Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><strong>^^credits^^ Credits Remaining</strong></p>\r\n\r\n<p><a href=\"{path}my-services-project?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,1,'','a'),(44,412,408,'<h4>^^subject^^</h4>\r\n\r\n<p>^^detail^^</p>\r\n\r\n<p><a class=\"btn\" href=\"{path}forum-thread-view?r=^^reference_code^^\">View or Reply</a></p>\r\n',0,0,0,'','a'),(45,489,417,'',0,0,0,'','a'),(46,1047,296,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">\r\n		[[<p><img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" /></p>||<p><img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" /></p>]]\r\n	</div>\r\n	<div class=\"col-sm-6\">\r\n		<h3 style=\"margin-bottom:0\">^^first_name^^ ^^last_name^^</h3>\r\n		<h4>^^title^^</h4>\r\n		<p>^^phone^^</p>\r\n		<p>^^bio^^</p>\r\n		<p style=\"display:none;\">^^alphabetical^^</p>\r\n		<p style=\"display:none;\">^^sort^^</p>\r\n		<div>   \r\n			<ul class=\"list-inline social-list\">\r\n				<li>[[<a href=\"^^twitter^^\" target=\"_blank\"><i class=\"ti-twitter-alt\">&nbsp;</i></a>||<i class=\"ti-twitter-alt\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n                <li>[[<a href=\"^^linkedin^^\" target=\"_blank\"><i class=\"ti-linkedin\">&nbsp;</i></a>||<i class=\"ti-linkedin\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n			</ul>\r\n			<p>&nbsp;</p>\r\n		</div>    \r\n	</div>\r\n</div>',0,0,0,'','a'),(47,316,0,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">\r\n		[[<p><img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" /></p>||<p><img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" /></p>]]\r\n	</div>\r\n	<div class=\"col-sm-6\">\r\n		<h3 style=\"margin-bottom:0\">^^first_name^^ ^^last_name^^</h3>\r\n		<h4>^^title^^</h4>\r\n		<p>^^phone^^</p>\r\n		<p>^^bio^^</p>\r\n		<p style=\"display:none;\">^^alphabetical^^</p>\r\n		<p style=\"display:none;\">^^sort^^</p>\r\n		<div>   \r\n			<ul class=\"list-inline social-list\">\r\n				<li>[[<a href=\"^^twitter^^\" target=\"_blank\"><i class=\"ti-twitter-alt\">&nbsp;</i></a>||<i class=\"ti-twitter-alt\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n                <li>[[<a href=\"^^linkedin^^\" target=\"_blank\"><i class=\"ti-linkedin\">&nbsp;</i></a>||<i class=\"ti-linkedin\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n			</ul>\r\n			<p>&nbsp;</p>\r\n		</div>    \r\n	</div>\r\n</div>',0,0,0,'','b'),(48,330,0,'<h4>^^organization^^</h4>\r\n\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td style=\"width: 20%;\"><strong>Services:</strong></td>\r\n			<td>^^services^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Description:</strong></td>\r\n			<td>^^description^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Contact:</strong></td>\r\n			<td>^^salutation^^ ^^first_name^^ ^^last_name^^<br />\r\n			^^phone^^<br />\r\n			^^email^^</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'','b'),(49,365,0,'<div class=\"post-snippet mb64\">\r\n    ^^media^^\r\n	<div class=\"post-title\">\r\n    	<span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n    	<a href=\"#\"> </a>\r\n		<h4 class=\"inline-block\">^^title^^</h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^ </span></li>\r\n	</ul>\r\n	<hr />\r\n	^^details^^\r\n</div>',0,0,0,'','b'),(50,1014,0,'<div class=\"post-snippet mb64\">\r\n    ^^media^^\r\n	<div class=\"post-title\">\r\n    	<span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n    	<a href=\"#\"> </a>\r\n		<h4 class=\"inline-block\">^^title^^</h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^ </span></li>\r\n	</ul>\r\n	<hr />\r\n	^^details^^\r\n</div>',0,0,0,'','b'),(51,354,0,'<h4>^^category^^</h4>\r\n\r\n<div>[[<img src=\"{path}^^photo^^\" alt=\"^^item^^\"/>||]]</div>\r\n\r\n<h3>^^item^^</h3>\r\n\r\n<h5>[[<span style=\"color: red; font-weight: bold;\">^^status^^</span> <span style=\"font-decoration: strikethrough;\"><span style=\"text-decoration: line-through;\">^^price^^</span></span>||^^price^^]]</h5>\r\n\r\n<p>^^description^^</p>\r\n\r\n<p><em>^^seller^^</em></p>\r\n',0,0,0,'','b'),(52,402,0,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) &nbsp; ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb32\" style=\"font-size: 125%\">^^details^^</div>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Add your Team</h6>\r\n\r\n<p>Grant access to anyone on your team to participate in this support ticket by including them as a <a href=\"#software_watcher\">watcher</a>.</p>\r\n[[\r\n\r\n<h6 class=\"heading-title\">File Attachments</h6>\r\n\r\n<p>^^comment_attachments^^</p>\r\n||]]</div>\r\n\r\n<div class=\"col-sm-3 mb24\">\r\n<h6 class=\"heading-title\">Support Ticket #^^reference_code^^</h6>\r\n[[\r\n\r\n<p class=\"mt8 mb16\"><i>Latest Reply</i></p>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n<a href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a></div>\r\n||]]</div>\r\n',0,0,0,'','b'),(53,409,0,'<table border=\"0\" style=\"width: 100%; padding: 1em;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>\r\n			<h3>^^subject^^</h3>\r\n\r\n			<p><span class=\"text-fine-print\">Posted by: <strong>^^submitter^^</strong> on ^^submitted_date_and_time^^</span></p>\r\n\r\n			<p>^^detail^^</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'','b'),(54,222,0,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-12 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb24\" style=\"font-size:125%\">^^details^^</div>\r\n\r\n<p><a href=\"{path}my-conversation?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n</div>\r\n</div>\r\n',0,0,0,'','b'),(55,438,0,'<hr>\r\n<p><strong>Enter the ^^name^^ field and any conditionals to personalize the email.</strong> (For example, &quot;Dear [[^^name^^||Subscriber]],&quot; will become &quot;Dear Johnny Appleseed,&quot; or &quot;Dear Subscriber,&quot; when the posting is sent as an e-mail campaign.)</p>\r\n\r\n<p><strong>^^to^^</strong></p>\r\n\r\n<p><strong>Add a signature </strong>area at the bottom of your e-mailed page.</p>\r\n\r\n<p><strong>^^signature^^</strong></p>\r\n<hr>\r\n\r\n',0,0,0,'','b'),(56,540,0,'<div class=\"row\">\r\n<div class=\"col-sm-12 text-center\">[[\r\n<div class=\"embed-video-container embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/^^youtube-id^^?hl=en_US&amp;rel=0\"></iframe></div>\r\n||\r\n\r\n<div class=\"embed-video-container embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"https://player.vimeo.com/video/^^vimeo-id^^?badge=0&amp;title=0&amp;byline=0&amp;title=0\"></iframe></div>\r\n]]\r\n\r\n<h5>^^title^^</h5>\r\n\r\n<p>Sort Order: ^^sort^^</p>\r\n</div>\r\n</div>\r\n',0,0,0,'','b'),(57,85,0,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-12 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb24\" style=\"font-size:125%\">^^details^^</div>\r\n\r\n<p><a href=\"{path}my-conversation?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n</div>\r\n</div>\r\n',0,0,0,'','b'),(58,648,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) on ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16\" style=\"font-size:125%\">^^details^^</div>\r\n</div>\r\n\r\n<div class=\"col-sm-3\">[[<i class=\"mb16\" style=\"display:block\">Latest Reply</i>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n<a href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||]]</div>\r\n</div>\r\n',0,0,0,'','b'),(59,1029,0,'<div class=\"row\">\r\n<div class=\"col-sm-12\">\r\n<h3>^^subject^^</h3>\r\n</div>\r\n\r\n<div class=\"col-sm-9 mb16\">Created for\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^first_name^^ ^^last_name^^</h6>\r\n(^^e-mail^^) &nbsp; ^^submitted_date_and_time^^%%relative%%\r\n\r\n<div class=\"mt16 mb32\" style=\"font-size: 125%\">^^details^^</div>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Add your Team</h6>\r\n\r\n<p>Grant access to anyone on your team to participate in this support ticket by including them as a <a href=\"#software_watcher\">watcher</a>.</p>\r\n[[\r\n\r\n<h6 class=\"heading-title\">File Attachments</h6>\r\n\r\n<p>^^comment_attachments^^</p>\r\n||]]</div>\r\n\r\n<div class=\"col-sm-3 mb24\">\r\n<h6 class=\"heading-title\">Service Project #^^reference_code^^</h6>\r\n\r\n[[<p class=\"mt8 mb16\"><i>Latest Reply</i></p>\r\n\r\n<h6 class=\"name bold-h6\" style=\"display: inline\">^^newest_comment_name^^</h6>\r\n<br />\r\n^^newest_comment_date_and_time^^%%relative%%:<br />\r\n    <a style=\"display:block\" href=\"#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a><br />||]]\r\n\r\n<h3 style=\"display:inline\">^^credits^^</h3> <strong>Credits Remaining</strong>\r\n\r\n<p class=\"mt24\"><a class=\"btn mb0\" href=\"{path}{software_directory}/do.php?action=prefill_product_form&amp;field_name=id&amp;field_value=^^reference_code^^&amp;url={path}order-services\">Add Credits</a></p>\r\n    \r\n<p>You can add additional credits to this project at any time when you are ready for our services team to continue working on your project.</p>\r\n</div>\r\n</div>\r\n',0,0,0,'','b'),(60,1032,0,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Project Summary</h6>\r\n\r\n<p>Project Number: <strong>^^reference_code^^</strong><br />\r\nClient Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nClient Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><strong>^^credits^^ Credits Remaining</strong></p>\r\n\r\n<p><a href=\"{path}my-services-project?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,0,'','b'),(61,396,0,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Support Ticket Summary</h6>\r\n\r\n<p>Support Ticket #:<strong>^^reference_code^^</strong><br />\r\nCustomer Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nCustomer Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><a href=\"{path}my-support-ticket?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,0,'','b'),(62,399,0,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Support Ticket Summary</h6>\r\n\r\n<p>Support Ticket #:<strong>^^reference_code^^</strong><br />\r\nCustomer Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nCustomer Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><a href=\"{path}my-support-ticket?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n',0,0,0,'','b'),(63,1024,0,'<h3>^^subject^^</h3>\r\n\r\n<p>^^details^^</p>\r\n\r\n<hr />\r\n<h6 class=\"heading-title\">Project Summary</h6>\r\n\r\n<p>Project Number: <strong>^^reference_code^^</strong><br />\r\nClient Name: <strong>^^first_name^^ ^^last_name^^</strong><br />\r\nClient Email: <strong>^^e-mail^^</strong></p>\r\n\r\n<p><strong>^^credits^^ Credits Remaining</strong></p>\r\n\r\n<p><a href=\"{path}my-services-project?r=^^reference_code^^\" style=\"padding: 8px 26px;border: 1px solid;\">View or Reply</a></p>\r\n\r\n<p>&nbsp;</p>\r\n',0,0,0,'','b'),(64,412,0,'<h4>^^subject^^</h4>\r\n\r\n<p>^^detail^^</p>\r\n\r\n<p><a class=\"btn\" href=\"{path}forum-thread-view?r=^^reference_code^^\">View or Reply</a></p>\r\n',0,0,0,'','b'),(65,489,0,'',0,0,0,'','b'),(66,1047,0,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">\r\n		[[<p><img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" /></p>||<p><img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" /></p>]]\r\n	</div>\r\n	<div class=\"col-sm-6\">\r\n		<h3 style=\"margin-bottom:0\">^^first_name^^ ^^last_name^^</h3>\r\n		<h4>^^title^^</h4>\r\n		<p>^^phone^^</p>\r\n		<p>^^bio^^</p>\r\n		<p style=\"display:none;\">^^alphabetical^^</p>\r\n		<p style=\"display:none;\">^^sort^^</p>\r\n		<div>   \r\n			<ul class=\"list-inline social-list\">\r\n				<li>[[<a href=\"^^twitter^^\" target=\"_blank\"><i class=\"ti-twitter-alt\">&nbsp;</i></a>||<i class=\"ti-twitter-alt\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n                <li>[[<a href=\"^^linkedin^^\" target=\"_blank\"><i class=\"ti-linkedin\">&nbsp;</i></a>||<i class=\"ti-linkedin\" style=\"opacity:0.3\">&nbsp;</i>]]</li>\r\n			</ul>\r\n			<p>&nbsp;</p>\r\n		</div>    \r\n	</div>\r\n</div>',0,0,0,'','b');
/*!40000 ALTER TABLE `form_item_view_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_list_view_browse_fields`
--

DROP TABLE IF EXISTS `form_list_view_browse_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_list_view_browse_fields` (
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_columns` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `shortcut` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort_order` enum('ascending','descending') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ascending',
  `date_format` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  KEY `page_id` (`page_id`),
  KEY `form_field_id` (`form_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_list_view_browse_fields`
--

LOCK TABLES `form_list_view_browse_fields` WRITE;
/*!40000 ALTER TABLE `form_list_view_browse_fields` DISABLE KEYS */;
INSERT INTO `form_list_view_browse_fields` VALUES (1064,255,26,0,'ascending',''),(219,255,26,0,'ascending',''),(219,42,3,0,'ascending',''),(1064,42,3,0,'ascending',''),(227,257,5,0,'ascending',''),(568,118,3,0,'ascending',''),(568,111,3,0,'ascending',''),(289,133,3,0,'ascending',''),(574,255,26,0,'ascending',''),(574,42,3,0,'ascending',''),(574,41,4,1,'ascending',''),(574,40,4,1,'ascending',''),(289,130,3,0,'ascending',''),(1046,255,26,0,'ascending',''),(1013,257,5,0,'ascending',''),(1015,257,5,0,'ascending',''),(1016,257,5,0,'ascending',''),(1046,42,3,0,'ascending',''),(1047,255,26,0,'ascending',''),(1047,42,3,0,'ascending',''),(1047,41,4,1,'ascending',''),(1047,40,4,1,'ascending',''),(1074,133,3,0,'ascending',''),(1074,130,3,0,'ascending','');
/*!40000 ALTER TABLE `form_list_view_browse_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_list_view_filters`
--

DROP TABLE IF EXISTS `form_list_view_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_list_view_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `standard_field` enum('','reference_code','complete','tracking_code','affiliate_code','referring_url','submitter','submitted_date_and_time','last_modifier','last_modified_date_and_time','number_of_views','number_of_comments','newest_comment_name','newest_comment','newest_comment_date_and_time','newest_comment_id','newest_activity_date_and_time','comment_attachments') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `operator` enum('contains','does not contain','is equal to','is not equal to','is less than','is less than or equal to','is greater than','is greater than or equal to') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contains',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value` enum('','current date','current date and time','current time','days ago','viewer','viewers email address') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=488 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_list_view_filters`
--

LOCK TABLES `form_list_view_filters` WRITE;
/*!40000 ALTER TABLE `form_list_view_filters` DISABLE KEYS */;
INSERT INTO `form_list_view_filters` VALUES (484,289,'last_modified_date_and_time',0,'is greater than or equal to','','days ago',90),(97,354,'submitted_date_and_time',0,'is greater than or equal to','','days ago',0),(98,354,'submitted_date_and_time',0,'is less than or equal to','','current date and time',0),(99,354,'',136,'does not contain','Yes','',0),(125,353,'',31,'is less than or equal to','','current date',0),(148,384,'',161,'contains','sports','',0),(485,289,'complete',0,'is equal to','Complete','',0),(468,219,'complete',0,'is equal to','Complete','',0),(471,1064,'complete',0,'is equal to','Complete','',0);
/*!40000 ALTER TABLE `form_list_view_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_list_view_pages`
--

DROP TABLE IF EXISTS `form_list_view_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_list_view_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_form_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `layout` longtext COLLATE utf8_unicode_ci NOT NULL,
  `order_by_1_standard_field` enum('','random','reference_code','complete','tracking_code','affiliate_code','referring_url','submitter','submitted_date_and_time','last_modifier','last_modified_date_and_time','number_of_views','number_of_comments','newest_comment_name','newest_comment','newest_comment_date_and_time','newest_comment_id','newest_activity_date_and_time','comment_attachments') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_1_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_by_2_standard_field` enum('','reference_code','complete','tracking_code','affiliate_code','referring_url','submitter','submitted_date_and_time','last_modifier','last_modified_date_and_time','number_of_views','number_of_comments','newest_comment_name','newest_comment','newest_comment_date_and_time','newest_comment_id','newest_activity_date_and_time','comment_attachments') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_2_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_by_3_standard_field` enum('','reference_code','complete','tracking_code','affiliate_code','referring_url','submitter','submitted_date_and_time','last_modifier','last_modified_date_and_time','number_of_views','number_of_comments','newest_comment_name','newest_comment','newest_comment_date_and_time','newest_comment_id','newest_activity_date_and_time','comment_attachments') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_3_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `maximum_number_of_results` int(10) unsigned NOT NULL DEFAULT '0',
  `maximum_number_of_results_per_page` int(10) unsigned NOT NULL DEFAULT '0',
  `search` tinyint(4) NOT NULL DEFAULT '0',
  `form_item_view_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `search_advanced` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `search_advanced_show_by_default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `search_advanced_layout` longtext COLLATE utf8_unicode_ci NOT NULL,
  `browse` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `show_results_by_default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `browse_show_by_default_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_by_1_type` enum('ascending_alphabetical','ascending_numerical','descending_alphabetical','descending_numercial') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ascending_alphabetical',
  `order_by_2_type` enum('ascending_alphabetical','ascending_numerical','descending_alphabetical','descending_numercial') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ascending_alphabetical',
  `order_by_3_type` enum('ascending_alphabetical','ascending_numerical','descending_alphabetical','descending_numercial') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ascending_alphabetical',
  `header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `search_label` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `viewer_filter` tinyint(4) NOT NULL DEFAULT '0',
  `viewer_filter_submitter` tinyint(4) NOT NULL DEFAULT '0',
  `viewer_filter_watcher` tinyint(4) NOT NULL DEFAULT '0',
  `viewer_filter_editor` tinyint(4) NOT NULL DEFAULT '0',
  `collection` enum('a','b') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'a',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `page_id_collection` (`page_id`,`collection`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_list_view_pages`
--

LOCK TABLES `form_list_view_pages` WRITE;
/*!40000 ALTER TABLE `form_list_view_pages` DISABLE KEYS */;
INSERT INTO `form_list_view_pages` VALUES (4,219,296,'<div class=\"col-md-4 col-sm-6 p0\">\r\n	<div class=\"image-tile inner-title hover-reveal text-center mb0\">\r\n        <a href=\"^^form_item_view^^\">[[<img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />||<img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />]]</a>\r\n		<div class=\"title\">\r\n		<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n		<span>^^title^^</span></div>\r\n	</div>\r\n</div>\r\n','',321,'',0,'',0,0,25,1,316,0,0,'',1,1,0,'ascending_numerical','ascending_alphabetical','ascending_alphabetical','','','Search Directory',0,1,1,1,'a'),(8,299,183,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','newest_activity_date_and_time',0,'',0,'',0,0,25,1,648,1,0,'<div class=\"row\">\r\n	<div class=\"col-sm-2\">\r\n    	Submitter:\r\n	</div>\r\n	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n    </div>\r\n	<div class=\"col-sm-2\">\r\n    	E-mail:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'e-mail\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Company:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'company\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Phone:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'business_phone\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search',0,1,1,1,'a'),(12,227,313,'<div class=\"post-snippet mb64\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"post-title\"><span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n		<h4 class=\"inline-block\"><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^</span></li>\r\n	</ul>\r\n	<hr />\r\n    ^^summary^^\r\n    <a class=\"btn btn-secondary\" href=\"^^form_item_view^^\">Read More</a>\r\n</div>','',276,'',0,'',0,0,0,1,365,0,0,'',1,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search Blog',0,1,1,1,'a'),(19,289,290,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">^^category^^<br />\r\n	[[<a href=\"^^form_item_view^^\"><img src=\"{path}^^photo^^\" alt=\"^^item^^\"/></a>||\r\n		<a href=\"^^form_item_view^^\">\r\n			<div style=\"width: auto; border: 1px solid; padding: 2em;\">\r\n    			<p><strong>No Image :(</strong></p>\r\n				<p>If you are the seller, we recommend you add an image to improve this listing.</p>\r\n			</div>\r\n    	</a>\r\n	]]</div>\r\n\r\n	<div class=\"col-sm-6\"><span>&nbsp;</span>\r\n		<h4>^^item^^</h4>\r\n		<h5>[[<span style=\"color: red;\">^^status^^</span> <span style=\"text-decoration: line-through;\"><span style=\"font-decoration: strikethrough;\">^^price^^</span></span>||^^price^^]]</h5>\r\n        <p class=\"mb8\"><a class=\"btn btn-primary btn-xs\" href=\"^^form_item_view^^\">View Details</a></p>\r\n        <span style=\"font-size:75%\">Last Modified by ^^last_modifier^^ on ^^last_modified_date_and_time^^ [[<br />\r\n		Last Comment by ^^newest_comment_name^^ on ^^newest_comment_date_and_time^^<br />\r\n		||]]</span>\r\n	</div>\r\n</div>\r\n\r\n<hr style=\"margin: 3em 0 2.5em\" />','',133,'',0,'',0,0,25,1,354,0,0,'',1,1,0,'ascending_numerical','ascending_numerical','ascending_alphabetical','','','Search Ads',0,1,1,1,'a'),(64,492,395,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'newest_activity_date_and_time',0,'',0,0,25,1,402,1,0,'<div class=\"row\">\r\n	<div class=\"col-sm-2\">\r\n    	Submitter:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Email:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'e-mail\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,1,0,'descending_numercial','descending_alphabetical','ascending_alphabetical','','','Search Tickets',0,1,1,1,'a'),(40,401,395,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'newest_activity_date_and_time',0,'',0,0,25,1,402,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Ticket #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_numercial','descending_alphabetical','ascending_alphabetical','','','Search Tickets',1,1,1,1,'a'),(42,407,408,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No replies yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','newest_activity_date_and_time',0,'',0,'',0,0,25,1,409,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','Search Forum',0,1,1,1,'a'),(99,1046,296,'<div class=\"col-md-4 col-sm-6 p0\">\r\n    \r\n  	<div class=\"image-tile inner-title text-center mb0\">\r\n        <a href=\"^^form_item_view^^\">\r\n        	[[\r\n            <img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\"/>\r\n            ||\r\n            <img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\"/>\r\n            ]]\r\n        </a>\r\n        [[\r\n        <span style=\"display:none\">^^complete^^</span>\r\n		<div class=\"title\">\r\n			<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n			<span class=\"primary-background-color\" style=\"padding: 0 8px; margin: 0 8px\">^^title^^</span>\r\n      	</div>\r\n        ||\r\n       	<div class=\"title\">\r\n			<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n			<span>^^title^^</span>\r\n      	</div>\r\n        ]]\r\n   </div>\r\n</div>','',321,'',0,'',0,0,25,1,1047,0,0,'',1,1,0,'ascending_numerical','ascending_alphabetical','ascending_alphabetical','','','Search',0,1,1,1,'a'),(45,435,313,'<ul>\r\n	<li><a href=\"^^form_item_view^^\">^^title^^</a> <span class=\"date\">^^publish-date^^%%F%% <span class=\"number\">^^publish-date^^%%j%%, ^^publish-date^^%%Y%%</span> </span></li>\r\n</ul>\r\n','',276,'',0,'',0,3,3,0,365,0,0,'',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"widget\">\r\n   <h6 class=\"title\">Recent Posts</h6>\r\n   <hr>\r\n   <ul class=\"link-list recent-posts\">','  </ul>\r\n</div>','Search',0,1,1,1,'a'),(47,437,313,'<h4><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n\r\n<p><span class=\"text-fine-print\">By ^^submitter^^ / Last Modified: ^^last_modified_date_and_time^^&nbsp;</span></p>\r\n','last_modified_date_and_time',0,'',0,'',0,1,1,0,438,0,0,'',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search',0,1,1,1,'a'),(49,439,313,'<h3>^^title^^</h3>\r\n\r\n<p>^^to^^^^details^^</p>\r\n\r\n<p>^^signature^^</p>\r\n','last_modified_date_and_time',0,'',0,'',0,1,1,0,0,0,0,'',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search',0,1,1,1,'a'),(70,508,472,'<h5>^^email^^ was just added to your mailing list.</h5>','submitted_date_and_time',0,'',0,'',0,1,1,0,0,0,0,'',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search',0,1,1,1,'a'),(71,539,538,'<div class=\"col-sm-6 text-center\">\r\n    [[\r\n	<div class=\"embed-video-container embed-responsive embed-responsive-16by9\">\r\n        <iframe allowfullscreen=\"\" frameborder=\"0\" class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/^^youtube-id^^?hl=en_US&amp;rel=0\"></iframe>\r\n    </div>\r\n	||\r\n	<div class=\"embed-video-container embed-responsive embed-responsive-16by9\">\r\n        <iframe allowfullscreen=\"\" frameborder=\"0\" class=\"embed-responsive-item\" src=\"https://player.vimeo.com/video/^^vimeo-id^^?badge=0&amp;title=0&amp;byline=0&amp;title=0\"></iframe>\r\n    </div>\r\n	]]\r\n<p><span style=\"font-size: 125%\">^^title^^</span><a class=\"view-in-edit-mode-only btn btn-primary\" href=\"^^form_item_view^^\" style=\"display:none\">Edit Video</a></p>\r\n</div>\r\n','',254,'',0,'',0,0,12,0,540,0,0,'',0,1,0,'ascending_numerical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','Search Videos',0,1,1,1,'a'),(77,568,288,'<hr />\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td>\r\n			<h4><a href=\"^^form_item_view^^\">^^organization^^</a></h4>\r\n			</td>\r\n			<td style=\"text-align: right;\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td style=\"width: 15%;\"><strong>Services:</strong></td>\r\n			<td>^^services^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Description:</strong></td>\r\n			<td>^^description^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Contact:</strong></td>\r\n			<td>\r\n			<p>^^salutation^^ ^^first_name^^ ^^last_name^^<br />\r\n			^^phone^^<br />\r\n			^^email^^</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',111,'',0,'',0,0,25,1,330,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">First Name:</td>\r\n			<td class=\"table-cell-mobile-wrap\">{{name: &#39;first_name&#39;, size: 20}}</td>\r\n			<td class=\"table-cell-mobile-fill\">Last Name:</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name: &#39;last_name&#39;, size: 30}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p style=\"text-align: right;\"><br />\r\n<span>{{name: &#39;submit_button&#39;}}&nbsp; {{name: &#39;clear_button&#39;}}</span></p>\r\n',0,1,118,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search Directory',0,1,1,1,'a'),(83,597,408,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','number_of_views',0,'',0,'',0,0,25,1,409,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','Search Forum',0,1,1,1,'a'),(101,1062,313,'<div class=\"col-sm-4\">\r\n    <a href=\"^^form_item_view^^\">\r\n        ^^media^^\r\n    </a>\r\n    <a href=\"^^form_item_view^^\">\r\n        <h4 class=\"mb8\">^^title^^</h4>\r\n    </a>\r\n    <ul class=\"list-inline mb16\">\r\n        <li>^^publish-date^^%%F%% ^^publish-date^^%%j%%, ^^publish-date^^%%Y%%</li>\r\n        <li>\r\n            <a href=\"#\">^^submitter^^</a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}blog?227_query=^^category^^\">\r\n                <span class=\"label\">^^category^^</span>\r\n            </a>\r\n        </li>\r\n    </ul>\r\n    <p class=\"mb0\">\r\n        ^^summary^^\r\n    </p>\r\n</div>','',276,'',0,'',0,3,3,0,365,0,0,'',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','Search',0,1,1,1,'a'),(103,1064,296,'<div class=\"col-md-3 col-sm-4 mb24\">\r\n    <a href=\"^^form_item_view^^\">[[<img src=\"{path}^^photo^^\" class=\"mb24\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />||<img class=\"mb24\" src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />]]</a>\r\n    <h6 class=\"uppercase mb0 color-primary\">^^first_name^^ ^^last_name^^</h6>\r\n    <span>^^title^^</span>\r\n</div>\r\n','',321,'',0,'',0,0,25,0,316,0,0,'',0,1,0,'ascending_numerical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','Search Directory',0,1,1,1,'a'),(89,638,408,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','newest_activity_date_and_time',0,'',0,'',0,0,25,1,409,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','Search Forum',1,1,1,0,'a'),(84,598,408,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','number_of_comments',0,'',0,'',0,0,25,1,409,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','Search Forum',0,1,1,1,'a'),(90,647,183,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','newest_activity_date_and_time',0,'',0,'',0,0,25,1,648,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Conversation #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search',1,1,1,1,'a'),(91,1013,313,'<div class=\"post-snippet mb64\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"post-title\"><span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n		<h4 class=\"inline-block\"><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^</span></li>\r\n	</ul>\r\n	<hr />\r\n    ^^summary^^\r\n    <a class=\"btn btn-secondary\" href=\"^^form_item_view^^\">Read More</a>\r\n</div>','',276,'',0,'',0,0,0,1,1014,0,0,'',1,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','Search Blog',0,1,1,1,'a'),(92,1015,313,'<div class=\"col-sm-6 post-snippet masonry-item\">\r\n    <a href=\"^^form_item_view^^\">\r\n        ^^media^^\r\n    </a>\r\n    <div class=\"inner\">\r\n        <a href=\"^^form_item_view^^\">\r\n            <h5 class=\"mb0\">^^title^^</h5>\r\n            <span class=\"inline-block mb16\">^^publish-date^^%%F%% ^^publish-date^^%%d%%, ^^publish-date^^%%Y%%</span>\r\n        </a>\r\n        <hr>\r\n        ^^summary^^\r\n        <a class=\"btn btn-sm\" href=\"^^form_item_view^^\">Read More</a>\r\n        <ul class=\"tags pull-right\">\r\n            <li>\r\n                <a class=\"btn btn-sm btn-icon\" href=\"#\">\r\n                    <i class=\"ti-twitter-alt\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a class=\"btn btn-sm btn-icon\" href=\"#\">\r\n                    <i class=\"ti-facebook\"></i>\r\n                </a>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n</div>','',276,'',0,'',0,0,0,1,365,0,0,'',1,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row masonry-loader\">\r\n    <div class=\"col-sm-12 text-center\">\r\n        <div class=\"spinner\"></div>\r\n    </div>\r\n</div>\r\n<div class=\"row masonry masonryFlyIn mb40\">','</div>','Search Blog',0,1,1,1,'a'),(93,1016,313,'<div class=\"col-sm-4 post-snippet masonry-item\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"inner\">\r\n		<h5 class=\"mb0\"><a href=\"^^form_item_view^^\">^^title^^</a></h5>\r\n		<a href=\"^^form_item_view^^\"><span class=\"inline-block mb16\">^^publish-date^^%%F%% ^^publish-date^^%%d%%, ^^publish-date^^%%Y%%</span> </a>\r\n		<hr />\r\n        ^^summary^^\r\n        <a class=\"btn btn-sm\" href=\"^^form_item_view^^\">Read More</a>\r\n	</div>\r\n</div>','',276,'',0,'',0,0,0,1,365,0,0,'',1,1,0,'descending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row masonry-loader\">\r\n    <div class=\"col-sm-12 text-center\">\r\n        <div class=\"spinner\"></div>\r\n    </div>\r\n</div>\r\n<div class=\"row masonry masonryFlyIn mb40\">','</div>','Search Blog',0,1,1,1,'a'),(94,1025,1023,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb8\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span>&nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:\r\n\r\n<p class=\"mt16\"><a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\"><i>^^newest_comment^^</i></a></p>\r\n<strong>^^credits^^</strong> Credits Remaining &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><strong>^^credits^^</strong> Credits Remaining &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n]]</div>\r\n','',0,'newest_activity_date_and_time',0,'',0,0,25,1,1029,1,0,'<div class=\"row\">\r\n	<div class=\"\">\r\n        <div class=\"col-sm-2\">\r\n    		Submitter:\r\n		</div>\r\n    </div>\r\n	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Credits:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'credits\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,1,0,'descending_numercial','descending_alphabetical','ascending_alphabetical','','','Search Tickets',0,1,1,1,'a'),(95,1028,1023,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a> &nbsp; / &nbsp; <strong>^^credits^^</strong> Credits Remaining</div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a> &nbsp; / &nbsp; <strong>^^credits^^</strong> Credits Remaining</p>\r\n</div>\r\n]]</div>\r\n','',0,'newest_activity_date_and_time',0,'',0,0,25,1,1029,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Project #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,1,0,'descending_numercial','descending_alphabetical','ascending_alphabetical','','','Search Projects',1,1,1,1,'a'),(106,1074,290,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">^^category^^<br />\r\n	[[<a href=\"^^form_item_view^^\"><img src=\"{path}^^photo^^\" alt=\"^^item^^\"/></a>||\r\n		<a href=\"^^form_item_view^^\">\r\n			<div style=\"width: auto; border: 1px solid; padding: 2em;\">\r\n    			<p><strong>No Image :(</strong></p>\r\n				<p>If you are the seller, we recommend you add an image to improve this listing.</p>\r\n			</div>\r\n    	</a>\r\n	]]</div>\r\n\r\n	<div class=\"col-sm-6\"><span>&nbsp;</span>\r\n		<h4>^^item^^</h4>\r\n		<h5>[[<span style=\"color: red;\">^^status^^</span> <span style=\"text-decoration: line-through;\"><span style=\"font-decoration: strikethrough;\">^^price^^</span></span>||^^price^^]]</h5>\r\n        <p class=\"mb8\"><a class=\"btn btn-primary btn-xs\" href=\"^^form_item_view^^\">View / Edit</a></p>\r\n        <span style=\"font-size:75%\">Last Modified by ^^last_modifier^^ on ^^last_modified_date_and_time^^ [[<br />\r\n		Last Comment by ^^newest_comment_name^^ on ^^newest_comment_date_and_time^^<br />\r\n		||]]</span>\r\n	</div>\r\n</div>\r\n\r\n<hr style=\"margin: 3em 0 2.5em\" />','',133,'',0,'',0,0,25,0,354,0,0,'',0,1,0,'ascending_numerical','ascending_numerical','ascending_alphabetical','','','Search Ads',1,1,0,0,'a'),(107,219,0,'<div class=\"col-md-4 col-sm-6 p0\">\r\n	<div class=\"image-tile inner-title hover-reveal text-center mb0\">\r\n        <a href=\"^^form_item_view^^\">[[<img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />||<img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />]]</a>\r\n		<div class=\"title\">\r\n		<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n		<span>^^title^^</span></div>\r\n	</div>\r\n</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(108,299,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<div class=\"row\">\r\n	<div class=\"col-sm-2\">\r\n    	Submitter:\r\n	</div>\r\n	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n    </div>\r\n	<div class=\"col-sm-2\">\r\n    	E-mail:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'e-mail\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Company:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'company\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Phone:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'business_phone\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(109,227,0,'<div class=\"post-snippet mb64\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"post-title\"><span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n		<h4 class=\"inline-block\"><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^</span></li>\r\n	</ul>\r\n	<hr />\r\n    ^^summary^^\r\n    <a class=\"btn btn-secondary\" href=\"^^form_item_view^^\">Read More</a>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(110,289,0,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">^^category^^<br />\r\n	[[<a href=\"^^form_item_view^^\"><img src=\"{path}^^photo^^\" alt=\"^^item^^\"/></a>||\r\n		<a href=\"^^form_item_view^^\">\r\n			<div style=\"width: auto; border: 1px solid; padding: 2em;\">\r\n    			<p><strong>No Image :(</strong></p>\r\n				<p>If you are the seller, we recommend you add an image to improve this listing.</p>\r\n			</div>\r\n    	</a>\r\n	]]</div>\r\n\r\n	<div class=\"col-sm-6\"><span>&nbsp;</span>\r\n		<h4>^^item^^</h4>\r\n		<h5>[[<span style=\"color: red;\">^^status^^</span> <span style=\"text-decoration: line-through;\"><span style=\"font-decoration: strikethrough;\">^^price^^</span></span>||^^price^^]]</h5>\r\n        <p class=\"mb8\"><a class=\"btn btn-primary btn-xs\" href=\"^^form_item_view^^\">View Details</a></p>\r\n        <span style=\"font-size:75%\">Last Modified by ^^last_modifier^^ on ^^last_modified_date_and_time^^ [[<br />\r\n		Last Comment by ^^newest_comment_name^^ on ^^newest_comment_date_and_time^^<br />\r\n		||]]</span>\r\n	</div>\r\n</div>\r\n\r\n<hr style=\"margin: 3em 0 2.5em\" />','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(111,492,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<div class=\"row\">\r\n	<div class=\"col-sm-2\">\r\n    	Submitter:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Email:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'e-mail\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(112,401,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Ticket #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(113,407,0,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No replies yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','',0,0,0,0,'b'),(114,1046,0,'<div class=\"col-md-4 col-sm-6 p0\">\r\n    \r\n  	<div class=\"image-tile inner-title text-center mb0\">\r\n        <a href=\"^^form_item_view^^\">\r\n        	[[\r\n            <img src=\"{path}^^photo^^\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\"/>\r\n            ||\r\n            <img src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\"/>\r\n            ]]\r\n        </a>\r\n        [[\r\n        <span style=\"display:none\">^^complete^^</span>\r\n		<div class=\"title\">\r\n			<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n			<span class=\"primary-background-color\" style=\"padding: 0 8px; margin: 0 8px\">^^title^^</span>\r\n      	</div>\r\n        ||\r\n       	<div class=\"title\">\r\n			<h5 class=\"uppercase mb0\">^^first_name^^ ^^last_name^^</h5>\r\n			<span>^^title^^</span>\r\n      	</div>\r\n        ]]\r\n   </div>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(115,435,0,'<ul>\r\n	<li><a href=\"^^form_item_view^^\">^^title^^</a> <span class=\"date\">^^publish-date^^%%F%% <span class=\"number\">^^publish-date^^%%j%%, ^^publish-date^^%%Y%%</span> </span></li>\r\n</ul>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"widget\">\r\n   <h6 class=\"title\">Recent Posts</h6>\r\n   <hr>\r\n   <ul class=\"link-list recent-posts\">','  </ul>\r\n</div>','',0,0,0,0,'b'),(116,437,0,'<h4><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n\r\n<p><span class=\"text-fine-print\">By ^^submitter^^ / Last Modified: ^^last_modified_date_and_time^^&nbsp;</span></p>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(117,439,0,'<h3>^^title^^</h3>\r\n\r\n<p>^^to^^^^details^^</p>\r\n\r\n<p>^^signature^^</p>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(118,508,0,'<h5>^^email^^ was just added to your mailing list.</h5>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(119,539,0,'<div class=\"col-sm-6 text-center\">\r\n    [[\r\n		<div class=\"embed-video-container embed-responsive embed-responsive-16by9\">\r\n            <iframe allowfullscreen=\"\" frameborder=\"0\" class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/^^youtube-id^^?hl=en_US&amp;rel=0\"></iframe>\r\n    	</div>\r\n	||\r\n	<div class=\"embed-video-container embed-responsive embed-responsive-16by9\">\r\n        <iframe allowfullscreen=\"\" frameborder=\"0\" class=\"embed-responsive-item\" src=\"https://player.vimeo.com/video/^^vimeo-id^^?badge=0&amp;title=0&amp;byline=0&amp;title=0\"></iframe>\r\n    </div>\r\n	]]\r\n<p><span style=\"font-size: 125%\">^^title^^</span><a class=\"view-in-edit-mode-only btn btn-primary\" href=\"^^form_item_view^^\" style=\"display:none\">Edit Video</a></p>\r\n</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','',0,0,0,0,'b'),(120,568,0,'<hr />\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td>\r\n			<h4><a href=\"^^form_item_view^^\">^^organization^^</a></h4>\r\n			</td>\r\n			<td style=\"text-align: right;\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr valign=\"top\">\r\n			<td style=\"width: 15%;\"><strong>Services:</strong></td>\r\n			<td>^^services^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Description:</strong></td>\r\n			<td>^^description^^</td>\r\n		</tr>\r\n		<tr valign=\"top\">\r\n			<td><strong>Contact:</strong></td>\r\n			<td>\r\n			<p>^^salutation^^ ^^first_name^^ ^^last_name^^<br />\r\n			^^phone^^<br />\r\n			^^email^^</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">First Name:</td>\r\n			<td class=\"table-cell-mobile-wrap\">{{name: &#39;first_name&#39;, size: 20}}</td>\r\n			<td class=\"table-cell-mobile-fill\">Last Name:</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name: &#39;last_name&#39;, size: 30}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p style=\"text-align: right;\"><br />\r\n<span>{{name: &#39;submit_button&#39;}}&nbsp; {{name: &#39;clear_button&#39;}}</span></p>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(121,597,0,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','',0,0,0,0,'b'),(122,1062,0,'<div class=\"col-sm-4\">\r\n    <a href=\"^^form_item_view^^\">\r\n        ^^media^^\r\n    </a>\r\n    <a href=\"^^form_item_view^^\">\r\n        <h4 class=\"mb8\">^^title^^</h4>\r\n    </a>\r\n    <ul class=\"list-inline mb16\">\r\n        <li>^^publish-date^^%%F%% ^^publish-date^^%%j%%, ^^publish-date^^%%Y%%</li>\r\n        <li>\r\n            <a href=\"#\">^^submitter^^</a>\r\n        </li>\r\n        <li>\r\n            <a href=\"{path}blog?227_query=^^category^^\">\r\n                <span class=\"label\">^^category^^</span>\r\n            </a>\r\n        </li>\r\n    </ul>\r\n    <p class=\"mb0\">\r\n        ^^summary^^\r\n    </p>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','',0,0,0,0,'b'),(123,1064,0,'<div class=\"col-md-3 col-sm-4 mb24\">\r\n    <a href=\"^^form_item_view^^\">[[<img src=\"{path}^^photo^^\" class=\"mb24\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />||<img class=\"mb24\" src=\"{path}staff-photo-not-available.png\" alt=\"^^first_name^^ ^^last_name^^\" title=\"^^first_name^^ ^^last_name^^\" />]]</a>\r\n    <h6 class=\"uppercase mb0 color-primary\">^^first_name^^ ^^last_name^^</h6>\r\n    <span>^^title^^</span>\r\n</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row\">','</div>','',0,0,0,0,'b'),(124,638,0,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','',0,0,0,0,'b'),(125,598,0,'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"2\" style=\"vertical-align: top;\" width=\"75%\">\r\n			<p><a href=\"^^form_item_view^^\" style=\"font-size: 20px;\">^^subject^^</a><br />\r\n			^^submitted_date_and_time^^%%relative%% by ^^submitter^^</p>\r\n			</td>\r\n			<td style=\"vertical-align: top;\" width=\"10%\">&nbsp;</td>\r\n			<td style=\"width: 8%;\">^^number_of_comments^^</td>\r\n			<td style=\"width: 6%;\">^^number_of_views^^</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"width: 3%; vertical-align: top;\"><span class=\"glyphicons tiny comments color-secondary\">&nbsp;</span></td>\r\n			<td style=\"vertical-align: top;\">[[^^newest_comment_date_and_time^^%%relative%% ^^newest_comment_name^^ &nbsp; &nbsp; <a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\">^^newest_comment^^</a>||No comments yet. <a href=\"^^form_item_view^^#software_add_comment\">Be the first</a>.]]</td>\r\n			<td>&nbsp;</td>\r\n			<td>&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" class=\"table-mobile-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}} to {{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: left;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-hide\"><strong>Mobile Version </strong>(so it&#39;s not a duplicate)...</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Subject:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;subject&#39;,size: 20}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Topic:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;topic&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Submitter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;submitter&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td valign=\"middle\">Commenter:</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n			<td valign=\"middle\">{{name: &#39;newest_comment_name&#39;}}</td>\r\n			<td valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<table align=\"center\" border=\"0\" class=\"table-desktop-hide\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td>Newest Activity:<br />\r\n			<span class=\"text-fine-print\">Include only posts and replies between certain&nbsp;<strong>Dates.</strong><br />\r\n			(mm/dd/yyyy hh:mm AM/PM)</span></td>\r\n		</tr>\r\n		<tr>\r\n			<td>{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is greater than or equal to&#39;, help: false, size: 18}}<br />\r\n			{{name: &#39;newest_activity_date_and_time&#39;, operator: &#39;is less than or equal to&#39;, help: false, size: 18}}</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">&nbsp;</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: right;\" valign=\"middle\">{{name: &#39;submit_button&#39;}}&nbsp;&nbsp; {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"colorbar\">\r\n	<table style=\"width: 100%; padding: 1em;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n		<tbody>\r\n		<tr>\r\n			<td style=\"width: 81%;\">\r\n				<span style=\"padding-left: 1em;\"><strong>Subject</strong></span>\r\n			</td>\r\n			<td style=\"width: 11%;\">\r\n				<strong>Replies</strong>\r\n			</td>\r\n			<td> &nbsp; </td>\r\n			<td style=\"width: 8%;\">\r\n				<strong>Views</strong>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n	</table>','</div>','',0,0,0,0,'b'),(126,647,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></p>\r\n</div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Conversation #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(127,1013,0,'<div class=\"post-snippet mb64\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"post-title\"><span class=\"label\">^^publish-date^^%%M%% ^^publish-date^^%%d%%</span>\r\n		<h4 class=\"inline-block\"><a href=\"^^form_item_view^^\">^^title^^</a></h4>\r\n	</div>\r\n	<ul class=\"post-meta\">\r\n		<li><i class=\"ti-user\"></i><span>Written by ^^submitter^^ </span></li>\r\n		<li><i class=\"ti-tag\"></i><span>Category <a href=\"{path}blog?227_query=^^category^^\" title=\"^^category^^\">^^category^^</a> </span></li>\r\n		<li><i class=\"ti-eye\"></i><span>Views ^^number_of_views^^ </span></li>\r\n		<li><i class=\"ti-comment-alt\"></i><span>Comments ^^number_of_comments^^</span></li>\r\n	</ul>\r\n	<hr />\r\n    ^^summary^^\r\n    <a class=\"btn btn-secondary\" href=\"^^form_item_view^^\">Read More</a>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(128,1015,0,'<div class=\"col-sm-6 post-snippet masonry-item\">\r\n    <a href=\"^^form_item_view^^\">\r\n        ^^media^^\r\n    </a>\r\n    <div class=\"inner\">\r\n        <a href=\"^^form_item_view^^\">\r\n            <h5 class=\"mb0\">^^title^^</h5>\r\n            <span class=\"inline-block mb16\">^^publish-date^^%%F%% ^^publish-date^^%%d%%, ^^publish-date^^%%Y%%</span>\r\n        </a>\r\n        <hr>\r\n        ^^summary^^\r\n        <a class=\"btn btn-sm\" href=\"^^form_item_view^^\">Read More</a>\r\n        <ul class=\"tags pull-right\">\r\n            <li>\r\n                <a class=\"btn btn-sm btn-icon\" href=\"#\">\r\n                    <i class=\"ti-twitter-alt\"></i>\r\n                </a>\r\n            </li>\r\n            <li>\r\n                <a class=\"btn btn-sm btn-icon\" href=\"#\">\r\n                    <i class=\"ti-facebook\"></i>\r\n                </a>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row masonry-loader\">\r\n    <div class=\"col-sm-12 text-center\">\r\n        <div class=\"spinner\"></div>\r\n    </div>\r\n</div>\r\n<div class=\"row masonry masonryFlyIn mb40\">','</div>','',0,0,0,0,'b'),(129,1016,0,'<div class=\"col-sm-4 post-snippet masonry-item\">\r\n    <a href=\"^^form_item_view^^\">^^media^^</a>\r\n	<div class=\"inner\">\r\n		<h5 class=\"mb0\"><a href=\"^^form_item_view^^\">^^title^^</a></h5>\r\n		<a href=\"^^form_item_view^^\"><span class=\"inline-block mb16\">^^publish-date^^%%F%% ^^publish-date^^%%d%%, ^^publish-date^^%%Y%%</span> </a>\r\n		<hr />\r\n        ^^summary^^\r\n        <a class=\"btn btn-sm\" href=\"^^form_item_view^^\">Read More</a>\r\n	</div>\r\n</div>','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','<div class=\"row masonry-loader\">\r\n    <div class=\"col-sm-12 text-center\">\r\n        <div class=\"spinner\"></div>\r\n    </div>\r\n</div>\r\n<div class=\"row masonry masonryFlyIn mb40\">','</div>','',0,0,0,0,'b'),(130,1025,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb8\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span>&nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:\r\n\r\n<p class=\"mt16\"><a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\"><i>^^newest_comment^^</i></a></p>\r\n<strong>^^credits^^</strong> Credits Remaining &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><strong>^^credits^^</strong> Credits Remaining &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a></div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<div class=\"row\">\r\n	<div class=\"\">\r\n        <div class=\"col-sm-2\">\r\n    		Submitter:\r\n		</div>\r\n    </div>\r\n	<div class=\"col-sm-4\">\r\n    	{{name: \'submitter\', dynamic: true}}\r\n	</div>\r\n	<div class=\"col-sm-2\">\r\n    	Credits:\r\n	</div>\r\n   	<div class=\"col-sm-4\">\r\n    	{{name: \'credits\', dynamic: true}}\r\n	</div>\r\n   	<div class=\"col-sm-2\">\r\n    	Subject:\r\n	</div>\r\n   	<div class=\"col-sm-10\">\r\n    	{{name: \'subject\', dynamic: true}}\r\n	</div>\r\n    <div class=\"col-sm-12\">\r\n    	{{name: \'submit_button\'}} {{name: \'clear_button\'}}\r\n	</div>\r\n</div>',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(131,1028,0,'<div class=\"row mb32\">\r\n<div class=\"col-sm-12\">\r\n<h4 class=\"mb12\"><a href=\"^^form_item_view^^\">^^subject^^</a></h4>\r\n</div>\r\n[[\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\"><span class=\"name bold-h6\">^^newest_comment_name^^</span> &nbsp; ^^newest_comment_date_and_time^^%%relative%% writes:<br />\r\n<a href=\"^^form_item_view^^#software_comment_^^newest_comment_id^^\" style=\"font-size:125%\">^^newest_comment^^</a><br />\r\n<strong>^^number_of_comments^^</strong> Replies &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a> &nbsp; / &nbsp; <strong>^^credits^^</strong> Credits Remaining</div>\r\n||\r\n\r\n<div class=\"col-sm-11 col-sm-offset-1\">\r\n<p>No Replies yet &nbsp; / &nbsp; <a href=\"^^form_item_view^^#software_add_comment\">Add a Reply</a> &nbsp; / &nbsp; <strong>^^credits^^</strong> Credits Remaining</p>\r\n</div>\r\n]]</div>\r\n','',0,'',0,'',0,0,0,0,0,0,0,'<table border=\"0\" style=\"width: 100%;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"table-cell-mobile-fill\">Project #:&nbsp; {{name: &#39;reference_code&#39;}}</td>\r\n			<td class=\"table-cell-mobile-fill table-cell-desktop-hide\" style=\"text-align: right;\">&nbsp;</td>\r\n			<td class=\"table-cell-mobile-wrap\" style=\"text-align: right;\">{{name:&#39;submit_button&#39;}} {{name: &#39;clear_button&#39;}}</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b'),(132,1074,0,'<div class=\"row\">\r\n	<div class=\"col-sm-6\">^^category^^<br />\r\n	[[<a href=\"^^form_item_view^^\"><img src=\"{path}^^photo^^\" alt=\"^^item^^\"/></a>||\r\n		<a href=\"^^form_item_view^^\">\r\n			<div style=\"width: auto; border: 1px solid; padding: 2em;\">\r\n    			<p><strong>No Image :(</strong></p>\r\n				<p>If you are the seller, we recommend you add an image to improve this listing.</p>\r\n			</div>\r\n    	</a>\r\n	]]</div>\r\n\r\n	<div class=\"col-sm-6\"><span>&nbsp;</span>\r\n		<h4>^^item^^</h4>\r\n		<h5>[[<span style=\"color: red;\">^^status^^</span> <span style=\"text-decoration: line-through;\"><span style=\"font-decoration: strikethrough;\">^^price^^</span></span>||^^price^^]]</h5>\r\n        <p class=\"mb8\"><a class=\"btn btn-primary btn-xs\" href=\"^^form_item_view^^\">View / Edit</a></p>\r\n        <span style=\"font-size:75%\">Last Modified by ^^last_modifier^^ on ^^last_modified_date_and_time^^ [[<br />\r\n		Last Comment by ^^newest_comment_name^^ on ^^newest_comment_date_and_time^^<br />\r\n		||]]</span>\r\n	</div>\r\n</div>\r\n\r\n<hr style=\"margin: 3em 0 2.5em\" />','',0,'',0,'',0,0,0,0,0,0,0,'',0,0,0,'ascending_alphabetical','ascending_alphabetical','ascending_alphabetical','','','',0,0,0,0,'b');
/*!40000 ALTER TABLE `form_list_view_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_view_directories_form_list_views_xref`
--

DROP TABLE IF EXISTS `form_view_directories_form_list_views_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_view_directories_form_list_views_xref` (
  `form_view_directory_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_list_view_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form_list_view_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `subject_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `form_view_directory_page_id` (`form_view_directory_page_id`),
  KEY `form_list_view_page_id` (`form_list_view_page_id`),
  KEY `subject_form_field_id` (`subject_form_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_view_directories_form_list_views_xref`
--

LOCK TABLES `form_view_directories_form_list_views_xref` WRITE;
/*!40000 ALTER TABLE `form_view_directories_form_list_views_xref` DISABLE KEYS */;
INSERT INTO `form_view_directories_form_list_views_xref` VALUES (410,407,'Foods',174),(284,289,'Classified Ads',131),(541,227,'Blog Postings',36),(541,492,'Support Tickets',166),(541,1025,'Services Projects',314),(541,299,'Conversations',291),(650,492,'Support Tickets',166),(650,219,'Staff Directory',42),(650,407,'Forum',174),(650,299,'Contact Forms',10),(650,289,'Classified Ads',131),(650,227,'Blog Postings',36),(650,539,'Videos',249),(541,289,'Classified Ads',131),(541,407,'Forum',174),(541,508,'Mailing List',208),(541,219,'Staff Directory',42),(541,539,'Videos',249);
/*!40000 ALTER TABLE `form_view_directories_form_list_views_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_view_directory_pages`
--

DROP TABLE IF EXISTS `form_view_directory_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_view_directory_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `summary` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `summary_days` int(10) unsigned NOT NULL DEFAULT '0',
  `summary_maximum_number_of_results` int(10) unsigned NOT NULL DEFAULT '0',
  `form_list_view_heading` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `subject_heading` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number_of_submitted_forms_heading` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_view_directory_pages`
--

LOCK TABLES `form_view_directory_pages` WRITE;
/*!40000 ALTER TABLE `form_view_directory_pages` DISABLE KEYS */;
INSERT INTO `form_view_directory_pages` VALUES (4,284,1,180,10,'Area','Subject','Forms'),(5,541,1,90,25,'Resource','Subject','Items');
/*!40000 ALTER TABLE `form_view_directory_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reference_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tracking_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `http_referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `submitted_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `quiz_score` tinyint(4) NOT NULL DEFAULT '0',
  `form_editor_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL DEFAULT '0',
  `address_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `complete` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  KEY `page_id` (`page_id`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`),
  KEY `submitted_timestamp` (`submitted_timestamp`),
  KEY `address_name` (`address_name`(250)),
  KEY `complete` (`complete`)
) ENGINE=MyISAM AUTO_INCREMENT=727 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
INSERT INTO `forms` VALUES (594,313,40,65,'P8TDKZMZGV','','','',1539282332,40,1548292830,0,0,2130706433,'you-can-use-a-block-quote-in-your-blog-posts',1),(593,313,40,65,'34T4VA6ENP','','','',1539281541,40,1548294949,0,0,2130706433,'you-can-use-an-embedded-audio-player-in-your-blog-posts',1),(595,313,40,65,'D6Y22B5WOY','','','',1539282621,40,1548295206,0,0,2130706433,'you-can-use-a-vimeo-video-player-in-your-blog-posts',1),(565,313,40,65,'XGNS682J9Y','','','',1538965341,40,1548292879,0,0,2130706433,'you-can-use-a-photo-in-your-blog-posts',1),(597,313,40,0,'Z2FOP2UJHR','','','',1539311218,40,1548291700,0,0,0,'consectetur-adipiscing-elit',1),(598,313,40,0,'OZRUL66I74','','','',1539311218,40,1548292465,0,0,0,'sed-lorem-diam-semper-ut-iaculis-eu-scelerisque-sed-neque',1),(599,313,40,0,'WD39C8TANL','','','',1539311218,40,1548292058,0,0,0,'phasellus-neque-quam-auctor-adipiscing-tincidunt-vel-euismod-auctor-dolor',1),(600,313,40,0,'6UD3E9EJ41','','','',1539311218,40,1548292143,0,0,0,'you-can-use-a-youtube-video-player-in-your-blog-posts',1),(652,296,40,65,'1WLELY5GTL','','','',1544170633,40,1544173500,0,0,2130706433,'managing-director',1),(651,296,40,65,'B7LQ9FFXFS','','','',1544170527,40,1546038692,0,0,2130706433,'founder',1),(653,296,40,65,'QMYMQ99TC2','','','',1544170744,40,1544200284,0,0,2130706433,'business-manager',1),(654,296,40,65,'XZ5W8VT85F','','','',1544170873,40,1544173604,0,0,2130706433,'director-of-marketing',1),(655,296,40,65,'D6D85ZVHXG','','','',1544171388,40,1544174230,0,0,2130706433,'director-of-communications',1),(656,296,40,65,'U1WYX7JKEY','','','',1544171452,40,1544173804,0,0,2130706433,'director-of-personnel',1),(657,296,40,65,'NDWQAMZBAD','','','',1544171560,40,1544173822,0,0,2130706433,'social-media-coordinator',1),(658,296,40,65,'JNJXNZO5HC','','','',1544171609,40,1544173854,0,0,2130706433,'director-of-technology',1),(659,296,40,65,'H5WXLDMTW0','','','',1544171671,40,1544173583,0,0,2130706433,'director-of-sales',1),(660,296,40,65,'3RP6JGZ0QM','','','',1544171736,40,1545329776,0,0,2130706433,'director-of-product-development',0),(661,296,40,65,'0G9LK264SD','','','',1544171827,40,1545335947,0,0,2130706433,'customer-service-director',0),(662,296,40,65,'FF8EWG6G2A','','','',1544171863,40,1545335960,0,0,2130706433,'director-of-operations',0),(702,296,40,65,'OEJY2I59M6','','','',1545337061,40,1545341581,0,0,2130706433,'director-of-special-projects',0),(668,538,40,65,'AG7BVXWNV5','','','',1544549682,40,1544556051,0,0,2130706433,'',1),(669,538,40,65,'C593F6ETS0','','','',1544550066,40,1547835516,0,0,2130706433,'',1),(667,538,40,65,'GS44TVDMOY','','','',1544549659,40,1544551931,0,0,2130706433,'',1),(670,538,40,65,'ZJ3FQ829RT','','','',1544550087,40,1544550087,0,0,2130706433,'',1),(671,538,40,65,'DKPYM20Q1A','','','',1544550119,40,1544550119,0,0,2130706433,'',1),(672,538,40,65,'PO4BMVVEMS','','','',1544550146,40,1544550146,0,0,2130706433,'',1);
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gift_cards`
--

DROP TABLE IF EXISTS `gift_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gift_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `balance` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quantity_number` tinyint(4) NOT NULL DEFAULT '0',
  `from_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recipient_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `expiration_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gift_cards`
--

LOCK TABLES `gift_cards` WRITE;
/*!40000 ALTER TABLE `gift_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `gift_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `key_codes`
--

DROP TABLE IF EXISTS `key_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `key_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `offer_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `expiration_date` date NOT NULL DEFAULT '0000-00-00',
  `single_use` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `report` enum('key_code','offer_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'key_code',
  `notes` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `key_codes`
--

LOCK TABLES `key_codes` WRITE;
/*!40000 ALTER TABLE `key_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `key_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `log_ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_user` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_timestamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_timestamp` (`log_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_regions`
--

DROP TABLE IF EXISTS `login_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `not_logged_in_header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `not_logged_in_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `logged_in_header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `logged_in_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `login_form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_regions`
--

LOCK TABLES `login_regions` WRITE;
/*!40000 ALTER TABLE `login_regions` DISABLE KEYS */;
INSERT INTO `login_regions` VALUES (1,'site-login','<a style=\"text-decoration: none\" href=\"{path}user-login-register\"><i class=\"ti-user\">&nbsp;</i> My Account</a>','','<a style=\"text-decoration: none\" href=\"{path}my-account\"><i class=\"ti-user\">&nbsp;</i>','</a>&middot; <a style=\"text-decoration: none\" href=\"{path}user-logout\">logout</a>',2,1537472973,40,1548292395,0);
/*!40000 ALTER TABLE `login_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `link_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `link_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `link_target` enum('Same Window','New Window') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Same Window',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `security` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `link_page_id` (`link_page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=611 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (329,11,0,'Home',1,0,'#mega-menu','Same Window',40,1538357317,40,1538418067,0),(330,11,329,'Home Page Layouts...',1,0,'#title','Same Window',40,1538357352,40,1548292118,0),(331,11,330,'Home I',1,863,'','Same Window',40,1538357395,40,1546302463,0),(368,11,366,'Account Profile',2,975,'','Same Window',40,1538429753,40,1538429753,0),(377,11,343,'2 Columns',1,994,'','Same Window',40,1538589898,40,1539376642,0),(334,11,0,'System',7,0,'#mega-menu','Same Window',40,1538357667,40,1538697585,0),(376,11,343,'3 Columns',2,343,'','Same Window',40,1538440419,40,1539376660,0),(379,11,343,'2 Columns (Wide)',4,996,'','Same Window',40,1538590233,40,1539376698,0),(367,11,366,'My Account',1,971,'','Same Window',40,1538429728,40,1538429728,0),(340,11,334,'Login Pages...',1,0,'#title','Same Window',40,1538415791,40,1548294935,0),(341,11,340,'Login',1,895,'','Same Window',40,1538415823,40,1538415823,0),(343,11,549,'Photo Galleries...',3,0,'#title','Same Window',40,1538416830,40,1548294246,0),(571,11,548,'Case Study',4,936,'','Same Window',40,1545893910,40,1546291505,0),(369,11,366,'Email Preferences',3,978,'','Same Window',40,1538429780,40,1538429780,0),(570,11,401,'All Staff Directory',3,1046,'','Same Window',40,1545339876,40,1545342056,0),(580,11,455,'Email Template',7,649,'','Same Window',40,1546025934,40,1548291716,0),(357,11,0,'Shop',4,0,'#mega-menu','Same Window',40,1538429257,40,1540401995,0),(358,11,380,'Sidebar Left',1,985,'','Same Window',40,1538429302,40,1538597581,0),(359,11,381,'Sidebar Left',1,0,'{path}shop-product-sidebar/Office_Chair','Same Window',40,1538429331,40,1548293840,0),(360,11,340,'Change Random Password',2,980,'','Same Window',40,1538429441,40,1538429441,0),(361,11,340,'Forgot Password',3,981,'','Same Window',40,1538429463,40,1538429463,0),(362,11,340,'Register',4,126,'','Same Window',40,1538429480,40,1538429480,0),(363,11,340,'Register Confirmation',5,974,'','Same Window',40,1538429495,40,1538429495,0),(364,11,340,'Register Member',6,982,'','Same Window',40,1538429537,40,1538429537,0),(365,11,340,'Register Member Confirmation',7,983,'','Same Window',40,1538429562,40,1538429562,0),(366,11,334,'Account Pages...',2,0,'#title','Same Window',40,1538429594,40,1548294925,0),(370,11,366,'Shipping Address',6,976,'','Same Window',40,1538429809,40,1542394589,0),(371,11,366,'Change Password',4,979,'','Same Window',40,1538429833,40,1538429833,0),(372,11,366,'View Order',5,0,'{path}my-account-view-order?id=437','Same Window',40,1538429876,40,1547829952,0),(373,11,455,'Miscellaneous...',4,0,'#title','Same Window',40,1538429928,40,1548291676,0),(374,11,455,'Site Search',5,216,'','Same Window',40,1538429946,40,1548291693,0),(375,11,455,'Site Error',6,902,'','Same Window',40,1538429968,40,1548291706,0),(378,11,343,'4 Columns',3,995,'','Same Window',40,1538590074,40,1539376670,0),(380,11,357,'Catalog Layouts...',1,0,'#title','Same Window',40,1538596619,40,1548294387,0),(381,11,357,'Product Layouts...',2,0,'#title','Same Window',40,1538596674,40,1548295027,0),(382,11,380,'Sidebar Right',2,997,'','Same Window',40,1538597222,40,1538597590,0),(383,11,381,'Sidebar Right',2,0,'{path}shop-product-sidebar-right/Office_Chair','Same Window',40,1538597737,40,1548293850,0),(384,11,380,'4 Columns',3,999,'','Same Window',40,1538600493,40,1538600493,0),(385,11,380,'3 Columns',4,1000,'','Same Window',40,1538601920,40,1538601920,0),(386,11,380,'2 Columns',5,1001,'','Same Window',40,1538602073,40,1538602073,0),(387,11,381,'No Sidebar',3,0,'{path}shop-product-fullwidth/Office_Chair','Same Window',40,1538603511,40,1548293880,0),(388,11,0,'Blog',3,0,'#mega-menu','Same Window',40,1538608226,40,1538964491,0),(466,11,401,'All Conversations',5,299,'','Same Window',40,1539367211,40,1541186523,0),(391,11,0,'More',5,0,'#mega-menu','Same Window',40,1538613593,40,1539213518,0),(548,11,478,'(Column 1)',7,0,'#hide','Same Window',40,1541184351,40,1541184351,0),(396,11,545,'My Conversations',4,647,'','Same Window',40,1538692235,40,1543595439,0),(397,11,442,'Order Service Plans',2,485,'','Same Window',40,1538696686,40,1541191338,0),(404,11,401,'Staff Home',1,294,'','Same Window',40,1538756082,40,1540404826,0),(395,11,545,'New Conversation',5,1005,'','Same Window',40,1538674833,40,1541124194,0),(398,11,482,'Make a Payment',3,251,'','Same Window',40,1538697226,40,1548294521,0),(460,11,442,'Donation Payment',7,476,'','Same Window',40,1539272730,40,1548294647,0),(467,11,401,'All Support Tickets',6,492,'','Same Window',40,1539367256,40,1541186537,0),(401,11,440,'Staff...',1,0,'#title','Same Window',40,1538755503,40,1548294835,0),(405,11,401,'Staff Calendar',2,297,'','Same Window',40,1538756110,40,1544650159,0),(406,11,441,'Event Registration',8,283,'','Same Window',40,1538759542,40,1539877599,0),(407,11,482,'Take Exam',7,291,'','Same Window',40,1538759580,40,1548294710,0),(408,11,441,'Collect Dues',7,394,'','Same Window',40,1538759834,40,1539195565,0),(409,11,441,'Membership Access',5,418,'','Same Window',40,1538760630,40,1541199597,0),(410,11,482,'Order Services',1,639,'','Same Window',40,1538760767,40,1548294480,0),(594,11,512,'Agency II',3,877,'','Same Window',40,1546902840,40,1547587066,0),(413,11,482,'Students...',5,0,'#title','Same Window',40,1538762236,40,1548294676,0),(414,11,482,'Order Exam',6,506,'','Same Window',40,1538762308,40,1548294697,0),(416,11,357,'Checkout...',3,0,'#title','Same Window',40,1538954602,40,1548294353,0),(417,11,416,'Billing Information',3,179,'','Same Window',40,1538954634,40,1548294104,0),(418,11,416,'Shipping Arrival',1,144,'','Same Window',40,1538954668,40,1548294084,0),(419,11,416,'Shipping Methods',2,145,'','Same Window',40,1538954688,40,1548294068,0),(423,11,428,'Blog Cards No Sidebar',4,1016,'','Same Window',40,1538963428,40,1539375160,0),(424,11,428,'Blog Cards',3,1015,'','Same Window',40,1538963459,40,1539374247,0),(425,11,429,'Blog Post No Sidebar',2,0,'{path}blog-no-sidebar/consectetur-adipiscing-elit','Same Window',40,1538963513,40,1548293828,0),(428,11,388,'Blog Layouts...',1,0,'#title','Same Window',40,1538964149,40,1548294272,0),(427,11,428,'Blog No Sidebar',2,1013,'','Same Window',40,1538963984,40,1539370577,0),(429,11,388,'Blog Post Layouts...',2,0,'#title','Same Window',40,1538964167,40,1548294321,0),(437,11,391,'Calendaring...',2,0,'#title','Same Window',40,1539129139,40,1548294807,0),(438,11,437,'Calendar',1,200,'','Same Window',40,1539129163,40,1539192010,0),(439,11,437,'Calendar Event',2,0,'{path}calendar-event?id=28','Same Window',40,1539129192,40,1548293143,0),(440,11,0,'Portals',6,0,'#mega-menu','Same Window',40,1539192133,40,1539192189,0),(441,11,440,'Members...',2,0,'#title','Same Window',40,1539192235,40,1548294846,0),(442,11,440,'Customers...',3,0,'#title','Same Window',40,1539192274,40,1548294857,0),(443,11,441,'Members Home',1,284,'','Same Window',40,1539192489,40,1541187702,0),(444,11,441,'Members Calendar',2,285,'','Same Window',40,1539192517,40,1544636970,0),(559,11,482,'Schedule Training',4,481,'','Same Window',40,1541191308,40,1548294532,0),(447,11,441,'Trial Membership',6,417,'','Same Window',40,1539193559,40,1544638669,0),(557,11,441,'Members Directory',3,568,'','Same Window',40,1541190794,40,1544636978,0),(448,11,381,'Shopping Cart',5,248,'','Same Window',40,1539209981,40,1541123919,0),(449,11,442,'Purchase Downloads',3,1012,'','Same Window',40,1539211144,40,1539211144,0),(450,11,416,'Order Preview',4,101,'','Same Window',40,1539271142,40,1540914018,0),(451,11,416,'Order Receipt',5,269,'','Same Window',40,1539271183,40,1540914042,0),(455,11,334,'Affiliate Pages...',4,0,'#title','Same Window',40,1539272090,40,1548291616,0),(454,11,455,'Affiliate Sign Up',1,169,'','Same Window',40,1539272008,40,1548293936,0),(456,11,455,'Affiliate Confirmation',2,180,'','Same Window',40,1539272467,40,1548291649,0),(457,11,455,'Affiliate Welcome',3,181,'','Same Window',40,1539272493,40,1548291659,0),(458,11,442,'Donors...',5,0,'#title','Same Window',40,1539272643,40,1548294609,0),(459,11,442,'Donation Form',6,266,'','Same Window',40,1539272659,40,1548294628,0),(461,11,442,'Donation Receipt',8,477,'','Same Window',40,1539272784,40,1548294653,0),(462,11,428,'Blog',1,227,'','Same Window',40,1539284577,40,1539370356,0),(463,11,429,'Blog Post',1,0,'{path}blog/consectetur-adipiscing-elit','Same Window',40,1539284605,40,1548293810,0),(464,11,548,'Contact Us',7,1003,'','Same Window',40,1539366499,40,1546291567,0),(565,11,437,'My Support Tickets',4,401,'','Same Window',40,1542393094,40,1543595452,0),(482,11,440,'Clients...',5,0,'#title','Same Window',40,1539877177,40,1548294451,0),(564,11,437,'Support Tickets...',3,0,'#title','Same Window',40,1542393066,40,1548294820,0),(562,11,548,'Forum',6,407,'','Same Window',40,1541199784,40,1546291523,0),(476,11,441,'Classified Ads',4,289,'','Same Window',40,1539377927,40,1544636951,0),(478,11,0,'Pages',2,0,'#mega-menu','Same Window',40,1539378139,40,1541184376,0),(479,11,548,'About Us',1,886,'','Same Window',40,1539378209,40,1546022292,0),(573,11,512,'Design Elements',6,1052,'','Same Window',40,1545936038,40,1548291796,0),(558,11,482,'Class Registration',8,483,'','Same Window',40,1541191120,40,1548294721,0),(484,11,482,'My Services',2,1028,'','Same Window',40,1539879006,40,1548294509,0),(568,11,401,'All Services Projects',7,1025,'','Same Window',40,1543339008,40,1543339035,0),(488,11,545,'Mailing List Form',1,472,'','Same Window',40,1539905645,40,1541124087,0),(494,11,512,'Agency',2,866,'','Same Window',40,1541119496,40,1547587058,0),(495,11,511,'Software',3,867,'','Same Window',40,1541119525,40,1547579089,0),(496,11,511,'Product',4,868,'','Same Window',40,1541119540,40,1547586641,0),(497,11,512,'Firm',4,869,'','Same Window',40,1541119701,40,1547587166,0),(498,11,511,'Home VI',1,964,'','Same Window',40,1541119720,40,1548295062,0),(499,11,511,'Training',5,965,'','Same Window',40,1541119743,40,1547587009,0),(501,11,511,'Music',6,873,'','Same Window',40,1541119826,40,1547587199,0),(502,11,512,'Event',1,874,'','Same Window',40,1541119865,40,1548295105,0),(506,11,511,'Gift Shop',2,881,'','Same Window',40,1541119996,40,1547587179,0),(507,11,330,'Home II',2,882,'','Same Window',40,1541120066,40,1546302470,0),(508,11,330,'Home III',3,883,'','Same Window',40,1541120078,40,1546302477,0),(509,11,330,'Home IV',4,884,'','Same Window',40,1541120093,40,1546302483,0),(510,11,330,'Home V',5,885,'','Same Window',40,1541120109,40,1546302489,0),(511,11,329,'(Column 2)',2,0,'#hide','Same Window',40,1541120426,40,1541120434,0),(512,11,329,'(Column 3)',3,0,'#hide','Same Window',40,1541120453,40,1541120453,0),(515,11,512,'Design...',5,0,'#title','Same Window',40,1541121159,40,1548292166,0),(588,11,548,'Support',5,1057,'','Same Window',40,1546283820,40,1546291514,0),(589,11,548,'Services',3,1058,'','Same Window',40,1546286557,40,1546286557,0),(543,11,548,'Staff Directory',2,219,'','Same Window',40,1541123704,40,1546291551,0),(566,11,437,'New Support Ticket',5,395,'','Same Window',40,1542727359,40,1542727373,0),(544,11,381,'Cart...',4,0,'#title','Same Window',40,1541123903,40,1548294343,0),(545,11,391,'Mailing List...',1,0,'#title','Same Window',40,1541124057,40,1548294783,0),(546,11,545,'Mailing List Offer',2,579,'','Same Window',40,1541124122,40,1541124122,0),(547,11,545,'Conversations...',3,0,'#title','Same Window',40,1541124241,40,1548294794,0),(549,11,478,'(Column 2)',8,0,'#hide','Same Window',40,1541184367,40,1541184367,0),(590,11,549,'Coming Soon',1,1059,'','Same Window',40,1546296044,40,1547835793,0),(551,11,549,'Video Gallery',2,539,'','Same Window',40,1541184966,40,1547835783,0),(555,11,401,'Latest Activity',4,541,'','Same Window',40,1541186482,40,1541186508,0),(569,11,442,'Buy Tickets',1,479,'','Same Window',40,1545091345,40,1545091363,0),(560,11,442,'My Content',4,558,'','Same Window',40,1541191380,40,1541191394,0),(610,11,401,'Create Quote',8,496,'','Same Window',40,1548292688,40,1548292688,0),(599,12,0,'Home',1,0,'{path}','Same Window',40,1548295088,40,1548293806,0),(600,12,0,'About Us',2,886,'','Same Window',40,1548295099,40,1548295099,0),(601,12,0,'Blog',3,227,'','Same Window',40,1548295118,40,1548293013,0),(602,12,0,'Shop',4,1000,'','Same Window',40,1548295130,40,1548293027,0),(603,12,0,'Contact Us',8,1003,'','Same Window',40,1548295155,40,1548295187,0),(609,12,0,'Donate',7,266,'','Same Window',40,1548294555,40,1548294684,0),(605,12,0,'Services',5,1058,'','Same Window',40,1548295177,40,1548291795,0),(606,12,0,'Staff',9,294,'','Same Window',40,1548291666,40,1548291673,1),(608,12,0,'Support',6,1057,'','Same Window',40,1548292930,40,1548292952,0);
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_level_popup_position` enum('Top','Bottom','Left','Right') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Bottom',
  `second_level_popup_position` enum('Top','Bottom','Left','Right') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Right',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `effect` enum('','Pop-up','Accordion') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (11,'sandbox-menu','Bottom','Right',40,1538357225,40,1548292688,'',''),(12,'site-menu','Bottom','Right',40,1548295067,40,1548293806,'','');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `frequency` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `width` int(10) unsigned NOT NULL DEFAULT '0',
  `height` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `next_order_number`
--

DROP TABLE IF EXISTS `next_order_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `next_order_number` (
  `next_order_number` bigint(20) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `next_order_number`
--

LOCK TABLES `next_order_number` WRITE;
/*!40000 ALTER TABLE `next_order_number` DISABLE KEYS */;
INSERT INTO `next_order_number` VALUES (1001);
/*!40000 ALTER TABLE `next_order_number` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_actions`
--

DROP TABLE IF EXISTS `offer_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('discount order','discount product','add product','discount shipping') COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_order_amount` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_order_percentage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `discount_product_product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_product_amount` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_product_percentage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_product_product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `add_product_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `add_product_discount_amount` int(10) unsigned NOT NULL DEFAULT '0',
  `add_product_discount_percentage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_shipping_percentage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_actions`
--

LOCK TABLES `offer_actions` WRITE;
/*!40000 ALTER TABLE `offer_actions` DISABLE KEYS */;
INSERT INTO `offer_actions` VALUES (3,'Get 10% Discount on the entire order','discount order',0,10,0,0,0,0,0,0,0,2,1537472973,0),(4,'Get $5 off the Monthly Service Plan','discount product',0,0,38,500,0,0,0,0,0,2,1537472973,0),(5,'Get 15% Discount on the entire order','discount order',0,15,0,0,0,0,0,0,0,2,1537472973,0),(11,'10% Off Mocha Office Chair','discount product',0,0,70,0,10,0,0,0,0,40,1540918037,0),(7,'Free Shipping','discount shipping',0,0,0,0,0,0,0,0,0,2,1537472973,100),(8,'$5 Off Concert Tickets','discount product',0,0,56,500,0,0,0,0,0,40,1545262782,0),(9,'Get $3 off eBook','discount product',0,0,37,300,0,0,0,0,0,2,1537472973,0),(10,'Save 10% on Annual Service Plan','discount product',0,0,39,0,10,0,0,0,0,40,1539215394,0),(12,'10% Off Green Office Chair','discount product',0,0,71,0,10,71,1,0,10,40,1540918089,0),(13,'10% Off Orange Office Chair','discount product',0,0,72,0,10,0,0,0,0,40,1540918108,0);
/*!40000 ALTER TABLE `offer_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_actions_shipping_methods_xref`
--

DROP TABLE IF EXISTS `offer_actions_shipping_methods_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_actions_shipping_methods_xref` (
  `offer_action_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `offer_action_id` (`offer_action_id`),
  KEY `shipping_method_id` (`shipping_method_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_actions_shipping_methods_xref`
--

LOCK TABLES `offer_actions_shipping_methods_xref` WRITE;
/*!40000 ALTER TABLE `offer_actions_shipping_methods_xref` DISABLE KEYS */;
INSERT INTO `offer_actions_shipping_methods_xref` VALUES (7,2);
/*!40000 ALTER TABLE `offer_actions_shipping_methods_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_rules`
--

DROP TABLE IF EXISTS `offer_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `required_subtotal` int(10) unsigned NOT NULL DEFAULT '0',
  `required_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_rules`
--

LOCK TABLES `offer_rules` WRITE;
/*!40000 ALTER TABLE `offer_rules` DISABLE KEYS */;
INSERT INTO `offer_rules` VALUES (4,'Order more than $100',10000,0,2,1537472973),(5,'Order the Monthly Service Plan',100,1,2,1537472973),(6,'Order more than $200',20000,0,2,1537472973),(8,'Orders over 100 dollars',10000,0,2,1537472973);
/*!40000 ALTER TABLE `offer_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_rules_products_xref`
--

DROP TABLE IF EXISTS `offer_rules_products_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_rules_products_xref` (
  `offer_rule_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `offer_rule_id` (`offer_rule_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_rules_products_xref`
--

LOCK TABLES `offer_rules_products_xref` WRITE;
/*!40000 ALTER TABLE `offer_rules_products_xref` DISABLE KEYS */;
INSERT INTO `offer_rules_products_xref` VALUES (5,38);
/*!40000 ALTER TABLE `offer_rules_products_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `require_code` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('enabled','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'enabled',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `offer_rule_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `upsell` tinyint(4) NOT NULL DEFAULT '0',
  `upsell_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `upsell_trigger_subtotal` int(10) unsigned NOT NULL DEFAULT '0',
  `upsell_trigger_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `upsell_action_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `upsell_action_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `scope` enum('order','recipient') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'order',
  `multiple_recipients` tinyint(4) NOT NULL DEFAULT '0',
  `only_apply_best_offer` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `offer_rule_id` (`offer_rule_id`),
  KEY `code` (`code`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (4,'12345','$5 Off our Monthly Service Plan with promotion code.',1,'disabled','2019-01-01','2099-12-31',5,2,1537472973,1,'Enter Promotion Code \"12345\" and get $5 off our Monthly Service Plan.',0,0,'Details',0,'order',0,1),(5,'Office Chair Sale - 10% Off Green!','You are saving 10% off this Green Office Chair!',0,'disabled','2019-01-01','2099-12-31',0,40,1540933004,0,'',0,0,'',0,'order',0,1),(6,'Orders over $100 get free shipping','All orders over $100 will get free shipping!',0,'disabled','2019-01-01','2099-12-31',8,2,1537472973,1,'Order $100 or more and you\'ll get free shipping.',9900,0,'',0,'order',0,1),(7,'5OFFTIX','Congrats! $5 off your concert tickets!',0,'disabled','2019-01-01','2099-12-31',0,40,1545262811,0,'',0,0,'',0,'order',0,1),(9,'Save 10% on Annual Service Plans','Save 10% on our annual service plans for a limited time!',0,'disabled','2019-01-01','2099-12-31',0,40,1542858129,0,'',0,0,'',0,'order',0,1),(10,'10Off','Get 10% Off your Order',1,'disabled','2019-01-01','2099-12-31',8,40,1539661593,0,'',0,0,'',0,'order',0,1);
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers_offer_actions_xref`
--

DROP TABLE IF EXISTS `offers_offer_actions_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers_offer_actions_xref` (
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offer_action_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `offer_id` (`offer_id`),
  KEY `offer_action_id` (`offer_action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers_offer_actions_xref`
--

LOCK TABLES `offers_offer_actions_xref` WRITE;
/*!40000 ALTER TABLE `offers_offer_actions_xref` DISABLE KEYS */;
INSERT INTO `offers_offer_actions_xref` VALUES (4,4),(6,7),(7,8),(9,10),(10,3),(5,12);
/*!40000 ALTER TABLE `offers_offer_actions_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opt_in`
--

DROP TABLE IF EXISTS `opt_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opt_in` (
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `opt_in` tinyint(4) NOT NULL DEFAULT '0',
  KEY `contact_id` (`contact_id`),
  KEY `contact_group_id` (`contact_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opt_in`
--

LOCK TABLES `opt_in` WRITE;
/*!40000 ALTER TABLE `opt_in` DISABLE KEYS */;
/*!40000 ALTER TABLE `opt_in` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_form_pages`
--

DROP TABLE IF EXISTS `order_form_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_form_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_layout` enum('list','drop-down selection') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'list',
  `add_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `add_button_next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `skip_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `skip_button_next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=231 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_form_pages`
--

LOCK TABLES `order_form_pages` WRITE;
/*!40000 ALTER TABLE `order_form_pages` DISABLE KEYS */;
INSERT INTO `order_form_pages` VALUES (218,418,46,'list','Continue',1076,'',0),(206,266,21,'list','Continue to Payment',476,'',0),(215,283,30,'list','Continue',256,'',0),(211,251,26,'list','Continue',1076,'',0),(216,394,39,'list','Continue',1076,'',0),(222,496,41,'drop-down selection','Add to Quote',497,'',0),(224,506,42,'list','Continue',1076,'',0),(226,639,44,'list','Continue',1076,'',0),(229,485,47,'list','Add Service Plan',1076,'',0),(228,1012,23,'list','Continue',1076,'',0);
/*!40000 ALTER TABLE `order_form_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_gift_cards`
--

DROP TABLE IF EXISTS `order_item_gift_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item_gift_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quantity_number` tinyint(4) NOT NULL DEFAULT '0',
  `from_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recipient_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `quantity_number` (`quantity_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_gift_cards`
--

LOCK TABLES `order_item_gift_cards` WRITE;
/*!40000 ALTER TABLE `order_item_gift_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_gift_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `added_by_offer` tinyint(4) NOT NULL DEFAULT '0',
  `discounted_by_offer` tinyint(4) NOT NULL DEFAULT '0',
  `quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '0',
  `tax` int(11) NOT NULL DEFAULT '0',
  `shipping` int(10) unsigned NOT NULL DEFAULT '0',
  `recurring_profile_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recurring_profile_enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurring_payment_period` enum('','Monthly','Weekly','Every Two Weeks','Twice every Month','Every Four Weeks','Quarterly','Twice every Year','Yearly') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recurring_number_of_payments` int(10) unsigned NOT NULL DEFAULT '0',
  `recurring_start_date` date NOT NULL DEFAULT '0000-00-00',
  `calendar_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `recurrence_number` int(10) unsigned NOT NULL DEFAULT '0',
  `offer_action_id` int(10) unsigned NOT NULL DEFAULT '0',
  `show_shipped_quantity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `shipped_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `add_watcher` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `ship_to_id` (`ship_to_id`),
  KEY `product_id` (`product_id`),
  KEY `offer_id` (`offer_id`),
  KEY `calendar_event_id` (`calendar_event_id`),
  KEY `recurrence_number` (`recurrence_number`),
  KEY `offer_action_id` (`offer_action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_preview_pages`
--

DROP TABLE IF EXISTS `order_preview_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_preview_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `card_verification_number_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `terms_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offline_payment_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_receipt_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `order_receipt_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_receipt_email_format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `order_receipt_email_header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `order_receipt_email_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `order_receipt_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `offline_payment_always_allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `product_description_type` enum('full_description','short_description') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full_description',
  `pre_save_hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `post_save_hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `auto_registration` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_preview_pages`
--

LOCK TABLES `order_preview_pages` WRITE;
/*!40000 ALTER TABLE `order_preview_pages` DISABLE KEYS */;
INSERT INTO `order_preview_pages` VALUES (12,101,0,98,'Purchase Now',269,'',1,'Order Receipt #','html','Order Receipt','',533,0,'full_description','','',1),(14,1077,0,98,'Purchase Now',269,'',1,'Order Receipt #','html','Order Receipt','',533,0,'full_description','','',1);
/*!40000 ALTER TABLE `order_preview_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_receipt_pages`
--

DROP TABLE IF EXISTS `order_receipt_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_receipt_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_description_type` enum('full_description','short_description') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full_description',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_receipt_pages`
--

LOCK TABLES `order_receipt_pages` WRITE;
/*!40000 ALTER TABLE `order_receipt_pages` DISABLE KEYS */;
INSERT INTO `order_receipt_pages` VALUES (1,269,'full_description'),(2,477,'short_description'),(3,533,'short_description'),(4,534,'short_description');
/*!40000 ALTER TABLE `order_receipt_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_report_filters`
--

DROP TABLE IF EXISTS `order_report_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_report_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_report_id` int(10) unsigned NOT NULL DEFAULT '0',
  `field` enum('','order_date','order_status','order_number','transaction_id','authorization_code','special_offer_code','referral_source_code','reference_code','tracking_code','http_referer','currency_code','ip_address','product_name','utm_source','utm_medium','utm_campaign','utm_term','utm_content','payment_method','card_type','cardholder','card_number','custom_field_1','custom_field_2','billing_salutation','billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_zip_code','billing_country','billing_phone_number','billing_fax_number','billing_email_address','opt_in_status','po_number','tax_status','ship_to_name','shipping_salutation','shipping_first_name','shipping_last_name','shipping_company','shipping_address_1','shipping_address_2','shipping_city','shipping_state','shipping_zip_code','shipping_country','shipping_address_type','shipping_phone_number','arrival_date_code','shipping_method_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `operator` enum('contains','does not contain','is equal to','is not equal to','is less than','is less than or equal to','is greater than','is greater than or equal to') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contains',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value` enum('','current date','days ago') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_report_id` (`order_report_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_report_filters`
--

LOCK TABLES `order_report_filters` WRITE;
/*!40000 ALTER TABLE `order_report_filters` DISABLE KEYS */;
INSERT INTO `order_report_filters` VALUES (11,1,'order_date','is greater than or equal to','2019-01-01','',0),(10,1,'order_status','is not equal to','incomplete','',0);
/*!40000 ALTER TABLE `order_report_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_reports`
--

DROP TABLE IF EXISTS `order_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `detail` tinyint(4) NOT NULL DEFAULT '0',
  `summarize_by_1` enum('','year','month','day','order_status','special_offer_code','referral_source_code','reference_code','tracking_code','http_referer','ip_address','utm_source','utm_medium','utm_campaign','utm_term','utm_content','payment_method','card_type','cardholder','card_number','custom_field_1','custom_field_2','billing_salutation','billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_zip_code','billing_country','billing_phone_number','billing_fax_number','billing_email_address','opt_in_status','po_number','tax_status','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_1` enum('alphabet','number of orders','total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `summarize_by_2` enum('','year','month','day','order_status','special_offer_code','referral_source_code','reference_code','tracking_code','http_referer','ip_address','utm_source','utm_medium','utm_campaign','utm_term','utm_content','payment_method','card_type','cardholder','card_number','custom_field_1','custom_field_2','billing_salutation','billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_zip_code','billing_country','billing_phone_number','billing_fax_number','billing_email_address','opt_in_status','po_number','tax_status','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_2` enum('alphabet','number of orders','total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `summarize_by_3` enum('','year','month','day','order_status','special_offer_code','referral_source_code','reference_code','tracking_code','http_referer','ip_address','utm_source','utm_medium','utm_campaign','utm_term','utm_content','payment_method','card_type','cardholder','card_number','custom_field_1','custom_field_2','billing_salutation','billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_zip_code','billing_country','billing_phone_number','billing_fax_number','billing_email_address','opt_in_status','po_number','tax_status','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_3` enum('alphabet','number of orders','total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_reports`
--

LOCK TABLES `order_reports` WRITE;
/*!40000 ALTER TABLE `order_reports` DISABLE KEYS */;
INSERT INTO `order_reports` VALUES (1,'This Year\'s Orders (by State)',1,'billing_state','alphabet','billing_city','alphabet','','alphabet',2,1537472973,2,1537472973);
/*!40000 ALTER TABLE `order_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `billing_first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_company` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_address_1` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_address_2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_zip_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_phone_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_fax_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cardholder` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_number` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expiration_month` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expiration_year` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_verification_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `po_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subtotal` int(11) NOT NULL DEFAULT '0',
  `tax` int(10) unsigned DEFAULT NULL,
  `total` int(11) NOT NULL DEFAULT '0',
  `order_number` bigint(20) unsigned DEFAULT NULL,
  `order_date` int(10) unsigned DEFAULT NULL,
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_salutation` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `referral_source_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `special_offer_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `discount` int(10) unsigned NOT NULL DEFAULT '0',
  `shipping` int(10) unsigned NOT NULL DEFAULT '0',
  `opt_in` tinyint(4) NOT NULL DEFAULT '1',
  `tax_exempt` tinyint(4) NOT NULL DEFAULT '0',
  `billing_complete` tinyint(4) NOT NULL DEFAULT '0',
  `reference_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tracking_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `http_referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `commission` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_field_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `authorization_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payment_method` enum('','Credit/Debit Card','PayPal Express Checkout','Offline Payment') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ip_address` int(10) unsigned NOT NULL DEFAULT '0',
  `gift_card_discount` int(10) unsigned NOT NULL DEFAULT '0',
  `offline_payment_allowed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `billing_address_verified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('incomplete','complete','exported') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'incomplete',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `surcharge` int(10) unsigned NOT NULL DEFAULT '0',
  `utm_source` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_medium` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_campaign` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_term` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mailchimp_sync_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `mailchimp_sync_error` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  KEY `order_date` (`order_date`),
  KEY `status` (`status`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`),
  KEY `user_id` (`user_id`),
  KEY `mailchimp_sync_timestamp` (`mailchimp_sync_timestamp`),
  KEY `billing_email_address` (`billing_email_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_folder` int(10) unsigned DEFAULT NULL,
  `page_style` int(10) unsigned DEFAULT NULL,
  `page_home` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_search` int(1) DEFAULT NULL,
  `page_meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_meta_keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `page_user` int(10) unsigned DEFAULT NULL,
  `page_timestamp` int(10) unsigned DEFAULT NULL,
  `page_type` enum('standard','change password','set password','email a friend','error','folder view','forgot password','login','logout','photo gallery','membership confirmation','membership entrance','my account','my account profile','email preferences','view order','update address book','custom form','custom form confirmation','form list view','form item view','form view directory','calendar view','calendar event view','catalog','catalog detail','express order','order form','shopping cart','shipping address and arrival','shipping method','billing information','order preview','order receipt','registration confirmation','registration entrance','search results','affiliate sign up form','affiliate sign up confirmation','affiliate welcome') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'standard',
  `page_search_keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `comments` tinyint(4) NOT NULL DEFAULT '0',
  `comments_automatic_publish` tinyint(4) NOT NULL DEFAULT '0',
  `comments_administrator_email_to_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_submitter_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `comments_submitter_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_administrator_email_conditional_administrators` tinyint(4) NOT NULL DEFAULT '0',
  `seo_score` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seo_analysis` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `seo_analysis_current` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_administrator_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_watcher_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `comments_watcher_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_allow_user_to_select_name` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_require_login_to_comment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_allow_new_comments` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_disallow_new_comment_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_allow_file_attachments` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sitemap` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_show_submitted_date_and_time` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mobile_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `system_region_header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `system_region_footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `comments_watchers_managed_by_submitter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_find_watchers_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `comments_label` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `layout_type` enum('system','custom') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'system',
  `layout_modified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`),
  KEY `page_timestamp` (`page_timestamp`),
  KEY `page_name` (`page_name`),
  KEY `page_folder` (`page_folder`),
  KEY `page_home` (`page_home`),
  KEY `page_type` (`page_type`)
) ENGINE=MyISAM AUTO_INCREMENT=1079 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page`
--

LOCK TABLES `page` WRITE;
/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` VALUES (995,'photo-gallery-4col',87,725,'','Photo Gallery',0,'This page is photo gallery. All image files that share the same folder with this page will be included.','photos',40,1546302678,'photo gallery','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(144,'checkout-shipping-arrival',286,720,'','Shipping Address & Arrival',0,'Specify the destination of each of your shipments from this page.','',40,1548294008,'shipping address and arrival','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(145,'checkout-shipping-methods',286,720,'','Shipping Methods',0,'','',40,1548292732,'shipping method','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(216,'site-search',286,715,'','Site Search',0,'This is the site search & results page. It will search the index of all searchable pages within it\'s Folder and Subfolders.','',40,1548294957,'search results','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(298,'staff-calendar-event',288,718,'','Staff Calendar Event',0,'This this event is from the Staff, Class, Members, or Public Calendar.','',40,1547837953,'calendar event view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,340,'','',0,0,'','','system',0),(308,'exam-confirmation',104,718,'','Exam Confirmation',0,'You have passed the exam!','',40,1547848829,'custom form confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(85,'new-conversation-confirmation',290,714,'','Contact Us Confirmation',0,'','',40,1547838196,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(98,'checkout-preview-terms',286,718,'','Order Terms & Conditions',0,'','',40,1547838786,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(981,'user-forgot-password',125,760,'','',0,'','',40,1548293317,'forgot password','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(101,'checkout-preview',286,720,'','Preview and Payment Page',0,'','',40,1548295108,'order preview','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(269,'checkout-receipt',286,718,'','Order Receipt',0,'','',40,1548294599,'order receipt','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(256,'express-order',286,718,'','Order Preview & Payment',0,'','',40,1548292830,'express order','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(126,'user-login-register',125,712,'','User Login and Guest Registration Form',0,'This page is automatically displayed if access to a protected page or file is accessed and the user is not yet logged in.','',40,1548295196,'registration entrance','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(985,'shop-sidebar',286,720,'','Shop',0,'Browse or search our catalog of products.','',40,1548293640,'catalog','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(986,'shop-product-sidebar',286,720,'','',0,'','',40,1548292724,'catalog detail','',1,0,'example@example.com',0,'',0,0,'',0,'Please approve this new product review.',0,'',1,0,1,'',0,0,0,0,'','',0,0,'Product Review','If you own this product, please tell others what you think about it.','custom',1),(994,'photo-gallery-2col',87,725,'','Photo Gallery',0,'This page is photo gallery. All image files that share the same folder with this page will be included.','photos',40,1546302678,'photo gallery','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(983,'member-login-register-confirmation',287,718,'','',0,'','',40,1547838638,'membership confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(976,'my-account-address',167,718,'','',0,'','',40,1547837134,'update address book','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1023,'new-services-project',288,740,'','New Services Project',0,'','',40,1547848829,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(258,'members-access-response-email',123,741,'','Member Access Response Email',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(169,'affiliate-signup-form',167,714,'','Become an Affiliate',0,'Earn one-time or recurring commissions when your friends buy from us.','',40,1548293924,'affiliate sign up form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,335,'','',0,0,'','','system',0),(283,'event-registration',286,714,'','Event Registration Form',0,'Please select your level of participation in our upcoming conference.','',40,1548294455,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(277,'my-account-view-order',167,718,'','View Order',0,'Viewing any saved cart or reordering is a snap.','',40,1547837134,'view order','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,328,'','',0,0,'','','system',0),(266,'donation',286,718,'','Donation',0,'Please select the giving opportunity that is right for you.','giving, online giving, donate',40,1548292039,'order form','donate',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1024,'services-project-confirmation',288,714,'','New Services Project Confirmation',0,'','',40,1547838196,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(350,'event-registration-participant-email',123,741,'','Event Registration Participant Email',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(351,'event-registration-exhibitor-email',123,741,'','Event Registration Exhibitor Email',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1025,'all-services-projects',288,740,'','All Service Projects',0,'All service projects for all customers that purchase service credits are displayed here.','',40,1548294671,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1005,'new-conversation',290,731,'','New Conversation',0,'Start a new conversation with our Sales team.','',40,1547848099,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(179,'checkout-billing-information',286,720,'','',0,'','',40,1548293199,'billing information','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(180,'affiliate-signup-confirmation',167,741,'','Affiliate Application Confirmation',0,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','',40,1547846806,'affiliate sign up confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(181,'affiliate-approval-welcome-email',123,741,'','Welcome to the Affiliate Program',0,'','',40,1547848829,'affiliate welcome','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(182,'affiliate-terms',286,718,'','Sales Affiliate Terms and Conditions',0,'','',40,1547838706,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,328,'','',0,0,'','','system',0),(183,'conversation-widget',295,728,'','',0,'','',40,1547836150,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(191,'calendar-event',286,714,'','Calendar Event Details',0,'','search calendar',40,1548293073,'calendar event view','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to a calendar event.',415,'A new comment was just added to a calendar event.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(296,'staff-directory-form',288,740,'','Staff Directory Form',0,'Use this form to add new staff to your staff directory.','',40,1547838064,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',0),(297,'staff-calendar',288,745,'','Staff Calendar',0,'This calendar page will show all events from the staff calendar.','',40,1546302678,'calendar view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,328,'','',0,0,'','','system',0),(285,'members-calendar',287,753,'','Members Calendar',0,'This calendar page will show all event on the members calendar.','',40,1547848829,'calendar view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(286,'members-calendar-event',287,750,'','Members Calendar Event',0,'','',40,1547837953,'calendar event view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,336,'','',0,0,'','','system',0),(288,'members-directory-form',287,750,'','Members Directory Form',0,'Add your organization to our Members Directory.  You can edit your own entry at any time.','',40,1547848829,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(289,'classified-ads',287,750,'','Classified Ads',0,'This page displays all the active classified ads posted by others that have access.','',40,1548293612,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(290,'classified-ads-form',287,750,'','Classified Ads Form',0,'','',40,1548292237,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(291,'exam',104,714,'','Exam',0,'See if you have the skills to succeed.','',40,1547848829,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(292,'exam-response-email',123,741,'','Exam Completion Response Email',0,'','',40,1547848829,'custom form confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(299,'all-conversations',288,740,'','All Conversations',0,'When someone submits a \'Contact Us\' form on the public website, it lands here as a new Conversation.','',40,1548292739,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(294,'staff-home',288,740,'','Staff Home Page',0,'Protected intranet area home page for your organization.','',40,1548293970,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(200,'calendar',286,718,'','Calendar',0,'This calendar will show all events from all calendars that you have access too. ','',40,1547847986,'calendar view','calendar',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(533,'checkout-receipt-notification-email',123,741,'','Order Receipt',0,'','',40,1548295153,'order receipt','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',1),(534,'checkout-receipt-donation-email',123,741,'','Donation Receipt',0,'','',40,1548295200,'order receipt','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',1),(284,'members-home',287,750,'','Member Home',0,'Now that you\'re logged in as a Member, enjoy the Member Portal.','',40,1547848034,'form view directory','members',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(492,'all-support-tickets',288,740,'','All Support Tickets',0,'All support tickets will appear on this page for staff to view.','',40,1548294624,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(982,'member-login-register',125,712,'','',0,'','',40,1548292493,'membership entrance','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(219,'staff-directory',286,718,'','Staff Directory',0,'A directory of our staff.','directory',40,1548110532,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(222,'new-conversation-email',123,741,'','Contact Us Response',0,'This page is sent to the submitter\'s email address when this form is submitted.','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(227,'blog',286,733,'','Blog',0,'Add any number of blogs to your site.','blog, news, blog posts',40,1548293557,'form list view','Blog',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(497,'quote',286,718,'','My Quote',0,'This page displays the items in the current cart and the subtotals before shipping and taxes.','',40,1548295197,'express order','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(476,'donation-payment',286,718,'','Donation Payment',0,'','',40,1548291664,'express order','donate',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(303,'exam-notification-email',123,741,'','Exam Notification Email',0,'','',40,1547848829,'custom form confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(248,'cart',286,720,'','Shopping Cart',0,'This page displays the items in the current cart and the subtotals before shipping and taxes.','',40,1548293531,'shopping cart','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(251,'payment',290,714,'','Make a Payment',0,'Use this form to apply a secure payment towards your account.','',40,1548293650,'order form','make payment',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(313,'blog-form',288,745,'','Blog Form',0,'Use this form to add new blog postings to your blog.','',40,1547838064,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',0),(316,'staff-directory-item',286,718,'','Staff Directory Item',0,'This page displays a staff directory entry.','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(330,'members-directory-item',287,750,'','Members Directory',0,'','',40,1547838196,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,336,'','',0,0,'','','system',0),(1012,'download-access',286,714,'','Purchase Download Access',0,'Purchase instand access to downloadable goods.','',40,1548293631,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(980,'set-pass',125,760,'','',0,'','',40,1548295166,'set password','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(343,'photo-gallery-3col',87,725,'','Photo Gallery',0,'This page is photo gallery. All image files that share the same folder with this page will be included.','photos',40,1546302678,'photo gallery','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(354,'classified-ads-item',287,750,'','Classified Ad',0,'As a member you can post your questions here.','',40,1548294420,'form item view','',1,1,'',1040,'Re: ^^item^^',0,0,'',0,'',1040,'Re: ^^item^^',0,1,1,'We\'re sorry. New comments are no longer being accepted.',1,0,1,0,'','',0,0,'Question / Answer','If you have a question for the owner, or you are the owner and have an answer, please post it here.','system',0),(1040,'classified-ads-comment-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(365,'blog-post',286,733,'','Blog Article',0,'Staff can edit the blog posting and visitors can add a comment.','blog, news, blog posts',40,1548293605,'form item view','',1,0,'example@example.com',414,'New Blog Comment:  ^^title^^ (^^newest_comment_name^^)',0,0,'',0,'Blog Comment:  ^^title^^',414,'New Blog Comment:  ^^title^^ (^^newest_comment_name^^)',1,1,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(579,'mailing-list-confirmation',286,718,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1014,'blog-post-no-sidebar',286,735,'','Blog Article',0,'Staff can edit the blog posting and visitors can add a comment.','blog, news, blog posts',40,1548293614,'form item view','',1,0,'example@example.com',414,'New Blog Comment:  ^^title^^ (^^newest_comment_name^^)',0,0,'',0,'Blog Comment:  ^^title^^',414,'New Blog Comment:  ^^title^^ (^^newest_comment_name^^)',1,1,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(394,'collect-dues',286,714,'','Collect Dues',0,'This order form page collect dues online from your membership.','',40,1548293565,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(395,'new-support-ticket',291,714,'','New Support Ticket',0,'','',62,1548294916,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(396,'support-ticket-confirmation-email',123,741,'','',0,'','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(399,'support-ticket-confirmation',291,714,'','New Support Ticket Confirmation',0,'','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(401,'my-support-tickets',290,744,'','My Support Tickets',0,'Support tickets that match your account\'s e-mail address.','',40,1548291729,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(402,'my-support-ticket',290,718,'','My Support Ticket',0,'','',40,1547838196,'form item view','',1,1,'example@example.com',403,'Re: ^^subject^^ (Support Ticket #^^reference_code^^)',0,0,'',0,'Re: ^^subject^^ (Support Ticket #^^reference_code^^)',403,'Re: ^^subject^^ (Support Ticket #^^reference_code^^)',0,1,1,'We\'re sorry. New comments are no longer being accepted.',1,0,1,0,'','',1,0,'Reply','','system',0),(403,'support-ticket-update-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'A comment has been added to your conversation.',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(407,'forum',286,714,'','Forum',0,'Join the most recent discussions.','',40,1548294391,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(408,'forum-thread-form',290,714,'','Forum - New Thread Form',0,'Start a new forum thread.','',40,1547838064,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(409,'forum-thread-view',286,714,'','Forum Thread',0,'','forum search',40,1547848829,'form item view','',1,1,'',0,'',0,0,'',0,'',433,'Re: ^^subject^^',1,1,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Reply','','system',0),(1013,'blog-no-sidebar',286,735,'','Blog',0,'Add any number of blogs to your site.','blog, news, blog posts',40,1548293574,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(412,'forum-thread-email',123,741,'','',0,'','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(414,'blog-new-comment-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(415,'new-comment-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(417,'trial-membership-form',286,714,'','Trial Membership',0,'Use this form to be granted access to our Members Portal for 30 days.','',40,1547848504,'custom form','trial',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(418,'members-access',286,714,'','Membership & Renewal',0,'Purchase or renew your membership access online now.','',40,1548293584,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(433,'forum-thread-reply-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(637,'egift-card-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(638,'forum-my-forum',286,714,'','Forum',0,'Join the discussion you created, participated in, or are watching.','',40,1548294479,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(639,'order-services',286,714,'','Order Services',0,'Use this form to either start a new services project or add credits to an existing services project.','',40,1548293670,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(435,'blog-widget',295,752,'','',0,'','',40,1548112282,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(437,'news-1-select',288,740,'','Send News',0,'Here\'s a niffy way to send submitted form data.','',40,1548110592,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(438,'news-2-prepare',288,740,'','E-Mail Campaign Process: Step 2',0,'Prepare the select the blog posting you wish to email.','',40,1547838196,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,340,'','',0,0,'','','system',0),(439,'news-3-email',123,741,'','E-Mail Campaign Process: Step 3',0,'Send the page as an email campaign.','',40,1548112300,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(496,'staff-create-quote',288,740,'','Create Quote',0,'This order form includes all product so that they can be selected and a saved cart link send to a customer.','',40,1548294192,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(472,'mailing-list-widget',295,752,'','Mailing List Widget',0,'','',40,1547848829,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(477,'donation-receipt',286,718,'','Donation Receipt',0,'','',40,1548295180,'order receipt','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(479,'buy-tickets',286,761,'','Buy Tickets',0,'This page displays all the events that tickets are available for.','',40,1548294730,'calendar view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(480,'buy-tickets-event',286,714,'','Ticket Event Details',0,'','',40,1548293627,'calendar event view','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to a calendar event.',415,'A new comment was just added to an event you are interested in.',1,1,1,'We\'re sorry. New reviews are no longer being accepted.',0,0,1,0,'','',0,0,'Review','Did you go to this concert? Let everyone know how you liked it!','system',0),(481,'schedule-training',286,718,'','Schedule Training',0,'Schedule a one hour training session with our professionals.','',40,1547836781,'calendar view','reservations',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(482,'schedule-training-event',286,714,'','Schedule Training',0,'','',40,1547848829,'calendar event view','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to a calendar event.',415,'A new comment was just added to a calendar event.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(483,'class-registration',286,718,'','Class Registration',0,'This page displays all the class registration events available.','',40,1547847163,'calendar view','class registration',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'<h3 class=\"bold italic uppercase fade-3-4\" style=\"text-align: center;\">Class Schedule</h3>\n\n<p>&nbsp;</p>','',0,0,'Comment','','system',0),(485,'service-plans',286,714,'','Service Plans',0,'Sign up for one of our recurring service plans and we will automatically bill your payment card each month.','',40,1548293547,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(484,'class-registration-event',286,714,'','Class Registration',0,'','',40,1547848829,'calendar event view','',0,1,'example@example.com',0,'',0,0,'',0,'A new comment was just added to a calendar event.',415,'A new comment was just added to a calendar event.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(506,'order-exam',286,714,'','Order Exam',0,'See if you have the skills to succeed. Order today!','',40,1548293488,'order form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(491,'members-access-renewal-email',123,741,'','Member Access Renewal Email',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(489,'trial-membership-confirmation',287,750,'','Membership Trial Form Confirmation',0,'This page is displayed after the trial membership form page is submitted.','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(508,'mailing-list-alert-email',123,741,'','',0,'','',40,1548110312,'form list view','',0,1,'',0,'A comment has been added to your conversation.',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(538,'video-gallery-form',288,740,'','',0,'','',40,1547848829,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'','','system',0),(539,'video-gallery',286,718,'','Video Gallery',0,'Enjoy our video library.','',40,1548294727,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(540,'video-gallery-item',288,740,'','',0,'','',40,1548294824,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,328,'','',0,0,'','','system',0),(541,'staff-latest-activity',288,740,'','Staff Latest Activity',0,'Form View Directory that displays the latest activity across the site filtered by the viewers access.','',40,1548292436,'form view directory','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(568,'members-directory',287,750,'','Members Directory',0,'Connect with other members and keep your own entry up-to-date.','',40,1548110443,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(558,'my-account-content',219,714,'','My Content',0,'Using a Folder View Page Type, each User can see all content they have access too within the Parent Folder specified.','',40,1547837134,'folder view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'<h3>My Content</h3>','',0,0,'Comment','','system',0),(1028,'my-services-projects',290,714,'','My Services Projects',0,'Services Projects that match your account\'s e-mail address.','',40,1548112316,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(584,'buy-tickets-wait-list',286,714,'','Ticket Wait List Form',0,'This form is linked to from the Calendar Event for ticket sales for the Concert in the Park Event.','',40,1547838064,'custom form','',0,1,'example@example.com',0,'',0,0,'',0,'A new comment was just added to a Wait List form.',415,'A new comment was just added to an event Wait List you submitted.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1029,'my-services-project',290,718,'','',0,'','',62,1548294365,'form item view','',1,1,'example@example.com',1031,'Re: ^^subject^^',0,0,'',0,'Re: ^^subject^^',1031,'Re: ^^subject^^',0,1,1,'We\'re sorry. New comments are no longer being accepted.',1,0,1,0,'','',1,0,'Reply','','system',0),(597,'forum-most-popular',286,714,'','Forum',0,'Join the most popular discussions.','',40,1548294447,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(598,'forum-most-active',286,714,'','Forum',0,'Join the most active discussions.','',40,1548294465,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(647,'my-conversations',290,714,'','My Conversations',0,'Contact forms that match your account\'s e-mail address or you are added as a watcher.','',40,1548295026,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(648,'my-conversation',290,718,'','My Conversation',0,'','',40,1547838196,'form item view','',1,1,'example@example.com',649,'Re: ^^subject^^',0,0,'',0,'Re: ^^subject^^',649,'Re: ^^subject^^',0,1,1,'We\'re sorry. New comments are no longer being accepted.',1,0,1,0,'','',1,0,'Reply','','system',0),(649,'conversation-update-email',123,741,'','Conversation Update',0,'This page is emailed to the moderator and submitter when a reply is added.','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(654,'ebook-offer-dialog',286,400,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(656,'mailing-list-offer-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(655,'ticket-discount-dialog',286,400,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(964,'home-6',286,710,'','',0,'','',40,1548195751,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(979,'my-account-change-password',167,714,'','',0,'','',40,1548294915,'change password','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(863,'home-1',286,608,'yes','',0,'','',40,1548291684,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(866,'home-agency',286,611,'','',0,'','',40,1548196658,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(867,'home-software',286,612,'','',0,'','',40,1548270381,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(868,'home-product',286,613,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(869,'home-firm',286,614,'','',0,'','',40,1547836781,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(965,'home-training',286,711,'','',0,'','',40,1547847799,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(971,'my-account',167,718,'','',0,'','',40,1548294943,'my account','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(873,'home-music',286,618,'','',0,'','',40,1547836781,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(874,'home-event',286,619,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(877,'home-agency-2',286,622,'','',0,'','',40,1547836781,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(881,'home-giftshop',286,626,'','',0,'','',40,1547836781,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(882,'home-2',286,627,'','',0,'','',40,1548184095,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(883,'home-3',286,628,'','',0,'','',40,1548292385,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(884,'home-4',286,629,'','',0,'','',40,1548191763,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(885,'home-5',286,630,'','',0,'','',40,1548192083,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(886,'about-us',286,631,'','About Us',0,'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','',40,1547846760,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1059,'coming-soon',286,760,'','Coming Soon',0,'This page can be used as the \'home\' page of your site when you are building your site.','',40,1548293076,'custom form','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1060,'coming-soon-confirmation',286,760,'','',0,'','',40,1548293076,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(895,'user-login',125,760,'','',0,'','',40,1548294017,'login','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1062,'blog-posts-widget',295,757,'','',0,'','',40,1548112236,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'<h3 class=\"bold italic uppercase fade-3-4\" style=\"text-align: center;\">Latest Blog Posts</h3>\n\n<p>&nbsp;</p>','',0,0,'Comment','','custom',1),(902,'site-error',125,717,'','',0,'','',40,1547838678,'error','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1052,'design-elements',288,754,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(936,'case-study',286,681,'','Case Study',0,'This is a page that displays a case study.','',40,1547847129,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1049,'photo-gallery-widget',189,752,'','Photo Gallery',0,'This page is photo gallery. All image files that share the same folder with this page will be included.','photos',40,1547836150,'photo gallery','',0,1,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1050,'slider-gallery-widget',294,752,'','',0,'','',40,1547848829,'photo gallery','',0,1,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1064,'staff-directory-widget',286,757,'','',0,'','',40,1548110487,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1063,'upcoming-concerts-widget',295,758,'','',0,'','',40,1547836150,'calendar view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(960,'user-logout',125,640,'','',0,'','',40,1548292564,'logout','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1045,'calendar-widget',295,752,'','',0,'','',40,1547836150,'calendar view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1046,'all-staff-directory',288,740,'','Staff Directory',0,'A directory of all our staff.','',40,1548292777,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1047,'all-staff-directory-item',288,740,'','Staff Directory Item',0,'A directory of our staff.','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(974,'user-login-register-confirmation',167,718,'','',0,'','',40,1547838638,'registration confirmation','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(975,'my-account-profile',167,714,'','',0,'','',40,1548294388,'my account profile','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(978,'my-account-email-preferences',125,714,'','',0,'','',40,1548293238,'email preferences','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(996,'photo-gallery-2col-wide',87,727,'','Photo Gallery',0,'This page is photo gallery. All image files that share the same folder with this page will be included.','photos',40,1546302678,'photo gallery','',1,0,'example@example.com',0,'',0,0,'',0,'A new comment was just added to the photo gallery.',415,'A new comment was just added to the photo gallery.',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(997,'shop-sidebar-right',286,720,'','Shop',0,'Browse or search our catalog of products.','',40,1548293649,'catalog','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(998,'shop-product-sidebar-right',286,720,'','',0,'','',40,1548291986,'catalog detail','',1,0,'example@example.com',0,'',0,0,'',0,'Please approve this new product review.',0,'',1,0,1,'',0,0,0,0,'','',0,0,'Product Review','If you own this product, please tell others what you think about it.','custom',1),(999,'shop-fullwidth-4col',286,720,'','Shop',0,'Browse or search our catalog of products.','',40,1548293660,'catalog','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1000,'shop-fullwidth',286,720,'','Shop',0,'Browse or search our catalog of products.','',40,1548293669,'catalog','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1001,'shop-fullwidth-2col',286,720,'','Shop',0,'Browse or search our catalog of products.','',40,1548293678,'catalog','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1002,'shop-product-fullwidth',286,720,'','',0,'','',40,1548292344,'catalog detail','',1,0,'example@example.com',0,'',0,0,'',0,'Please approve this new product review.',0,'',1,0,1,'',0,0,0,0,'','',0,0,'Product Review','If you own this product, please tell others what you think about it.','custom',1),(1003,'contact-us',286,729,'','Contact Us',0,'Use this form to contact our Sales team.','',40,1548292351,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1015,'blog-cards',286,738,'','Blog',0,'Add any number of blogs to your site.','blog, news, blog posts',40,1548293584,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1016,'blog-cards-no-sidebar',286,739,'','Blog',0,'Add any number of blogs to your site.','blog, news, blog posts',40,1548293594,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1031,'services-project-update-email',123,741,'','',0,'','',40,1547848829,'standard','',0,1,'',0,'A comment has been added to your conversation.',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1032,'services-project-confirmation-email',123,741,'','',0,'','',40,1547848829,'form item view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1057,'support',286,634,'','Support',0,'Outstanding Customer Support.','',40,1548292141,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1058,'services',286,636,'','Services',0,'We offer several professional services to help you.','',40,1547848598,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1071,'order-assistance-email',123,741,'','',0,'','',40,1548293352,'standard','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','system',0),(1077,'order-preview',286,718,'','Preview and Payment Page',0,'','',40,1548294726,'order preview','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1074,'my-classified-ads',287,750,'','My Classified Ads',0,'This page displays all the classified ads posted by the user that is viewing this page.','',40,1548293808,'form list view','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1),(1076,'order-billing-information',286,718,'','',0,'','',40,1548293146,'billing information','',0,1,'',0,'',0,0,'',0,'',0,'',1,0,1,'We\'re sorry. New comments are no longer being accepted.',0,0,1,0,'','',0,0,'Comment','','custom',1);
/*!40000 ALTER TABLE `page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photo_gallery_pages`
--

DROP TABLE IF EXISTS `photo_gallery_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photo_gallery_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_columns` int(10) unsigned NOT NULL DEFAULT '0',
  `thumbnail_max_size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photo_gallery_pages`
--

LOCK TABLES `photo_gallery_pages` WRITE;
/*!40000 ALTER TABLE `photo_gallery_pages` DISABLE KEYS */;
INSERT INTO `photo_gallery_pages` VALUES (1,342,8,50),(2,343,0,0),(10,994,0,0),(11,995,0,0),(12,996,0,0),(13,1049,0,0),(14,1050,0,0);
/*!40000 ALTER TABLE `photo_gallery_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pregion`
--

DROP TABLE IF EXISTS `pregion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pregion` (
  `pregion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pregion_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pregion_content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `pregion_page` int(10) unsigned DEFAULT NULL,
  `pregion_order` int(2) DEFAULT NULL,
  `pregion_user` int(10) unsigned DEFAULT NULL,
  `pregion_timestamp` int(10) unsigned DEFAULT NULL,
  `collection` enum('a','b') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'a',
  PRIMARY KEY (`pregion_id`),
  KEY `pregion_page` (`pregion_page`),
  KEY `pregion_order` (`pregion_order`),
  KEY `pregion_page_collection` (`pregion_page`,`collection`)
) ENGINE=MyISAM AUTO_INCREMENT=2884 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pregion`
--

LOCK TABLES `pregion` WRITE;
/*!40000 ALTER TABLE `pregion` DISABLE KEYS */;
INSERT INTO `pregion` VALUES (687,'1192473569_4','',222,4,40,1543875869,'a'),(489,'1129748774_1','<h3 class=\"uppercase\">Affiliate Sign Up</h3>',169,1,40,1548293924,'a'),(2512,'1459431318_6','',169,6,40,1539271496,'a'),(2515,'1459431462_6','',180,6,40,1539271618,'a'),(490,'1129748774_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',169,2,40,1542397950,'a'),(491,'1129748774_3','<p class=\"lead\" style=\"text-align: center;\">Earn one-time or recurring commissions when your friends buy from us.</p>',169,3,40,1542398007,'a'),(492,'1129748774_4','',169,4,2,1537472973,'a'),(527,'1140311429_1','<h3 class=\"uppercase\">Affiliate Confirmation</h3>',180,1,40,1542400966,'a'),(2514,'1459431431_6','<p><strong>Thank You!&nbsp;</strong> We have received your application&nbsp;and we will review it and&nbsp;get back to you within the next few business days with your approval status and additional information.</p>\n\n<p>A copy of this application has been e-mailed to you.</p>',180,6,40,1539271618,'a'),(531,'1140311551_1','<h3 class=\"uppercase\">Affiliate Notification</h3>',181,1,40,1542402062,'a'),(528,'1140311429_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',180,2,40,1542400885,'a'),(529,'1140311429_3','',180,3,1,1537472973,'a'),(532,'1140311551_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',181,2,40,1542402022,'a'),(533,'1140311551_3','<h4>Welcome to the Affiliate Program</h4>\n\n<p>We have approved your Affiliate Application and&nbsp;you are ready to invite your business associates, friends, and others from your&nbsp;Website and your mailing list to visit our site and purchase our products.</p>\n\n<p>Your commissions are tracked by your Affiliate Code (below). By adding your Affiliate Code to the end of any&nbsp;link&nbsp;you place to&nbsp;our Website, we will track any purchases made through your Affiliate Link- even if purchaser&nbsp;leaves our site and returns&nbsp;at a later date. This ensures that you get credit for their purchases, even if they return by clicking a link without your Affiliate Code or another Affiliate&#39;s Link!</p>\n\n<p><strong>Add the Affiliate Link below to your&nbsp;newsletters and to&nbsp;your Website today and&nbsp;begin making commissions!</strong></p>',181,3,40,1545274056,'a'),(686,'1192473569_3','<p class=\"lead\" style=\"text-align: center;\">Thank you! We have received your information and will follow up with you shortly.</p>',222,3,40,1543874028,'a'),(660,'1192473255_1','<h3 class=\"uppercase\">Site Search</h3>',216,1,40,1537916848,'a'),(661,'1192473255_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}home\">Home</a></li>\n</ul>',216,2,40,1538086926,'a'),(662,'1192473255_3','<p class=\"lead\" style=\"text-align: center;\">Find any content that you have access too. All pages, products, and even file attachments will be searched.</p>',216,3,40,1545868395,'a'),(663,'1192473255_4','',216,4,40,1537994922,'a'),(2693,'1465244608_6','',482,6,40,1545084763,'a'),(2692,'1465244593_6','',482,6,40,1545084747,'a'),(2688,'1465235638_6','',484,6,40,1545075810,'a'),(2689,'1465235671_6','',484,6,40,1545075824,'a'),(2690,'1465235685_6','<h6 class=\"heading-title\">About Class Registration</h6>\n\n<p>If there is still a remaining seat in this class, and the class is not over yet, the &#39;Reserve My Seat&#39; button will appear for you to reserve your seat by placing an order.</p>',484,6,40,1545081774,'a'),(2311,'1458160600_1','',902,1,40,1543455403,'a'),(2359,'1458316052_4','',979,4,40,1538156191,'a'),(2415,'1458750039_3','<p class=\"lead\" style=\"text-align: center;\">Put this page in any Folder and all images in that Folder and sub folders will be displayed here.</p>',996,3,40,1538590178,'a'),(393,'1124812360_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}cart\">Shopping Cart</a></li>\n</ul>',144,2,40,1545763513,'a'),(394,'1124812360_3','',144,3,40,1548294008,'a'),(956,'1192552461_1','<h3 class=\"uppercase\">New Classified Ad</h3>',290,1,40,1544638472,'a'),(2668,'1464798710_6','',354,6,40,1544638864,'a'),(957,'1192552461_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}classified-ads\">Classified Ads</a></li>\n</ul>',290,2,40,1544638544,'a'),(958,'1192552461_3','<p class=\"lead\" style=\"text-align: center;\">As a member, you can post a classified ad for other members to see.</p>',290,3,40,1544638453,'a'),(959,'1192552461_4','',290,4,1,1537472973,'a'),(960,'1192552490_1','<h3 class=\"uppercase\">exam</h3>',291,1,40,1538953264,'a'),(961,'1192552490_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',291,2,40,1542667591,'a'),(962,'1192552490_3','',291,3,40,1538953519,'a'),(2484,'1458921784_6','',291,6,40,1538762010,'a'),(963,'1192552490_4','',291,4,40,1538953584,'a'),(964,'1192552529_1','<h3 class=\"uppercase\">Exam Confirmation</h3>',292,1,40,1542660144,'a'),(965,'1192552529_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',292,2,40,1545274173,'a'),(966,'1192552529_3','<h3 class=\"lead\" style=\"text-align: center;\">Congratulations! You passed the exam with the required 100% correct score!</h3>\n\n<p class=\"lead\" style=\"text-align: center;\"><strong>Please save this email as proof of your exam score.</strong></p>',292,3,40,1542660248,'a'),(967,'1192552529_4','',292,4,40,1542660100,'a'),(995,'1192553530_4','',299,4,40,1543514558,'a'),(994,'1192553530_3','<p class=\"lead\" style=\"text-align: center;\">Click on the sales conversation to add a reply which will be emailed to the project owner and all participants.</p>',299,3,40,1543514519,'a'),(993,'1192553530_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',299,2,40,1542407934,'a'),(992,'1192553530_1','<h3 class=\"uppercase\">All Conversations</h3>',299,1,40,1542407864,'a'),(972,'1192552878_1','<h3 class=\"uppercase\">Staff Home</h3>',294,1,40,1539194710,'a'),(2541,'1460564934_6','',294,6,40,1541104068,'a'),(2642,'1463782772_6','',85,6,40,1543623626,'a'),(2643,'1463783118_6','<h6 class=\"heading-title\">Conversations</h6>\n\n<p>Conversations are threaded discussions that are private between you and our sales team. If you have a question, you can quickly start a new conversation with us at anytime. So you won&#39;t miss anything, we will send you an email notice of new conversations, or when any comments are added to your conversations.</p>\n\n<p><strong>Why Conversations?</strong><br />\nWe use Conversations to move scattered dialogues from cluttered inboxes.&nbsp; Never again will you have to keep up with our email responses! You can also login to our website and view the history of all your Conversations.</p>',85,6,40,1543623257,'a'),(2542,'1460564941_6','<h6 class=\"heading-title\">About Staff Portal</h6>\n\n<p>When you first log in, we will redirect you here (using the User &#39;Start Page&#39; feature).&nbsp;This area is a protected intranet portal for you and your organization. We created it using the built-in features.</p>',294,6,40,1548294244,'a'),(2549,'1461024047_6','',283,6,40,1540864226,'a'),(973,'1192552878_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',294,2,40,1539194729,'a'),(1533,'1286381564_1','<h3 class=\"uppercase\">Send News</h3>',437,1,40,1544163418,'a'),(1534,'1286381564_2','',437,2,2,1537472973,'a'),(1535,'1286381564_3','<p class=\"lead\" style=\"text-align: center;\">Although you can send any page as an e-mail campaign,&nbsp;here&#39;s a niffy way to send the last modified blog posting in an email campaign.</p>',437,3,40,1544163600,'a'),(1536,'1286381564_4','<h5 style=\"text-align: left;\">Step 1. Below is the last modified Blog Posting. Click on it.</h5>\n\n<p>HINT: If you want to send another blog posting, browse the <a href=\"{path}blog\">blog</a>, click on the blog posting (to view the blog posting page where the blog posting comments and edit button appears) and Edit the Blog posting and resave it. &nbsp;This will change the blog posting&#39;s last modified date and time. Once you have done this, the blog posting will appear below.</p>',437,4,40,1544163616,'a'),(974,'1192552878_3','<p class=\"lead\" style=\"text-align: center;\">Welcome to the&nbsp;protected intranet staff portal for you and your organization.</p>',294,3,40,1543595361,'a'),(975,'1192552878_4','<h2>Welcome to liveSite!</h2>\n\n<p>To speed up your learning curve and get you ready to launch, we&#39;ve already created dozens of &quot;apps&quot; ready for you to customize as your own, including a blog, shop, sales and support conversations, mailing list, forum, classified ads, membership portal, and much more! We built the whole site using built-in liveSite features. Of course, you can tweak anything or build new &quot;apps&quot; we haven&#39;t even thought of yet!</p>\n\n<h4 style=\"text-align: center;\"><span class=\"ti-direction-alt\">&nbsp;</span> Before you make any changes, please watch these videos.</h4>\n\n<p class=\"embed-responsive\" style=\"text-align: center;\"><iframe allowfullscreen=\"\" frameborder=\"0\" src=\"https://www.youtube.com/embed/videoseries?list=PLqTc84xcP7z4KZAoUxwV9zaLoZdeIyjZM\"></iframe></p>',294,4,40,1548293970,'a'),(980,'1192553326_1','<h3 class=\"uppercase\">Staff Directory Form</h3>',296,1,40,1543946303,'a'),(981,'1192553326_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',296,2,40,1547059443,'a'),(982,'1192553326_3','<p class=\"lead\" style=\"text-align: center;\">Add another entry to your <a href=\"{path}all-staff-directory\">All Staff Directory</a> (and optionally the public <a href=\"{path}staff-directory\">Staff Directory</a>).</p>',296,3,40,1547059393,'a'),(983,'1192553326_4','',296,4,1,1537472973,'a'),(984,'1192553349_1','<h3 class=\"uppercase\">Staff Calendar</h3>',297,1,40,1539143906,'a'),(985,'1192553349_2','<ul class=\"breadcrumb\">\n	<li><a class=\"link-button-secondary-small\" href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',297,2,40,1539143906,'a'),(986,'1192553349_3','<p class=\"lead\" style=\"text-align: center;\">This calendar page will show all events from the Staff, Class, Members, and Public Calendars.</p>',297,3,40,1539143906,'a'),(987,'1192553349_4','',297,4,2,1537472973,'a'),(1712,'1310875653_3','<p class=\"lead\" style=\"text-align: center;\">Sign up for one of our recurring service plans and we will automatically bill your payment card each billing period.</p>',485,3,40,1539215546,'a'),(988,'1192553372_1','<h3 class=\"uppercase\">Staff Event</h3>',298,1,40,1539194302,'a'),(989,'1192553372_2','',298,2,2,1537472973,'a'),(990,'1192553372_3','<p class=\"lead\" style=\"text-align: center;\">This this event is from the Staff, Class, Members, or Public Calendar. Optional event notes are visible on this page.</p>',298,3,40,1539144728,'a'),(991,'1192553372_4','',298,4,1,1537472973,'a'),(256,'1087175450_1','<h3 class=\"uppercase\">New Conversation</h3>',85,1,40,1543622161,'a'),(257,'1087175450_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a href=\"{path}my-conversations\">My Conversations</a></li>\n</ul>',85,2,40,1543623233,'a'),(1462,'1275512253_4','',85,4,40,1543625905,'a'),(390,'1124778654_3','<p class=\"lead\" style=\"text-align: center;\">Thank you! We have received your information and will follow up with you shortly.</p>',85,3,40,1543625905,'a'),(283,'1100618404_2','<ul class=\"breadcrumb\">\n	<li><a href=\"#\" onclick=\"close_window();return false;\">Close Window</a></li>\n</ul>',98,2,40,1547762568,'a'),(401,'1124812761_3','<h3>Add your purchase and return policies here...</h3>\n\n<p>To make these terms required for purchase, link this page into your Order Preview and Express Order pages (set within their Page Properties).</p>',98,3,40,1542645626,'a'),(392,'1124812360_1','<h3 class=\"uppercase\">Shipping &amp; Arrival</h3>',144,1,40,1538954978,'a'),(452,'1125022405_4','',126,4,1,1537472973,'a'),(288,'1100752726_1','<h3 class=\"uppercase\">Preview &amp; PaymenT</h3>',101,1,40,1541781550,'a'),(400,'1124812577_3','',101,3,2,1537472973,'a'),(289,'1100752726_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}cart\">Shopping Cart</a></li>\n</ul>',101,2,40,1545763428,'a'),(2602,'1462904100_1','<h3 class=\"uppercase\">My Services Projects</h3>',1028,1,40,1542744289,'a'),(2603,'1462904100_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',1028,2,40,1542744239,'a'),(1382,'1258130594_1','<h3 class=\"uppercase\">New Support Ticket</h3>',399,1,40,1539965103,'a'),(2534,'1460125136_6','',399,6,40,1539965335,'a'),(2535,'1460125199_6','<h6 class=\"heading-title\">Support Ticket Confirmation</h6>\n\n<p>A copy of your request has been sent to your e-mail address.</p>\n\n<p>You will receive any email when a reply is added to your support ticket by our support team.</p>\n\n<p>If there is any additional information you would like to include with your request, including file attachments, you can do so by adding a reply at any time.</p>',399,6,40,1543521723,'a'),(1398,'1258132094_1','<h3 class=\"uppercase\">Support Ticket Update</h3>',403,1,40,1542727763,'a'),(1383,'1258130594_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-primary-large\" href=\"{path}my-support-tickets\">My Support Tickets</a></li>\n</ul>',399,2,40,1539965139,'a'),(1384,'1258130594_3','<p class=\"lead\" style=\"text-align: center;\">We have received your support request and will follow up with you shortly.</p>',399,3,40,1543521510,'a'),(1385,'1258130594_4','',399,4,40,1543521723,'a'),(1390,'1258131168_1','<h3 class=\"uppercase\">My Support Tickets</h3>',401,1,40,1539820275,'a'),(1391,'1258131168_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',401,2,40,1539820295,'a'),(1392,'1258131168_3','<p class=\"lead\" style=\"text-align: center;\">Support Tickets you have submitted (or you have been added as a watcher to participate).</p>',401,3,40,1543514103,'a'),(292,'1100753156_1','<p>New Releases<hr /></p><p /><p /><p><table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p align=\"center\"><a href=\"{path}body_by_God\"><img hspace=\"0\" src=\"{path}bodyforgod-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>Body By God</strong><br /><em>by Lisa Young</em></a></p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p align=\"center\"><img hspace=\"0\" src=\"{path}canwedothat-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>Can We Do That?</strong><br /><em>by Ed Young</em></p></td></tr><tr><td><p align=\"center\"><br /><br /><img hspace=\"0\" src=\"{path}fataldistractions-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>Fatal Distraction</strong><br /><em>by Ed Young</em></p></td><td><p align=\"center\"><br /><br /><img hspace=\"0\" src=\"{path}hifi-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>High Definition Living</strong><br /><em>by Ed Young</em></p></td></tr><tr><td><p align=\"center\"><br /><br /><img hspace=\"0\" src=\"{path}kidceo-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>Kid CEO</strong><br /><em>by Ed Young</em></p></td><td><p align=\"center\"><br /><br /><img hspace=\"0\" src=\"{path}you-sm.jpg\" align=\"baseline\" border=\"0\" /><br /><strong>You!</strong><br /><em>by Ed Young</em></p></td></tr></tbody></table></p>',103,1,41,1537472973,'a'),(403,'1124814733_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',103,3,41,1537472973,'a'),(293,'1100753156_2','',103,2,41,1537472973,'a'),(282,'1100618404_1','<h3 class=\"uppercase\">Order Terms</h3>',98,1,40,1542645626,'a'),(404,'1124815516_1','',146,1,41,1537472973,'a'),(405,'1124815516_2','<p />',146,2,41,1537472973,'a'),(406,'1124815516_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',146,3,41,1537472973,'a'),(568,'1177690891_1','<h3 class=\"uppercase\">Calendar</h3>',191,1,40,1545088712,'a'),(569,'1177690891_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}calendar\">Calendar</a></li>\n</ul>',191,2,40,1545088730,'a'),(570,'1177690891_4','',191,4,40,1545089654,'a'),(2360,'1458316134_1','<h3 class=\"uppercase\">Set New Password</h3>',980,1,40,1548295085,'a'),(2337,'1458251261_3','',975,3,40,1548293838,'a'),(2338,'1458251261_4','',975,4,40,1538091400,'a'),(2339,'1458251261_5','',975,5,40,1538091400,'a'),(524,'1130359153_1','<h3 class=\"uppercase\">Billing Information</h3>',179,1,40,1548294044,'a'),(2333,'1458241683_3','<h3>Welcome!</h3>\n\n<p>We just created a user account for you and you are now logged in!</p>',974,3,40,1545275550,'a'),(2334,'1458241683_4','',974,4,40,1538081822,'a'),(2336,'1458251261_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',975,2,40,1538091681,'a'),(2665,'1464797439_6','',284,6,40,1544637607,'a'),(1791,'1311194206_1','<h3 class=\"uppercase\">Order Exam</h3>',506,1,40,1541185570,'a'),(1792,'1311194206_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',506,2,40,1538762480,'a'),(904,'1192490733_1','<h3 class=\"uppercase\">View Order</h3>',277,1,40,1539195093,'a'),(905,'1192490733_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',277,2,40,1539195093,'a'),(1663,'1310767978_1','<h3 class=\"uppercase\">MAiling List Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',472,1,40,1545870751,'a'),(1664,'1310767978_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',472,2,40,1545870600,'a'),(1665,'1310767978_3','<h6 class=\"heading-title\">Mailing List Widget</h6>\n\n<p>This widget is embedded in the Site Footer Designer Region.</p>\n\n<p>It will add the email to the &#39;Mailing List&#39; Contact Group. It also emails the submitter a personalized offer code for $3 off the eBook (but only if the email is a new email). Just a simple example of the power of liveSite Custom Form Workflows.</p>',472,3,40,1545891754,'a'),(1666,'1310767978_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;mailing-list-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors.</p>\n\n<p>Related Pages: <a href=\"{path}mailing-list-confirmation\">Mailing List Confirmation</a>&nbsp; / &nbsp; <a href=\"{path}mailing-list-offer-email\">Mailing List Offer Email</a>&nbsp; / &nbsp; <a href=\"{path}mailing-list-alert-email\">Mailing List Alert Email</a></p>',472,4,40,1545891659,'a'),(906,'1192490733_3','<p class=\"lead\" style=\"text-align: center;\">Click &#39;Reorder&#39; to add all these items to a new quote where you can then make any changes and purchase again.</p>',277,3,40,1542329310,'a'),(907,'1192490733_4','',277,4,1,1537472973,'a'),(2611,'1462904162_4','',1029,4,40,1542744301,'a'),(2616,'1462904376_1','<h3 class=\"uppercase\">Services Project Update</h3>',1031,1,40,1542744561,'a'),(2617,'1462904376_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-services-projects\">My Services Projects</a></li>\n</ul>',1031,2,40,1543279236,'a'),(2618,'1462904376_3','<p class=\"lead\">The following reply has been added to a Services Project of interest to you.</p>',1031,3,40,1543359986,'a'),(2619,'1462904376_4','<p>&nbsp;</p>\n\n<p><strong>Why am I getting this email?</strong></p>\n\n<p>You are either listed as the owner of this Services Project, or you have been give access to view and reply by the owner. If you wish to view, reply, or remove yourself as a watcher, all of these actions can be completed from the Services Project page itself. To access the protected Services Project page, you will need to follow the link above to register or login.</p>',1031,4,40,1543522218,'a'),(874,'1192490314_3','',269,3,40,1541710715,'a'),(2547,'1461023885_6','',251,6,40,1540864035,'a'),(2548,'1461023914_6','<h6 class=\"heading-title\">About Payments</h6>\n\n<p>You can use this form to collect payments from your clients that need to balance their account with you.</p>',251,6,40,1547832139,'a'),(875,'1192490314_4','',269,4,1,1537472973,'a'),(2667,'1464798184_6','',289,6,40,1544638410,'a'),(1696,'1310852962_1','<h3 class=\"uppercase\">Schedule Training</h3>',481,1,40,1545081514,'a'),(2691,'1465244568_6','<h6 class=\"heading-title\">About Training Sessions</h6>\n\n<p>You can book your training session instantly from our real-time training calendar. If this specific training session has not been booked by someone already, you will see a &#39;Book Session&#39; button to reserve the session.</p>',482,6,40,1545085013,'a'),(1738,'1310963589_1','<h3 class=\"uppercase\">All Support Tickets</h3>',492,1,40,1542407988,'a'),(536,'1140312109_2','',182,2,2,1537472973,'a'),(2648,'1464323086_4','',538,4,40,1544163225,'a'),(537,'1140312109_3','<p>This <span class=\"text-highlighter\">Sample Agreement</span> contains the complete terms and conditions that apply to an individual or company&#39;s participation in the My Organization Affiliate Referral Program (the &quot;Program&quot;). As used in this Agreement, &quot;Company&quot; means My Organization, Inc. (dba My Organization), Incorporated and &quot;Affiliate&quot; means the individual and/or company indicated in the registration form which by reference is made a part of this agreement.</p>\n\n<p><strong>Participation</strong><br />\nTo participate in the My Organization Affiliate Referral Program, the Affiliate registration form must be completed and submitted to My Organization. All applicants are immediately eligible to participate in the My Organization Affiliate Referral program however such participation is subject to ongoing, periodic review by My Organization and may be revoked at anytime without notice. Participation may be suspended or terminated if we determine (at our sole discretion) that your site is unsuitable for the Program. Unsuitable sites include those that:</p>\n\n<ul>\n	<li>Promote sexually explicit materials</li>\n	<li>Promote violence</li>\n	<li>Promote discrimination based on race, sex, religion, nationality, disability, sexual orientation, or age</li>\n	<li>Promote illegal activities</li>\n	<li>Violate intellectual property rights</li>\n	<li>Or are otherwise deemed inconsistent with the best interests of My Organization.</li>\n</ul>\n\n<p>This agreement shall apply only to Affiliate owned and managed web sites as indicated in the registration form. Under no circumstances may My Organization Affiliate Referral links be placed on sites not registered with My Organization.</p>\n\n<p><strong>Creating the Affiliate Link</strong><br />\nOnce you have completed the online application you will be presented with a set of guidelines and graphical artwork to use in linking to the <a href=\"http://www.myorganization.com/\">http://www.myorganization.com/</a> website. To permit accurate tracking, reporting, and affiliate referral fee accrual, we will provide you with a special link format which includes your Referrer ID number to be used in all links between your site and our <a href=\"http://www.myorganization.com/\">http://www.myorganization.com/</a> website. You must ensure that each of the links between your site and our site properly utilizes the exact link format and Referrer ID in order to obtain credit for any sales resulting from customers coming to the <a href=\"http://www.myorganization.com/\">http://www.myorganization.com/</a> website from your link. The My Organization Affiliate Referral program will capture the Affiliate&#39;s Referrer ID and the linking websites URL. You will only earn referral fees with sales on our site occurring directly through your My Organization Affiliate Referrer ID. My Organization will not be liable to you with respect to any failure by you to correctly configure the My Organization Affiliate Referral link, by any failure resulting from issues of internet connectivity, shopper behavior, web site or application failure on your server or our server or any other such action which may result in your affiliate referral not being credited for a sale including to the extent that such failure may result in any reduction of amounts which would otherwise be paid to you pursuant to this Agreement.</p>\n\n<p><strong>Bonifide Orders</strong><br />\nMy Organization will process all qualified orders placed by customers who follow My Organization affiliate links from your site to <a href=\"http://www.myorganization.com/\">http://www.myorganization.com/</a>. We reserve the right to reject orders that do not comply with any requirements that My Organization may from time to time establish. My Organization assumes responsibility for all aspects of order processing and fulfillment. My Organization will prepare and maintain all order forms; process payments, cancellations; and handle customer service. My Organization will track sales made to customers who purchase products using My Organization affiliate links from your site to our site and will make available to you reports summarizing this sales activity. Report forms, content, and frequency of the reports may vary from time to time at our discretion.</p>\n\n<p><strong>Referral Fees</strong><br />\nMy Organization will pay approved affiliate referral partners referral fees on designated Product sales to new My Organization customers. For a Product sale to be eligible to earn a referral fee, the customer must follow a My Organization Affiliate Link from your site to our site, select and purchase the Product during that shopping session, accept delivery of the Product, and remit full payment to us. We will not, however, pay referral fees on any Products that are purchased by a customer after the customer has re-entered our site via a means other than the My Organization Affiliate Link, even if the customer previously followed a link from your site to our site. Orders by Existing My Organization Customers and business associates are not eligible.</p>\n\n<p>You may not purchase products during sessions initiated through the links on your site for your own personal use. This includes orders for products to be used by you or your friends, relatives or associates in any manner. Such purchases may result (in our sole discretion) in the withholding of referral fees or the termination of this Agreement. Products that are eligible to earn referral fees under the rules set forth above are referred to as &quot;Qualifying Products.&quot; In addition, you may not: (a) directly or indirectly offer any person or entity any consideration or incentive (including, without limitation, payment of money (including any rebate), or granting of any discount or other benefit) for using My Organization Affiliate Links on your site to access our site (e.g., by implementing any &quot;rewards&quot; program for persons or entities who use My Organization Affiliate Links on your site to access our site); or (b) post any My Organization Affiliate Links on any Web site or other platform that is accessible through any Internet Access Appliance. If we determine, in our sole discretion, that you have offered any person or entity any such consideration or incentive, or posted My Organization Affiliate Links on any such Web site or platform, we may (without limiting any other rights or remedies available to us) withhold any referral fees otherwise payable to you under this Agreement and/or terminate this Agreement.</p>\n\n<p><strong>Existing Customers Exclusion</strong><br />\nExisting My Organization customers include (i) any individual or entity that had previously purchased My Organization products or services; (ii) any individual or entity that had previously been logged as a Sales Lead by signing up for the My Organization mailing list, demo registrations, or by directly contacting My Organization; (iii) any individual or entity that has an existing business relationship with My Organization or My Organization.</p>\n\n<p><strong>Referral Fee Schedule</strong><br />\nYou will earn referral fees based on qualifying revenues according to referral fee schedules to be established by us. &quot;qualifying revenues&quot; are revenues derived by us from our sales of qualifying products, excluding costs for shipping, handling, gift-wrapping, taxes, service charges, credit card processing fees, and bad debt. The current referral fee schedule is 30%.</p>\n\n<p><strong>Payment of Referral Commissions</strong><br />\nMy Organization will review all Affiliate Referral accounts approximately once each month. All accounts with affiliate referral fees totaling $25.00 or more will be paid. Payments will NOT include taxes, shipping costs, subsequently refunded products or services or fraudulent charges. Payment will be by check for the referral fees earned on our sales of Qualifying Products that were purchased during that month. However, if the referral fees payable to you for any monthly period are less than $25.00, we will hold those referral fees until the total amount due is at least $25.00 or (if earlier) until this Agreement is terminated. If a Product that generated a referral fee is returned by the customer, we will deduct the corresponding referral fee from your next monthly payment. If there is no subsequent payment, we will send you a bill for the referral fee.</p>\n\n<p><strong>Qualifying Products</strong><br />\nQualifying My Organization Products or Services includes the following:</p>\n\n<ul>\n	<li>Product A</li>\n	<li>Product B</li>\n</ul>\n\n<p><strong>Policies and Pricing</strong><br />\nCustomers who buy products through this Program will be deemed to be customers of My Organization, Incorporated Accordingly, all My Organization rules, policies, and operating procedures concerning customer orders, customer service, and product sales will apply to those customers. We may change our policies and operating procedures at any time. For example, we will determine the prices to be charged for products sold under this Program in accordance with our own pricing policies. Product prices and availability may vary from time to time. Because price changes may affect products that you already have listed on your site, you may not include price information in your product descriptions. We will use commercially reasonable efforts to present accurate information, but we cannot guarantee the availability or price of any particular product.</p>\n\n<p><strong>Identifying Yourself as a My Organization Affiliate Referrer</strong><br />\nWe will make available to you a small graphic image that identifies your site as a Program participant. You must display this logo or the phrase &quot;MyProductA Partner&quot; or &quot;MyProductB Partner&quot; somewhere on your site. We may modify the text or graphic image of this notice from time to time. You may not make any press release with respect to this Agreement or your participation in the Program without our prior written consent, which may be given or withheld in our sole discretion. In addition, you may not in any manner misrepresent or embellish the relationship between us and you, or express or imply any relationship or affiliation between us and you or any other person or entity except as expressly permitted by this Agreement (including by expressing or implying that My Organization supports, sponsors, endorses or contributes money to any charity or other cause).</p>\n\n<p><strong>Limited License</strong><br />\nWe grant you a nonexclusive, revocable right to use the graphic image and text and such other images for which we grant express permission, solely for the purpose of identifying your site as a My Organization Affiliate Program participant and to assist in generating product sales. You may not modify the graphic image or text, or any other of our images, in any way. We reserve all of our rights in the graphic image and text, any other images, our trade names and trademarks, and all other intellectual property rights. You agree to follow our Trademark Guidelines, as those guidelines may change from time to time. We may revoke your license at any time by giving you written notice.</p>\n\n<p><strong>Responsibility for Your Site</strong><br />\nYou will be solely responsible for the development, operation, and maintenance of your site and for all materials that appear on your site. For example, you will be solely responsible for:</p>\n\n<ul>\n	<li>The technical operation of your site and all related equipment</li>\n	<li>Creating and posting Product descriptions on your site and linking those descriptions to our catalog</li>\n	<li>The accuracy and appropriateness of materials posted on your site (including, among other things, all Product-related materials)</li>\n	<li>Ensuring that materials posted on your site do not violate or infringe upon the rights of any third party (including, for example, copyrights, trademarks, privacy, or other personal or proprietary rights)</li>\n	<li>Ensuring that materials posted on your site are not libelous or otherwise illegal</li>\n</ul>\n\n<p><strong>Liability</strong><br />\nWe disclaim all liability for these matters. Further, you will indemnify and hold us harmless from all claims, damages, and expenses (including, without limitation, attorneys&#39; fees) relating to the development, operation, maintenance, and contents of your site.</p>\n\n<p><strong>Term of the Agreement</strong><br />\nThe term of this Agreement will begin upon our acceptance of your application and will end when terminated by either party. Either you or we may terminate this Agreement at any time, with or without cause, by giving the other party written notice of termination. Upon the termination of this Agreement for any reason, you will immediately cease use of, and remove from your site, all links to our site, and all My Organization and MyProductA and MyProductB trademarks, trade dress and logos, and all other materials provided by or on behalf of us to you pursuant hereto or in connection with the Affiliate Referral Program. You are only eligible to earn referral fees on our sales of Qualifying Products occurring during the term, and referral fees earned through the date of termination will remain payable only if the related orders are not canceled or returned. We may withhold your final payment for a reasonable time to ensure that the correct amount is paid.</p>\n\n<p><strong>Modification</strong><br />\nWe may modify any of the terms and conditions contained in this Agreement, at any time and in our sole discretion, by posting a change notice or a new agreement on our site. Modifications may include, for example, changes in the scope of available referral fees, referral fee schedules, payment procedures, and Program rules. IF ANY MODIFICATION IS UNACCEPTABLE TO YOU, YOUR ONLY RECOURSE IS TO TERMINATE THIS AGREEMENT. YOUR CONTINUED PARTICIPATION IN THE PROGRAM FOLLOWING OUR POSTING OF A CHANGE NOTICE OR NEW AGREEMENT ON OUR SITE WILL CONSTITUTE BINDING ACCEPTANCE OF THE CHANGE.</p>\n\n<p><strong>Relationship of Parties</strong><br />\nYou and we are independent contractors, and nothing in this Agreement will create any partnership, joint venture, agency, franchise, sales representative, or employment relationship between the parties. You will have no authority to make or accept any offers or representations on our behalf. You will not make any statement, whether on your site or otherwise, that reasonably would contradict anything in this Section.</p>\n\n<p><strong>Limitation of Liability</strong><br />\nWe will not be liable for indirect, special, or consequential damages (or any loss of revenue, profits, or data) arising in connection with this Agreement or the Program, even if we have been advised of the possibility of such damages. Further, our aggregate liability arising with respect to this Agreement and the Program will not exceed the total referral fees paid or payable to you under this Agreement.</p>\n\n<p><strong>Disclaimers</strong><br />\nWe make no express or implied warranties or representations with respect to the Affiliate Referral Program or any products sold through the Affiliate Referral Program (including, without limitation, warranties of fitness, merchantability, non-infringement, or any implied warranties arising out of a course of performance, dealing, or trade usage). In addition, we make no representation that the operation of our site will be uninterrupted or error-free, and we will not be liable for the consequences of any interruptions or errors.</p>\n\n<p><strong>Independent Investigation</strong><br />\nYOU ACKNOWLEDGE THAT YOU HAVE READ THIS AGREEMENT AND AGREE TO ALL ITS TERMS AND CONDITIONS. YOU UNDERSTAND THAT WE MAY AT ANY TIME (DIRECTLY OR INDIRECTLY) SOLICIT CUSTOMER REFERRALS ON TERMS THAT MAY DIFFER FROM THOSE CONTAINED IN THIS AGREEMENT OR OPERATE WEB SITES THAT ARE SIMILAR TO OR COMPETE WITH YOUR WEB SITE. YOU HAVE INDEPENDENTLY EVALUATED THE DESIRABILITY OF PARTICIPATING IN THE PROGRAM AND ARE NOT RELYING ON ANY REPRESENTATION, GUARANTEE, OR STATEMENT OTHER THAN AS SET FORTH IN THIS AGREEMENT.</p>\n\n<p><strong>Miscellaneous</strong><br />\nThis Agreement will be governed by the laws of the United States and the State of AnyState, without reference to rules governing choice of laws. Any action relating to this Agreement must be brought in the federal or state courts located in Anytown, AnyState and you irrevocably consent to the jurisdiction of such courts. You may not assign this Agreement, by operation of law or otherwise, without our prior written consent. Subject to that restriction, this Agreement will be binding on, inure to the benefit of, and enforceable against the parties and their respective successors and assigns. Our failure to enforce your strict performance of any provision of this Agreement will not constitute a waiver of our right to subsequently enforce such provision or any other provision of this Agreement.</p>',182,3,40,1539271687,'a'),(538,'1140312109_4','',182,4,41,1537472973,'a'),(459,'1125030792_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',161,3,41,1537472973,'a'),(872,'1192490314_1','<h3 class=\"uppercase\">Order Receipt</h3>',269,1,40,1540242461,'a'),(2551,'1461025153_6','',417,6,40,1544811264,'a'),(2550,'1461024058_6','<h6 class=\"heading-title\">About Event Registration</h6>\n\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem antium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</p>\n\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem antium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</p>',283,6,40,1540864219,'a'),(873,'1192490314_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',269,2,40,1542662999,'a'),(462,'1125032769_3','<p />',162,3,41,1537472973,'a'),(862,'1192490150_3','<p class=\"lead\" style=\"text-align: center;\">Please select the giving opportunity that is right for you.&nbsp;You will asked to login or register before viewing the next page.</p>',266,3,40,1542642734,'a'),(863,'1192490150_4','',266,4,2,1537472973,'a'),(1678,'1310833895_1','<h3 class=\"uppercase\">Donation Payment</h3>',476,1,40,1539797386,'a'),(1679,'1310833895_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}donation\">Donation Form</a></li>\n</ul>',476,2,40,1539797319,'a'),(1680,'1310833895_3','',476,3,40,1541093380,'a'),(1681,'1310833895_4','',476,4,40,1541092758,'a'),(2589,'1462832313_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',1024,2,40,1542745190,'a'),(2583,'1462830858_2','<ul class=\"breadcrumb\">\n	<li>&nbsp;<a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">StafF Home</a></li>\n</ul>',1023,2,40,1542745169,'a'),(1187,'1197875211_3','<h4>Welcome Participant!</h4>\n\n<p>Thank for signing up for our trade show.&nbsp; Here is all the information you will need...</p>',350,3,40,1545273786,'a'),(1188,'1197875211_4','',350,4,1,1537472973,'a'),(1189,'1197875317_1','<h3 class=\"uppercase\">Event Registration</h3>',351,1,40,1545273353,'a'),(1190,'1197875317_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',351,2,40,1545273353,'a'),(860,'1192490150_1','<h3 class=\"uppercase\">Donation</h3>',266,1,40,1538761137,'a'),(861,'1192490150_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',266,2,40,1538761757,'a'),(1364,'1252705585_3','',394,3,40,1539214155,'a'),(1365,'1252705585_4','',394,4,2,1537472973,'a'),(1800,'1311352111_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',508,2,40,1545263143,'a'),(1802,'1311352111_4','',508,4,40,1545263081,'a'),(458,'1125030792_2','',161,2,41,1537472973,'a'),(948,'1192552387_1','<h3 class=\"uppercase\">Members Directory Form</h3>',288,1,40,1544638000,'a'),(949,'1192552387_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}members-directory\">Members Directory</a></li>\n</ul>',288,2,40,1544638161,'a'),(950,'1192552387_3','<p class=\"lead\" style=\"text-align: center;\">Add your organization to our Members Directory.&nbsp; You can edit your own entry at any time.</p>',288,3,40,1544637985,'a'),(951,'1192552387_4','',288,4,1,1537472973,'a'),(952,'1192552427_1','<h3 class=\"uppercase\">Classified Ads</h3>',289,1,40,1544638352,'a'),(2669,'1464798731_6','<h6 class=\"heading-title\">About Classified Ads</h6>\n\n<p><span class=\"text-fine-print\">All members have access to post a classified ad or ask the seller for more information and the seller will be notified and will be able to reply.</span></p>\n\n<p>Ads that are not &quot;submitted&quot; but &quot;saved for later&quot; will not be displayed until they are marked &quot;complete&quot;.</p>\n\n<p><span class=\"text-fine-print\">Sellers can edit their own ads at any time and mark them as &quot;incomplete&quot;&nbsp; item to unlist them or SOLD to mark them as sold.</span></p>\n\n<p><span class=\"text-fine-print\">All ads will will be removed (from the view) after 90 days from the time of the last edit.</span></p>\n\n<h6 class=\"heading-title\">Sellers</h6>\n\n<p><span class=\"text-fine-print\">If this is your ad, an &quot;Edit&quot; button will appear below your ad on this page so you can edit your ad.</span></p>',354,6,40,1548294420,'a'),(2666,'1464798164_6','<p><a class=\"btn\" href=\"{path}classified-ads-form\">New Classified Ad</a></p>\n\n<h6 class=\"heading-title\">About Classified Ads</h6>\n\n<p><span class=\"text-fine-print\">All members have access to post a classified ad or ask the seller for more information and the seller will be notified and will be able to reply.</span></p>\n\n<p>Ads that are not &quot;submitted&quot; but &quot;saved for later&quot; will not be displayed until they are marked &quot;complete&quot;.</p>\n\n<p><span class=\"text-fine-print\">Sellers can <a href=\"{path}my-classified-ads\">edit their own ads</a> at any time.</span></p>\n\n<p><span class=\"text-fine-print\">All ads will will be removed (from the view) after 90 days from the time of the last edit.</span></p>',289,6,40,1548293612,'a'),(953,'1192552427_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n</ul>',289,2,40,1544638380,'a'),(954,'1192552427_3','<p class=\"lead\" style=\"text-align: center;\">Members helping members&nbsp;&mdash; Craigslist-style.</p>',289,3,40,1544638189,'a'),(955,'1192552427_4','',289,4,1,1537472973,'a'),(936,'1192552294_1','<h3 class=\"uppercase\">Members Calendar</h3>',285,1,40,1539144879,'a'),(937,'1192552294_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n</ul>',285,2,40,1539194235,'a'),(938,'1192552294_3','<p class=\"lead\" style=\"text-align: center;\">This calendar page will show all event on the members calendar.</p>',285,3,40,1539144879,'a'),(939,'1192552294_4','',285,4,2,1537472973,'a'),(2788,'1466447859_5','<div class=\"feature feature-1 feature-1\">\n<div class=\"left\"><i class=\"ti-layers-alt icon-lg\"><span style=\"display:none\">&nbsp;</span></i></div>\n\n<div class=\"right\">\n<h3>Building</h3>\n\n<p class=\"mb0\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>\n</div>\n</div>',1058,5,40,1546292964,'a'),(940,'1192552321_1','<h3 class=\"uppercase\">Members Event</h3>',286,1,40,1539194034,'a'),(941,'1192552321_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}members-calendar\">Members Calendar</a></li>\n</ul>',286,2,40,1544662791,'a'),(942,'1192552321_3','<p class=\"lead\" style=\"text-align: center;\">This event is from the Members Calendar.</p>',286,3,40,1539194034,'a'),(943,'1192552321_4','',286,4,2,1537472973,'a'),(2787,'1466447843_4','<div class=\"feature feature-1 feature-1\">\n<div class=\"left\"><i class=\"ti-palette icon-lg\"><span style=\"display:none\">&nbsp;</span></i></div>\n\n<div class=\"right\">\n<h3>Designing</h3>\n\n<p class=\"mb0\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>\n</div>\n</div>',1058,4,40,1546292868,'a'),(2331,'1458241683_1','<h3 class=\"uppercase\">User Registration</h3>',974,1,40,1545275499,'a'),(822,'1192489349_3','',256,3,40,1542657400,'a'),(823,'1192489349_4','',256,4,40,1540954765,'a'),(828,'1192489585_1','<h3 class=\"uppercase\">Membership Access</h3>',258,1,40,1545273482,'a'),(829,'1192489585_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n</ul>',258,2,40,1545273206,'a'),(830,'1192489585_3','<h5>Thank you for your Membership!</h5>\n\n<p>We have added 1 year to your current membership access. Click the button below to login and access the member portal.</p>\n\n<p style=\"text-align: left;\"><a class=\"btn\" href=\"{path}members-home\">Members Home</a></p>',258,3,40,1545273505,'a'),(831,'1192489585_4','',258,4,1,1537472973,'a'),(1393,'1258131168_4','',401,4,2,1537472973,'a'),(2510,'1459374016_6','',394,6,40,1539214173,'a'),(2511,'1459374037_6','<h6 class=\"heading-title\">About Dues</h6>\n\n<p><span class=\"text-fine-print\">This is different than <a href=\"{path}members-access\">Pay of Access</a> which grants access to the site&#39;s <a href=\"{path}members-home\">Member Portal</a>.</span></p>\n\n<p><span class=\"text-fine-print\">This order form is for collecting membership dues online when you don&#39;t have a need for a member portal.</span></p>',394,6,40,1544637257,'a'),(1394,'1258131212_1','<h3 class=\"uppercase\">Support Ticket</h3>',402,1,40,1540239711,'a'),(2604,'1462904100_3','<p class=\"lead\" style=\"text-align: center;\">Services Projects assigned to your account (or have been added as a watcher to participate).</p>',1028,3,40,1543251026,'a'),(1395,'1258131212_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-support-tickets\">My Support Tickets</a></li>\n</ul>',402,2,40,1540239711,'a'),(1396,'1258131212_3','',402,3,40,1542732211,'a'),(1397,'1258131212_4','',402,4,2,1537472973,'a'),(1685,'1310836664_4','',477,4,2,1537472973,'a'),(1399,'1258132094_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-support-tickets\">My Support Tickets</a></li>\n</ul>',403,2,40,1543513955,'a'),(1400,'1258132094_3','<p class=\"lead\"><span style=\"color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 28px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; display: inline !important; float: none; background-color: rgb(255, 255, 255);\">The following reply has been added to a Support Ticket of interest to you.</span></p>',403,3,40,1543522144,'a'),(1401,'1258132094_4','<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\">&nbsp;</p>\n\n<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: border-box; font-weight: bold;\">Why am I getting this email?</strong></p>\n\n<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\">You are either listed as the owner of this Support Ticket, or you have been give access to view and reply by the owner. If you wish to view, reply, or remove yourself as a watcher, all of these actions can be completed from the Support Ticket page itself. To access the protected Support Ticket page, you will need to follow the link above to register or login.</p>',403,4,40,1543522247,'a'),(2610,'1462904162_3','<p class=\"lead\" style=\"text-align: center;\">This is a private conversation thread between you and our services team. All text and file attachments are secured.</p>',1029,3,40,1543266403,'a'),(457,'1125030792_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>Outside The Lines</strong><br />Music performed by FC Music Team member, Eric Orson</p><p>Includes:</p><p>The Noise We Make <br />Lifeboat <br />Take Me Higher <br />We Fall Down <br />Breathe <br />Did You Feel the Mountains Tremble? <br />Everybody <br />Taking it to the Streets <br />The Wonderful Cross <br />Amazing Love</p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}orson-lg.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',161,1,41,1537472973,'a'),(2375,'1458318222_4','',983,4,40,1538158361,'a'),(2719,'1465482389_1','<h3 class=\"uppercase\">Calendar Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',1045,1,40,1545870786,'a'),(820,'1192489349_1','<h3 class=\"uppercase\">Express Order</h3>',256,1,40,1548294321,'a'),(821,'1192489349_2','',256,2,40,1548293408,'a'),(2877,'1470091739_1','<h3 class=\"uppercase\">Preview &amp; PaymenT</h3>',1077,1,40,1548294350,'a'),(2419,'1458756757_3','',997,3,40,1538596896,'a'),(2420,'1458756757_4','',997,4,40,1538596896,'a'),(2421,'1458756757_5','',997,5,40,1538596896,'a'),(2422,'1458756757_6','',997,6,40,1538596896,'a'),(2423,'1458757506_1','<h3 class=\"uppercase mb8\">Product</h3>',998,1,40,1548293696,'a'),(2418,'1458756757_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',997,2,40,1538596896,'a'),(2751,'1466053595_5','<h5 class=\"uppercase\">Result</h5>\n\n<p class=\"mb0\">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>',936,5,40,1545893734,'a'),(2416,'1458750039_4','',996,4,40,1538590178,'a'),(2388,'1458328320_3','',986,3,40,1538168459,'a'),(2389,'1458328320_4','',986,4,40,1538168459,'a'),(343,'1112458926_1','',126,1,40,1545275325,'a'),(344,'1112458926_2','',126,2,2,1537472973,'a'),(416,'1124839091_3','<p><em><strong></strong></em></p>',126,3,41,1537472973,'a'),(2387,'1458328320_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}catalog\">Shop</a></li>\n</ul>',986,2,40,1538245698,'a'),(2682,'1464970250_3','<p class=\"lead\" style=\"text-align: center;\">A question / answer has been added to a classified ad of interest to you.</p>',1040,3,40,1544810958,'a'),(2683,'1464970250_4','<hr size=\"2\" width=\"100%\" />\n<p><strong>Why did I get this e-mail?</strong><br />\nYou have requested to be notified via e-mail whenever a new comment is added to this classified ad.</p>\n\n<p><strong>I am no longer interested in this classified ad. How do I stop the e-mail notifications?</strong><br />\nClick on the &quot;View or Reply&quot; link above to go to the classified ad. Then go to the bottom of the page and click &#39;Remove Me&#39;.</p>\n\n<p><strong>If I remove myself from this notification, can I still get other notifications I have added myself too?</strong><br />\nYes. Each classified ad is handled separately.</p>',1040,4,40,1544810586,'a'),(2681,'1464970250_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}classified-ads\">Classified Ads</a></li>\n</ul>',1040,2,40,1544810460,'a'),(2680,'1464970250_1','<h3 class=\"uppercase\">Classified Ad Update</h3>',1040,1,40,1545274389,'a'),(1740,'1310963589_3','<p class=\"lead\" style=\"text-align: center;\"><span style=\"color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 28px; orphans: auto; text-align: center; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; display: inline !important; float: none; background-color: rgb(255, 255, 255);\">Click on the Support Ticket to add a reply which will be emailed to the project owner and all participants.</span></p>',492,3,40,1543521269,'a'),(1741,'1310963589_4','',492,4,2,1537472973,'a'),(2380,'1458320123_1','<h3 class=\"uppercase mb8\">Shop</h3>',985,1,40,1548293640,'a'),(2381,'1458320123_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',985,2,40,1538245750,'a'),(2382,'1458320123_3','',985,3,40,1538160262,'a'),(2383,'1458320123_4','',985,4,40,1538160262,'a'),(2384,'1458320123_5','',985,5,40,1538160262,'a'),(2385,'1458320123_6','',985,6,40,1538160262,'a'),(2506,'1459370585_3','<p class=\"lead\" style=\"text-align: center;\">Purchase instant access to downloadable goods.</p>',1012,3,40,1539210880,'a'),(2417,'1458756757_1','<h3 class=\"uppercase\">Shop</h3>',997,1,40,1548293649,'a'),(2407,'1458749382_3','<p class=\"lead\" style=\"text-align: center;\">Put this page in any Folder and all images in that Folder and sub folders will be displayed here.</p>',994,3,40,1538589521,'a'),(2370,'1458318174_3','<p><em><strong></strong></em></p>',982,3,40,1538158313,'a'),(2371,'1458318174_4','',982,4,40,1538158313,'a'),(2372,'1458318222_1','<h3 class=\"uppercase\">Member registration</h3>',983,1,40,1545275583,'a'),(2373,'1458318222_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',983,2,40,1545275615,'a'),(2374,'1458318222_3','<h3>Welcome Member!</h3>\n\n<p>We just upgraded your account to membership status and you are now logged in!</p>',983,3,40,1545275627,'a'),(2369,'1458318174_2','',982,2,40,1538158313,'a'),(1362,'1252705585_1','<h3 class=\"uppercase\">Collect Dues</h3>',394,1,40,1538762986,'a'),(2513,'1459431357_6','<h6 class=\"heading-title\">About Affiliate Sign up form</h6>\n\n<p>You will need to have a user account on our website before you can complete this form.</p>\n\n<p>Once your application is submitted, we will review and approve it for you in a few business days.<br />\n<br />\n<em>Hint: Your Affiliate Code should be a word that is easy for your friends to remember and reminds them of you. Your website name, organization name, or your name or nickname are good examples.</em></p>',169,6,40,1547837315,'a'),(1363,'1252705585_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',394,2,40,1538762958,'a'),(2368,'1458318174_1','',982,1,40,1545275453,'a'),(2586,'1462830858_6','',1023,6,40,1542670997,'a'),(2587,'1462830858_6','<h6 class=\"heading-title\">Services Project Information</h6>\n\n<p>Typically, a Services Project is created automatically when your client <a href=\"{path}order-services\">orders services</a>, but as Staff, there are times when you want to start a project manually on behalf of your client and then invite them to add credits to it at a later time.</p>\n\n<p>Enter your client&#39;s information and not your own. Make sure you load this page with <a href=\"{path}new-services-project?connect_to_contact=false\">Connect to Contact</a> turned &quot;off&quot; before you start completing the form so liveSite doesn&#39;t try to connect it to <em>your</em> account when it&#39;s submitted. You want the project to be associated with your client&#39;s email address (and account) instead.</p>',1023,6,40,1543545788,'a'),(1793,'1311194206_3','<p class=\"lead\" style=\"text-align: center;\"><span class=\"text-fine-print\">Order your Exam today! </span></p>',506,3,40,1542657123,'a'),(2573,'1462817001_6','',506,6,40,1542657178,'a'),(2414,'1458750039_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',996,2,40,1538590178,'a'),(2358,'1458316052_3','',979,3,40,1538156191,'a'),(395,'1124812360_4','',144,4,40,1538955347,'a'),(396,'1124812452_1','<h3 class=\"uppercase\">Shipping Methods</h3>',145,1,40,1538955093,'a'),(397,'1124812452_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}cart\">Shopping Cart</a></li>\n</ul>',145,2,40,1545763492,'a'),(398,'1124812452_3','',145,3,40,1548294028,'a'),(399,'1124812452_4','',145,4,40,1541781313,'a'),(407,'1124816210_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>Healthy Cooking God\'s Way</strong></p><p>The beauty of the Body for God Cookbook, by Lisa Young, lies in its simplicity. For years, we have taken the pure foods that God has provided and made their existence in our daily diets complex but less valuable. This assortment of recipes and hints provided will assist each of us in preparing foods which fuel our bodies for God. Whether you are already into the &quot;health kick&quot; or you\'re just kicking the tires of healthy living, this book is for you. It\'s our prayer that you will be motivated to keep your body healthy and energized for God\'s great adventure!</p><p>This 160-page hardback book was designed to accompany the Body by God series that was presented by Senior Pastor, Ed Young.</p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}bodyforgod-lg.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',147,1,41,1537472973,'a'),(408,'1124816210_2','',147,2,41,1537472973,'a'),(410,'1124827010_1','<span class=\"Heading\">New Releases</span>',148,1,41,1537472973,'a'),(411,'1124827010_2','<br /><p><span class=\"PageTitle\"></span></p><p><table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td><p align=\"center\"><strong><a href=\"{path}music_rob_johnson_through_the_rain\"><img hspace=\"0\" src=\"{path}robjohnson-sm.jpg\" align=\"baseline\" border=\"0\" /></a></strong></p></td><td><p align=\"left\"><a href=\"{path}music_rob_johnson_through_the_rain\"><strong>Though the Rain<br /></strong><em>Music CD</em><br />Worship',148,2,41,1537472973,'a'),(409,'1124816210_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',147,3,41,1537472973,'a'),(443,'1124918747_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>Through the Rain</strong><br />Music performed by FC Music Team leader, Rob Johnson</p><p>Includes:</p><p>That\'s What Love Should Be <br />My Heart Is Set On Heaven <br />You Inspire Me <br />Through The Rain <br />My World <br />Pictures On The Wall <br />Out Of The Darkness <br />Mercy Of Your Grace <br />The Star <br />You\'ve Been There</p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}robjohnson-lg.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',158,1,41,1537472973,'a'),(412,'1124827010_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',148,3,41,1537472973,'a'),(2386,'1458328320_1','<h3 class=\"uppercase\">Product</h3>',986,1,40,1548293688,'a'),(418,'1124854097_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>Facing life\'s six most common phobias</strong></p><p>Fear is an unavoidable part of the human experience. Anxiety, fear and phobia threaten to encompass us as we embrace the ever-chaging, fast-paced technological and seemingly unstable age we live in today. Because we are taught that it is weak to show fear, that cowards are despised, and that being a hero means knowing no fear, we try to hide our fears and anxieties. Condemning ourselves, our self-image takes a nosedive with our self-esteem. In an entertaining style and tone, Ed Young explains the nature and causes of these paralyzing fears and phobias, and offers practical and biblical guidelines for dealing with them according to God\'s will.</p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}knowfear-lg.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',149,1,41,1537472973,'a'),(419,'1124854097_2','',149,2,41,1537472973,'a'),(420,'1124854097_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',149,3,41,1537472973,'a'),(421,'1124854652_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><span class=\"kitName\"><strong>How to keep your children from running your life</strong></span><br /><br />In these days of corporate scandal, there',150,1,41,1537472973,'a'),(422,'1124854652_2','',150,2,41,1537472973,'a'),(423,'1124854652_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',150,3,41,1537472973,'a'),(437,'1124911348_1','<span class=\"Heading\"><p>Fall Picnic</p></span>',156,1,41,1537472973,'a'),(438,'1124911348_2','<p>Sunday, September 18, 2005<br />Our Church Fall Church Picnic and Food Drive<br />4:00 - 7:00 p.m.</p><p>Please complete the registration form and let us know you are coming.</p><p>Make an online donation to the North American Food Bank.</p><p>Please pick up your B3 Wristbands at the the Information Center on Sunday mornings.',156,2,41,1537472973,'a'),(1414,'1271797181_1','<h3 class=\"uppercase\">Forum</h3>',407,1,40,1544563053,'a'),(2657,'1464725218_6','',409,6,40,1544565428,'a'),(1415,'1271797181_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',407,2,40,1544562996,'a'),(1416,'1271797181_3','<div class=\"row\">\n<div class=\"col-sm-8\">\n<p class=\"lead\" style=\"text-align: center;\">Join the most recent discussions!</p>\n</div>\n\n<div class=\"col-sm-4\"><a class=\"link-button-secondary-small\" href=\"{path}forum\" style=\"filter: alpha(opacity=50); opacity: 0.5;\">Most Recent</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-popular\">Most Popular</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-active\">Most Active</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-my-forum\">My Forum</a></div>\n</div>',407,3,40,1544634486,'a'),(1417,'1271797181_4','',407,4,2,1537472973,'a'),(1418,'1271797219_1','<h3 class=\"uppercase\">New Forum Thread</h3>',408,1,40,1544561520,'a'),(2654,'1464721467_6','',408,6,40,1544561632,'a'),(2655,'1464721479_6','<h6 class=\"heading-title\">About Forum Threads</h6>\n\n<p>Please use this form to start a new forum thread. Visitors will be able to view your new thread and those that register will be able to reply and even watch for other replies.</p>\n\n<h6 class=\"heading-title\">Forum Participation</h6>\n\n<p>Please be aware that this forum is monitored and so please be mindful that mean-spirited content and spam will be edited or removed as necessary.</p>',408,6,40,1544566302,'a'),(1419,'1271797219_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',408,2,40,1544561467,'a'),(1420,'1271797219_3','<p class=\"lead\" style=\"text-align: center;\">Now that you&#39;ve logged in, you can start a new forum thread.</p>',408,3,40,1544634623,'a'),(1421,'1271797219_4','',408,4,2,1537472973,'a'),(1422,'1271797276_1','<h3 class=\"uppercase\">Forum Thread</h3>',409,1,40,1544563795,'a'),(2656,'1464725074_6','<h6 class=\"heading-title\">Forum Participation</h6>\n\n<p>We encourage you to register or login to reply to a forum thread. Please be aware that this forum is monitored and so please be mindful that mean-spirited replies or spam will be edited or removed as necessary.</p>\n\n<p>You can also <a href=\"{path}contact-us\">contact us</a> if you feel a particular reply is not productive.</p>',409,6,40,1544566355,'a'),(1423,'1271797276_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',409,2,40,1544563789,'a'),(1424,'1271797276_3','<p class=\"lead\" style=\"text-align: center;\">Visitors can read the forum. Guests that login first, can add a reply or get notified of new replies.</p>',409,3,40,1544650096,'a'),(1425,'1271797276_4','',409,4,2,1537472973,'a'),(1434,'1271860105_1','<h3 class=\"uppercase\"><span>New Forum Thread</span></h3>',412,1,40,1545274364,'a'),(1461,'1272573905_4','',418,4,40,1538763163,'a'),(2486,'1458923066_6','',418,6,40,1538763274,'a'),(2505,'1459370585_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1012,2,40,1539210724,'a'),(1436,'1271860105_3','',412,3,40,1544653175,'a'),(1437,'1271860105_4','',412,4,40,1544653767,'a'),(1460,'1272573905_3','<p class=\"lead\" style=\"text-align: center;\">Upgrade your membership to our Member Portal.</p>',418,3,40,1538763255,'a'),(1442,'1271880263_1','<h3 class=\"uppercase\">Notification</h3>',414,1,40,1545273985,'a'),(1443,'1271880263_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',414,2,40,1545273985,'a'),(1444,'1271880263_3','<h4>New Blog Comment</h4>\n\n<p>A new blog comment was just added.</p>',414,3,40,1545273985,'a'),(1445,'1271880263_4','<hr size=\"2\" width=\"100%\" />\n<p><strong>Why did I get this e-mail?</strong></p>\n\n<p>You have requested to be notified via e-mail whenever a new comment is posted to a blog article.</p>\n\n<p><strong>I am no longer interested in this blog article.&nbsp; How do I stop the e-mail notifications?</strong><br />\nClick on&nbsp; the &quot;View all Comments&quot; link above to go to the blog article.&nbsp; Then go to the bottom of the page and click &#39;Remove Me&#39;.</p>\n\n<p><strong>If I remove myself from this notification, can I still get other notifications I have added myself too?</strong><br />\nYes. Each blog article is handled separately.</p>',414,4,40,1545274004,'a'),(1446,'1271881065_1','<h3 class=\"uppercase\">Notification</h3>',415,1,40,1545273925,'a'),(1447,'1271881065_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',415,2,40,1545273906,'a'),(1454,'1272398773_1','<h3 class=\"uppercase\">Trial Membership</h3>',417,1,40,1544811188,'a'),(1448,'1271881065_3','<h4>New Comment</h4>\n\n<p>A new comment was just added.</p>',415,3,40,1545273906,'a'),(1449,'1271881065_4','<hr width=\"100%\" size=\"2\" />\r\n<strong>Why did I get this e-mail?</strong>\r\n<p>\r\nYou have requested to be notified via e-mail whenever a new comment is posted one of our website pages.\r\n</p>\r\n<p>\r\n<strong>I am no longer interested in this website page.&nbsp; How do I stop the e-mail notifications?</strong><br />\r\nClick on&nbsp; the &quot;View all Comments&quot; link above to go to the website page.&nbsp; Then go to the bottom of the page and click \'Remove Me\'.\r\n</p>\r\n<p>\r\n<strong>If I remove myself from this notification, can I still get other notifications I have added myself too?</strong><br />\r\nYes. Each website page is handled seperately. \r\n</p>\r\n',415,4,2,1537472973,'a'),(1455,'1272398773_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',417,2,40,1544811281,'a'),(1726,'1310951241_1','<h3 class=\"uppercase\">Trial Membership</h3>',489,1,40,1544813155,'a'),(1727,'1310951241_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',489,2,40,1544821387,'a'),(1728,'1310951241_3','<p class=\"lead\" style=\"text-align: center;\">Congratulations! Your trial membership has been activated.</p>',489,3,40,1544813183,'a'),(1729,'1310951241_4','<h5>Here&#39;s what just happened?</h5>\n\n<ul>\n	<li>1) If you were not already registered, we created a new user account for you and logged you in (see below for your login information).</li>\n	<li>&nbsp;</li>\n	<li>2) We added a value to your <strong>Member ID</strong> to your user account&#39;s Contact.</li>\n	<li>&nbsp;</li>\n	<li>3) We added 30 days to today&#39;s date and added that value into your Contact&#39;s <strong>Member Expiration Date</strong>.</li>\n	<li>&nbsp;</li>\n	<li>4) We changed the <strong>Start Page</strong> in your user account to the Members Home page, so each time you login, you will be taken to that page for your convenience.</li>\n</ul>',489,4,40,1545069709,'a'),(1456,'1272398773_3','<p class=\"lead\" style=\"text-align: center;\">Get instant access to our Membership Portal for 30 days.</p>',417,3,40,1544812959,'a'),(1457,'1272398773_4','',417,4,2,1537472973,'a'),(1245,'1251486128_1','<h3 class=\"uppercase\">Blog Post</h3>',365,1,40,1548293605,'a'),(2728,'1465490561_3','',1047,3,40,1545339728,'a'),(1246,'1251486128_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}blog\">Blog </a></li>\n</ul>',365,2,40,1539283317,'a'),(1247,'1251486128_3','',365,3,40,1539283996,'a'),(1248,'1251486128_4','<h6 class=\"heading-title\">About The Blog</h6>\n\n<hr />\n<p>Here you will find the latest news and announcements from our team.</p>',365,4,40,1544818069,'a'),(461,'1125032769_2','<p /><p /><p><table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p align=\"center\"><a href=\"{path}music_eric_orson_outside_the_lines\"><img hspace=\"0\" src=\"{path}orson-sm.jpg\" align=\"baseline\" border=\"0\" /></a></p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><a href=\"{path}music_eric_orson_outside_the_lines\"><strong>Eric Orson<br /></strong>Outside the Lines</a></p></td></tr><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><br /></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td></tr><tr><td><p align=\"center\"><a href=\"{path}music_the_mix_free\"><img hspace=\"0\" src=\"{path}student-mix-sm.jpg\" align=\"baseline\" border=\"0\" /></a></p></td><td><p><a href=\"{path}music_the_mix_free\"><strong>FREE<br /></strong>The Mix Student Ministry Band</a></p></td></tr><tr><td><br /></td><td></td></tr><tr><td><p align=\"center\"><a href=\"{path}music_rob_johnson_through_the_rain\"><img hspace=\"0\" src=\"{path}robjohnson-sm.jpg\" align=\"baseline\" border=\"0\" /></a></p></td><td><p align=\"left\"><a href=\"{path}music_rob_johnson_through_the_rain\"><strong>Rob Johnson<br /></strong>Through the Rain</a></p></td></tr></tbody></table></p><p /><p />',162,2,41,1537472973,'a'),(446,'1124919500_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>RPMs: Recognizing Potential Mates<br /></strong><em>(DVD/CD Message Series)</em><br /><br />Whether you\'re a single adult, a student, or a parent, this creatively driven series of talks will provide foundational principles on how to date and select a mate God\'s way.<br />We\'re going to cruise past the cultural myths and embark on a supercharged ride to the ultimate relational destination.</p><p>Ed Young, Senior Pastor of Fellowship Church brings you this &quot;accelarating&quot; message!</p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}rpm_dvd.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',159,1,41,1537472973,'a'),(444,'1124918747_2','',158,2,41,1537472973,'a'),(445,'1124918747_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',158,3,41,1537472973,'a'),(447,'1124919500_2','',159,2,41,1537472973,'a'),(448,'1124919500_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',159,3,41,1537472973,'a'),(460,'1125032769_1','<span class=\"PageTitle\">Music</span> ',162,1,41,1537472973,'a'),(454,'1125026613_1','<table style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 100%; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none; cssFloat: none\" cellspacing=\"1\" cellpadding=\"1\" rules=\"none\" border=\"0\" frame=\"void\"><tbody><tr><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><p><strong>FREE</strong><br />The Mix Student Ministry Band</p><p>This release from our youth band includes:</p><p>Rain Down <br />Beautiful One <br />Indescribable <br />History Maker <br />A Love <br />All for Love <br />O Praise Him <br />Now That You\'re Near <br />World on Fire <br />Humble Plea <br />My Glorious </p></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; WIDTH: 50px; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"></td><td style=\"BACKGROUND-IMAGE: none; VERTICAL-ALIGN: top; BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none; TEXT-ALIGN: left; BORDER-BOTTOM-STYLE: none\"><img hspace=\"0\" src=\"{path}student-mix-lg.jpg\" align=\"baseline\" border=\"0\" /></td></tr></tbody></table>',160,1,41,1537472973,'a'),(455,'1125026613_2','',160,2,41,1537472973,'a'),(456,'1125026613_3','<br /><br /><p><strong>Categories<br /><br /></strong>New Releases<br />Most Popular<br />Pastor\'s Picks</p>',160,3,41,1537472973,'a'),(539,'1146888035_1','<h3 class=\"uppercase\">Conversation Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',183,1,40,1545870672,'a'),(530,'1140311429_4','',180,4,41,1537472973,'a'),(1734,'1310955291_1','<h3 class=\"uppercase\">Membership RENEWAL</h3>',491,1,40,1545273644,'a'),(1735,'1310955291_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',491,2,40,1545273575,'a'),(1736,'1310955291_3','<h4>Membership Expiration Notice</h4>\n\n<p><strong>Your membership is about to expire.</strong> Don&#39;t loose access to the Member Portal. <strong>Renew today!</strong></p>\n\n<p style=\"text-align: left;\"><a class=\"btn\" href=\"{path}members-access\">Renew Now</a></p>\n\n<p style=\"text-align: left;\">This will be your only reminder, so please act now!</p>\n\n<p style=\"text-align: left;\">Thank you.</p>',491,3,40,1545273602,'a'),(1737,'1310955291_4','',491,4,2,1537472973,'a'),(1682,'1310836664_1','<h3 class=\"uppercase\">Donation Receipt</h3>',477,1,40,1542654617,'a'),(2579,'1462818476_6','',558,6,40,1542658627,'a'),(1683,'1310836664_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',477,2,40,1542663022,'a'),(1684,'1310836664_3','',477,3,40,1542654682,'a'),(2572,'1462816972_6','<h6 class=\"heading-title\">Exam Information</h6>\n\n<p>Once you order your exam, you will be given instant access to the exam for 30 days.</p>\n\n<p>Answer 100% of the Exam questions correctly and you pass! You will get as many attempts as you wish.</p>\n\n<p>We store each attempt so we can track your progress.</p>\n\n<p>Good luck.</p>',506,6,40,1542657167,'a'),(2820,'1467396454_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1064,2,40,1547236593,'a'),(2792,'1466454687_3','',1059,3,40,1546295507,'a'),(928,'1192546312_1','<h3 class=\"uppercase\">Event Registration</h3>',283,1,40,1538761659,'a'),(929,'1192546312_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',283,2,40,1538761722,'a'),(930,'1192546312_3','<p class=\"lead\" style=\"text-align: center;\">Please select your level of participation in our upcoming conference.</p>',283,3,40,1547052117,'a'),(931,'1192546312_4','',283,4,2,1537472973,'a'),(525,'1130359153_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}cart\">Shopping Cart</a></li>\n</ul>',179,2,40,1545763469,'a'),(2402,'1458513027_6','',179,6,40,1539016884,'a'),(526,'1130359153_3','',179,3,40,1548294037,'a'),(1513,'1275660252_4','',179,4,40,1538353329,'a'),(2405,'1458749382_1','<h3 class=\"uppercase\">Photo Gallery</h3>\n\n<p>2 Columns</p>',994,1,40,1545762554,'a'),(2406,'1458749382_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',994,2,40,1538589521,'a'),(534,'1140311551_4','',181,4,40,1542402078,'a'),(535,'1140312109_1','<h3 class=\"uppercase\">Affiliate Terms</h3>',182,1,40,1539271717,'a'),(540,'1146888035_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',183,2,40,1545870540,'a'),(2449,'1458772304_1','<h3 class=\"uppercase\">Contact Us</h3>',1003,1,40,1538612757,'a'),(2450,'1458772304_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1003,2,40,1538612757,'a'),(2451,'1458772304_3','<p>Please complete and submit this form to start a new conversation with our sales team. If you need help, we invite you to visit our <a href=\"{path}support\">Support</a> and <a href=\"{path}services\">Services</a>.</p>\n\n<hr />\n<p>Our Organization<br />\n300 Alamo Plaza<br />\nSan Antonio, TX 78205</p>\n\n<hr />\n<p>Phone: (555) 555-5550<br />\nFax: (555) 555-5551</p>',1003,3,40,1548292351,'a'),(2452,'1458772304_4','',1003,4,40,1538612443,'a'),(2585,'1462830858_4','<p class=\"lead\" style=\"text-align: center;\">Staff members can create a new services project <a href=\"{path}new-services-project?connect_to_contact=false\">on behalf</a> of a client.<br />\n(Clients that <a href=\"{path}order-services\">order services</a> will trigger this form to be submitted automatically.)</p>',1023,4,40,1543545498,'a'),(2504,'1459370585_1','<h3 class=\"uppercase\">Purchase Downloads</h3>',1012,1,40,1539211170,'a'),(2487,'1458923139_6','<h6 class=\"heading-title\">About Memberships</h6>\n\n<p>When you purchase this product, your website account will be upgraded and you will be able to access anything in our Member Portal.</p>\n\n<ul>\n	<li>If you don&#39;t have a membership, or your membership has already expired, your membership will expire in 365 days from the purchase date. If your membership has not expired yet, you will not loose any days since we add 365 days to your <em>current</em> expiration date.</li>\n</ul>\n\n<p><span class=\"text-fine-print\">If you are just collecting dues online from your members, you can use <a href=\"{path}members-dues\">this form</a> instead.</span></p>\n\n<p><strong>TIP:</strong> You can also grant access to a specific Private Folder when a product is purchased, so you options are endless!</p>',418,6,40,1540864127,'a'),(2485,'1458921871_6','<h6 class=\"heading-title\">Exam Instructions</h6>\n\n<p>Now that you&#39;ve paid for this Exam, please complete the questions below and submit to have your Exam automatically scored.</p>\n\n<p>Please update your account information (if necessary) before you answer the exam questions.</p>\n\n<p>If you get 100% of the questions correct, then you will pass the exam and see a confirmation page.</p>\n\n<p>If you do not get 100% of the questions correct, you will be able to try again. All attempts to pass are stored in our database and we can review how many times you tried before passing.</p>\n\n<p>You have 30 days from the date of purchase to complete this exam before you access is automatically removed.</p>\n\n<p>Good luck!</p>',291,6,40,1542658161,'a'),(1693,'1310850903_1','<h3 class=\"uppercase\">Buy Tickets</h3>',480,1,40,1545091612,'a'),(2700,'1465252581_6','',584,6,40,1545155782,'a'),(2697,'1465251406_6','',480,6,40,1545091576,'a'),(2701,'1465315643_6','',584,6,40,1545156148,'a'),(1694,'1310850903_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}buy-tickets\">Buy Tickets</a></li>\n</ul>',480,2,40,1548293626,'a'),(541,'1146888035_3','<h6 class=\"heading-title\">Conversation Widget</h6>\n\n<p>This form is embedded in the public&nbsp; <a href=\"{path}contact-us\">Contact Us</a> page available to site visitors as well as the <a href=\"{path}new-conversation\">New&nbsp; Conversation</a> page available to registered users.</p>\n\n<p>It auto-registers visitors so they can securely reply to the conversation it creates, and sends out email replies (see it&#39;s Page Properties for more info).</p>\n\n<p>Leave this page in a Public Folder so all can access the widget and submit the form from the original pages.</p>',183,3,40,1545891818,'a'),(542,'1146888035_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;conversation-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>',183,4,40,1545873700,'a'),(1689,'1310850667_1','<h3 class=\"uppercase\">Buy Tickets</h3>',479,1,40,1545091385,'a'),(2698,'1465251437_6','',480,6,40,1545091594,'a'),(2699,'1465251455_6','<h6 class=\"heading-title\">About Tickets</h6>\n\n<p>If the event is not sold out and the event has not past, you will see the &#39;Buy Tickets&#39; button.</p>\n\n<p>If no more tickets are available for the event, then a Wait List message and workflow is presented.</p>',480,6,40,1547142845,'a'),(1690,'1310850667_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',479,2,40,1545091408,'a'),(1691,'1310850667_3','<div class=\"row\">\n<div class=\"col-sm-7 col-sm-offset-2\">\n<p class=\"lead\" style=\"text-align: center;\">Click on the Event Title for more information, purchase tickets, or join the wait list.</p>\n</div>\n\n<div class=\"col-sm-3\">\n<p class=\"bg-primary text-center\"><span style=\"color: green; font-weight: bold; font-size: 130%; margin-right: 0.25em;\">●</span> Tickets Available</p>\n</div>\n</div>',479,3,40,1548294730,'a'),(1692,'1310850667_4','',479,4,40,1547070183,'a'),(2815,'1467302817_1','<h3 class=\"uppercase\">Upcoming Concerts Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',1063,1,40,1547148521,'a'),(2816,'1467302817_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1063,2,40,1547142956,'a'),(1435,'1271860105_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',412,2,40,1544653126,'a'),(672,'1192473449_1','<h3 class=\"uppercase\">Staff Directory</h3>',219,1,40,1544165930,'a'),(673,'1192473449_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',219,2,40,1543946395,'a'),(2644,'1464323047_3','<p class=\"lead\" style=\"text-align: center;\">Post a youtube or vimeo video to the <a href=\"{path}video-gallery\">Video Gallery</a> page.</p>',538,3,40,1544545419,'a'),(2645,'1464323047_4','<p><br />\nThe posted video will not allow the viewer to leave your site, and no other similar videos will be displayed when the video ends.</p>',538,4,40,1544163186,'a'),(674,'1192473449_3','',219,3,40,1545329864,'a'),(675,'1192473449_4','',219,4,2,1537472973,'a'),(684,'1192473569_1','<h3 class=\"uppercase\">New Conversation</h3>',222,1,40,1543873947,'a'),(685,'1192473569_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a href=\"{path}my-conversations\">My Conversations</a></li>\n</ul>',222,2,40,1543873984,'a'),(2595,'1462887341_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',1025,2,40,1542727480,'a'),(2590,'1462832313_3','',1024,3,40,1542672513,'a'),(2594,'1462887341_1','<h3 class=\"uppercase\">All Services Projects</h3>',1025,1,40,1542727520,'a'),(2591,'1462832313_4','<p class=\"lead\" style=\"text-align: center;\">You have created a new services project <a href=\"{path}services-project-form?connect_to_contact=false\">on behalf</a> of your client.<br />\nA new project email has been sent to the client.</p>',1024,4,40,1543360882,'a'),(2592,'1462832313_6','',1024,6,40,1542672452,'a'),(2593,'1462832313_6','<h6 class=\"heading-title\">Service Project Confirmation</h6>\n\n<p>A copy of this project has been sent to your client and attached to their own <a href=\"{path}my-services-projects\">my projects</a> account page.</p>\n\n<p>They have been granted access to the protected project thread where they can view, reply, and add additional team members (as watchers).</p>',1024,6,40,1543360944,'a'),(1191,'1197875317_3','<h4>Welcome Event Exhibitor!</h4>\n\n<p>Thank you for signing up for our trade show.&nbsp; Here are the instructions you will need to know...</p>',351,3,40,1545273377,'a'),(1192,'1197875317_4','',351,4,1,1537472973,'a'),(602,'1189557884_1','<h3 class=\"uppercase\">Calendar</h3>',200,1,40,1545088699,'a'),(2684,'1464970974_6','<h6 class=\"heading-title\">About Trial Memberships</h6>\n\n<p>Anyone can sign up for a trial membership access to our members portal by completing this form. If you are a visitor, you will be registered automatically.</p>',417,6,40,1544812799,'a'),(603,'1189557884_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',200,2,40,1539194335,'a'),(604,'1189557884_3','<p class=\"lead\" style=\"text-align: center;\">This calendar will show all events from all calendars that you have access too. Select a specific calendar to refine the view.</p>',200,3,40,1547847972,'a'),(605,'1189557884_4','',200,4,40,1539129377,'a'),(2694,'1465249723_6','',191,6,40,1545089893,'a'),(1116,'1194045171_1','<h3 class=\"uppercase\">Members Directory Entry</h3>',330,1,40,1544666434,'a'),(1117,'1194045171_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}members-directory\">Members Directory</a></li>\n</ul>',330,2,40,1544666662,'a'),(1118,'1194045171_3','<p class=\"lead\" style=\"text-align: center;\">Connect with other members and keep your own entry up-to-date.</p>',330,3,40,1544666388,'a'),(1119,'1194045171_4','',330,4,1,1537472973,'a'),(1372,'1258130085_3','',396,3,40,1543521818,'a'),(1373,'1258130085_4','',396,4,40,1543521972,'a'),(1366,'1258130006_1','<h3 class=\"uppercase\">New Support Ticket</h3>',395,1,40,1539820450,'a'),(1367,'1258130006_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-support-tickets\">My Support Tickets</a></li>\n</ul>',395,2,40,1539820450,'a'),(1368,'1258130006_3','',395,3,40,1539906668,'a'),(1369,'1258130006_4','<p class=\"lead\" style=\"text-align: center;\">As a paying customer you have access to our support team.</p>',395,4,40,1543362289,'a'),(1371,'1258130085_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-support-tickets\">My Support Tickets</a></li>\n</ul>',396,2,40,1543521803,'a'),(1370,'1258130085_1','<h3 class=\"uppercase\">New Support Ticket</h3>',396,1,40,1542727608,'a'),(2509,'1459370585_6','',1012,6,40,1539211055,'a'),(2528,'1459534420_1','<h3 class=\"uppercase\">Blog</h3>',1016,1,40,1548293594,'a'),(2529,'1459534420_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1016,2,40,1539374559,'a'),(2530,'1459534420_3','',1016,3,40,1539374559,'a'),(2531,'1459534420_4','',1016,4,40,1539374831,'a'),(2582,'1462830858_1','<h3 class=\"uppercase\">New Services Project</h3>',1023,1,40,1542671155,'a'),(2532,'1460066529_6','',395,6,40,1539906684,'a'),(2533,'1460066545_6','<h6 class=\"heading-title\">Is this the right form to use?</h6>\n\n<p>If you are an existing customer, and you have a technical question or issue with your product or service, please complete this form.</p>\n\n<p>If you have a sales question, please start a<a href=\"{path}new-conversation\"> Conversation</a> instead.</p>\n\n<p>If you need assistance with professional services, you can <a href=\"{path}order-services\">Order Services</a> to start a new services project or reopen an existing one.</p>',395,6,40,1543944138,'a'),(2536,'1460139536_6','',401,6,40,1539979708,'a'),(2537,'1460139569_6','<h6 class=\"heading-title\">Support Tickets</h6>\n\n<p>Support Tickets are threaded discussions that are private between you and our technical support team. If you have a question, you can quickly start a new support ticket with us at anytime. So you won&#39;t miss anything, we will send you an email update whenever a reply is added to any of your support tickets.</p>',401,6,40,1543593581,'a'),(2545,'1461023423_6','',485,6,40,1540863590,'a'),(2546,'1461023451_6','<h6 class=\"uppercase\">About Our Plans</h6>\n\n<p>Sign up for one of our monthly recurring service plans and we will charge your payment card each billing period automatically.</p>\n\n<p>You can <a href=\"{path}new-support-ticket\">contact support</a> once you have placed your order if you need help or wish to cancel your plan.</p>',485,6,40,1547832541,'a'),(2544,'1460576746_6','',541,6,40,1542407812,'a'),(2685,'1464973494_6','<p style=\"text-align: center;\"><a class=\"btn btn-primary\" href=\"{path}members-home\">CONTINUE TO MEMBERS PORTAL</a></p>\n\n<p>If within 30 days, if you do not <a href=\"{path}members-access\">Pay for Access</a>, then you&#39;re login will be downgraded automatically and you won&#39;t be able to access any Membership Folders until you do.</p>',489,6,40,1545069882,'a'),(2664,'1464797423_6','',284,6,40,1544637713,'a'),(2552,'1461025250_6','',496,6,40,1540865422,'a'),(2553,'1461025289_6','',496,6,40,1543946039,'a'),(2647,'1464323073_3','',538,3,40,1544163304,'a'),(2620,'1462904437_1','<h3 class=\"uppercase\">New Services Project</h3>',1032,1,40,1542744620,'a'),(2621,'1462904437_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-services-projects\">My Services Projects</a></li>\n</ul>',1032,2,40,1543263469,'a'),(2622,'1462904437_3','',1032,3,40,1543262974,'a'),(2623,'1462904437_4','',1032,4,40,1543262785,'a'),(2640,'1463423826_6','',1029,6,40,1543263977,'a'),(2745,'1466033835_3','<h6 class=\"heading-title\">About Slider Gallery Widget</h6>\n\n<p>Place this page itself into any folder on your website and it will display all the photos in that folder.</p>\n\n<p>Take care to to include too many photos in the folder or the widget will load slowly.</p>\n\n<p><strong>All photos must be the same dimensions for the slider widget to work correctly.</strong></p>\n\n<p>By editing this page&#39;s custom layout, you can change the title and other behaviors.</p>',1050,3,40,1545875984,'a'),(2744,'1466033835_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1050,2,40,1545873974,'a'),(2641,'1463423838_6','',1029,6,40,1543264556,'a'),(2653,'1464721050_6','',407,6,40,1544561204,'a'),(2662,'1464794039_6','',638,6,40,1544634219,'a'),(2508,'1459370585_6','<h6 class=\"uppercase\">About Our Downloads</h6>\n\n<p>When you purchase any of our downloadable products, your website account will be upgraded and you will be able to instantly access the downloads.</p>',1012,6,40,1542669210,'a'),(2357,'1458316052_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',979,2,40,1538156191,'a'),(2356,'1458316052_1','<h3 class=\"uppercase\">Change Password</h3>',979,1,40,1538156301,'a'),(935,'1192552277_4','<h3>About The Member Portal</h3>\n\n<p>Any pages and files placed in a Membership Folder are accessible to all members in good standing (e.g. a User with a linked Contact that has both a <strong>Member ID</strong> and optionally, a <strong>Member Expiration Date</strong>.</p>\n\n<p>Anyone can become a member instantly when they <a href=\"{path}members-access\">order access</a>.</p>\n\n<h3>More Membership Features</h3>\n\n<p>You can also allow for<a href=\"{path}trial-membership-form\"> trial memberships</a>.</p>\n\n<p>You can also <a href=\"{path}collect-dues\">collect dues </a>online, even if you don&#39;t need to provide access to a Member Portal.</p>\n\n<p>You can also create an <a href=\"{path}event-registration\">event registration</a> for your members or anyone else for that matter.</p>\n\n<p>This is the &#39;Start Page&#39; assigned to all members when they become a member of this site.</p>\n\n<h3>Latest Member Activity</h3>\n\n<p>Here are all the recent form submissions from your members portal.</p>',284,4,40,1547831023,'a'),(934,'1192552277_3','<p class=\"lead\" style=\"text-align: center;\">Welcome Members!</p>',284,3,40,1544635819,'a'),(933,'1192552277_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',284,2,40,1539194592,'a'),(932,'1192552277_1','<h3 class=\"uppercase\">Members Home</h3>',284,1,40,1539194600,'a'),(2670,'1464798957_6','',568,6,40,1544639119,'a'),(704,'1192475315_1','<h3 class=\"uppercase\">Blog</h3>',227,1,40,1548293557,'a'),(705,'1192475315_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',227,2,40,1539279829,'a'),(706,'1192475315_3','',227,3,2,1537472973,'a'),(707,'1192475315_4','<h6 class=\"heading-title\">About The Blog</h6>\n\n<hr />\n<p>Here you will find the latest news and announcements from our team.</p>\n\n<p>&nbsp;</p>',227,4,40,1545891229,'a'),(1754,'1310969929_1','<h3 class=\"uppercase\">Create Quote</h3>',496,1,40,1541187067,'a'),(2790,'1466454687_1','<h3 class=\"uppercase\">Coming Soon</h3>\n\n<div class=\"countdown\" data-date=\"01/01/2020\">&nbsp;</div>\n\n<p>&nbsp;</p>\n\n<p>We&#39;ll be launching our new site in the coming weeks. Please join our announcement list to get notified when we launch.</p>',1059,1,40,1548263433,'a'),(2791,'1466454687_2','<p>We won&#39;t share your email with third parties.</p>',1059,2,40,1546295563,'a'),(1755,'1310969929_2','<ul class=\"breadcrumb\">\n	<li class=\"paragraph-box-primary\"><a class=\"link-button-secondary-small\" href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',496,2,40,1539383416,'a'),(1756,'1310969929_3','<h4 style=\"text-align: center;\">Create a quote &amp; email a shared link to your customers.</h4>',496,3,40,1548294192,'a'),(1757,'1310969929_4','<h6 class=\"heading-title\">About Quotes</h6>\n\n<p>By listing all the products from across your site on one page, you can easily create a quote (saved cart) and send it to your customer, members, donors, or anyone else who can then complete the checkout process on your website.</p>\n\n<p>Before you start a new quote, make sure you start a new cart by clicking <a href=\"livesite/do.php?action=reset_order&amp;url=/staff-create-quote\">this link</a> once.</p>\n\n<p>After you click &quot;Add to Quote&quot;, you will see the Cart page.&nbsp;The saved cart link to e-mail to your customer can be found at the bottom of that page.</p>',496,4,40,1548294129,'a'),(1758,'1310971170_1','<h3 class=\"uppercase\">Quote</h3>',497,1,40,1541187250,'a'),(1759,'1310971170_2','',497,2,2,1537472973,'a'),(1760,'1310971170_3','<h6 class=\"heading-title\">About This Quote Page</h6>\n\n<p>This quote page was created by taking an Express Order Page Type and adding HTML to the page&#39;s custom layout to disabled all fields so none of the product data, payment amounts, or items can be edited or removed. You can edit the quote from any other <a href=\"{path}express-order\">Express Order</a> or <a href=\"{path}cart\">Shopping Cart Page</a> before sending this page it to your customer. If you want your customer to be able to edit the quote, simply send them the Express Order or Shopping Cart page instead.</p>',497,3,40,1548293762,'a'),(1761,'1310971170_4','',497,4,40,1541187220,'a'),(1031,'1192574696_4','',308,4,1,1537472973,'a'),(1030,'1192574696_3','<h3 class=\"lead\" style=\"text-align: center;\">Congratulations! You passed the exam with the required 100% correct score!</h3>\n\n<p class=\"lead\" style=\"text-align: center;\"><strong>Please print this page for your records. A copy has also been emailed to you.</strong></p>',308,3,40,1542660305,'a'),(1029,'1192574696_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',308,2,40,1538954109,'a'),(1028,'1192574696_1','<h3 class=\"uppercase\" style=\"text-align: left;\">Exam Confirmation</h3>',308,1,40,1542659517,'a'),(1186,'1197875211_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',350,2,40,1545273814,'a'),(2752,'1466053595_4','<h5 class=\"uppercase\">Process</h5>\n\n<p class=\"mb0\">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>',936,4,40,1545893734,'a'),(2721,'1465482389_3','<h6 class=\"heading-title\">Calendar Widget</h6>\n\n<p>This dynamically displays the next 5 upcoming calendar events from the main calendar.</p>\n\n<p>You&#39;ll find this displayed on the footer.</p>',1045,3,40,1545870786,'a'),(2742,'1466030486_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;calendar-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors.</p>',1045,4,40,1545873660,'a'),(1185,'1197875211_1','<h3 class=\"uppercase\">Event Registration</h3>',350,1,40,1545273814,'a'),(788,'1192482053_1','<h3 class=\"uppercase\">Shopping Cart</h3>',248,1,40,1539210044,'a'),(789,'1192482053_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}shop-sidebar\">Shop</a></li>\n</ul>',248,2,40,1539210134,'a'),(790,'1192482053_3','',248,3,1,1537472973,'a'),(791,'1192482053_4','',248,4,40,1538269153,'a'),(2495,'1459176745_6','',179,6,40,1545763231,'a'),(2757,'1466182276_1','<h3 class=\"uppercase mb0\">About Us</h3>',886,1,40,1546022415,'a'),(2408,'1458749382_4','',994,4,40,1538589521,'a'),(2409,'1458749821_1','<h3 class=\"uppercase\">Photo Gallery</h3>\n\n<p>4 Columns</p>',995,1,40,1545762541,'a'),(2410,'1458749821_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',995,2,40,1538589960,'a'),(2411,'1458749821_3','<p class=\"lead\" style=\"text-align: center;\">Put this page in any Folder and all images in that Folder and sub folders will be displayed here.</p>',995,3,40,1538589960,'a'),(2412,'1458749821_4','',995,4,40,1538589960,'a'),(2413,'1458750039_1','<h3 class=\"uppercase\">Photo Gallery</h3>\n\n<p>2 Columns / Wide</p>',996,1,40,1545762599,'a'),(2526,'1459530533_3','',1015,3,40,1539370672,'a'),(2527,'1459530533_4','<h6 class=\"heading-title\">About The Blog</h6>\n\n<hr />\n<p>Here you will find the latest news and announcements from our team.</p>\n\n<p>&nbsp;</p>',1015,4,40,1545891204,'a'),(2521,'1459527896_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}blog-no-sidebar\">Blog No Sidebar </a></li>\n</ul>',1014,2,40,1539370630,'a'),(2522,'1459527896_3','',1014,3,40,1539368035,'a'),(2523,'1459527896_4','<h6 class=\"heading-title\">About The Author</h6>\n\n<hr />\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem antium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</p>',1014,4,40,1539368035,'a'),(2524,'1459530533_1','<h3 class=\"uppercase\">Blog</h3>',1015,1,40,1548293584,'a'),(2525,'1459530533_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1015,2,40,1539370672,'a'),(2520,'1459527896_1','<h3 class=\"uppercase\">Blog Post</h3>',1014,1,40,1548293614,'a'),(2516,'1459527519_1','<h3 class=\"uppercase mb0\">Blog</h3>',1013,1,40,1548293574,'a'),(2517,'1459527519_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1013,2,40,1539367658,'a'),(2518,'1459527519_3','',1013,3,40,1539367658,'a'),(2519,'1459527519_4','<h6 class=\"heading-title\">About The Author</h6>\n\n<hr />\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem antium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</p>',1013,4,40,1539367658,'a'),(1794,'1311194206_4','',506,4,2,1537472973,'a'),(800,'1192485499_1','<h3 class=\"uppercase\">Make A Payment</h3>',251,1,40,1539279698,'a'),(801,'1192485499_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',251,2,40,1538697325,'a'),(802,'1192485499_3','<p class=\"lead\">Now that you&#39;ve logged in, you can apply a payment to your account balance.</p>',251,3,40,1538696949,'a'),(803,'1192485499_4','',251,4,2,1537472973,'a'),(2507,'1459370585_4','',1012,4,40,1539210724,'a'),(1739,'1310963589_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',492,2,40,1542407959,'a'),(1008,'1192555270_1','<h3 class=\"uppercase\">Exam Notification</h3>',303,1,40,1542660550,'a'),(1009,'1192555270_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',303,2,40,1545274085,'a'),(1010,'1192555270_3','<p class=\"lead\" style=\"text-align: center;\">A exam has just been successfully completed with a passing score.</p>',303,3,40,1542660590,'a'),(1011,'1192555270_4','',303,4,1,1537472973,'a'),(2459,'1458833550_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a href=\"{path}my-conversations\">My Conversations</a></li>\n</ul>',1005,2,40,1544562440,'a'),(2460,'1458833550_3','<p class=\"lead\" style=\"text-align: center;\">Please complete this form and our sales team will get back with you shortly.</p>',1005,3,40,1543594500,'a'),(2461,'1458833550_4','',1005,4,40,1538674671,'a'),(2462,'1458833550_5','',1005,5,40,1538673689,'a'),(2458,'1458833550_1','<h3 class=\"uppercase\">New ConversatioN</h3>',1005,1,40,1538674613,'a'),(2463,'1458833550_6','<h6 class=\"heading-title\">Is This the Right Form?</h6>\n\n<p>If you need to contact our sales team, please complete this form.</p>\n\n<p>If you are trying to reach our support team, and you are a customer, please open a <a href=\"{path}new-support-ticket\">Support Ticket.</a></p>\n\n<p>If you would like to engage our professional services team, please <a href=\"{path}order-services\">Order Services</a>.</p>\n\n<p>For your convenience, this form will also update your Account Profile information.</p>',1005,6,40,1543594668,'a'),(1048,'1192735148_1','<h3 class=\"uppercase\">Add Blog PosT</h3>',313,1,40,1540404457,'a'),(1049,'1192735148_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',313,2,40,1543945830,'a'),(1050,'1192735148_3','<p class=\"lead\" style=\"text-align: center;\">Add a post to the <a href=\"{path}blog\">blog</a>.</p>',313,3,40,1544201169,'a'),(1051,'1192735148_4','',313,4,2,1537472973,'a'),(1063,'1193152917_4','',316,4,2,1537472973,'a'),(1061,'1193152917_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-directory\">Staff Directory</a></li>\n</ul>',316,2,40,1544171055,'a'),(1060,'1193152917_1','<h3 class=\"uppercase\">Staff Directory</h3>',316,1,40,1544171024,'a'),(1062,'1193152917_3','',316,3,40,1545329745,'a'),(1158,'1196881775_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',343,2,40,1538440500,'a'),(1157,'1196881775_1','<h3 class=\"uppercase\">Photo Gallery</h3>\n\n<p>3 Columns</p>',343,1,40,1545762567,'a'),(1159,'1196881775_3','<p class=\"lead\" style=\"text-align: center;\">Put this page in any Folder and all images in that Folder and sub folders will be displayed here.</p>',343,3,40,1538440448,'a'),(1160,'1196881775_4','',343,4,2,1537472973,'a'),(1799,'1311352111_1','<h3 class=\"uppercase\">Mailing List</h3>',508,1,40,1545264356,'a'),(2672,'1464826268_6','',330,6,40,1544666822,'a'),(2673,'1464826275_6','',330,6,40,1544666758,'a'),(1801,'1311352111_3','',508,3,40,1545263184,'a'),(1713,'1310875653_4','',485,4,40,1538696538,'a'),(1201,'1198256816_1','<h3 class=\"uppercase\">Classified Ad</h3>',354,1,40,1544639014,'a'),(2671,'1464798980_6','<h6 class=\"heading-title\">About Members Directory</h6>\n\n<p>As a member, you can add an entry to the directory. Once you add an entry you can click on the entry and edit, or delete it at any time. If you do delete it, you can add your entry again.</p>\n\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}members-directory-form\">Add Your Entry</a></p>',568,6,40,1544668038,'a'),(1202,'1198256816_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}classified-ads\">Classified Ads</a></li>\n</ul>',354,2,40,1544639056,'a'),(1203,'1198256816_3','<p class=\"lead\" style=\"text-align: center;\">As a member you can post your questions to the seller here.</p>',354,3,40,1544638831,'a'),(1204,'1198256816_4','',354,4,2,1537472973,'a'),(1697,'1310852962_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',481,2,40,1545081514,'a'),(1698,'1310852962_3','<div class=\"row\">\n<div class=\"col-sm-7 col-sm-offset-2\">\n<p class=\"lead\" style=\"text-align: center;\">Click on any available training session to reserve it.</p>\n</div>\n\n<div class=\"col-sm-3\">\n<p style=\"text-align: right;\"><span style=\"color: green; font-weight: bold; font-size: 130%; margin-right: 0.25em;\">●</span>Still Available</p>\n</div>\n</div>',481,3,40,1545086195,'a'),(1699,'1310852962_4','',481,4,40,1545086141,'a'),(2696,'1465249754_6','',191,6,40,1545089910,'a'),(2695,'1465249739_6','<h6 class=\"heading-title\">About Calendars</h6>\n\n<p>Calendars can simply provide general information about an event, but calendars can also be tied to available seats and even online ordering so booking appointments, training sessions, concert tickets, and other event-related products and services can be managed automatically and not oversold.</p>\n\n<p>Event locations are also managed automatically so events cannot be accidentally scheduled to overlap in the same location at the same time.</p>',191,6,40,1548293073,'a'),(1700,'1310853116_1','<h3 class=\"uppercase\">Schedule Training</h3>',482,1,40,1545082609,'a'),(1701,'1310853116_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}schedule-training\">Schedule Training</a></li>\n</ul>',482,2,40,1545082609,'a'),(1702,'1310853116_4','<p class=\"lead\" style=\"text-align: center;\">Available training sessions can be booked up to the minute before they start.</p>',482,4,40,1545082550,'a'),(1703,'1310874308_1','<h3 class=\"uppercase\">Class Registration</h3>',483,1,40,1545075292,'a'),(1704,'1310874308_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',483,2,40,1545075316,'a'),(1705,'1310874308_3','<div class=\"row\">\n<div class=\"col-sm-7 col-sm-offset-2\">\n<p class=\"lead\" style=\"text-align: center;\">Order online to reserve your seat.</p>\n</div>\n\n<div class=\"col-sm-3\">\n<p style=\"text-align: right;\"><span style=\"color: green; font-weight: bold; font-size: 130%; margin-right: 0.25em;\">●</span>Class Available</p>\n</div>\n</div>',483,3,40,1545081719,'a'),(1706,'1310874308_4','',483,4,40,1546984056,'a'),(2817,'1467302817_3','<h6 class=\"heading-title\">Upcoming Concert Widget</h6>\n\n<p>This dynamically displays the next 4 upcoming events from the ticket calendar.</p>\n\n<p>You&#39;ll find this displayed on the <a href=\"{path}home-music\">Music</a> Home Page.</p>',1063,3,40,1547152556,'a'),(1707,'1310874332_1','<h3 class=\"uppercase\">Class Registration</h3>',484,1,40,1545081901,'a'),(1708,'1310874332_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}class-registration\">Class Registration</a></li>\n</ul>',484,2,40,1545082635,'a'),(1709,'1310874332_4','<p class=\"lead\" style=\"text-align: center;\">Order online to reserve your seat.</p>',484,4,40,1545079059,'a'),(1710,'1310875653_1','<h3 class=\"uppercase\">Service Plans</h3>',485,1,40,1538761118,'a'),(1711,'1310875653_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',485,2,40,1538696620,'a'),(1695,'1310850903_4','',480,4,40,1545091545,'a'),(1458,'1272573905_1','<h3 class=\"uppercase\">Membership &amp; Renewal</h3>',418,1,40,1547847881,'a'),(1459,'1272573905_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',418,2,40,1538763153,'a'),(1520,'1278695840_1','<h4>New Forum Reply</h4>',433,1,40,1544653556,'a'),(2686,'1464973523_6','',489,6,40,1544813690,'a'),(1521,'1278695840_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',433,2,40,1544653544,'a'),(1522,'1278695840_3','<p class=\"lead\" style=\"text-align: center;\">A reply has been added to a forum thread you are interested in.</p>',433,3,40,1544653452,'a'),(1523,'1278695840_4','<hr size=\"2\" width=\"100%\" />\n<p><strong>Why did I get this e-mail?</strong><br />\nYou have requested to be notified via e-mail whenever a new reply is added to this forum thread.</p>\n\n<p><strong>I am no longer interested in this forum thread. How do I stop the e-mail notifications?</strong><br />\nClick on the &quot;View or Reply&quot; link above to go to the forum thread. Then go to the bottom of the page and click &#39;Remove Me&#39;.</p>\n\n<p><strong>If I remove myself from this notification, can I still get other notifications I have added myself too?</strong><br />\nYes. Each forum thread is handled separately.</p>',433,4,40,1544653716,'a'),(1527,'1286330593_1','<h3 class=\"uppercase\">Blog Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',435,1,40,1545873579,'a'),(1528,'1286330593_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',435,2,40,1545869954,'a'),(1529,'1286330593_3','<h6 class=\"heading-title\">Blog Widget</h6>\n\n<p>This dynamically displays the latest 3 blog postings.</p>\n\n<p>You&#39;ll find this displayed on the footer and some sidebar pages.</p>\n\n<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;blog-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>',435,3,40,1545873504,'a'),(2741,'1466029762_4','',435,4,40,1545873504,'a'),(2727,'1465490561_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n	<li><a href=\"{path}all-staff-directory\">All Staff Directory</a></li>\n</ul>',1047,2,40,1545330817,'a'),(1537,'1286381928_1','<h3 class=\"uppercase\">Send News</h3>',438,1,40,1544163854,'a'),(2726,'1465490561_1','<h3 class=\"uppercase\">All Staff Directory</h3>',1047,1,40,1545330700,'a'),(2652,'1464720987_6','<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}forum-thread-form\">New Forum Thread</a></p>\n\n<h6 class=\"heading-title\">About the Forum</h6>\n\n<p>Visitors can read the forum. Registered users can start a new forum thread or reply to an existing thread.</p>',407,6,40,1548294391,'a'),(2650,'1464387197_3','',539,3,40,1544544703,'a'),(1538,'1286381928_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',438,2,40,1544163854,'a'),(1539,'1286381928_3','<p class=\"lead\" style=\"text-align: center;\">Although you can send any page as an e-mail campaign,&nbsp;here&#39;s a niffy way to send the last modified blog posting in an email campaign.</p>',438,3,40,1544163941,'a'),(1540,'1286381928_4','<div class=\"row\">\n<div class=\"col-sm-6\">\n<h5>Step 2: Click &quot;Edit&quot; button below to personalize your email template fields and &quot;Save&quot;.</h5>\n</div>\n\n<div class=\"col-sm-6\">\n<h5>Step 3. Create &amp; Send your email campaign</h5>\n\n<p><a class=\"btn\" href=\"{path}livesite/add_email_campaign.php?page_id=439\">Create &amp; Send</a></p>\n</div>\n</div>',438,4,40,1544164842,'a'),(1541,'1286382103_1','<h3 class=\"uppercase\">Latest News</h3>',439,1,40,1544164948,'a'),(1542,'1286382103_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">HomE</a></li>\n</ul>',439,2,40,1545274317,'a'),(1543,'1286382103_3','',439,3,2,1537472973,'a'),(1544,'1286382103_4','',439,4,40,1544164913,'a'),(2722,'1465489764_1','<h3 class=\"uppercase\">All Staff Directory</h3>',1046,1,40,1545330228,'a'),(2723,'1465489764_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',1046,2,40,1545330262,'a'),(2724,'1465489764_3','<p class=\"lead\" style=\"text-align: center;\">This is the private staff directory.&nbsp; <span class=\"primary-background-color\">&nbsp; Highlighted &nbsp; </span> &nbsp; staff are also listed in the public <a href=\"{path}staff-directory\">Staff Directory</a>.</p>',1046,3,40,1545341743,'a'),(2725,'1465489764_4','',1046,4,40,1545340857,'a'),(2295,'1457730278_1','<h4 class=\"uppercase\">Login</h4>',895,1,40,1537837316,'a'),(2296,'1457731714_2','',895,2,40,1537837667,'a'),(2335,'1458251261_1','<h3 class=\"uppercase\">My Account Profile</h3>',975,1,40,1538091681,'a'),(2737,'1466029221_1','<h3 class=\"uppercase\">Photo Gallery Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',1049,1,40,1545870444,'a'),(2294,'1457729766_2','',960,2,40,1537569905,'a'),(2293,'1457729766_1','<h4 class=\"uppercase\">GoodBye.</h4>',960,1,40,1537572075,'a'),(2821,'1467396454_3','<h6 class=\"heading-title\">Staff Directory Widget</h6>\n\n<p>This dynamically displays the same public staff directory entries as this <a href=\"{path}staff-directory\">page</a> only with different options and layout.</p>\n\n<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;staff-directory-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>',1064,3,40,1547237142,'a'),(2822,'1467396454_4','',1064,4,40,1547236593,'a'),(2789,'1466447876_6','<h2 class=\"mb8\">Start a Services Project</h2>\n\n<p class=\"lead mb40\">We make it easy to get started.</p>\n\n<p><a class=\"btn\" href=\"{path}order-services\">Order Services</a></p>',1058,6,40,1546290248,'a'),(2813,'1466566254_3','<h6 class=\"heading-title\">Blog Posts Widget</h6>\n\n<p>This dynamically displays the latest 3 blog postings as responsive boxes.</p>\n\n<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;blog-posts-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>',1062,3,40,1546410562,'a'),(2794,'1466454842_1','<h3 class=\"uppercase\">thank you!</h3>\n\n<p class=\"lead\">We will notify you when our site launches.</p>\n\n<p>&nbsp;</p>',1060,1,40,1546295985,'a'),(2795,'1466454842_2','',1060,2,40,1546295985,'a'),(2796,'1466454842_3','<h3 class=\"lead\">Thank you!</h3>\n\n<p>If you are new to our site, we have just sent you an offer code for $3 off our eBook.</p>\n\n<p>We will also add you to our mailing list. You can opt-out at any time from any message we send you in the future.</p>\n\n<p>We will not share your email with anyone!</p>\n\n<p><a class=\"btn\" href=\"javascript:history.go(-1)\">Back to previous page </a></p>',1060,3,40,1546294981,'a'),(2797,'1466454842_4','',1060,4,40,1546294981,'a'),(2812,'1466566254_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1062,2,40,1546406393,'a'),(2800,'1466517591_1','',863,1,40,1546379625,'a'),(2809,'1466542837_1','',882,1,40,1546470174,'a'),(2801,'1466517670_1','',863,1,40,1546365384,'a'),(2808,'1466535873_1','',866,1,40,1546469780,'a'),(2802,'1466521356_2','',863,2,40,1546361495,'a'),(2803,'1466521356_3','<h1 class=\"large\">liveSite brings your beautiful website designs to life</h1>\n\n<h6 class=\"uppercase\">A complete page-based solution</h6>\n\n<p class=\"lead\">Connect our built-in and proven application components together<br />\nto create your own custom website solutions.</p>\n\n<p><a class=\"btn btn-lg btn-white\" href=\"{path}about-us\">Learn More</a></p>',863,3,40,1546361495,'a'),(2818,'1467302817_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;upcoming-concerts-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors.</p>',1063,4,40,1547148599,'a'),(2810,'1466555641_2','',882,2,40,1546470302,'a'),(2811,'1466566254_1','<h3 class=\"uppercase\">Blog Posts Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',1062,1,40,1546410171,'a'),(1931,'1334603197_1','<h3 class=\"uppercase\">Add Video</h3>',538,1,40,1544163279,'a'),(2646,'1464323068_3','<p class=\"lead\" style=\"text-align: center;\">Post a youtube video to the <a href=\"{path}video-gallery\">Video Gallery</a> page.</p>',538,3,40,1544163207,'a'),(1932,'1334603197_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}staff-home\">&nbsp;Staff Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',538,2,40,1544163279,'a'),(1933,'1334604214_1','<h3 class=\"uppercase\">Video Gallery</h3>',539,1,40,1547835597,'a'),(2649,'1464387191_3','',539,3,40,1544550181,'a'),(1934,'1334604214_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',539,2,40,1544227372,'a'),(1935,'1334605178_1','<h3 class=\"uppercase\">Video Gallery Item</h3>',540,1,40,1544551355,'a'),(2651,'1464711197_3','<p class=\"lead\" style=\"text-align: center;\">Click on the &quot;Edit&quot; button below to edit this video from the <a href=\"{path}video-gallery\">Video Gallery</a>.</p>',540,3,40,1544556087,'a'),(1936,'1334605178_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',540,2,40,1544551403,'a'),(1937,'1334700738_1','<h3 class=\"uppercase\">Latest Activity</h3>',541,1,40,1548292436,'a'),(2543,'1460576719_6','<p>&nbsp;</p>\n\n<p>Adding this Page&#39;s ID and &quot;search=true&quot; when linking to this page exposes the &quot;Search by Submitter&quot; Search feature:</p>\n\n<p><a class=\"btn\" href=\"{path}staff-latest-activity?541_search=true\">/staff-latest-activity?541_search=true</a></p>',541,6,40,1546552791,'a'),(1938,'1334700738_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}staff-home\">Staff Home</a></li>\n</ul>',541,2,40,1540416940,'a'),(1939,'1334700738_3','<p class=\"lead\" style=\"text-align: center;\">Here are all the recent form submissions from across your site that you have access too.</p>',541,3,40,1540416983,'a'),(1940,'1334700738_4','',541,4,2,1537472973,'a'),(1909,'1332885417_1','<h3 class=\"uppercase\">Order Receipt</h3>',533,1,40,1542662882,'a'),(1913,'1332897690_1','<h3 class=\"uppercase\">Donation Receipt</h3>',534,1,40,1542663096,'a'),(1910,'1332885417_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">home</a></li>\n</ul>',533,2,40,1545274274,'a'),(1911,'1332885417_3','',533,3,40,1542663040,'a'),(2605,'1462904100_4','',1028,4,40,1542744239,'a'),(2606,'1462904100_6','',1028,6,40,1542744239,'a'),(2607,'1462904100_6','<h6 class=\"heading-title\">Services Projects</h6>\n\n<p>Services Projects provide a simple way to communicate securely with the professional services team assigned to your project. You can make requests, attach resource files, and get a current accounting of remaining credits available for work to be completed. You can also add credits to any project to keep the team focused and available for you at any time.</p>\n\n<p>You can start a new Services Project at any time by <a href=\"{path}order-services\">Ordering Services</a>.</p>',1028,6,40,1543594319,'a'),(2608,'1462904162_1','<h3 class=\"uppercase\">Services Project</h3>',1029,1,40,1542744383,'a'),(2609,'1462904162_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}my-services-projects\">My Services Projects</a></li>\n</ul>',1029,2,40,1542744408,'a'),(1912,'1332885417_4','',533,4,2,1537472973,'a'),(1914,'1332897690_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',534,2,40,1545274261,'a'),(1915,'1332897690_3','',534,3,40,1542663070,'a'),(2580,'1462826586_6','',979,6,40,1542666737,'a'),(2581,'1462826598_6','<h6 class=\"heading-title\">Password Information</h6>\n\n<p>Your password is always stored as encrypted data.</p>',979,6,40,1542666877,'a'),(2596,'1462887341_3','<p class=\"lead\" style=\"text-align: center;\">Click on the Services Project to add a reply which will be emailed to the project owner and all participants.</p>',1025,3,40,1543514440,'a'),(2597,'1462887341_4','',1025,4,40,1542727480,'a'),(1916,'1332897690_4','',534,4,2,1537472973,'a'),(2739,'1466029221_3','<h6 class=\"heading-title\">About Photo Gallery Widget</h6>\n\n<p>Place this page itself into any folder on your website and it will display all the photos in that folder.</p>\n\n<p>You can even click on the photos to enlarge them.</p>\n\n<p>Photos can be of different dimensions but similar dimensions will stack and flow best.</p>\n\n<p>Take care to to include too many photos in the folder or the widget will load slowly.</p>\n\n<p>An example of this widget is in the sidebar of the <a href=\"{path}blog\">Blog</a> page.</p>',1049,3,40,1545891113,'a'),(2740,'1466029221_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;photo-gallery-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>',1049,4,40,1545873629,'a'),(2011,'1353595086_1','<h3 class=\"uppercase\">Members Directory</h3>',568,1,40,1544636825,'a'),(2012,'1353595086_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n</ul>',568,2,40,1544636883,'a'),(2013,'1353595086_3','<p class=\"lead\" style=\"text-align: center;\">Connect with other members and keep your own entry up-to-date.</p>',568,3,40,1544636810,'a'),(2014,'1353595086_4','',568,4,40,1544639096,'a'),(1981,'1350414691_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',558,2,40,1542658709,'a'),(2578,'1462818425_6','<h6 class=\"heading-title\">My Content Information</h6>\n\n<p>This page will display any Private Folder content that you have access too based on orders you have made by purchasing access to the downloadable goods and exam products.</p>\n\n<p>Your access to this content can be set to expire in which case it will disappear from this page over time.</p>',558,6,40,1542668390,'a'),(1982,'1350414691_3','<p class=\"lead\" style=\"text-align: center;\">Listing of all purchased content your User account currently has access too.</p>',558,3,40,1542670425,'a'),(1983,'1350414691_4','',558,4,2,1537472973,'a'),(1980,'1350414691_1','<h3 class=\"uppercase\">My Content</h3>',558,1,40,1542658676,'a'),(2057,'1357179167_1','<h3 class=\"uppercase\">Mailing List</h3>',579,1,40,1545264338,'a'),(2720,'1465482389_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1045,2,40,1545870643,'a'),(2760,'1466184229_4','<h4 class=\"uppercase\">Services We Offer</h4>',886,4,40,1546282933,'a'),(2780,'1466443895_1','<h3 class=\"uppercase mb0\">Support</h3>',1057,1,40,1546284034,'a'),(2781,'1466443919_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1057,2,40,1546284075,'a'),(2782,'1466444003_3','<h3>Outstanding Customer Support</h3>\n\n<p class=\"lead mb40\" style=\"text-align: center;\">Once you make a purchase, you will have access to our technical support team.</p>\n\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}new-support-ticket\"><i class=\"ti-lock\">&nbsp;</i> Create Support Ticket</a></p>\n\n<p style=\"text-align: center;\">If you are not a customer yet, please <a href=\"{path}contact-us\">contact us</a> and our sales team can help you.</p>',1057,3,40,1548294417,'a'),(2786,'1466447824_3','<div class=\"feature feature-1 feature-1\">\n<div class=\"left\"><i class=\"ti-ruler-alt-2 icon-lg\"><span style=\"display:none\">&nbsp;</span></i></div>\n\n<div class=\"right\">\n<h3>Planning</h3>\n\n<p class=\"mb0\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>\n</div>\n</div>',1058,3,40,1546292805,'a'),(2784,'1466446274_1','<h3 class=\"uppercase mb0\">Services</h3>',1058,1,40,1546286413,'a'),(2785,'1466446367_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1058,2,40,1546286506,'a'),(2779,'1466442754_5','<h3>Lorem ipsum dolor</h3>\n\n<p class=\"lead\">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>',886,5,40,1546282893,'a'),(2783,'1466444419_4','<h3 style=\"text-align: center;\">&nbsp;</h3>\n\n<h3 style=\"text-align: center;\">Outstanding Services</h3>\n\n<p class=\"lead\" style=\"text-align: center;\">We also offer a full line of <a href=\"{path}services\">services</a> to ensure your project gets done smoothly.</p>\n\n<p class=\"lead\" style=\"text-align: center;\">&nbsp;</p>\n\n<p class=\"lead\" style=\"text-align: center;\">&nbsp;</p>',1057,4,40,1548292141,'a'),(2058,'1357179167_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>\n\n<p>&nbsp;</p>',579,2,40,1539118500,'a'),(2059,'1357179167_3','<h3 class=\"lead\">Thank you!</h3>\n\n<p>If you are new to our site, we have just sent you an offer code for $3 off our eBook.</p>\n\n<p>We will also add you to our mailing list. You can opt-out at any time from any message we send you in the future.</p>\n\n<p>We will not share your email with anyone!</p>\n\n<p><a class=\"btn\" href=\"javascript:history.go(-1)\">Back to previous page </a></p>',579,3,40,1545264018,'a'),(2060,'1357179167_4','',579,4,2,1537472973,'a'),(2814,'1466566254_4','',1062,4,40,1546406393,'a'),(2819,'1467396454_1','<h3 class=\"uppercase\">Staff Directory Widget</h3>',1064,1,40,1547236641,'a'),(2793,'1466454687_4','',1059,4,40,1546295507,'a'),(2588,'1462832313_1','<h3 class=\"uppercase\">New Services Project Confirmation</h3>',1024,1,40,1542672506,'a'),(2584,'1462830858_3','',1023,3,40,1542670997,'a'),(2077,'1366649256_1','<h3 class=\"uppercase\">Buy Tickets: Wait List</h3>',584,1,40,1545092704,'a'),(2702,'1465316009_6','<h6 class=\"heading-title\">About Wait List</h6>\n\n<p>We&#39;re sorry that the event is sold out, but sometimes we get more tickets or there are cancellations so please submit this form so we can contact you if tickets become available.</p>\n\n<p>Please be sure to specify the event, date, and number of tickets you need.</p>',584,6,40,1545159831,'a'),(2078,'1366649256_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}buy-tickets\">Buy Tickets</a></li>\n</ul>',584,2,40,1545092704,'a'),(2079,'1366649256_4','<p class=\"lead\" style=\"text-align: center;\">Please add yourself to the wait list for this event.</p>',584,4,40,1545092745,'a'),(2108,'1394142235_1','<h3 class=\"uppercase\">Forum</h3>',597,1,40,1544633861,'a'),(2660,'1464793973_5','',598,5,40,1544634125,'a'),(2661,'1464794017_6','<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}forum-thread-form\">New Forum Thread</a></p>\n\n<h6 class=\"heading-title\">About the Forum</h6>\n\n<p>Visitors can read the forum. Registered users can start a new forum thread or reply to an existing thread.</p>',638,6,40,1548294479,'a'),(2109,'1394142235_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',597,2,40,1544633917,'a'),(2110,'1394142235_3','<div class=\"row\">\n<div class=\"col-sm-8\">\n<p class=\"lead\" style=\"text-align: center;\">Join the most popular discussions!</p>\n</div>\n\n<div class=\"col-sm-4\"><a class=\"link-button-secondary-small\" href=\"{path}forum\">Most Recent</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-popular\" style=\"filter: alpha(opacity=50); opacity: 0.5;\">Most Popular</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-active\">Most Active</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-my-forum\">My Forum</a></div>\n</div>',597,3,40,1544634503,'a'),(2111,'1394142235_4','',597,4,2,1537472973,'a'),(2112,'1394142258_1','<h3 class=\"uppercase\">Forum</h3>',598,1,40,1544633872,'a'),(2663,'1464794290_6','',597,6,40,1544634453,'a'),(2659,'1464793964_6','<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}forum-thread-form\">New Forum Thread</a></p>\n\n<h6 class=\"heading-title\">About the Forum</h6>\n\n<p>Visitors can read the forum. Registered users can start a new forum thread or reply to an existing thread.</p>',598,6,40,1548294465,'a'),(2113,'1394142258_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',598,2,40,1544633929,'a'),(2114,'1394142258_3','<div class=\"row\">\n<div class=\"col-sm-8\">\n<p class=\"lead\" style=\"text-align: center;\">Join the most active discussions!</p>\n</div>\n\n<div class=\"col-sm-4\">\n<p><a class=\"link-button-secondary-small\" href=\"{path}forum\">Most Recent</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-popular\">Most Popular</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-active\" style=\"filter: alpha(opacity=50); opacity: 0.5;\">Most Active</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-my-forum\">My Forum</a></p>\n</div>\n</div>',598,3,40,1544634525,'a'),(2115,'1394142258_4','',598,4,2,1537472973,'a'),(2227,'1412608529_1','<h3 class=\"uppercase\">Forum</h3>',638,1,40,1544633883,'a'),(2658,'1464793950_6','<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}forum-thread-form\">New Forum Thread</a></p>\n\n<h6 class=\"heading-title\">About the Forum</h6>\n\n<p>Visitors can read the forum. Registered users can start a new forum thread or reply to an existing thread.</p>',597,6,40,1548294447,'a'),(2226,'1412266544_4','',637,4,40,1545273014,'a'),(2225,'1412266544_3','<h3>$^^amount^^ Gift Card</h3>\n\n<hr />\n<p>&nbsp;</p>\n\n<blockquote>\n<table border=\"0\">\n	<tbody>\n		<tr>\n			<td style=\"vertical-align: top;\">From:</td>\n			<td>&nbsp;</td>\n			<td style=\"vertical-align: top;\">[[^^from_name^^||Anonymous Giver]]</td>\n		</tr>\n		<tr>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n			<td>&nbsp;</td>\n		</tr>\n		<tr>\n			<td style=\"vertical-align: top;\">Message:&nbsp;</td>\n			<td>&nbsp;</td>\n			<td style=\"vertical-align: top;\">^^message^^</td>\n		</tr>\n	</tbody>\n</table>\n</blockquote>\n\n<p>&nbsp;</p>\n\n<hr />\n<p><br />\nYour Gift Card Code:&nbsp;<strong>^^code^^</strong></p>\n\n<p>To redeem your gift card, simply enter the gift card code above during your check out after <a href=\"{path}\">shopping online</a>&nbsp;at our website.</p>\n\n<p><span class=\"text-fine-print\">This Gift Card is subject to the terms and conditions <a href=\"{path}\">posted</a> on our website.</span></p>',637,3,40,1545273048,'a'),(2224,'1412266544_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',637,2,40,1545273077,'a'),(2223,'1412266544_1','<h3 class=\"uppercase\">A Gift for You</h3>',637,1,40,1545274425,'a'),(2228,'1412608529_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a class=\"link-button-secondary-small\" href=\"{path}forum\">Forum</a></li>\n</ul>',638,2,40,1544633943,'a'),(2229,'1412608529_3','<div class=\"row\">\n<div class=\"col-sm-8\">\n<p class=\"lead\" style=\"text-align: center;\">Join the discussion you created, participated in, or are watching!</p>\n</div>\n\n<div class=\"col-sm-4\">\n<p><a class=\"link-button-secondary-small\" href=\"{path}forum\">Most Recent</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-popular\">Most Popular</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-most-active\">Most Active</a> &nbsp; <a class=\"link-button-secondary-small\" href=\"{path}forum-my-forum\" style=\"filter: alpha(opacity=50); opacity: 0.5;\">My Forum</a></p>\n</div>\n</div>',638,3,40,1544634579,'a'),(2230,'1412608529_4','',638,4,2,1537472973,'a'),(2231,'1412619705_1','<h3 class=\"uppercase\">Order Services</h3>',639,1,40,1538763726,'a'),(2232,'1412619705_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',639,2,40,1538763655,'a'),(2233,'1412619705_3','<p class=\"lead\" style=\"text-align: center;\">Start a new services project or add credits to an existing services project.</p>',639,3,40,1543251148,'a'),(2234,'1412619705_4','',639,4,2,1537472973,'a'),(2261,'1436975566_1','<h3 class=\"uppercase\">My Conversations</h3>',647,1,40,1538691928,'a'),(2482,'1458851995_6','',647,6,40,1538693128,'a'),(2262,'1436975566_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',647,2,40,1538691991,'a'),(2263,'1436975566_3','<p class=\"lead\" style=\"text-align: center;\">Private and secure sales conversations you have access too.</p>',647,3,40,1543939792,'a'),(2264,'1436975566_4','',647,4,2,1537472973,'a'),(2265,'1436977077_1','<h3 class=\"uppercase\">My Conversation</h3>',648,1,40,1543538557,'a'),(2266,'1436977077_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a href=\"{path}my-conversations\">My Conversations</a></li>\n</ul>',648,2,40,1543595577,'a'),(2267,'1436977077_3','',648,3,2,1537472973,'a'),(2268,'1436977077_4','',648,4,2,1537472973,'a'),(2269,'1436978524_1','<h3 class=\"uppercase\">Conversation Update</h3>',649,1,40,1543874880,'a'),(2270,'1436978524_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}my-account\">My Account</a></li>\n	<li><a href=\"{path}my-conversations\">My Conversations</a></li>\n</ul>',649,2,40,1543874856,'a'),(2271,'1436978524_3','<p class=\"lead\" style=\"text-align: center;\">A reply has been added to a conversation of interest to you.</p>',649,3,40,1543874833,'a'),(2272,'1436978524_4','<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\">&nbsp;</p>\n\n<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: border-box; font-weight: bold;\">Why am I getting this email?</strong></p>\n\n<p style=\"box-sizing: border-box; margin: 0px 0px 24px; padding: 0px; font-weight: normal; color: rgb(102, 102, 102); font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: start; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(255, 255, 255);\">You are either listed as the owner of this conversation, or you have been give access to view and reply by the owner. If you wish to view, reply, or remove yourself as a watcher, all of these actions can be completed from the conversation page itself. To access the protected conversation page, you will need to follow the link above to register or login.</p>',649,4,40,1543940219,'a'),(2289,'1453764094_1','<h3 class=\"uppercase\">Mailing List</h3>',656,1,40,1545264317,'a'),(2285,'1453416864_1','<h3 style=\"text-align: center; padding-top: 0px; margin-top: 0px;\">Special Offer</h3>\n\n<p style=\"text-align: center;\"><a href=\"{path}shop-product-sidebar/eBook\" target=\"_parent\"><img alt=\"\" src=\"{path}ebook.jpg\" style=\"width: 350px; height: 350px;\" /></a></p>\n\n<h5 style=\"text-align: center;\">Save $3 on our popular eBook!</h5>\n\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}shop-product-sidebar/eBook\" target=\"_parent\">Order Now</a></p>\n\n<p style=\"text-align: center;\">&nbsp;</p>',654,1,40,1545260304,'a'),(2286,'1453416864_2','',654,2,2,1537472973,'a'),(2287,'1453503037_1','<h3 style=\"text-align: center; padding-top: 0px; margin-top: 0px;\">$5 Off Tickets!</h3>\n\n<p style=\"text-align: center;\"><a href=\"{path}buy-tickets?o=5OFFTIX\"><img alt=\"\" src=\"{path}concert-in-the-park.jpg\" /></a></p>\n\n<h4 style=\"text-align: center;\">Concert in the Park!</h4>\n\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}buy-tickets?o=5OFFTIX\" target=\"_parent\">Find Tickets</a></p>\n\n<p style=\"text-align: center;\">&nbsp;</p>',655,1,40,1545262891,'a'),(2288,'1453503037_2','',655,2,2,1537472973,'a'),(2290,'1453764094_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',656,2,40,1545263355,'a'),(2291,'1453764094_3','<div>[[\n<h5>Thank you for joining our mailing list!</h5>\n\n<p>We have created a personal offer code, just for you, to get $3 off our popular eBook.</p>\n\n<p>Your personal offer code is: <strong>^^key_code^^</strong></p>\n\n<p>Simply click <strong><a href=\"{path}shop-product-sidebar/eBook?o=^^key_code^^\" style=\"text-decoration: underline\">this link to our website</a></strong> to have the offer code automatically applied. Or, type the code above into the Offer Code field of the shopping cart and click &quot;Update Cart&quot;.</p>\n\n<p>Offer code may only be used once and may not be used in combination with other offers. Offer code will expire in 30 days.</p>\n||\n\n<h5>Thank you for confirming your mailing list subscription!</h5>\n\n<p>Since you have subscribed to our mailing list in the past, we aren&#39;t allowed to send you the eBook offer, but be on the lookout for other fresh offers delivered to your inbox soon!</p>\n]]</div>',656,3,40,1545264288,'a'),(2292,'1453764094_4','',656,4,40,1545263462,'a'),(2759,'1466182354_3','<h4 class=\"uppercase\">We Are Different</h4>\n\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>',886,3,40,1546282937,'a'),(2319,'1458229563_1','<h3 class=\"uppercase\">My Account</h3>',971,1,40,1538069789,'a'),(2320,'1458229563_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',971,2,40,1538082905,'a'),(2321,'1458229563_3','',971,3,40,1538676824,'a'),(2322,'1458229563_4','',971,4,40,1538069702,'a'),(2303,'1458149498_5','',216,5,40,1537999460,'a'),(2304,'1458149513_6','<ul class=\"accordion accordion-1\">\n	<li>\n	<div class=\"title\"><span>Search Tips</span></div>\n\n	<div class=\"content\">\n	<p>To search for a single keyword:<br />\n	<span style=\"font-family: \'courier new\', courier;\">mykeyword</span><br />\n	<br />\n	To search for a single phrase:<br />\n	<span style=\"font-family: \'courier new\', courier;\">&quot;my phrase&quot;</span><br />\n	<br />\n	To search for one AND the other:<br />\n	<span style=\"font-family: \'courier new\', courier;\">+&quot;my phrase&quot; +mykeyword</span><br />\n	<br />\n	To search for one OR the other:<br />\n	<span style=\"font-family: \'courier new\', courier;\">&quot;my phrase&quot; mykeyword</span><br />\n	<br />\n	To search for one WITHOUT the other:<br />\n	&nbsp;<span style=\"font-family: \'courier new\', courier;\">+&quot;my phrase&quot; -mykeyword</span><br />\n	<br />\n	To search for a file by name:<br />\n	<span style=\"font-family: \'courier new\', courier;\">filename.pdf</span><br />\n	<br />\n	<span class=\"text-fine-print\">NOTE: When plus (+) and quotes (&quot;&quot;) search operators are not used for all terms in the search, the minus (-) operator is ignored.</span></p>\n\n	<p><strong><span class=\"text-fine-print\">Powered exclusively by liveSite Advanced Site Search.</span></strong></p>\n	</div>\n	</li>\n</ul>',216,6,40,1545868317,'a'),(2738,'1466029221_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1049,2,40,1545870468,'a'),(2323,'1458229563_5','',971,5,40,1538072080,'a'),(2324,'1458229563_6','',971,6,40,1538069702,'a'),(2312,'1458160731_2','',902,2,40,1547832760,'a'),(2743,'1466033835_1','<h3 class=\"uppercase\">SliDer Gallery Widget</h3>\n\n<p>This page&#39;s <span class=\"notice-color\" style=\"font-weight:bold\">System Region</span> is embedded in other pages.</p>',1050,1,40,1545874029,'a'),(2753,'1466093563_1','',1052,1,40,1545934271,'a'),(2754,'1466093563_2','',1052,2,40,1545934279,'a'),(2755,'1466093563_3','',1052,3,40,1545933702,'a'),(2756,'1466093563_4','',1052,4,40,1545933702,'a'),(2758,'1466182304_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',886,2,40,1546022443,'a'),(2367,'1458317323_4','',981,4,40,1538157462,'a'),(2366,'1458317323_3','',981,3,40,1538157462,'a'),(2361,'1458316134_2','',980,2,40,1538157350,'a'),(2365,'1458317323_2','',981,2,40,1538157462,'a'),(2332,'1458241683_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',974,2,40,1545275531,'a'),(2364,'1458317323_1','<h3 class=\"uppercase\">Password Reset</h3>',981,1,40,1538157634,'a'),(2362,'1458316134_3','',980,3,40,1538156273,'a'),(2363,'1458316134_4','',980,4,40,1538156273,'a'),(2340,'1458251261_6','<h6 class=\"heading-title\">About My Account Profile</h6>\n\n<p>This information will used to prefill any future forms and orders with your information so you don&#39;t have to re-enter it each time.</p>',975,6,40,1548294388,'a'),(2341,'1458252460_1','<h3 class=\"uppercase\">Shipping Address</h3>',976,1,40,1539797726,'a'),(2342,'1458252460_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',976,2,40,1538092599,'a'),(2343,'1458252460_3','<p class=\"lead\" style=\"text-align: center;\">Add / Update a recipient in your address book so you can quickly order and reorder shipments just by using their &#39;Ship to Name&#39; (e.g. &quot;Mom&quot; or &quot;Tom&quot;).</p>',976,3,40,1539797717,'a'),(2344,'1458252460_4','',976,4,40,1538092599,'a'),(2345,'1458252460_5','',976,5,40,1538092599,'a'),(2346,'1458252460_6','',976,6,40,1539106185,'a'),(2354,'1458259905_5','',978,5,40,1548293238,'a'),(2355,'1458259905_6','<h6 class=\"heading-title\">About Email Preferences</h6>\n\n<p>Update your Contact Email we use to send promotional emails to you. You can also update your opt-in / opt-out selections.</p>\n\n<p><strong>To opt out of all promotional mailing lists, uncheck &quot;Yes, you may send me promotional emails.&quot; </strong></p>\n\n<p>Administrative emails such as sending reset passwords, order receipts, etc., are not affected by these settings.</p>\n\n<p>Note: Your Contact Email is not the same as your User Email associated with your account. Your User Email cannot be changed for security reasons.</p>',978,6,40,1544648564,'a'),(2352,'1458259905_3','',978,3,40,1538100044,'a'),(2353,'1458259905_4','',978,4,40,1538100044,'a'),(2351,'1458259905_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}my-account\">My Account</a></li>\n</ul>',978,2,40,1538100547,'a'),(2350,'1458259905_1','<h3 class=\"uppercase\">Email Preferences</h3>',978,1,40,1538100088,'a'),(2424,'1458757506_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}catalog\">Shop</a></li>\n</ul>',998,2,40,1538597645,'a'),(2425,'1458757506_3','',998,3,40,1538597645,'a'),(2426,'1458757506_4','',998,4,40,1538597645,'a'),(2427,'1458758619_1','<h3 class=\"uppercase mb8\">Shop</h3>',999,1,40,1548293660,'a'),(2428,'1458758619_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',999,2,40,1538598758,'a'),(2429,'1458758619_3','',999,3,40,1538598758,'a'),(2430,'1458758619_4','',999,4,40,1538598758,'a'),(2431,'1458758619_5','',999,5,40,1538598758,'a'),(2432,'1458758619_6','',999,6,40,1538598758,'a'),(2433,'1458760403_1','<h3 class=\"uppercase mb8\">Shop</h3>',1000,1,40,1548293669,'a'),(2434,'1458760403_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1000,2,40,1538600542,'a'),(2435,'1458760403_3','',1000,3,40,1538600542,'a'),(2436,'1458760403_4','',1000,4,40,1538600542,'a'),(2437,'1458760403_5','',1000,5,40,1538600542,'a'),(2438,'1458760403_6','',1000,6,40,1538600542,'a'),(2439,'1458761847_1','<h3 class=\"uppercase mb8\">Shop</h3>',1001,1,40,1548293678,'a'),(2440,'1458761847_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1001,2,40,1538601986,'a'),(2441,'1458761847_3','',1001,3,40,1538601986,'a'),(2442,'1458761847_4','',1001,4,40,1538601986,'a'),(2443,'1458761847_5','',1001,5,40,1538601986,'a'),(2444,'1458761847_6','',1001,6,40,1538601986,'a'),(2445,'1458762199_1','<h3 class=\"uppercase mb8\">Product</h3>',1002,1,40,1548293704,'a'),(2446,'1458762199_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n	<li><a href=\"{path}catalog\">Shop</a></li>\n</ul>',1002,2,40,1538602338,'a'),(2447,'1458762199_3','',1002,3,40,1538602338,'a'),(2448,'1458762199_4','',1002,4,40,1538602338,'a'),(2483,'1458851999_6','<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}new-conversation\">New Conversation</a></p>\n\n<h6 class=\"heading-title\">Conversations</h6>\n\n<p>Conversations are threaded discussions that are private between you and our sales team. If you have a question, you can quickly start a new conversation with us at anytime. So you won&#39;t miss anything, we will send you an email notice of new conversations, or when any comments are added to your conversations.</p>\n\n<p><strong>Why Conversations?</strong><br />\nWe use Conversations to move scattered dialogues from cluttered inboxes.&nbsp; Never again will you have to keep up with our email responses! You can also login to our website and view the history of all your Conversations.</p>',647,6,40,1548295026,'a'),(2488,'1458923617_6','',639,6,40,1538763844,'a'),(2489,'1458923708_6','<h6 class=\"uppercase\">About Our Services</h6>\n\n<p>We make it easy to work with us. If you have been provided an estimate for professional services, it was in the form of &#39;credits&#39;. Each credit amounts to a chunk of time used by our professional services team.</p>\n\n<p>You can purchase enough credits to the first part of your project, and you add credits to an existing project too.</p>',639,6,40,1542670615,'a'),(2729,'1465490561_4','',1047,4,40,1545336235,'a'),(2730,'1465496096_5','<h6 class=\"heading-title\">About Staff Entries</h6>\n\n<p>This form item view page uses the &quot;Save for Later&quot;custom form feature, allowing you can draft a staff entry and &quot;Save for Later&quot;, displaying it only in the private All Staff Directory. You can later &quot;Edit&quot; and &quot;Complete&quot; the entry and it will appear in the public <a href=\"{path}staff-directory\">Staff Directory</a>. You can also edit and mark a complete entry as &quot;Incomplete&quot; and it will no longer appear in the public Staff Directory.</p>',1047,5,40,1545336377,'a'),(2731,'1465497185_5','<h6 class=\"heading-title\">About Adding Staff Entries</h6>\n\n<p>You can &quot;Submit&quot; to instantly display this entry in your public Staff Directory. However, you can also &quot;Save for Later&quot; and this entry will only appear in the private All Staff Directory. You can later edit the entry, add more content and &quot;Save&quot; it.</p>',296,5,40,1545337538,'a'),(2732,'1465497265_5','',296,5,40,1545337507,'a'),(2746,'1466033835_4','<p>Simply add <span style=\"font-weight:bold;font-family:courier;font-size:110%\">&lt;system&gt;slider-gallery-widget&lt;/system&gt;</span> to any Page Style or Designer Region to include this page&#39;s content and behaviors. You don&#39;t need to ever link to this page directly.</p>\n\n<p>An example of this widget is in the sidebar of the <a href=\"{path}blog-cards\">Blog Cards</a> page.</p>',1050,4,40,1545891088,'a'),(2747,'1466053432_1','<h3 class=\"uppercase mb0\">Case Study</h3>',936,1,40,1545893571,'a'),(2748,'1466053432_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',936,2,40,1545893571,'a'),(2749,'1466053557_3','<h5 class=\"uppercase\">Brief</h5>\n\n<p class=\"mb0\">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>',936,3,40,1545893696,'a'),(2750,'1466053595_4','<h5 class=\"uppercase\">Result</h5>\n\n<p class=\"mb0\">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>',936,4,40,1545893765,'a'),(2849,'1469113303_3','<h3 style=\"text-align: center;\">[[^^name^^, ||]]Thank you for starting an order!</h3>\n\n<hr />\n<p class=\"lead\" style=\"text-align: center;\">We noticed that you haven&#39;t completed your order yet.</p>\n\n<p class=\"lead\" style=\"text-align: center;\">If you need our assistance with placing your order, please <a href=\"{path}contact-us\">contact us</a>.</p>\n\n<p class=\"lead\" style=\"text-align: center;\">&nbsp;</p>\n\n<p class=\"lead\" style=\"text-align: center;\">Ready to complete your order?</p>\n\n<p class=\"lead\" style=\"text-align: center;\"><a class=\"software_button_primary btn btn-primary\" href=\"{path}cart?r=^^order_reference_code^^&amp;t=reminder\">Retrieve My Order</a></p>',1071,3,40,1548293352,'a'),(2848,'1469113303_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}\">Home</a></li>\n</ul>',1071,2,40,1548295191,'a'),(2847,'1469113303_1','<h3 class=\"uppercase\">Order Assistance</h3>',1071,1,40,1548292140,'a'),(2850,'1469113303_4','',1071,4,40,1548295191,'a'),(2859,'1469806073_1','<h3 class=\"uppercase\">My Classified Ads</h3>',1074,1,40,1548293230,'a'),(2860,'1469806073_2','<ul class=\"breadcrumb\">\n	<li><a href=\"{path}members-home\">Members Home</a></li>\n	<li><a href=\"{path}classified-ads\">Classified Ads</a></li>\n</ul>',1074,2,40,1548293662,'a'),(2861,'1469806073_3','<p class=\"lead\" style=\"text-align: center;\">Below are the classified ads you have posted.<br />\nYou can edit, publish/unpublish, mark as &#39;sold&#39;, re-post, or delete them.</p>',1074,3,40,1548293808,'a'),(2862,'1469806073_4','',1074,4,40,1548293154,'a'),(2863,'1469806073_6','',1074,6,40,1548293154,'a'),(2864,'1469806073_6','<p><a class=\"btn\" href=\"{path}classified-ads-form\">New Classified Ad</a></p>\n\n<h6 class=\"heading-title\">About Classified Ads</h6>\n\n<p><span class=\"text-fine-print\">All members have access to post a classified ad or ask the seller for more information and the seller will be notified and will be able to reply.</span></p>\n\n<p>Ads that are not &quot;submitted&quot; but &quot;saved for later&quot; will not be displayed until they are marked &quot;complete&quot;.</p>\n\n<p><span class=\"text-fine-print\">Sellers can <a href=\"{path}my-classified-ads\">edit their own ads</a> at any time.</span></p>\n\n<p><span class=\"text-fine-print\">All ads will will be removed (from the view) after 90 days from the time of the last edit.</span></p>',1074,6,40,1548293594,'a'),(2874,'1470090617_4','',1076,4,40,1548293228,'a'),(2873,'1470090617_3','',1076,3,40,1548293228,'a'),(2878,'1470091739_2','',1077,2,40,1548294386,'a'),(2872,'1470090617_2','',1076,2,40,1548293331,'a'),(2871,'1470090617_1','<h3 class=\"uppercase\">Billing Information</h3>',1076,1,40,1548293228,'a'),(2875,'1470090617_6','',1076,6,40,1548293228,'a'),(2876,'1470090617_6','',1076,6,40,1548293228,'a'),(2879,'1470091739_3','',1077,3,40,1548294350,'a');
/*!40000 ALTER TABLE `pregion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preview_styles`
--

DROP TABLE IF EXISTS `preview_styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preview_styles` (
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `theme_id` int(10) unsigned NOT NULL DEFAULT '0',
  `style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `device_type` enum('desktop','mobile') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'desktop',
  KEY `page_id` (`page_id`),
  KEY `theme_id` (`theme_id`),
  KEY `style_id` (`style_id`),
  KEY `device_type` (`device_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preview_styles`
--

LOCK TABLES `preview_styles` WRITE;
/*!40000 ALTER TABLE `preview_styles` DISABLE KEYS */;
/*!40000 ALTER TABLE `preview_styles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_options`
--

DROP TABLE IF EXISTS `product_attribute_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `no_value` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_attribute_id` (`product_attribute_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_options`
--

LOCK TABLES `product_attribute_options` WRITE;
/*!40000 ALTER TABLE `product_attribute_options` DISABLE KEYS */;
INSERT INTO `product_attribute_options` VALUES (1,1,'$10',0,1),(2,1,'$25',0,2),(3,1,'$50',0,3),(4,1,'$100',0,4),(11,4,'Green',0,2),(10,4,'Mocha',0,1),(12,4,'Orange',0,3);
/*!40000 ALTER TABLE `product_attribute_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attributes`
--

DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attributes`
--

LOCK TABLES `product_attributes` WRITE;
/*!40000 ALTER TABLE `product_attributes` DISABLE KEYS */;
INSERT INTO `product_attributes` VALUES (1,'Gift Card Amount','Amount:',2,1537472973,2,1537472973),(4,'Office Chair Color','Color:',40,1540916474,40,1540916933);
/*!40000 ALTER TABLE `product_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_groups`
--

DROP TABLE IF EXISTS `product_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `short_description` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `full_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `image_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `display_type` enum('browse','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'browse',
  `details` longtext COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `featured` tinyint(4) NOT NULL DEFAULT '0',
  `featured_sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `new_date` date NOT NULL DEFAULT '0000-00-00',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `meta_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `meta_keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `address_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `seo_score` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seo_analysis` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `seo_analysis_current` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `attributes` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mailchimp_sync_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `enabled` (`enabled`),
  KEY `timestamp` (`timestamp`),
  KEY `mailchimp_sync_timestamp` (`mailchimp_sync_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_groups`
--

LOCK TABLES `product_groups` WRITE;
/*!40000 ALTER TABLE `product_groups` DISABLE KEYS */;
INSERT INTO `product_groups` VALUES (23,'Downloads',40,1546639431,31,50,'Downloads','','ebook.jpg','browse','','',0,0,'0000-00-00','','','','Downloads',0,'',0,'',1,1,0),(48,'Office Chair',40,1545761242,28,0,'Office Chair','<p>Available in several colors! Select one!</p>\r\n','office-chair-mocha-1.jpg','select','<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Description</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Color Guide</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<table class=\"table table-striped table-bordered\">\r\n		<thead>\r\n			<tr>\r\n				<th>Color</th>\r\n				<th>Details</th>\r\n			</tr>\r\n		</thead>\r\n		<tbody>\r\n			<tr>\r\n				<th scope=\"row\">Mocha</th>\r\n				<td>\r\n				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<th scope=\"row\">Green</th>\r\n				<td>\r\n				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n				<th scope=\"row\">Orange</th>\r\n				<td>\r\n				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>\r\n				</td>\r\n			</tr>\r\n		</tbody>\r\n	</table>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','office, chair',0,0,'0000-00-00','','','','Office_Chair',0,'',0,' <style>\r\n    img.image-url {display: none !important;}\r\n</style>\r\n<div class=\"image-slider slider-thumb-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-1.jpg\" style=\"visibility: hidden\"/>\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-2.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-3.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-4.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n    </ul>\r\n</div>',1,1,0),(49,'Downloads',40,1546639384,37,30,'Downloads','<h2>Downloads</h2>\r\n','ebook.jpg','browse','','',0,0,'0000-00-00','','','','shop-downloads',0,'',0,'',1,1,0),(42,'Exam',40,1541186000,31,10,'','','','browse','','',0,0,'0000-00-00','','','','Exam',0,'',0,'',1,1,0),(41,'Online Quotes',40,1548294565,31,9999,'','','','browse','','',0,0,'0000-00-00','','','','Online_Quotes',0,'',0,'',1,1,0),(26,'Payment Product',2,1537472973,31,10,'','','','browse','','',0,0,'0000-00-00','','','','payment-product',0,'',0,'',1,1,0),(21,'Donations',2,1537472973,31,10,'Donations','<p><img class=\"image-left-primary\" title=\"donate.jpg\" src=\"{path}donate.jpg\" alt=\"donate.jpg\" width=\"300\" height=\"200\" />Different donation product options.</p>','','browse','','',0,0,'0000-00-00','','','','Donations',0,'',0,'',1,1,0),(46,'Memberships',40,1540493660,31,10,'Memberships','<p>Whether you want to collect membership dues &amp; renewals online, automatically send renewal reminders, or grant access to your private members-only website area,&nbsp;it&#39;s easy!</p>\r\n','membership-cards.jpg','select','<p>&nbsp;</p>\r\n\r\n<ul class=\"list-tabs\">\r\n	<li><a href=\"#\">Features</a><br />\r\n	Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue.</li>\r\n	<li><a href=\"#\">Rules</a><br />\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim.</li>\r\n	<li><a href=\"#\">Details</a><br />\r\n	Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</li>\r\n</ul>\r\n','',0,0,'0000-00-00','','','','Memberships',0,'',0,'',1,1,0),(27,'Gift Baskets',40,1540928868,37,10,'Gift Baskets','<h2>Gift Baskets</h2>\r\n','gift-basket-mega.jpg','browse','<p>&nbsp;</p>\r\n\r\n<h4>About Perishable Products</h4>\r\n\r\n<p>Let your customers order non-taxable products that must be shipped during certain times of the week using specific shipping methods, and have a restricted delivery area. You can also guarantee delivery before specific holidays, or before a customer&#39;s own special arrival date. It&#39;s easy!</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<ul class=\"list-tabs\">\r\n	<li><a href=\"#\">Ingredients</a><br />\r\n	Nulla consectetur blandit justo in venenatis.<br />\r\n	<br />\r\n	Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh.<br />\r\n	<br />\r\n	Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue.</li>\r\n	<li><a href=\"#\">Nutrition Facts</a><br />\r\n	Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh.<br />\r\n	<br />\r\n	Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo.<br />\r\n	<br />\r\n	Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue.</li>\r\n</ul>\r\n','',1,1,'0000-00-00','','','','Gift_Baskets',0,'',0,'<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n		<div class=\"tab-title\"><span>Worry-free Delivery</span></div>\r\n	</li>\r\n   	<li>\r\n		<div class=\"tab-title\"><span>100% Guarantee</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n		<div class=\"tab-content\">\r\n			<p>Our Gift Baskets are perishable so during checkout we will only provide you with shipping options that will guarantee your gift will be delivered fresh on or before the arrival date you select.</p>\r\n		</div>\r\n	</li>\r\n   	<li>\r\n		<div class=\"tab-content\">\r\n			<p>All gifts are guaranteed to arrive fresh and in great shape. We are so confident, all our gifts have a 100% satisfaction guarantee.</p>\r\n		</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n',1,1,0),(28,'Office Supplies',40,1540929623,37,20,'Office Supplies','<h2>Office Supplies</h2>\r\n','office-chair-mocha-1.jpg','browse','','',0,0,'0000-00-00','','','','Office_Supplies',0,'',0,'',1,1,0),(39,'Collect Dues',2,1537472973,31,10,'','','','browse','','',0,0,'0000-00-00','','','','membership-dues',0,'',0,'',1,1,0),(29,'Free Shippable Product',2,1537472973,31,999,'','<p><img class=\"image-left-primary\" title=\"pen_case.jpg\" src=\"{path}pen_case.jpg\" alt=\"pen_case.jpg\" width=\"300\" height=\"205\" /></p>','pen_case.jpg','browse','','',0,0,'0000-00-00','','','','free-shippable-product',0,'',0,'',1,1,0),(30,'Event Registration',2,1537472973,31,10,'','<p><img class=\"image-left-primary\" title=\"calendar_white.gif\" src=\"{path}calendar_white.gif\" alt=\"calendar_white.gif\" width=\"319\" height=\"371\" /></p>','','browse','','',0,0,'0000-00-00','','','','event-sign-up-fees',0,'',0,'',1,1,0),(31,'All Product Groups',40,1548293348,0,0,'All Product Groups','<p>All Product Groups.</p>\r\n','','browse','','',0,0,'0000-00-00','','','','All_Product_Groups',0,'',0,'',1,1,0),(37,'Shop',40,1540927567,31,0,'','','','browse','','',0,0,'0000-00-00','','','','shop',0,'',0,'',1,1,0),(47,'Service Plans',40,1539215140,31,10,'Service Plans','','service-plans.jpg','select','','service plans',0,0,'0000-00-00','','','','Service_Plans',0,'',0,'',1,1,0),(51,'Gift Shop',40,1546895286,31,999,'','','','browse','','',0,0,'0000-00-00','','','','giftshop',0,'',0,'',1,1,0),(43,'eGift Cards',40,1540932205,37,60,'e-Gift Cards','','gift-card.jpg','select','<h3>About Our eGift Cards</h3>\r\n\r\n<p>Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!</p>\r\n\r\n<p>Send them instantly or select any future delivery date and the Gift Card will arrive in the recipient&#39;s inbox at 12pm on that special day!</p>\r\n','',0,0,'0000-00-00','Gift Cards','Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!  Send them instantly or select any future delivery date and the eGift Card will arrive in the recipient\'s ','Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!  Send them instantly or select any future delivery date and the eGift Card will arrive in the recipient\'s inbox at 12pm on that special day!','eGift_Cards',0,'',0,'',1,1,0),(44,'Services Project',40,1538763393,31,10,'Support Project','','','browse','','',0,0,'0000-00-00','','','','Support_Project',0,'',0,'',1,1,0),(56,'eGift Cards',40,1546895161,51,60,'e-Gift Cards','','gift-card.jpg','select','<h3>About Our eGift Cards</h3>\r\n\r\n<p>Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!</p>\r\n\r\n<p>Send them instantly or select any future delivery date and the Gift Card will arrive in the recipient&#39;s inbox at 12pm on that special day!</p>\r\n','',0,0,'0000-00-00','Gift Cards','Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!  Send them instantly or select any future delivery date and the eGift Card will arrive in the recipient\'s ','Send a personalized Gift Card with your own message via email to anyone in the world so they can go shopping on our website for their favorite gift!  Send them instantly or select any future delivery date and the eGift Card will arrive in the recipient\'s inbox at 12pm on that special day!','eGift_Cards[1]',0,'',0,'',1,1,0);
/*!40000 ALTER TABLE `product_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_groups_attributes_xref`
--

DROP TABLE IF EXISTS `product_groups_attributes_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_groups_attributes_xref` (
  `product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `default_option_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `product_group_id` (`product_group_id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_groups_attributes_xref`
--

LOCK TABLES `product_groups_attributes_xref` WRITE;
/*!40000 ALTER TABLE `product_groups_attributes_xref` DISABLE KEYS */;
INSERT INTO `product_groups_attributes_xref` VALUES (43,1,1,0),(48,4,1,0),(56,1,1,0);
/*!40000 ALTER TABLE `product_groups_attributes_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_submit_form_fields`
--

DROP TABLE IF EXISTS `product_submit_form_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_submit_form_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('','create','update') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `action` (`action`),
  KEY `form_field_id` (`form_field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_submit_form_fields`
--

LOCK TABLES `product_submit_form_fields` WRITE;
/*!40000 ALTER TABLE `product_submit_form_fields` DISABLE KEYS */;
INSERT INTO `product_submit_form_fields` VALUES (152,69,'create',318,'^^billing_last_name^^'),(153,69,'create',314,'New Services Project'),(154,69,'create',320,'^^quantity^^'),(155,69,'update',320,'^^quantity^^'),(151,69,'create',317,'^^billing_first_name^^'),(150,69,'create',315,'Thank you for starting a new Services Project (Order #^^order_number^^). We have applied ^^quantity^^ credit^^quantity_plural_suffix^^ to this Services Project.\r\n\r\nHow can we help you?'),(149,69,'create',319,'^^billing_email_address^^');
/*!40000 ALTER TABLE `product_submit_form_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `full_description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  `private_folder` int(10) unsigned DEFAULT NULL,
  `send_to_page` int(10) unsigned DEFAULT NULL,
  `email_page` int(10) unsigned DEFAULT NULL,
  `email_bcc` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `required_product` int(10) unsigned DEFAULT NULL,
  `selection_type` enum('checkbox','quantity','donation','autoselect') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'checkbox',
  `default_quantity` int(10) unsigned DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `recurring` tinyint(4) DEFAULT NULL,
  `start` int(10) unsigned DEFAULT NULL,
  `number_of_payments` int(10) unsigned DEFAULT NULL,
  `payment_period` enum('','Monthly','Weekly','Every Two Weeks','Twice every Month','Every Four Weeks','Quarterly','Twice every Year','Yearly') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `membership_renewal` int(10) unsigned DEFAULT NULL,
  `grant_private_access` tinyint(4) DEFAULT NULL,
  `short_description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shippable` tinyint(4) NOT NULL DEFAULT '0',
  `primary_weight_points` int(10) unsigned NOT NULL DEFAULT '0',
  `secondary_weight_points` int(10) unsigned NOT NULL DEFAULT '0',
  `preparation_time` int(10) unsigned NOT NULL DEFAULT '0',
  `free_shipping` tinyint(4) NOT NULL DEFAULT '0',
  `taxable` tinyint(4) NOT NULL DEFAULT '0',
  `commissionable` tinyint(4) NOT NULL DEFAULT '1',
  `commission_rate_limit` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `order_receipt_message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` longtext COLLATE utf8_unicode_ci NOT NULL,
  `recurring_profile_disabled_perform_actions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurring_profile_disabled_expire_membership` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurring_profile_disabled_revoke_private_access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurring_profile_disabled_email` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recurring_profile_disabled_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recurring_profile_disabled_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `extra_shipping_cost` int(10) unsigned NOT NULL DEFAULT '0',
  `form` tinyint(4) NOT NULL DEFAULT '0',
  `form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_label_column_width` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_quantity_type` enum('One Form per Quantity','One Form per Product') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'One Form per Quantity',
  `image_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recurring_schedule_editable_by_customer` tinyint(4) NOT NULL DEFAULT '0',
  `details` longtext COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `meta_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `meta_keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `order_receipt_bcc_email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `seo_score` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seo_analysis` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `seo_analysis_current` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `gtin` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `brand` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mpn` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `inventory` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `inventory_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `backorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `out_of_stock_message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `reward_points` int(10) unsigned NOT NULL DEFAULT '0',
  `code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `sage_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `private_days` int(10) unsigned NOT NULL DEFAULT '0',
  `google_product_category` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minimum_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `maximum_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_field_3` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_4` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `weight` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `submit_form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submit_form_custom_form_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_form_create` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submit_form_update` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_comment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_comment_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `add_comment_message` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `add_comment_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `add_comment_only_for_submit_form_update` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `gift_card` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `gift_card_email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gift_card_email_format` enum('plain_text','html') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'plain_text',
  `gift_card_email_body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `gift_card_email_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_form_update_where_field` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `submit_form_update_where_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `submit_form_quantity_type` enum('One Form per Quantity','One Form per Product') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'One Form per Quantity',
  `length` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `width` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `height` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `container_required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mailchimp_sync_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `name` (`name`),
  KEY `enabled` (`enabled`),
  KEY `shippable` (`shippable`),
  KEY `mailchimp_sync_timestamp` (`mailchimp_sync_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (29,'D10','<p>Please enter the amount you wish to donate and your payment schedule.</p>\r\n',0,0,0,0,'',0,'donation',0,40,1548292296,1,0,0,'Monthly',0,0,'Recurring Donation',0,0,0,0,0,0,0,'0.00','',16,'',0,0,0,0,'',0,0,0,'Schedule of Payments','','One Form per Quantity','',1,'','recurring, donation','','','','','Recurring_Donation',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(31,'D01','<p>Please enter the amount you wish to donate.</p>\r\n',0,0,0,0,'example@example.com',0,'donation',0,40,1548292238,0,0,0,'Monthly',0,0,'One-Time Donation',0,0,0,0,0,0,0,'0.00','',18,'',0,0,0,0,'',0,0,1,'Honorary Donation Information (Optional)','','One Form per Quantity','',0,'','donations','','','','','One-Time_Donation',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(35,'M20','<p>Get instant access to our Members Portal for one full year!</p>\r\n',59995,0,284,258,'example@example.com',0,'autoselect',1,40,1539209336,0,0,0,'Monthly',365,1,'One Year Membership Access',0,0,0,0,0,1,0,'0.00','<p>Thank you for purchasing&nbsp;<strong>one year&nbsp;of membership access</strong>.&nbsp;&nbsp;&nbsp;Your login account has been upgraded to allow you access&nbsp;to the protected membership areas of the website for 365 days from today. Click here to go to the protected <a href=\"{path}members-home\"><strong>members home page</strong></a>. You will receive a renewal reminder 2 weeks before your membership expires so you can renew it before it does.</p>\r\n',20,'',0,0,0,0,'',0,0,1,'Membership Information','','One Form per Quantity','membership-cards.jpg',0,'<p>&nbsp;</p>\r\n\r\n<ul class=\"list-tabs\">\r\n	<li><a href=\"#\">Features</a><br />\r\n	Nulla consectetur blandit justo in venenatis. Ut ut felis justo. Praesent cursus, diam eu pharetra viverra, lectus erat lobortis odio, a cursus neque lectus a arcu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue.</li>\r\n	<li><a href=\"#\">Rules</a><br />\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in aliquam sem. Praesent consectetur sem non tortor laoreet sit amet luctus risus euismod. Phasellus neque quam, auctor adipiscing tincidunt vel, euismod auctor dolor. Vestibulum a leo lacus. Pellentesque quis orci enim.</li>\r\n	<li><a href=\"#\">Details</a><br />\r\n	Sed lorem diam, semper ut iaculis eu, scelerisque sed neque. Suspendisse a turpis nibh. Morbi consectetur hendrerit eros, et vestibulum nibh adipiscing et. Curabitur non tincidunt sapien. Sed accumsan, augue eget dapibus venenatis, sem magna ultricies augue, at tincidunt erat urna et justo. Quisque semper diam ac nulla ultrices eu fermentum enim placerat. Ut feugiat vehicula massa, eget commodo quam adipiscing congue. Etiam quis dignissim nisi. Suspendisse placerat, libero nec porta vestibulum, lectus est iaculis dui, eu gravida ipsum enim ut nisl. Sed sapien dolor, pretium eget bibendum in, bibendum id eros. Quisque auctor, purus ut lobortis sodales, tortor arcu tincidunt justo, eu suscipit sem ante vel lorem. Cras nisl sapien, placerat id pellentesque sit amet, malesuada non mi. Duis elementum pellentesque suscipit.</li>\r\n</ul>\r\n','memberships','','','','','One_Year_Membership_Access',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(36,'DL1','<p>Download instantly upon purchase.</p>\r\n',49995,134,0,0,'',0,'checkbox',1,40,1545262269,0,0,0,'Monthly',0,1,'Software',0,0,0,0,0,1,1,'0.00','<h3 class=\"lead\" style=\"text-align: center;\">Thank you for purchasing our software!</h3>\r\n\r\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}software-download-file.txt\" target=\"_blank\">Download Software Now</a></p>\r\n\r\n<p style=\"text-align: center;\">(You will also find your software download available from your <a href=\"{path}my-account-content\">My Content</a> page.)</p>\r\n',22,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','software.jpg',0,'\r\n<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Features</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Specifications</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Details</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','software, downloads','','','','','Software',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(37,'DL2','<p>Download instantly upon purchase.</p>\r\n',1495,135,0,0,'',0,'checkbox',1,40,1545262287,0,0,0,'Monthly',0,1,'eBook',0,0,0,0,0,1,1,'0.00','<h3 class=\"lead\" style=\"text-align: center;\">Thank you for purchasing our eBook!</h3>\r\n\r\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"{path}ebook-download-file.txt\" target=\"_blank\">Download eBook Now</a></p>\r\n\r\n<p style=\"text-align: center;\">(You will also find your eBook download available from your <a href=\"{path}my-account-content\">My Content</a> page.)</p>\r\n',23,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','ebook.jpg',0,'\r\n<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Features</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Specifications</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Details</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','books, gifts, downloads','','','','','eBook',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(38,'SP1','<p>Sign up for our monthly recurring service plan. Recurring charges are captured automatically each billing period.</p>\r\n',7995,0,0,0,'',0,'checkbox',1,40,1548292218,1,0,0,'Monthly',0,0,'Monthly Service Plan',0,0,0,0,0,1,0,'0.00','<p>Thank you very much for signing up for our <strong>Monthly Service Plan</strong>.&nbsp;&nbsp;If you wish to cancel your service plan&nbsp;for any&nbsp;reason, please <a href=\"{path}contact-us\">contact us</a>.</p>\r\n',24,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','service-plans.jpg',0,'','service plan, recurring','','','','','Monthly_Service_Plan',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,1,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(72,'20O','<p>Orange you glad you selected this color? :)</p>\r\n',29995,0,0,0,'example@example.com',0,'quantity',0,40,1548293868,0,0,0,'Monthly',0,0,'Orange Office Chair',1,70,0,0,0,1,1,'0.00','',28,'',0,0,0,0,'',0,0,0,'Please select the color of each chair in your order','','One Form per Product','office-chair-orange-1.jpg',0,'','office, chair','','','','','Orange-Office-Chair',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'<style>\r\n    img.image-url {display: none !important;}\r\n</style>\r\n<div class=\"image-slider slider-thumb-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-orange-1.jpg\" style=\"visibility: hidden\"/>\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-orange-2.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-orange-3.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-orange-4.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n    </ul>\r\n</div>',0,0,'','W1R3S8','',1,0,0,'','','45.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(71,'20G','<p>This green chair will brighten up any office!</p>\r\n',29995,0,0,0,'example@example.com',0,'quantity',0,40,1548293889,0,0,0,'Monthly',0,0,'Green Office Chair',1,70,0,0,0,1,1,'0.00','',28,'',0,0,0,0,'',0,0,0,'Please select the color of each chair in your order','','One Form per Product','office-chair-green-1.jpg',0,'','office, chair','','','','','Green-Office-Chair',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'<style>\r\n    img.image-url {display: none !important;}\r\n</style>\r\n<div class=\"image-slider slider-thumb-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-green-1.jpg\" style=\"visibility: hidden\"/>\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-green-2.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-green-3.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-green-4.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n    </ul>\r\n</div>',0,0,'','W1R3S8','',1,0,0,'','','45.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(39,'SP2','<p>Sign up for our annual recurring service plan. Recurring charges are captured automatically each billing period.</p>\r\n\r\n<p class=\"lead\">Best Value! Save 10%!</p>\r\n',95940,0,0,0,'',0,'checkbox',1,40,1542904662,1,0,0,'Yearly',0,0,'Annual Service Plan',0,0,0,0,0,1,0,'0.00','<p>Thank you very much for signing up for our <strong>Annual Service Plan</strong>.&nbsp;&nbsp;If you wish to cancel your service plan&nbsp;for any&nbsp;reason, please <a href=\"{path}contact-us\">contact us</a>.</p>\r\n',41,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','service-plans.jpg',0,'','service plan, recurring','','','','','Annual_Service_Plan_(best_value)',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,1,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(40,'AP1','<p>Please enter the amount you would like to apply to your account with us.</p>\r\n',0,0,0,0,'example@example.com',0,'donation',0,40,1547832174,0,0,0,'Monthly',0,0,'Account Payment',0,0,0,0,0,1,0,'0.00','',25,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','',0,'','payments','','','','','Account_Payment',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(41,'CGB-076','<p>Wonderfully delicious gift you will enjoy for a long time!! Wonderfully delicious gift you will enjoy for a long time!! Wonderfully delicious gift you will enjoy for a long time!! Wonderfully delicious gift you will enjoy for a long time!! Wonderfully delicious gift you will enjoy for a long time!! Wonderfully delicious gift you will enjoy for a long time!!</p>\r\n',2995,0,0,0,'example@example.com',0,'quantity',0,40,1548293912,0,0,0,'Monthly',0,0,'Platinum Gift Basket',1,2,0,1,0,0,1,'0.00','',30,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-basket-platinum.jpg',0,'<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Worry-free Delivery</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>100% Guarantee</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Our Gift Baskets are perishable so during checkout we will only provide you with shipping options that will guarantee your gift will be delivered fresh on or before the arrival date you select.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>All gifts are guaranteed to arrive fresh and in great shape. We are so confident, all our gifts have a 100% satisfaction guarantee.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','chocolate, gifts, perishable','','','','','Platinum_Gift_Basket',0,'',0,'','','',1,0,1,'<p><span style=\"color: #ff0000;\"><strong>Back-ordered item.</strong> However, you can still order today and be the first to get the next one!</span></p>\r\n',0,'',0,0,'','','',1,0,0,'','','1.5000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(60,'T1','<p>Find out of you have the skills to succeed!<br />\r\nOnce you order your exam, you will be given instant access to the exam for 30 days.<br />\r\nAnswer 100% of the Exam questions correctly and you pass! You will get as many attempts as you wish.<br />\r\nWe store each attempt so we can track your progress.</p>\r\n',50000,104,0,0,'',0,'autoselect',1,40,1542662775,0,0,0,'Monthly',0,1,'Exam Fee',0,0,0,0,0,1,0,'0.00','<h5 style=\"text-align: center;\">You now have access to the <a href=\"{path}exam\">Exam</a> for 30 days. Good luck!<br />\r\n(If you are new to this site, you will find your login information at the bottom of this page.)</h5>\r\n',27,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','',0,'','exam','Exam','Order now and take your exam online instantly.','','','exam_Fee',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,30,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(42,'CGB-077','<p>Wonderfully great chocolate gift basket.</p>\r\n',4995,0,0,0,'example@example.com',0,'quantity',0,40,1548293035,0,0,0,'Monthly',0,0,'Premium Gift Basket',1,5,0,1,0,0,1,'0.00','<p>For each product purchased (in this case <strong>Chocolate Gift Basket</strong>), you can put an optional message on the Order Receipt page.</p>\r\n',30,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-basket-premium.jpg',0,'<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Worry-free Delivery</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>100% Guarantee</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Our Gift Baskets are perishable so during checkout we will only provide you with shipping options that will guarantee your gift will be delivered fresh on or before the arrival date you select.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>All gifts are guaranteed to arrive fresh and in great shape. We are so confident, all our gifts have a 100% satisfaction guarantee.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','chocolate, gifts, perishable','','','','','Premium_Gift_Basket',0,'',0,'','','',1,0,0,'<p><strong><span style=\"color: #ff0000;\">Out of Stock</span></strong></p>\r\n',0,'',0,0,'','','',1,0,0,'','','3.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(43,'CGB-078','<p>Full of chocolate delights!</p>\r\n',7995,0,0,0,'example@example.com',0,'quantity',0,40,1548293948,0,0,0,'Monthly',0,0,'Mega Gift Basket',1,8,0,1,0,0,1,'0.00','',30,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-basket-mega.jpg',0,'<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Worry-free Delivery</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>100% Guarantee</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Our Gift Baskets are perishable so during checkout we will only provide you with shipping options that will guarantee your gift will be delivered fresh on or before the arrival date you select.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>All gifts are guaranteed to arrive fresh and in great shape. We are so confident, all our gifts have a 100% satisfaction guarantee.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','chocolate, gifts, perishable','','','','','Mega_Gift_Basket',0,'',0,'','','',1,9987,1,'<p><span style=\"color: #ff0000;\">Sorry, this item is not currently available, but you can purcahse it now and we will send you the first one when they come in!</span></p>\r\n',0,'',0,0,'','','',1,0,0,'','','5.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(45,'543','<p>Protect your investment in&nbsp;fine-quality&nbsp;pens with this attractive, high-quality leather pen case.</p>\r\n',3995,0,0,0,'example@example.com',0,'checkbox',1,40,1548293799,0,0,0,'Monthly',0,0,'Leather Pen Case',1,4,0,0,0,1,1,'0.00','',29,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','pen-case.jpg',0,'<div class=\"tabbed-content text-tabs\">\r\n<ul class=\"tabs\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-title\"><span>Features</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Specifications</span></div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-title\"><span>Details</span></div>\r\n	</li>\r\n</ul>\r\n\r\n<ul class=\"content\">\r\n	<li class=\"active\">\r\n	<div class=\"tab-content\">\r\n	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n	<li>\r\n	<div class=\"tab-content\">\r\n	<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>\r\n	</div>\r\n	</li>\r\n</ul>\r\n</div>\r\n','gifts, office','','','','','Leather_Pen_Case',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','W1R1S8','',1,0,0,'','','0.2500',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(58,'M101','<p>Common strategies for the marketing of goods and services via the Internet range from public relations and corporate communications to advertising and electronic commerce. Students investigate and evaluate various marketing and communication strategies and tactics for the World Wide Web. Emphasis is placed on critical evaluation skills as well as website planning, development, design, and other factors which contribute to a website&#39;s success.</p>\r\n',99900,0,0,0,'',0,'checkbox',1,40,1545079418,0,0,0,'Monthly',0,0,'Web Marketing 101',0,0,0,0,0,1,0,'0.00','',39,'',0,0,0,0,'',0,0,1,'Attendee Preferences','','One Form per Quantity','',0,'','class, web marketing 101','','','','','Web_Marketing_101',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(47,'ER1','<p>Design Conference: Future trends in web design using liveSite. Full Day Pass &amp; Gifts, Hotel Accommodations Included.</p>\r\n',99900,0,0,351,'example@example.com',0,'checkbox',1,40,1547059216,0,0,0,'Monthly',0,0,'Conference Registration: EXHIBITOR',0,0,0,0,0,1,0,'0.00','<p>We will send you an Exhibitor Packet.</p>\r\n',40,'',0,0,0,0,'',0,0,1,'Exhibitor Information','','One Form per Quantity','',0,'','conference','','','','','Exhibitor_Registration',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(48,'EP1','<p>Design Conference: Future trends in web design using liveSite. Full Day Pass &amp; Gifts</p>\r\n',12900,0,0,350,'example@example.com',0,'checkbox',1,40,1547059133,0,0,0,'Monthly',0,0,'Conference Registration: PARTICIPANT',0,0,0,0,0,0,0,'0.00','<p>Thank you for registering! We will send you a Participant Packet.&nbsp;</p>\r\n',6,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','',0,'','conference','','','','','Participant_Registration',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(55,'MPR','<p>This&nbsp;is a non-taxable, non-shippable, non-commissionable, one-time payment.</p>\r\n\r\n<p>This product is also an &quot;auto-select&quot; product since a quantity of anything other than 1 doesn&#39;t make sense. The product&#39;s auto-select property will disable the selection/quantity input fields on the Order Form page.</p>\r\n',50000,0,0,0,'example@example.com',0,'autoselect',1,40,1538762999,0,0,0,'Monthly',0,0,'Membership Dues',0,0,0,0,0,0,0,'0.00','<p>For each product purchased (in this case <strong>Membership Dues</strong>), you can put an optional message on the Order Receipt page.</p>\r\n',43,'',0,0,0,0,'',0,0,0,'Membership Dues','','One Form per Quantity','',0,'','memberships','','','','','Membership_Dues',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(57,'WTS','<p><span class=\"red-color\">Please check to be sure you are reserving the correct date/hour.<br />\r\nTraining Sessions can be changed within 24 hours, otherwise they are not refundable.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n',12595,0,0,0,'',0,'checkbox',1,40,1545086041,0,0,0,'Monthly',0,0,'Widget Training Session',0,0,0,0,0,1,0,'0.00','',38,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','',0,'','training, training session','Widget Training Session','Book your private training session with one of our widget professionals.','','','Widget_Training_Session',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(56,'JWC','',4500,0,0,0,'',0,'checkbox',1,40,1548294182,0,0,0,'Monthly',0,0,'Concert Ticket',0,0,0,0,0,1,0,'0.00','<h4 style=\"text-align: center;\">Please bring this Order Receipt with you for admittance at the door.</h4>\r\n',34,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','concert-in-the-park.jpg',0,'','','','','','','concert_ticket',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,6,'','','0.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(65,'GC25','',2500,0,0,0,'',0,'checkbox',1,40,1540932225,0,0,0,'Monthly',0,0,'e-Gift Card',0,0,0,0,0,0,0,'0.00','',0,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-card.jpg',0,'','','','','','','25_Gift_Card',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,1,'$^^amount^^ Gift Card from [[^^from_name^^|| an Anonymous Giver]]','html','$^^amount^^ eGift Card\r\n\r\nTo: ^^recipient_email_address^^\r\nDelivery Date: ^^delivery_date^^\r\n\r\nFrom: [[^^from_name^^||An Anonymous Giver]]\r\nMessage: ^^message^^\r\n\r\n========================================\r\n\r\nYour Gift Card Code: ^^code^^\r\n\r\n========================================\r\n\r\nTo redeem your gift card, simply enter the gift card code above during your check out after shopping online at our website.\r\n\r\nThank you!',637,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(66,'GC50','',5000,0,0,0,'',0,'checkbox',1,40,1540932245,0,0,0,'Monthly',0,0,'e-Gift Card',0,0,0,0,0,0,0,'0.00','',0,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-card.jpg',0,'','','','','','','50_Gift_Card',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,1,'$^^amount^^ Gift Card from [[^^from_name^^|| an Anonymous Giver]]','html','$^^amount^^ eGift Card\r\n\r\nTo: ^^recipient_email_address^^\r\nDelivery Date: ^^delivery_date^^\r\n\r\nFrom: [[^^from_name^^||An Anonymous Giver]]\r\nMessage: ^^message^^\r\n\r\n========================================\r\n\r\nYour Gift Card Code: ^^code^^\r\n\r\n========================================\r\n\r\nTo redeem your gift card, simply enter the gift card code above during your check out after shopping online at our website.\r\n\r\nThank you!',637,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(67,'GC10','',1000,0,0,0,'',0,'checkbox',1,40,1540933070,0,0,0,'Monthly',0,0,'e-Gift Card',0,0,0,0,0,0,0,'0.00','',0,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-card.jpg',0,'','','','','','','10_Gift_Card',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,1,'$^^amount^^ Gift Card from [[^^from_name^^|| an Anonymous Giver]]','html','$^^amount^^ eGift Card\r\n\r\nTo: ^^recipient_email_address^^\r\nDelivery Date: ^^delivery_date^^\r\n\r\nFrom: [[^^from_name^^||An Anonymous Giver]]\r\nMessage: ^^message^^\r\n\r\n========================================\r\n\r\nYour Gift Card Code: ^^code^^\r\n\r\n========================================\r\n\r\nTo redeem your gift card, simply enter the gift card code above during your check out after shopping online at our website.\r\n\r\nThank you!',637,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(70,'20M','<p>Mocha looks great in any office!</p>\r\n',29995,0,0,0,'example@example.com',0,'quantity',0,40,1548293879,0,0,0,'Monthly',0,0,'Mocha Office Chair',1,70,0,0,0,1,1,'0.00','',28,'',0,0,0,0,'',0,0,0,'Please select the color of each chair in your order','','One Form per Product','office-chair-mocha-1.jpg',0,'','office, chair','','','','','Mocha-Office-Chair',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'<style>\r\n    img.image-url {display: none !important;}\r\n</style>\r\n<div class=\"image-slider slider-thumb-controls controls-inside\">\r\n    <ul class=\"slides\">\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-1.jpg\" style=\"visibility: hidden\"/>\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-2.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-3.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n        <li>\r\n            <img alt=\"Image\" src=\"{path}office-chair-mocha-4.jpg\" style=\"visibility: hidden\" />\r\n        </li>\r\n    </ul>\r\n</div>',0,0,'','W1R3S8','',1,0,0,'','','45.0000',0,0,0,0,0,0,'','',0,0,'','plain_text','',0,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(68,'GC100','',10000,0,0,0,'',0,'checkbox',1,40,1540932236,0,0,0,'Monthly',0,0,'e-Gift Card',0,0,0,0,0,0,0,'0.00','',0,'',0,0,0,0,'',0,0,0,'','','One Form per Quantity','gift-card.jpg',0,'','','','','','','100_Gift_Card',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',0,0,0,0,0,0,'','',0,1,'$^^amount^^ Gift Card from [[^^from_name^^|| an Anonymous Giver]]','html','$^^amount^^ eGift Card\r\n\r\nTo: ^^recipient_email_address^^\r\nDelivery Date: ^^delivery_date^^\r\n\r\nFrom: [[^^from_name^^||An Anonymous Giver]]\r\nMessage: ^^message^^\r\n\r\n========================================\r\n\r\nYour Gift Card Code: ^^code^^\r\n\r\n========================================\r\n\r\nTo redeem your gift card, simply enter the gift card code above during your check out after shopping online at our website.\r\n\r\nThank you!',637,'','','One Form per Quantity','0.0000','0.0000','0.0000',0,0),(69,'SPC','<p>Open a new project with our professional services team, or add credit(s) to an existing project.</p>\r\n',5000,0,0,0,'',0,'quantity',1,40,1543545881,0,0,0,'Monthly',0,0,'Services Project Credit',0,0,0,0,0,1,0,'0.00','<p style=\"text-align: center;\">We have successfully applied credits to one of your <a href=\"{path}my-services-projects\">Services Projects</a>.</p>\r\n',0,'',0,0,0,0,'',0,0,1,'Services Project Credits','20','One Form per Product','',0,'','','','','','','services_project_credit',0,'',0,'','','',0,0,0,'<p>Sorry, this item is not currently available.</p>\r\n',0,'',0,0,'','','',1,0,0,'','','0.0000',1,1023,1,1,1,1029,'Thank you for your Order #^^order_number^^. We have applied ^^quantity^^ more credit^^quantity_plural_suffix^^ to this Services Project. We will follow up with you shortly. \r\n\r\nPlease feel free to add any additional information that we might need for this project.\r\n','Our Organization',1,0,'','plain_text','',0,'reference_code','^^id^^','One Form per Product','0.0000','0.0000','0.0000',0,0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_attributes_xref`
--

DROP TABLE IF EXISTS `products_attributes_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_attributes_xref` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `product_id` (`product_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `option_id` (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_attributes_xref`
--

LOCK TABLES `products_attributes_xref` WRITE;
/*!40000 ALTER TABLE `products_attributes_xref` DISABLE KEYS */;
INSERT INTO `products_attributes_xref` VALUES (65,1,2,1),(66,1,3,1),(67,1,1,1),(68,1,4,1),(71,4,11,1),(70,4,10,1),(72,4,12,1);
/*!40000 ALTER TABLE `products_attributes_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_groups_xref`
--

DROP TABLE IF EXISTS `products_groups_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_groups_xref` (
  `product` int(10) unsigned DEFAULT NULL,
  `product_group` int(10) unsigned DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(4) NOT NULL DEFAULT '0',
  `featured_sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `new_date` date NOT NULL DEFAULT '0000-00-00',
  KEY `product` (`product`),
  KEY `product_group` (`product_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_groups_xref`
--

LOCK TABLES `products_groups_xref` WRITE;
/*!40000 ALTER TABLE `products_groups_xref` DISABLE KEYS */;
INSERT INTO `products_groups_xref` VALUES (29,21,0,0,0,'0000-00-00'),(37,23,0,0,0,'0000-00-00'),(35,46,0,0,0,'0000-00-00'),(40,26,0,0,0,'0000-00-00'),(43,27,0,0,0,'0000-00-00'),(42,27,0,0,0,'0000-00-00'),(41,27,0,0,0,'0000-00-00'),(45,29,0,0,0,'0000-00-00'),(31,21,0,0,0,'0000-00-00'),(47,30,0,0,0,'0000-00-00'),(48,30,0,0,0,'0000-00-00'),(72,48,3,0,0,'0000-00-00'),(45,28,99,0,0,'0000-00-00'),(36,23,0,0,0,'0000-00-00'),(71,48,2,0,0,'0000-00-00'),(39,47,0,0,0,'0000-00-00'),(38,47,0,0,0,'0000-00-00'),(37,49,0,0,0,'0000-00-00'),(55,39,0,0,0,'0000-00-00'),(29,41,17,0,0,'0000-00-00'),(31,41,16,0,0,'0000-00-00'),(40,41,15,0,0,'0000-00-00'),(55,41,14,0,0,'0000-00-00'),(56,41,13,0,0,'0000-00-00'),(35,41,12,0,0,'0000-00-00'),(47,41,11,0,0,'0000-00-00'),(48,41,10,0,0,'0000-00-00'),(39,41,9,0,0,'0000-00-00'),(38,41,8,0,0,'0000-00-00'),(37,41,7,0,0,'0000-00-00'),(36,41,6,0,0,'0000-00-00'),(41,41,5,0,0,'0000-00-00'),(42,41,4,0,0,'0000-00-00'),(43,41,3,0,0,'0000-00-00'),(45,41,2,0,0,'0000-00-00'),(36,49,0,0,0,'0000-00-00'),(60,42,0,0,0,'0000-00-00'),(70,48,1,0,0,'0000-00-00'),(68,43,4,0,0,'0000-00-00'),(66,43,3,0,0,'0000-00-00'),(65,43,2,0,0,'0000-00-00'),(67,43,1,0,0,'0000-00-00'),(69,44,0,0,0,'0000-00-00'),(42,51,0,0,0,'0000-00-00'),(43,51,0,0,0,'0000-00-00'),(41,51,0,0,0,'0000-00-00'),(68,56,4,0,0,'0000-00-00'),(66,56,3,0,0,'0000-00-00'),(65,56,2,0,0,'0000-00-00'),(67,56,1,0,0,'0000-00-00'),(71,41,0,0,0,'0000-00-00'),(70,41,0,0,0,'0000-00-00'),(72,41,0,0,0,'0000-00-00'),(67,41,0,0,0,'0000-00-00'),(68,41,0,0,0,'0000-00-00'),(65,41,0,0,0,'0000-00-00'),(66,41,0,0,0,'0000-00-00'),(58,41,0,0,0,'0000-00-00'),(69,41,0,0,0,'0000-00-00'),(60,41,0,0,0,'0000-00-00'),(57,41,0,0,0,'0000-00-00');
/*!40000 ALTER TABLE `products_groups_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_zones_xref`
--

DROP TABLE IF EXISTS `products_zones_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_zones_xref` (
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `product_id` (`product_id`),
  KEY `zone_id` (`zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_zones_xref`
--

LOCK TABLES `products_zones_xref` WRITE;
/*!40000 ALTER TABLE `products_zones_xref` DISABLE KEYS */;
INSERT INTO `products_zones_xref` VALUES (29,2),(29,1),(29,3),(31,2),(31,1),(31,3),(35,2),(35,1),(35,3),(36,2),(36,1),(36,3),(37,2),(37,1),(37,3),(38,2),(38,1),(38,3),(39,2),(39,1),(39,3),(43,1),(42,1),(41,1),(45,1),(72,2),(72,3),(72,1),(48,2),(48,1),(48,3),(71,2),(71,1),(71,3),(70,2),(70,1),(70,3),(55,2),(55,1),(55,3),(57,2),(57,1),(57,3),(58,2),(58,1),(58,3),(60,2),(60,1),(60,3),(65,2),(65,1),(65,3),(66,2),(66,1),(66,3),(67,2),(67,1),(67,3),(68,2),(68,1),(68,3),(69,2),(69,1),(69,3);
/*!40000 ALTER TABLE `products_zones_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_commission_profiles`
--

DROP TABLE IF EXISTS `recurring_commission_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurring_commission_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `period` enum('monthly','yearly') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'monthly',
  `number_of_commissions` int(10) unsigned NOT NULL DEFAULT '0',
  `product_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `product_short_description` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `affiliate_code` (`affiliate_code`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_commission_profiles`
--

LOCK TABLES `recurring_commission_profiles` WRITE;
/*!40000 ALTER TABLE `recurring_commission_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_commission_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_sources`
--

DROP TABLE IF EXISTS `referral_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_sources`
--

LOCK TABLES `referral_sources` WRITE;
/*!40000 ALTER TABLE `referral_sources` DISABLE KEYS */;
INSERT INTO `referral_sources` VALUES (6,'Word of Mouth','Referral',1,2,1537472973),(7,'Internet Search','Internet Search',25,2,1537472973),(8,'Other','Other',999,2,1537472973),(9,'Facebook','Facebook',0,2,1537472973),(10,'Twitter','Twitter',0,2,1537472973),(11,'Email Campaign','Email Campaign',0,2,1537472973),(12,'Colleagues','Colleagues',0,2,1537472973),(13,'Linked In','Linked In',0,2,1537472973);
/*!40000 ALTER TABLE `referral_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remaining_reservation_spots`
--

DROP TABLE IF EXISTS `remaining_reservation_spots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remaining_reservation_spots` (
  `calendar_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `recurrence_number` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_remaining_spots` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `calendar_event_id` (`calendar_event_id`),
  KEY `recurrence_number` (`recurrence_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remaining_reservation_spots`
--

LOCK TABLES `remaining_reservation_spots` WRITE;
/*!40000 ALTER TABLE `remaining_reservation_spots` DISABLE KEYS */;
/*!40000 ALTER TABLE `remaining_reservation_spots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_items`
--

DROP TABLE IF EXISTS `search_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_items` (
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `timestamp` (`timestamp`),
  KEY `page_id` (`page_id`),
  KEY `url` (`url`(250)),
  FULLTEXT KEY `content` (`content`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `keywords` (`keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_items`
--

LOCK TABLES `search_items` WRITE;
/*!40000 ALTER TABLE `search_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_results_pages`
--

DROP TABLE IF EXISTS `search_results_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_results_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `search_catalog_items` tinyint(4) NOT NULL DEFAULT '0',
  `product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `catalog_detail_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `search_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_results_pages`
--

LOCK TABLES `search_results_pages` WRITE;
/*!40000 ALTER TABLE `search_results_pages` DISABLE KEYS */;
INSERT INTO `search_results_pages` VALUES (1,216,1,37,986,1);
/*!40000 ALTER TABLE `search_results_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ship_date_adjustments`
--

DROP TABLE IF EXISTS `ship_date_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ship_date_adjustments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zip_code_prefix` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  `adjustment` smallint(6) NOT NULL DEFAULT '0',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `shipping_method_id` (`shipping_method_id`),
  KEY `zip_code_prefix_shipping_method_id` (`zip_code_prefix`,`shipping_method_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ship_date_adjustments`
--

LOCK TABLES `ship_date_adjustments` WRITE;
/*!40000 ALTER TABLE `ship_date_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ship_date_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ship_tos`
--

DROP TABLE IF EXISTS `ship_tos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ship_tos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_to_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `salutation` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `company` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_1` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_2` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `phone_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `arrival_date_id` int(10) unsigned NOT NULL DEFAULT '0',
  `arrival_date_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `arrival_date` date NOT NULL DEFAULT '0000-00-00',
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shipping_method_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `shipping_cost` int(10) unsigned NOT NULL DEFAULT '0',
  `complete` tinyint(4) NOT NULL DEFAULT '0',
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `original_shipping_cost` int(10) unsigned NOT NULL DEFAULT '0',
  `offer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `address_verified` tinyint(4) NOT NULL DEFAULT '0',
  `address_type` enum('','residential','business') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ship_date` date NOT NULL DEFAULT '0000-00-00',
  `packages` text COLLATE utf8_unicode_ci NOT NULL,
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ship_tos`
--

LOCK TABLES `ship_tos` WRITE;
/*!40000 ALTER TABLE `ship_tos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ship_tos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_address_and_arrival_pages`
--

DROP TABLE IF EXISTS `shipping_address_and_arrival_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_address_and_arrival_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `form_label_column_width` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `address_type_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_address_and_arrival_pages`
--

LOCK TABLES `shipping_address_and_arrival_pages` WRITE;
/*!40000 ALTER TABLE `shipping_address_and_arrival_pages` DISABLE KEYS */;
INSERT INTO `shipping_address_and_arrival_pages` VALUES (17,144,'Continue Checkout',145,1,'','',1,0);
/*!40000 ALTER TABLE `shipping_address_and_arrival_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_cutoffs`
--

DROP TABLE IF EXISTS `shipping_cutoffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_cutoffs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `arrival_date_id` int(10) unsigned NOT NULL DEFAULT '0',
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date_and_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `arrival_date_id` (`arrival_date_id`),
  KEY `shipping_method_id` (`shipping_method_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_cutoffs`
--

LOCK TABLES `shipping_cutoffs` WRITE;
/*!40000 ALTER TABLE `shipping_cutoffs` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_cutoffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_delivery_dates`
--

DROP TABLE IF EXISTS `shipping_delivery_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_delivery_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service` enum('usps_priority','usps_express','usps_ground','ups_next_day_air','ups_next_day_air_early','ups_next_day_air_saver','ups_2nd_day_air','ups_2nd_day_air_am','ups_3_day_select','ups_ground','fedex_first_overnight','fedex_priority_overnight','fedex_standard_overnight','fedex_2_day_am','fedex_2_day','fedex_express_saver','fedex_ground') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'usps_priority',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ship_date` date NOT NULL DEFAULT '0000-00-00',
  `delivery_date` date NOT NULL DEFAULT '0000-00-00',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `combination` (`service`,`zip_code`,`ship_date`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_delivery_dates`
--

LOCK TABLES `shipping_delivery_dates` WRITE;
/*!40000 ALTER TABLE `shipping_delivery_dates` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_delivery_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_method_pages`
--

DROP TABLE IF EXISTS `shipping_method_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_method_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `submit_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_description_type` enum('full_description','short_description') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full_description',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_method_pages`
--

LOCK TABLES `shipping_method_pages` WRITE;
/*!40000 ALTER TABLE `shipping_method_pages` DISABLE KEYS */;
INSERT INTO `shipping_method_pages` VALUES (7,145,'Continue Checkout',179,'full_description');
/*!40000 ALTER TABLE `shipping_method_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_methods`
--

DROP TABLE IF EXISTS `shipping_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `primary_weight_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `secondary_weight_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `item_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `base_transit_days` int(10) unsigned NOT NULL DEFAULT '0',
  `adjust_transit` tinyint(4) NOT NULL DEFAULT '0',
  `street_address` tinyint(4) NOT NULL DEFAULT '0',
  `po_box` tinyint(4) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('enabled','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'enabled',
  `start_time` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '9999-12-31 23:59:59',
  `transit_on_sunday` tinyint(4) NOT NULL DEFAULT '0',
  `transit_on_saturday` tinyint(4) NOT NULL DEFAULT '0',
  `base_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `available_on_sunday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_sunday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_monday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_monday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_tuesday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_tuesday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_wednesday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_wednesday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_thursday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_thursday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_friday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_friday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `available_on_saturday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `available_on_saturday_cutoff_time` time NOT NULL DEFAULT '00:00:00',
  `primary_weight_rate_first_item_excluded` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `secondary_weight_rate_first_item_excluded` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `item_rate_first_item_excluded` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `variable_base_rate` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `base_rate_2` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate_2_subtotal` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate_3` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate_3_subtotal` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate_4` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate_4_subtotal` int(10) unsigned NOT NULL DEFAULT '0',
  `service` enum('','usps_priority','usps_express','usps_ground','ups_next_day_air','ups_next_day_air_early','ups_next_day_air_saver','ups_2nd_day_air','ups_2nd_day_air_am','ups_3_day_select','ups_ground','fedex_first_overnight','fedex_priority_overnight','fedex_standard_overnight','fedex_2_day_am','fedex_2_day','fedex_express_saver','fedex_ground') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `protected` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `realtime_rate` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `handle_mon` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_tue` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_wed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_thu` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_fri` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_sat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `handle_sun` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_mon` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_tue` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_wed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_thu` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_fri` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_sat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ship_sun` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `end_of_day` time NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (`id`),
  KEY `protected` (`protected`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_methods`
--

LOCK TABLES `shipping_methods` WRITE;
/*!40000 ALTER TABLE `shipping_methods` DISABLE KEYS */;
INSERT INTO `shipping_methods` VALUES (1,'UPS 5-Day Ground','Your shipment will arrive within 5-7 business days.','UPS-5DAY',210,0,0,5,0,1,0,40,1548292582,'enabled','2019-01-01 00:00:00','9999-12-31 23:59:00',0,0,0,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',0,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(2,'FEDEX Overnight','Your shipment will be delivered the next business day.','FEDEX-OVERNIGHT',1325,0,100,1,0,1,1,40,1548292628,'enabled','2019-01-01 00:00:00','9999-12-31 23:59:00',0,0,0,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',0,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(3,'UPS 2nd Day','Your shipment will arrive within 2 business days.','UPS-2DAY',397,0,0,2,0,1,0,40,1548292610,'enabled','2019-01-01 00:00:00','9999-12-31 23:59:00',0,0,0,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',0,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(4,'Parcel Post','Your gifts will arrive within 7-14 business days.','PARCEL',156,0,0,14,0,1,1,40,1548292602,'enabled','2019-01-01 00:00:00','9999-12-31 23:59:00',0,0,0,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',0,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(5,'International','We will ship your gifts the best possible method to arrive by your requested arrival date.','INTL',965,0,100,0,1,1,1,40,1548292591,'enabled','2019-01-01 00:00:00','9999-12-31 23:59:00',0,0,0,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',0,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(6,'Regular Delivery','Your shipment will arrive within 5-7 business days.','UPS-5DAY',0,0,250,5,0,1,0,40,1548292794,'enabled','2019-01-01 00:00:00','2099-12-31 23:59:00',0,1,1000,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',1,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00'),(7,'Express Delivery','Your shipment will arrive within 2 business days.','UPS-2DAY',0,0,800,5,0,1,0,40,1548292863,'enabled','2019-01-01 00:00:00','2099-12-31 23:59:00',0,1,2000,1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',1,'00:00:00',0,0,0,0,0,0,0,0,0,0,'',1,0,0,1,1,1,1,1,0,0,1,1,1,1,1,0,0,'00:00:00');
/*!40000 ALTER TABLE `shipping_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_methods_zones_xref`
--

DROP TABLE IF EXISTS `shipping_methods_zones_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods_zones_xref` (
  `shipping_method_id` int(10) unsigned NOT NULL DEFAULT '0',
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `shipping_method_id` (`shipping_method_id`),
  KEY `zone_id` (`zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_methods_zones_xref`
--

LOCK TABLES `shipping_methods_zones_xref` WRITE;
/*!40000 ALTER TABLE `shipping_methods_zones_xref` DISABLE KEYS */;
INSERT INTO `shipping_methods_zones_xref` VALUES (1,1),(2,1),(3,1),(4,2),(5,3),(6,1),(7,1);
/*!40000 ALTER TABLE `shipping_methods_zones_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_rates`
--

DROP TABLE IF EXISTS `shipping_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service` enum('usps_priority','usps_express','usps_ground','ups_next_day_air','ups_next_day_air_early','ups_next_day_air_saver','ups_2nd_day_air','ups_2nd_day_air_am','ups_3_day_select','ups_ground','fedex_first_overnight','fedex_priority_overnight','fedex_standard_overnight','fedex_2_day_am','fedex_2_day','fedex_express_saver','fedex_ground') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'usps_priority',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `weight` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `length` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `width` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `height` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `rate` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `combination` (`service`,`zip_code`,`weight`,`length`,`width`,`height`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_rates`
--

LOCK TABLES `shipping_rates` WRITE;
/*!40000 ALTER TABLE `shipping_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_tracking_numbers`
--

DROP TABLE IF EXISTS `shipping_tracking_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_tracking_numbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `ship_to_id` (`ship_to_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_tracking_numbers`
--

LOCK TABLES `shipping_tracking_numbers` WRITE;
/*!40000 ALTER TABLE `shipping_tracking_numbers` DISABLE KEYS */;
INSERT INTO `shipping_tracking_numbers` VALUES (11,344,274,'Z2222222222222');
/*!40000 ALTER TABLE `shipping_tracking_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopping_cart_pages`
--

DROP TABLE IF EXISTS `shopping_cart_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopping_cart_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `update_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `checkout_button_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `next_page_id_with_shipping` int(10) unsigned NOT NULL DEFAULT '0',
  `next_page_id_without_shipping` int(10) unsigned NOT NULL DEFAULT '0',
  `shopping_cart_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quick_add_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quick_add_product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `special_offer_code_label` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `special_offer_code_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `product_description_type` enum('full_description','short_description') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'full_description',
  `hook_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopping_cart_pages`
--

LOCK TABLES `shopping_cart_pages` WRITE;
/*!40000 ALTER TABLE `shopping_cart_pages` DISABLE KEYS */;
INSERT INTO `shopping_cart_pages` VALUES (50,248,'Update Cart','Checkout',144,179,'Cart','Quick Add Items',0,'Offer Code','Click \'Update Cart\' to apply offer code.','full_description','');
/*!40000 ALTER TABLE `shopping_cart_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `short_links`
--

DROP TABLE IF EXISTS `short_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `short_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `destination_type` enum('page','product_group','product','url') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'page',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tracking_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `product_group_id` (`product_group_id`),
  KEY `product_id` (`product_id`),
  KEY `created_timestamp` (`created_timestamp`),
  KEY `last_modified_timestamp` (`last_modified_timestamp`),
  KEY `name` (`name`),
  KEY `name_2` (`name`),
  KEY `name_3` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `short_links`
--

LOCK TABLES `short_links` WRITE;
/*!40000 ALTER TABLE `short_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `short_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `states`
--

LOCK TABLES `states` WRITE;
/*!40000 ALTER TABLE `states` DISABLE KEYS */;
INSERT INTO `states` VALUES (3,'Alabama','AL',241,0,1537472973),(4,'Alaska','AK',241,0,1537472973),(5,'Arizona','AZ',241,0,1537472973),(6,'Arkansas','AR',241,0,1537472973),(7,'California','CA',241,0,1537472973),(8,'Colorado','CO',241,0,1537472973),(9,'Connecticut','CT',241,0,1537472973),(10,'Delaware','DE',241,0,1537472973),(11,'District of Columbia','DC',241,0,1537472973),(12,'Florida','FL',241,0,1537472973),(13,'Georgia','GA',241,0,1537472973),(14,'Hawaii','HI',241,0,1537472973),(15,'Idaho','ID',241,0,1537472973),(16,'Illinois','IL',241,0,1537472973),(17,'Indiana','IN',241,0,1537472973),(18,'Iowa','IA',241,0,1537472973),(19,'Kansas','KS',241,0,1537472973),(20,'Kentucky','KY',241,0,1537472973),(21,'Louisiana','LA',241,0,1537472973),(22,'Maine','ME',241,0,1537472973),(23,'Maryland','MD',241,0,1537472973),(24,'Massachusetts','MA',241,0,1537472973),(25,'Michigan','MI',241,0,1537472973),(26,'Minnesota','MN',241,0,1537472973),(27,'Mississippi','MS',241,0,1537472973),(28,'Missouri','MO',241,0,1537472973),(29,'Montana','MT',241,0,1537472973),(30,'Nebraska','NE',241,0,1537472973),(31,'Nevada','NV',241,0,1537472973),(32,'New Hampshire','NH',241,0,1537472973),(33,'New Jersey','NJ',241,0,1537472973),(34,'New Mexico','NM',241,0,1537472973),(35,'New York','NY',241,0,1537472973),(36,'North Carolina','NC',241,0,1537472973),(37,'North Dakota','ND',241,0,1537472973),(38,'Ohio','OH',241,0,1537472973),(39,'Oklahoma','OK',241,0,1537472973),(40,'Oregon','OR',241,0,1537472973),(41,'Pennsylvania','PA',241,0,1537472973),(42,'Rhode Island','RI',241,0,1537472973),(43,'South Carolina','SC',241,0,1537472973),(44,'South Dakota','SD',241,0,1537472973),(45,'Tennessee','TN',241,0,1537472973),(46,'Texas','TX',241,0,1537472973),(47,'Utah','UT',241,0,1537472973),(48,'Vermont','VT',241,0,1537472973),(49,'Virginia','VA',241,0,1537472973),(50,'Washington','WA',241,0,1537472973),(51,'West Virginia','WV',241,0,1537472973),(52,'Wisconsin','WI',241,0,1537472973),(53,'Wyoming','WY',241,0,1537472973),(54,'AE','AE',241,0,1537472973),(55,'AA','AA',241,0,1537472973),(56,'AP','AP',241,0,1537472973),(61,'Guam','GU',241,46,1537472973),(62,'American Samoa','AS',241,46,1537472973),(63,'Fed. ST Micronesia','FM',241,46,1537472973),(64,'Marshall Islands','MH',241,46,1537472973),(65,'Northern Ariana Is','MP',241,46,1537472973),(66,'Palau','PW',241,46,1537472973),(67,'Puerto Rico','PR',241,46,1537472973),(68,'Virgin Islands (US)','VI',241,46,1537472973);
/*!40000 ALTER TABLE `states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `style`
--

DROP TABLE IF EXISTS `style`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `style` (
  `style_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `style_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `style_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `style_user` int(10) unsigned DEFAULT NULL,
  `style_timestamp` int(10) unsigned DEFAULT NULL,
  `style_type` enum('custom','system') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'custom',
  `style_layout` enum('','one_column','one_column_email','one_column_mobile','two_column_sidebar_left','two_column_sidebar_right','three_column_sidebar_left') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `style_head` longtext COLLATE utf8_unicode_ci NOT NULL,
  `style_empty_cell_width_percentage` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `social_networking_position` enum('top_left','top_right','bottom_left','bottom_right','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bottom_left',
  `additional_body_classes` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `theme_id` int(10) unsigned NOT NULL DEFAULT '0',
  `collection` enum('a','b') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'a',
  `layout_type` enum('','system','custom') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`style_id`),
  KEY `style_timestamp` (`style_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=765 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `style`
--

LOCK TABLES `style` WRITE;
/*!40000 ALTER TABLE `style` DISABLE KEYS */;
/*!40000 ALTER TABLE `style` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submitted_form_info`
--

DROP TABLE IF EXISTS `submitted_form_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submitted_form_info` (
  `submitted_form_id` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_comments` int(10) unsigned NOT NULL DEFAULT '0',
  `newest_comment_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number_of_views` bigint(20) unsigned NOT NULL DEFAULT '0',
  KEY `submitted_form_id` (`submitted_form_id`),
  KEY `page_id` (`page_id`),
  KEY `newest_comment_id` (`newest_comment_id`),
  KEY `submitted_form_id_page_id` (`submitted_form_id`,`page_id`),
  KEY `number_of_views` (`number_of_views`),
  KEY `number_of_comments` (`number_of_comments`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submitted_form_info`
--

LOCK TABLES `submitted_form_info` WRITE;
/*!40000 ALTER TABLE `submitted_form_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `submitted_form_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submitted_form_views`
--

DROP TABLE IF EXISTS `submitted_form_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submitted_form_views` (
  `submitted_form_id` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `submitted_form_id` (`submitted_form_id`),
  KEY `page_id` (`page_id`),
  KEY `timestamp` (`timestamp`),
  KEY `submitted_form_id_page_id_timestamp` (`submitted_form_id`,`page_id`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submitted_form_views`
--

LOCK TABLES `submitted_form_views` WRITE;
/*!40000 ALTER TABLE `submitted_form_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `submitted_form_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_style_cells`
--

DROP TABLE IF EXISTS `system_style_cells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_style_cells` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `area` enum('site_top','site_header','area_header','page_header','page_content','page_content_left','page_content_right','sidebar','page_footer','area_footer','site_footer') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'site_top',
  `row` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `col` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `region_type` enum('','ad','cart','chat','common','designer','dynamic','login','menu','menu_sequence','mobile_switch','page','pdf','system','tag_cloud') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `region_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `style_id` (`style_id`)
) ENGINE=MyISAM AUTO_INCREMENT=40238 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_style_cells`
--

LOCK TABLES `system_style_cells` WRITE;
/*!40000 ALTER TABLE `system_style_cells` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_style_cells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_theme_css_rules`
--

DROP TABLE IF EXISTS `system_theme_css_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_theme_css_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `area` enum('site_wide','body','site_border','email_border','mobile_border','site_top','site_header','area_border','area_header','page_wrapper','page_border','page_header','page_content','page_content_left','page_content_right','sidebar','page_footer','area_footer','site_footer_border','site_footer') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'site_wide',
  `row` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `col` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `module` enum('','background_borders_and_spacing','headings_general','heading_1','heading_2','heading_3','heading_4','heading_5','heading_6','image_primary','image_secondary','input','layout','links','links_hover','menu','menu_item','menu_item_hover','submenu_background_borders_and_spacing','submenu_menu_item','submenu_menu_item_hover','paragraph','previous_and_next_buttons','primary_buttons','primary_buttons_hover','secondary_buttons','secondary_buttons_hover','text') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `property` enum('','pre_styling','advanced_styling','background_color','background_image','background_horizontal_position','background_type','background_repeat','background_vertical_position','borders_toggle','border_color','border_position','border_size','border_style','bottom_left','bottom_right','font_color','font_family','font_size','font_style','font_weight','height','line_height','margin_bottom','margin_left','margin_right','margin_top','menu_orientation','padding_bottom','padding_left','padding_right','padding_top','position','previous_and_next_buttons_toggle','previous_and_next_buttons_horizontal_offset','previous_and_next_buttons_vertical_offset','primary_color','rounded_corners_toggle','rounded_corner_bottom_left','rounded_corner_bottom_right','rounded_corner_top_left','rounded_corner_top_right','secondary_color','shadow_blur_radius','shadow_color','shadow_horizontal_offset','shadows_toggle','shadow_vertical_offset','text_decoration','top_left','top_right','width') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `region_type` enum('','ad','menu') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `region_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1819552 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_theme_css_rules`
--

LOCK TABLES `system_theme_css_rules` WRITE;
/*!40000 ALTER TABLE `system_theme_css_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_theme_css_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_cloud_keywords`
--

DROP TABLE IF EXISTS `tag_cloud_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_cloud_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_type` enum('','page','product','product_group') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8835 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_cloud_keywords`
--

LOCK TABLES `tag_cloud_keywords` WRITE;
/*!40000 ALTER TABLE `tag_cloud_keywords` DISABLE KEYS */;
INSERT INTO `tag_cloud_keywords` VALUES (8834,'online giving',266,'page'),(2817,'contact us',183,'page'),(2811,'news',227,'page'),(2810,'blog',227,'page'),(2813,'FAQs',564,'page'),(2814,'photos',343,'page'),(2812,'about us',574,'page'),(2818,'survey',271,'page'),(8754,'blog',1013,'page'),(8755,'news',1013,'page'),(8756,'blog posts',1013,'page'),(2819,'directory',219,'page'),(2824,'home',461,'page'),(2850,'blog posts',227,'page'),(2839,'search store',306,'page'),(2840,'search store',361,'page'),(2856,'videos',599,'page'),(8832,'giving',266,'page'),(8833,'donate',266,'page'),(8753,'blog posts',1016,'page'),(8830,'downloads',36,'product'),(8827,'gifts',37,'product'),(8826,'books',37,'product'),(8824,'gifts',45,'product'),(8825,'office',45,'product'),(8745,'blog',227,'page'),(8746,'news',227,'page'),(8747,'blog posts',227,'page'),(8748,'blog',1015,'page'),(8749,'news',1015,'page'),(8750,'blog posts',1015,'page'),(8751,'blog',1016,'page'),(8752,'news',1016,'page'),(8829,'software',36,'product'),(8828,'downloads',37,'product'),(8823,'perishable',41,'product'),(8822,'gifts',41,'product'),(8813,'office',48,'product_group'),(8814,'chair',48,'product_group'),(8815,'chocolate',43,'product'),(8816,'gifts',43,'product'),(8817,'perishable',43,'product'),(8818,'chocolate',42,'product'),(8819,'gifts',42,'product'),(8820,'perishable',42,'product'),(8821,'chocolate',41,'product'),(8771,'photos',994,'page'),(8772,'photos',996,'page'),(8773,'photos',343,'page'),(8774,'photos',995,'page'),(8776,'directory',219,'page');
/*!40000 ALTER TABLE `tag_cloud_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_cloud_keywords_xref`
--

DROP TABLE IF EXISTS `tag_cloud_keywords_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_cloud_keywords_xref` (
  `search_results_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_type` enum('','product','product_group') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  KEY `search_results_page_id` (`search_results_page_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_cloud_keywords_xref`
--

LOCK TABLES `tag_cloud_keywords_xref` WRITE;
/*!40000 ALTER TABLE `tag_cloud_keywords_xref` DISABLE KEYS */;
INSERT INTO `tag_cloud_keywords_xref` VALUES (216,36,'product'),(216,45,'product'),(216,42,'product'),(216,43,'product_group'),(216,37,'product'),(216,41,'product'),(216,43,'product'),(216,48,'product_group'),(216,36,'product'),(216,45,'product'),(216,42,'product'),(216,43,'product_group'),(216,37,'product'),(216,41,'product'),(216,43,'product'),(216,48,'product_group');
/*!40000 ALTER TABLE `tag_cloud_keywords_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_options`
--

DROP TABLE IF EXISTS `target_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `target_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trigger_form_field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trigger_option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `product_id` (`product_id`),
  KEY `trigger_form_field_id` (`trigger_form_field_id`),
  KEY `trigger_option_id` (`trigger_option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_options`
--

LOCK TABLES `target_options` WRITE;
/*!40000 ALTER TABLE `target_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `target_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_zones`
--

DROP TABLE IF EXISTS `tax_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_rate` decimal(6,3) unsigned NOT NULL DEFAULT '0.000',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_zones`
--

LOCK TABLES `tax_zones` WRITE;
/*!40000 ALTER TABLE `tax_zones` DISABLE KEYS */;
INSERT INTO `tax_zones` VALUES (1,'Texas Sales Tax','8.250',2,1537472973);
/*!40000 ALTER TABLE `tax_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_zones_countries_xref`
--

DROP TABLE IF EXISTS `tax_zones_countries_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_zones_countries_xref` (
  `tax_zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `tax_zone_id` (`tax_zone_id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_zones_countries_xref`
--

LOCK TABLES `tax_zones_countries_xref` WRITE;
/*!40000 ALTER TABLE `tax_zones_countries_xref` DISABLE KEYS */;
INSERT INTO `tax_zones_countries_xref` VALUES (1,241);
/*!40000 ALTER TABLE `tax_zones_countries_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_zones_states_xref`
--

DROP TABLE IF EXISTS `tax_zones_states_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_zones_states_xref` (
  `tax_zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `state_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `tax_zone_id` (`tax_zone_id`),
  KEY `state_id` (`state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_zones_states_xref`
--

LOCK TABLES `tax_zones_states_xref` WRITE;
/*!40000 ALTER TABLE `tax_zones_states_xref` DISABLE KEYS */;
INSERT INTO `tax_zones_states_xref` VALUES (1,46);
/*!40000 ALTER TABLE `tax_zones_states_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `update_address_book_pages`
--

DROP TABLE IF EXISTS `update_address_book_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `update_address_book_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `address_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `address_type_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `update_address_book_pages`
--

LOCK TABLES `update_address_book_pages` WRITE;
/*!40000 ALTER TABLE `update_address_book_pages` DISABLE KEYS */;
INSERT INTO `update_address_book_pages` VALUES (2,976,1,0);
/*!40000 ALTER TABLE `update_address_book_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_username` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_password` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_role` int(1) DEFAULT NULL,
  `user_home` int(10) unsigned DEFAULT NULL,
  `user_manage_contacts` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_manage_emails` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_user` int(10) unsigned DEFAULT NULL,
  `user_timestamp` int(10) unsigned DEFAULT NULL,
  `user_contact` int(10) unsigned DEFAULT NULL,
  `user_manage_ecommerce` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_manage_forms` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_manage_calendars` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_password_hint` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_manage_visitors` enum('no','yes') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `user_publish_calendar_events` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `user_create_pages` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_email_a_friend` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_photo_gallery` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_custom_form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_custom_form_confirmation` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_form_list_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_form_item_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_calendar_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_calendar_event_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_catalog` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_catalog_detail` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_express_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_order_form` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_shopping_cart` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_shipping_address_and_arrival` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_shipping_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_billing_information` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_order_preview` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_order_receipt` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_offline_payment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_form_view_directory` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_delete_pages` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_view_card_data` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_badge` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_reward_points` int(10) unsigned NOT NULL DEFAULT '0',
  `user_set_page_type_folder_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_badge_label` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `manage_ecommerce_reports` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_contact` (`user_contact`),
  KEY `user_timestamp` (`user_timestamp`),
  KEY `user_username` (`user_username`),
  KEY `user_email` (`user_email`),
  KEY `user_password` (`user_password`),
  KEY `user_role` (`user_role`),
  KEY `user_manage_contacts` (`user_manage_contacts`),
  KEY `user_manage_ecommerce` (`user_manage_ecommerce`),
  KEY `manage_ecommerce_reports` (`manage_ecommerce_reports`),
  KEY `user_set_offline_payment` (`user_set_offline_payment`),
  KEY `user_manage_calendars` (`user_manage_calendars`),
  KEY `user_manage_emails` (`user_manage_emails`),
  KEY `user_manage_visitors` (`user_manage_visitors`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_ad_regions_xref`
--

DROP TABLE IF EXISTS `users_ad_regions_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_ad_regions_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ad_region_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `ad_region_id` (`ad_region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_ad_regions_xref`
--

LOCK TABLES `users_ad_regions_xref` WRITE;
/*!40000 ALTER TABLE `users_ad_regions_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_ad_regions_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_calendars_xref`
--

DROP TABLE IF EXISTS `users_calendars_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_calendars_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `calendar_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_calendars_xref`
--

LOCK TABLES `users_calendars_xref` WRITE;
/*!40000 ALTER TABLE `users_calendars_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_calendars_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_common_regions_xref`
--

DROP TABLE IF EXISTS `users_common_regions_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_common_regions_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `common_region_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `common_region_id` (`common_region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_common_regions_xref`
--

LOCK TABLES `users_common_regions_xref` WRITE;
/*!40000 ALTER TABLE `users_common_regions_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_common_regions_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_contact_groups_xref`
--

DROP TABLE IF EXISTS `users_contact_groups_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_contact_groups_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `contact_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `contact_group_id` (`contact_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_contact_groups_xref`
--

LOCK TABLES `users_contact_groups_xref` WRITE;
/*!40000 ALTER TABLE `users_contact_groups_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_contact_groups_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_menus_xref`
--

DROP TABLE IF EXISTS `users_menus_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_menus_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `menu_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_menus_xref`
--

LOCK TABLES `users_menus_xref` WRITE;
/*!40000 ALTER TABLE `users_menus_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_menus_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_messages_xref`
--

DROP TABLE IF EXISTS `users_messages_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_messages_xref` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message_id` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `message_id` (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_messages_xref`
--

LOCK TABLES `users_messages_xref` WRITE;
/*!40000 ALTER TABLE `users_messages_xref` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_messages_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verified_shipping_addresses`
--

DROP TABLE IF EXISTS `verified_shipping_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verified_shipping_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_1` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address_2` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state_id` int(10) unsigned NOT NULL DEFAULT '0',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `state_id` (`state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verified_shipping_addresses`
--

LOCK TABLES `verified_shipping_addresses` WRITE;
/*!40000 ALTER TABLE `verified_shipping_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `verified_shipping_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_report_filters`
--

DROP TABLE IF EXISTS `visitor_report_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_report_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_report_id` int(10) unsigned NOT NULL DEFAULT '0',
  `field` enum('','date','site_search_terms','currency_code','http_referer','referring_host_name','referring_search_engine','referring_search_terms','pay_per_click_organic','first_visit','landing_page_name','tracking_code','affiliate_code','utm_source','utm_medium','utm_campaign','utm_term','utm_content','page_views','custom_form_submitted','custom_form_name','order_created','order_retrieved','order_checked_out','order_completed','city','state','zip_code','country','ip_address') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `operator` enum('contains','does not contain','is equal to','is not equal to','is less than','is less than or equal to','is greater than','is greater than or equal to') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contains',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value` enum('','current date','days ago') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dynamic_value_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `visitor_report_id` (`visitor_report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_report_filters`
--

LOCK TABLES `visitor_report_filters` WRITE;
/*!40000 ALTER TABLE `visitor_report_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_report_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_reports`
--

DROP TABLE IF EXISTS `visitor_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `detail` tinyint(4) NOT NULL DEFAULT '0',
  `summarize_by_1` enum('','year','month','day','site_search_terms','http_referer','referring_host_name','referring_search_engine','referring_search_terms','pay_per_click_organic','first_visit','landing_page_name','tracking_code','affiliate_code','utm_source','utm_medium','utm_campaign','utm_term','utm_content','page_views','custom_form_submitted','custom_form_name','order_created','order_retrieved','order_checked_out','order_completed','city','state','zip_code','country','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_1` enum('alphabet','number of visitors','number of page views','order total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `summarize_by_2` enum('','year','month','day','site_search_terms','http_referer','referring_host_name','referring_search_engine','referring_search_terms','pay_per_click_organic','first_visit','landing_page_name','tracking_code','affiliate_code','utm_source','utm_medium','utm_campaign','utm_term','utm_content','page_views','custom_form_submitted','custom_form_name','order_created','order_retrieved','order_checked_out','order_completed','city','state','zip_code','country','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_2` enum('alphabet','number of visitors','number of page views','order total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `summarize_by_3` enum('','year','month','day','site_search_terms','http_referer','referring_host_name','referring_search_engine','referring_search_terms','pay_per_click_organic','first_visit','landing_page_name','tracking_code','affiliate_code','utm_source','utm_medium','utm_campaign','utm_term','utm_content','page_views','custom_form_submitted','custom_form_name','order_created','order_retrieved','order_checked_out','order_completed','city','state','zip_code','country','currency_code') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_by_3` enum('alphabet','number of visitors','number of page views','order total') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'alphabet',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_reports`
--

LOCK TABLES `visitor_reports` WRITE;
/*!40000 ALTER TABLE `visitor_reports` DISABLE KEYS */;
INSERT INTO `visitor_reports` VALUES (1,'New Visitor Landing Page Report',0,'first_visit','number of visitors','landing_page_name','number of visitors','','alphabet',2,1537472973,2,1537472973);
/*!40000 ALTER TABLE `visitor_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `http_referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referring_host_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referring_search_engine` enum('','AlltheWeb','AltaVista','AOL','Ask.com','Bing','Comet Web Search','EarthLink','Excite','Google','HotBot','LookSmart','Lycos','Mamma.com','MetaCrawler','MSN','Netscape','Open Directory Project','Overture','Viewpoint','WebCrawler','Yahoo!') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referring_search_terms` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_visit` tinyint(4) NOT NULL DEFAULT '0',
  `landing_page_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tracking_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `affiliate_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `page_views` int(10) unsigned NOT NULL DEFAULT '0',
  `custom_form_submitted` tinyint(4) NOT NULL DEFAULT '0',
  `custom_form_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_created` tinyint(4) NOT NULL DEFAULT '0',
  `order_retrieved` tinyint(4) NOT NULL DEFAULT '0',
  `order_checked_out` tinyint(4) NOT NULL DEFAULT '0',
  `order_completed` tinyint(4) NOT NULL DEFAULT '0',
  `order_total` int(11) NOT NULL DEFAULT '0',
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `zip_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `start_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `stop_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL DEFAULT '0',
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site_search_terms` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_source` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_medium` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_campaign` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_term` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `utm_content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `start_timestamp` (`start_timestamp`),
  KEY `stop_timestamp` (`stop_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitors`
--

LOCK TABLES `visitors` WRITE;
/*!40000 ALTER TABLE `visitors` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watchers`
--

DROP TABLE IF EXISTS `watchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_type` enum('','submitted_form','calendar_event','product_group','product') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `page_id` (`page_id`),
  KEY `item_id` (`item_id`),
  KEY `email_address` (`email_address`),
  KEY `item_type` (`item_type`),
  KEY `item_type_2` (`item_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watchers`
--

LOCK TABLES `watchers` WRITE;
/*!40000 ALTER TABLE `watchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `watchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `primary_weight_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `secondary_weight_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `item_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `base_rate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (1,'US Domestic',0,0,0,2,1537472973,0),(2,'US Territories',0,0,0,2,1537472973,0),(3,'International',0,0,0,2,1537472973,0);
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones_countries_xref`
--

DROP TABLE IF EXISTS `zones_countries_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones_countries_xref` (
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `zone_id` (`zone_id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones_countries_xref`
--

LOCK TABLES `zones_countries_xref` WRITE;
/*!40000 ALTER TABLE `zones_countries_xref` DISABLE KEYS */;
INSERT INTO `zones_countries_xref` VALUES (1,241),(2,241),(3,256),(3,255),(3,254),(3,252),(3,250),(3,249),(3,248),(3,247),(3,246),(3,245),(3,244),(3,243),(3,240),(3,239),(3,238),(3,237),(3,236),(3,235),(3,234),(3,233),(3,232),(3,229),(3,228),(3,227),(3,225),(3,224),(3,223),(3,222),(3,220),(3,219),(3,218),(3,217),(3,216),(3,215),(3,206),(3,204),(3,203),(3,201),(3,200),(3,199),(3,198),(3,197),(3,196),(3,195),(3,194),(3,193),(3,191),(3,190),(3,213),(3,212),(3,209),(3,214),(3,208),(3,187),(3,186),(3,185),(3,184),(3,183),(3,180),(3,179),(3,178),(3,177),(3,176),(3,175),(3,174),(3,173),(3,172),(3,171),(3,170),(3,169),(3,168),(3,167),(3,166),(3,165),(3,161),(3,163),(3,160),(3,159),(3,158),(3,157),(3,156),(3,155),(3,154),(3,152),(3,151),(3,150),(3,148),(3,147),(3,146),(3,145),(3,144),(3,143),(3,142),(3,141),(3,140),(3,138),(3,137),(3,136),(3,135),(3,134),(3,133),(3,132),(3,131),(3,130),(3,127),(3,126),(3,125),(3,124),(3,123),(3,122),(3,121),(3,120),(3,119),(3,118),(3,117),(3,116),(3,114),(3,113),(3,112),(3,111),(3,110),(3,109),(3,108),(3,107),(3,106),(3,105),(3,104),(3,102),(3,101),(3,99),(3,100),(3,98),(3,97),(3,95),(3,94),(3,93),(3,91),(3,90),(3,89),(3,88),(3,87),(3,86),(3,85),(3,84),(3,82),(3,80),(3,79),(3,78),(3,77),(3,76),(3,75),(3,74),(3,71),(3,69),(3,68),(3,67),(3,66),(3,65),(3,63),(3,60),(3,59),(3,57),(3,56),(3,55),(3,52),(3,51),(3,50),(3,49),(3,48),(3,46),(3,45),(3,44),(3,42),(3,41),(3,38),(3,36),(3,35),(3,34),(3,33),(3,32),(3,31),(3,30),(3,29),(3,28),(3,27),(3,26),(3,25),(3,39),(3,23),(3,22),(3,21),(3,20),(3,18),(3,17),(3,16),(3,14),(3,13),(3,12),(3,11),(3,10),(3,9),(3,8),(3,7),(3,6),(3,5);
/*!40000 ALTER TABLE `zones_countries_xref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones_states_xref`
--

DROP TABLE IF EXISTS `zones_states_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones_states_xref` (
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `state_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `zone_id` (`zone_id`),
  KEY `state_id` (`state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones_states_xref`
--

LOCK TABLES `zones_states_xref` WRITE;
/*!40000 ALTER TABLE `zones_states_xref` DISABLE KEYS */;
INSERT INTO `zones_states_xref` VALUES (1,53),(1,52),(1,51),(1,50),(1,49),(1,48),(1,47),(1,46),(1,45),(1,44),(1,43),(1,42),(1,41),(1,40),(1,39),(1,38),(1,37),(1,36),(1,35),(1,34),(1,33),(1,32),(1,31),(1,30),(1,29),(1,28),(1,27),(1,26),(1,25),(1,24),(1,23),(1,22),(1,21),(1,20),(1,19),(1,18),(1,17),(1,16),(1,15),(1,13),(1,12),(1,11),(1,10),(1,9),(1,8),(1,7),(1,6),(1,5),(1,3),(2,68),(2,67),(2,66),(2,65),(2,64),(2,14),(2,61),(2,63),(2,56),(2,62),(2,4),(2,54),(2,55);
/*!40000 ALTER TABLE `zones_states_xref` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-23 20:28:04