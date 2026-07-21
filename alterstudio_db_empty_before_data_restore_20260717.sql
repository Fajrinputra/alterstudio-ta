-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: alterstudio_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `package_id` bigint(20) unsigned NOT NULL,
  `studio_location_id` bigint(20) unsigned NOT NULL,
  `studio_room_id` bigint(20) unsigned NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('WAITING_PAYMENT','DP_PAID','PAID','CANCELLED') NOT NULL DEFAULT 'WAITING_PAYMENT',
  `confirmed_at` datetime DEFAULT NULL,
  `payment_started_at` datetime DEFAULT NULL,
  `payment_type` enum('DP','FULL') NOT NULL DEFAULT 'DP',
  `selected_addons` longtext DEFAULT NULL,
  `addon_total` double NOT NULL DEFAULT 0,
  `total_price` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bookings_status_confirmed_idx` (`status`,`confirmed_at`),
  KEY `bookings_client_status_created_idx` (`client_id`,`status`,`created_at`),
  KEY `bookings_package_status_idx` (`package_id`,`status`),
  KEY `bookings_date_time_idx` (`booking_date`,`booking_time`),
  KEY `bookings_location_date_idx` (`studio_location_id`,`booking_date`),
  KEY `bookings_room_date_time_idx` (`studio_room_id`,`booking_date`,`booking_time`),
  CONSTRAINT `bookings_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `service_packages` (`id`),
  CONSTRAINT `bookings_studio_location_id_foreign` FOREIGN KEY (`studio_location_id`) REFERENCES `studio_locations` (`id`),
  CONSTRAINT `bookings_studio_room_id_foreign` FOREIGN KEY (`studio_room_id`) REFERENCES `studio_rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `landing_hero_slides`
--

DROP TABLE IF EXISTS `landing_hero_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `landing_hero_slides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `eyebrow` varchar(50) DEFAULT NULL,
  `title` varchar(50) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `landing_hero_slides_user_id_foreign` (`user_id`),
  KEY `hero_active_sort_idx` (`is_active`,`sort_order`),
  CONSTRAINT `landing_hero_slides_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landing_hero_slides`
--

LOCK TABLES `landing_hero_slides` WRITE;
/*!40000 ALTER TABLE `landing_hero_slides` DISABLE KEYS */;
/*!40000 ALTER TABLE `landing_hero_slides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_assets`
--

DROP TABLE IF EXISTS `media_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `type` enum('RAW','FINAL') NOT NULL,
  `path` varchar(255) NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_assets_project_id_foreign` (`project_id`),
  KEY `media_assets_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `media_assets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_assets_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_assets`
--

LOCK TABLES `media_assets` WRITE;
/*!40000 ALTER TABLE `media_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `media_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_04_152217_add_role_to_users_table',1),(5,'2026_02_04_152228_create_service_categories_table',1),(6,'2026_02_04_152238_create_service_packages_table',1),(7,'2026_02_04_152247_create_bookings_table',1),(8,'2026_02_04_152255_create_payments_table',1),(9,'2026_02_04_152302_create_projects_table',1),(10,'2026_02_04_152309_create_schedules_table',1),(11,'2026_02_04_152315_create_media_assets_table',1),(12,'2026_02_04_152327_create_photo_selections_table',1),(13,'2026_02_04_152346_create_payroll_rules_table',1),(14,'2026_02_04_152358_create_payroll_records_table',1),(15,'2026_02_05_000001_add_is_active_to_users_table',1),(16,'2026_02_05_100000_update_service_packages_add_fields',1),(17,'2026_02_05_100010_create_studio_locations_table',1),(18,'2026_02_05_120000_add_map_url_to_studio_locations',1),(19,'2026_02_06_000500_add_no_hp_to_users_table',1),(20,'2026_02_06_001000_add_avatar_no_hp_to_users_table',1),(21,'2026_02_15_000001_create_sessions_table',1),(22,'2026_02_15_010000_add_gallery_to_service_packages',1),(23,'2026_02_15_020000_add_overview_image_to_service_packages',1),(24,'2026_02_16_000100_add_fields_to_bookings',1),(25,'2026_02_16_010000_add_description_to_studio_locations',1),(26,'2026_02_16_020000_create_studio_rooms_table',1),(27,'2026_02_16_021000_add_duration_minutes_to_service_packages',1),(28,'2026_02_28_233700_add_expires_at_to_media_assets',1),(29,'2026_03_03_000900_add_selection_locked_to_projects',1),(30,'2026_03_04_120500_add_photo_path_to_studio_locations',1),(31,'2026_03_05_090000_add_photo_gallery_to_studio_locations',1),(32,'2026_03_10_080000_add_addons_to_bookings_table',1),(33,'2026_03_10_140000_create_landing_hero_slides_table',1),(34,'2026_03_10_150000_add_soft_deletes_to_service_packages_table',1),(35,'2026_03_11_090000_add_foreign_key_to_sessions_user_id',1),(36,'2026_03_11_110000_add_relations_to_password_reset_tokens_table',1),(37,'2026_03_11_111000_add_audit_relations_to_landing_hero_slides_table',1),(38,'2026_03_11_120000_drop_payroll_tables',1),(39,'2026_03_11_130000_drop_infrastructure_tables_for_strict_mode',1),(40,'2026_03_12_130000_add_studio_room_id_to_bookings_table',1),(41,'2026_03_31_100000_normalize_core_catalog_and_location_tables',1),(42,'2026_03_31_110000_drop_legacy_denormalized_columns',1),(43,'2026_03_31_120000_drop_schedule_location_and_package_overview_image',1),(44,'2026_03_31_130000_drop_redundant_schedule_status_column',1),(45,'2026_03_31_140000_remove_draft_from_booking_status_enum',1),(46,'2026_03_31_150000_remove_review_from_project_status_enum',1),(47,'2026_03_31_160000_remove_preview_and_watermark_from_media_assets_enum',1),(48,'2026_03_31_170000_add_unique_booking_id_to_projects_table',1),(49,'2026_04_07_100000_collapse_auxiliary_tables_into_core_schema',1),(50,'2026_04_08_090000_backfill_prices_in_service_package_addons_json',1),(51,'2026_04_09_090000_add_roles_json_to_users_table',1),(52,'2026_04_14_090000_add_confirmed_at_to_bookings_table',1),(53,'2026_04_14_103000_add_payment_started_at_to_bookings_table',1),(54,'2026_04_14_104000_create_studio_holidays_table',1),(55,'2026_04_14_120000_add_studio_location_id_to_studio_holidays_table',1),(56,'2026_05_01_210000_add_drive_post_production_fields_to_projects_table',1),(57,'2026_05_07_090000_add_photo_path_to_studio_rooms_table',1),(58,'2026_05_07_110000_drop_studio_holidays_table',1),(59,'2026_05_10_090000_add_owner_role_to_users_table',1),(60,'2026_05_25_000000_resize_varchar_columns_to_match_schema_spec',1),(61,'2026_05_25_010000_sync_column_types_to_schema_spec',1),(62,'2026_05_25_020000_drop_coordinates_from_studio_locations_table',1),(63,'2026_05_25_030000_drop_unused_location_and_project_columns',1),(64,'2026_05_25_040000_drop_roles_from_users_table',1),(65,'2026_05_25_050000_create_project_schedule_tables',1),(66,'2026_05_31_120000_align_database_to_final_schema',1),(67,'2026_06_04_120000_add_performance_indexes',1),(68,'2026_06_05_090000_align_password_tokens_and_schedule_scheduler_column',1),(69,'2026_07_08_090000_move_schedule_assignments_into_project_schedules',1),(70,'2026_07_08_100000_compact_short_varchar_lengths',1),(71,'2026_07_14_090000_use_user_id_as_password_reset_token_primary_key',2),(72,'2026_07_14_091000_drop_redundant_password_reset_user_id_index',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(50) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `password_reset_tokens_email_index` (`email`),
  CONSTRAINT `password_reset_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `type` enum('DP','FULL') NOT NULL,
  `amount` double NOT NULL,
  `status` enum('PENDING','PAID','FAILED','EXPIRED') NOT NULL DEFAULT 'PENDING',
  `reference` varchar(50) DEFAULT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_order_id_unique` (`order_id`),
  KEY `payments_status_type_paid_idx` (`status`,`type`,`paid_at`),
  KEY `payments_booking_status_idx` (`booking_id`,`status`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photo_selections`
--

DROP TABLE IF EXISTS `photo_selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photo_selections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `media_asset_id` bigint(20) unsigned NOT NULL,
  `selected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photo_selections_project_id_media_asset_id_unique` (`project_id`,`media_asset_id`),
  KEY `photo_selections_client_id_foreign` (`client_id`),
  KEY `photo_selections_media_asset_id_foreign` (`media_asset_id`),
  CONSTRAINT `photo_selections_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  CONSTRAINT `photo_selections_media_asset_id_foreign` FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`),
  CONSTRAINT `photo_selections_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photo_selections`
--

LOCK TABLES `photo_selections` WRITE;
/*!40000 ALTER TABLE `photo_selections` DISABLE KEYS */;
/*!40000 ALTER TABLE `photo_selections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_schedules`
--

DROP TABLE IF EXISTS `project_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `booking_id` bigint(20) unsigned NOT NULL,
  `studio_location_id` bigint(20) unsigned NOT NULL,
  `studio_room_id` bigint(20) unsigned NOT NULL,
  `scheduled_by` bigint(20) unsigned NOT NULL,
  `photographer_id` bigint(20) unsigned NOT NULL,
  `editor_id` bigint(20) unsigned NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `status` enum('SCHEDULED','LOCKED','CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_schedules_project_id_unique` (`project_id`),
  KEY `project_schedules_booking_id_foreign` (`booking_id`),
  KEY `project_schedules_studio_location_id_foreign` (`studio_location_id`),
  KEY `schedules_start_end_idx` (`start_at`,`end_at`),
  KEY `schedules_room_start_end_idx` (`studio_room_id`,`start_at`,`end_at`),
  KEY `schedules_photographer_time_idx` (`photographer_id`,`start_at`,`end_at`),
  KEY `schedules_editor_time_idx` (`editor_id`,`start_at`,`end_at`),
  KEY `project_schedules_scheduled_by_index` (`scheduled_by`),
  CONSTRAINT `project_schedules_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_schedules_editor_id_foreign` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `project_schedules_photographer_id_foreign` FOREIGN KEY (`photographer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `project_schedules_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_schedules_scheduled_by_foreign` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `project_schedules_studio_location_id_foreign` FOREIGN KEY (`studio_location_id`) REFERENCES `studio_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_schedules_studio_room_id_foreign` FOREIGN KEY (`studio_room_id`) REFERENCES `studio_rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_schedules`
--

LOCK TABLES `project_schedules` WRITE;
/*!40000 ALTER TABLE `project_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) unsigned NOT NULL,
  `status` enum('DRAFT','SCHEDULED','SHOOT_DONE','EDITING','FINAL') NOT NULL DEFAULT 'DRAFT',
  `selections_locked` tinyint(1) NOT NULL DEFAULT 0,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `raw_drive_url` varchar(2048) DEFAULT NULL,
  `raw_drive_uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `raw_drive_uploaded_at` timestamp NULL DEFAULT NULL,
  `edit_photo_codes` text DEFAULT NULL,
  `edit_request_note` text DEFAULT NULL,
  `edit_requested_at` timestamp NULL DEFAULT NULL,
  `final_drive_url` varchar(2048) DEFAULT NULL,
  `final_message` text DEFAULT NULL,
  `final_drive_uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `final_drive_uploaded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_booking_id_unique` (`booking_id`),
  KEY `projects_raw_drive_uploaded_by_foreign` (`raw_drive_uploaded_by`),
  KEY `projects_final_drive_uploaded_by_foreign` (`final_drive_uploaded_by`),
  KEY `projects_status_start_idx` (`status`,`start_at`),
  KEY `projects_start_at_idx` (`start_at`),
  KEY `projects_edit_requested_idx` (`edit_requested_at`),
  CONSTRAINT `projects_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_final_drive_uploaded_by_foreign` FOREIGN KEY (`final_drive_uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_raw_drive_uploaded_by_foreign` FOREIGN KEY (`raw_drive_uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_packages`
--

DROP TABLE IF EXISTS `service_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` double NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `max_people` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `cover_image` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `terms` text DEFAULT NULL,
  `features` longtext DEFAULT NULL,
  `addons` longtext DEFAULT NULL,
  `gallery` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `packages_category_active_price_idx` (`category_id`,`is_active`,`price`),
  KEY `packages_active_name_idx` (`is_active`,`name`),
  CONSTRAINT `service_packages_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_packages`
--

LOCK TABLES `service_packages` WRITE;
/*!40000 ALTER TABLE `service_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_locations`
--

DROP TABLE IF EXISTS `studio_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `photo_gallery` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `studio_locations_slug_unique` (`slug`),
  KEY `locations_active_name_idx` (`is_active`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_locations`
--

LOCK TABLES `studio_locations` WRITE;
/*!40000 ALTER TABLE `studio_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_rooms`
--

DROP TABLE IF EXISTS `studio_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `studio_location_id` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `photo_path` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_location_active_idx` (`studio_location_id`,`is_active`),
  CONSTRAINT `studio_rooms_studio_location_id_foreign` FOREIGN KEY (`studio_location_id`) REFERENCES `studio_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_rooms`
--

LOCK TABLES `studio_rooms` WRITE;
/*!40000 ALTER TABLE `studio_rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('OWNER','ADMIN','CLIENT','PHOTOGRAPHER','EDITOR','MANAGER') NOT NULL DEFAULT 'CLIENT',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `avatar_path` varchar(200) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_active_idx` (`role`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-17 11:29:18
