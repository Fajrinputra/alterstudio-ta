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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (20,11,12,1,1,'2026-05-12','11:00:00',NULL,'PAID','2026-05-09 15:34:36','2026-05-09 15:35:52','DP','[{\"label\":\"Tambah orang\",\"price\":50000,\"unit\":\"\",\"quantity\":1,\"subtotal\":50000},{\"label\":\"Tambah waktu\\/10m\",\"price\":100000,\"unit\":\"\",\"quantity\":2,\"subtotal\":200000}]',250000,3250000,'2026-05-09 08:32:02','2026-05-09 08:47:42'),(21,5,12,1,1,'2026-05-13','12:00:00',NULL,'CANCELLED','2026-05-12 00:25:52',NULL,'DP','[]',0,3000000,'2026-05-11 17:15:18','2026-05-11 17:30:00'),(22,5,12,1,1,'2026-05-13','11:30:00',NULL,'PAID','2026-05-12 00:36:34','2026-05-12 01:04:44','FULL','[]',0,3000000,'2026-05-11 17:35:55','2026-05-11 18:06:20'),(23,5,11,1,1,'2026-06-09','11:30:00',NULL,'PAID','2026-05-12 01:13:38','2026-05-12 01:14:02','DP','[]',0,1500000,'2026-05-11 18:13:15','2026-05-11 18:15:50'),(24,11,12,1,2,'2026-05-12','11:30:00',NULL,'DP_PAID','2026-05-12 01:46:23','2026-05-12 01:46:55','DP','[]',0,3000000,'2026-05-11 18:45:47','2026-05-11 18:47:23'),(25,5,21,1,1,'2026-05-28','11:00:00',NULL,'PAID','2026-05-25 08:25:25','2026-05-25 08:27:55','FULL','[]',0,750000,'2026-05-25 01:22:24','2026-05-25 01:28:35'),(26,5,8,1,2,'2026-05-28','11:00:00',NULL,'PAID','2026-05-25 08:25:45','2026-05-25 08:28:45','FULL','[]',0,60000,'2026-05-25 01:24:16','2026-05-25 01:29:20');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landing_hero_slides`
--

LOCK TABLES `landing_hero_slides` WRITE;
/*!40000 ALTER TABLE `landing_hero_slides` DISABLE KEYS */;
INSERT INTO `landing_hero_slides` VALUES (1,1,'SIGNATURE & CASA DE ALTER','ALTER STUDIO','Studio Fotografi terlengkap sekota padang','landing/hero/o5zMRp3EMVNzv3fn8Dkb4XVPvVjOzupDMmRUH0BV.jpg',1,1,'2026-03-10 02:09:03','2026-03-10 02:09:03'),(2,1,'SIGNATURE ALTER STUDIO','ABADIKAN MOMENT BERSAMA ORANG TERKASIH','First line for original quality with new experience','landing/hero/ShWeL9ZT8FzlX97OMzBzI62Jw5MmWmyflXVQTBMc.jpg',1,1,'2026-03-10 02:13:34','2026-03-10 02:13:34');
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
INSERT INTO `password_reset_tokens` VALUES ('fajrinputrapratama01@gmail.com',11,'$2y$12$47CKSWo/5gzYfdcbKlDNyed6.ClhVzkqKW3whtjZi9rKllglyXwlW','2026-05-09 08:15:15');
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (15,20,'DP',325000,'PAID',NULL,'ORDER-20-8a2a254b-b88d-4ca5-ad0d-ff25945cdd87','319d08f0-1c36-433c-ac67-10c82d62e7d8','settlement','2026-05-09 08:36:56','2026-05-09 08:36:27','2026-05-09 08:36:56'),(16,20,'FULL',2925000,'PAID','manual_onsite_settlement',NULL,NULL,'manual','2026-05-09 08:47:42','2026-05-09 08:47:42','2026-05-09 08:47:42'),(17,22,'FULL',3000000,'PAID',NULL,'ORDER-22-f00e41ae-1999-4b04-8be2-b3cddcc3605e','13264b22-40f9-44d7-95ab-2fb536fa520a','settlement','2026-05-11 18:06:20','2026-05-11 18:05:32','2026-05-11 18:06:20'),(18,23,'DP',150000,'PAID',NULL,'ORDER-23-6f9f8ea2-b64f-4702-a1da-a64eb06a1611','044d2a7d-ebdf-4fb9-b683-b5057592ce29','settlement','2026-05-11 18:14:33','2026-05-11 18:14:06','2026-05-11 18:14:33'),(19,23,'FULL',1350000,'PAID','manual_onsite_settlement',NULL,NULL,'manual','2026-05-11 18:15:50','2026-05-11 18:15:50','2026-05-11 18:15:50'),(20,24,'DP',300000,'PAID',NULL,'ORDER-24-fa4bace2-39ad-49ca-be49-85201370d316','5ee5404f-d9d6-4951-9afc-7471fe3c0003','settlement','2026-05-11 18:47:23','2026-05-11 18:46:59','2026-05-11 18:47:23'),(21,25,'FULL',750000,'PAID',NULL,'ORDER-25-4e46bd99-9e78-4a02-a9d9-0b1084481be3','0d8fc5e3-2bc3-4595-b958-76fffc876834','settlement','2026-05-25 01:28:35','2026-05-25 01:28:08','2026-05-25 01:28:35'),(22,26,'FULL',60000,'PAID',NULL,'ORDER-26-95b720fc-7271-4547-a10a-a23c28df0dde','517295f7-af87-488e-967d-a46869cda155','settlement','2026-05-25 01:29:20','2026-05-25 01:28:50','2026-05-25 01:29:20');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_schedules`
--

LOCK TABLES `project_schedules` WRITE;
/*!40000 ALTER TABLE `project_schedules` DISABLE KEYS */;
INSERT INTO `project_schedules` VALUES (1,19,20,1,1,1,3,14,'2026-05-12 11:00:00','2026-05-12 12:50:00','SCHEDULED','2026-05-25 03:12:18','2026-05-25 03:12:18'),(2,22,23,1,1,1,3,4,'2026-06-09 11:30:00','2026-06-09 12:15:00','SCHEDULED','2026-05-25 03:12:18','2026-05-25 03:12:18'),(3,24,25,1,1,1,3,4,'2026-05-28 11:00:00','2026-05-28 11:45:00','SCHEDULED','2026-05-25 03:12:18','2026-05-25 03:12:18');
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (19,20,'EDITING',1,'2026-05-12 11:00:00','2026-05-12 12:50:00','2026-05-09 08:32:02','2026-05-11 19:09:34','https://drive.google.com/drive/u/0/folders/1v24XXA3vpa3PbW1eWZ3pTSChUOn-lcTJ',3,'2026-05-11 18:55:59','D1.D2.D3.D4.D5.D6.D7.D8.D9.D10','HARUS MANTAP','2026-05-11 19:09:34',NULL,NULL,NULL,NULL),(20,21,'DRAFT',0,NULL,NULL,'2026-05-11 17:15:18','2026-05-11 17:15:18',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(21,22,'DRAFT',0,NULL,NULL,'2026-05-11 17:35:55','2026-05-11 17:35:55',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,23,'FINAL',1,'2026-06-09 11:30:00','2026-06-09 12:15:00','2026-05-11 18:13:15','2026-05-11 19:23:13','https://drive.google.com/drive/folders/1v24XXA3vpa3PbW1eWZ3pTSChUOn-lcTJ?usp=drive_link',3,'2026-05-11 19:19:01','A1.A2.A3.A4.A5.A6.A7.A8.A9.A10','HARUS MANTAP','2026-05-11 19:20:35','https://drive.google.com/drive/folders/1v24XXA3vpa3PbW1eWZ3pTSChUOn-lcTJ?usp=drive_link','DONE YA KAK',4,'2026-05-11 19:23:13'),(23,24,'DRAFT',0,NULL,NULL,'2026-05-11 18:45:47','2026-05-11 18:45:47',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(24,25,'SHOOT_DONE',0,'2026-05-28 11:00:00','2026-05-28 11:45:00','2026-05-25 01:22:24','2026-05-25 01:38:14','https://github.com/Fajrinputra/alterstudio-ta',3,'2026-05-25 01:38:14',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(25,26,'DRAFT',0,NULL,NULL,'2026-05-25 01:24:16','2026-05-25 01:24:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,'Pas Photo & Postcard','Pas Photo & Postcard','2026-02-15 12:45:03','2026-02-15 12:45:03'),(2,'Personal','Personal','2026-02-15 12:45:03','2026-02-15 12:45:03'),(3,'Group','Group','2026-02-15 12:45:03','2026-02-15 12:45:03'),(4,'Family','Family','2026-02-15 12:45:03','2026-02-15 12:45:03'),(5,'Graduation','Graduation','2026-02-15 12:45:03','2026-02-15 12:45:03'),(6,'Lainnya','Lainnya','2026-02-15 12:45:03','2026-02-15 12:45:03'),(9,'Catalog Test','Tes','2026-02-15 15:18:26','2026-02-15 15:18:26'),(10,'Wedding','Wedding photography','2026-04-06 23:19:10','2026-04-06 23:19:10'),(11,'TA Test Layanan','Kategori khusus data dummy pengujian tugas akhir.','2026-05-11 17:05:19','2026-05-11 17:05:19');
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_packages`
--

LOCK TABLES `service_packages` WRITE;
/*!40000 ALTER TABLE `service_packages` DISABLE KEYS */;
INSERT INTO `service_packages` VALUES (1,1,'Paket Pasphoto',75000,15,NULL,'Close-up (kepala sampai dada)',NULL,NULL,1,'Booking dengan DP; DP non-refundable; 5-6 take foto','[\"1 kostum\",\"5-6 take\",\"Cetak 2x3 (5 lbr)\",\"3x4 (5 lbr)\",\"4x6 (5 lbr)\"]','[{\"label\":\"Ganti kostum Rp25k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp25k\",\"price\":0,\"is_active\":true},{\"label\":\"Edit tambahan Rp25k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(2,1,'Paket Postcard',75000,15,NULL,'Full body',NULL,NULL,1,'Booking dengan DP; DP non-refundable; 5-6 take foto','[\"1 kostum\",\"5-6 take\",\"Cetak 3R (2 lbr)\"]','[{\"label\":\"Ganti kostum Rp25k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp25k\",\"price\":0,\"is_active\":true},{\"label\":\"Edit tambahan Rp25k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(3,2,'Paket Personal',350000,20,NULL,'Fokus individu, 20 menit',NULL,NULL,1,'Berpedoman pada durasi waktu','[\"1 kostum\",\"1 background\",\"3 foto edit\",\"file only\",\"all file\"]','[{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp150k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Edit tambahan Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(4,2,'Biograph I',700000,30,NULL,'30 menit',NULL,NULL,1,'Berpedoman pada durasi waktu','[\"1 kostum\",\"3 background\",\"6 foto edit\",\"file only\",\"all file\"]','[{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp150k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Edit tambahan Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(5,2,'Biograph II',1800000,50,NULL,'50 menit',NULL,NULL,1,'Berpedoman pada durasi waktu','[\"2 kostum\",\"5 background\",\"10 foto edit\",\"Square Magazine 20x20 (10 lbr)\",\"Cetak 20R + Bingkai\",\"all file\"]','[{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp150k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Edit tambahan Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(6,3,'Paket 2-4 Orang',85000,15,NULL,'15 menit',NULL,NULL,1,'Kombinasi foto harus wajar agar sesi teratur','[\"2 kostum & 2 background\",\"Cetak 4R (2 lbr\\/orang) atau 10R (1 lbr\\/orang)\"]','[{\"label\":\"Sesi personal Rp50k\\/orang\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(7,3,'Paket 5-10 Orang',75000,30,NULL,'30 menit',NULL,NULL,1,'Kombinasi foto harus wajar agar sesi teratur','[\"2 kostum & 2 background\",\"Cetak 4R (2 lbr\\/orang) atau 10R (1 lbr\\/orang)\"]','[{\"label\":\"Sesi personal Rp50k\\/orang\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(8,3,'Paket 11-19 Orang',60000,45,NULL,'45 menit',NULL,NULL,1,'Kombinasi foto harus wajar agar sesi teratur','[\"2 kostum & 2 background\",\"Cetak 4R (2 lbr\\/orang) atau 10R (1 lbr\\/orang)\"]','[{\"label\":\"Sesi personal Rp50k\\/orang\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(9,3,'>20 Orang',50000,90,NULL,'90 menit',NULL,NULL,1,'Kombinasi foto harus wajar agar sesi teratur','[\"2 kostum & 2 background\",\"Cetak 4R (2 lbr\\/orang) atau 10R (1 lbr\\/orang)\"]','[{\"label\":\"Sesi personal Rp50k\\/orang\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp150k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah background Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(10,4,'Mini Family',950000,30,8,'30 menit, maks 8 orang',NULL,NULL,1,NULL,'[\"1 kostum\",\"3 background\",\"7 foto edit\",\"Cetak 20R + Bingkai\",\"Cetak 16R + Bingkai\"]','[{\"label\":\"Tambah orang Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(11,4,'Family',1500000,45,10,'45 menit, maks 10 orang',NULL,NULL,1,NULL,'[\"2 kostum\",\"4 background\",\"10 foto edit\",\"Cetak 20R + Bingkai\",\"16R + Bingkai\",\"10R + Bingkai (3)\"]','[{\"label\":\"Tambah orang Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(12,4,'Big Family',3000000,90,20,'90 menit, maks 20 orang',NULL,NULL,1,NULL,'[\"3 kostum\",\"Semua background 1 studio\",\"18 foto edit\",\"Cetak 24R + Bingkai\",\"16R + Bingkai (2)\",\"10R + Bingkai (5)\"]','[{\"label\":\"Tambah orang Rp50k\",\"price\":0,\"is_active\":true},{\"label\":\"Tambah waktu Rp100k\\/10m\",\"price\":0,\"is_active\":true},{\"label\":\"Ganti kostum Rp50k\",\"price\":0,\"is_active\":true}]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(13,5,'Paket I',500000,25,NULL,'25 menit, maks 6 orang',NULL,NULL,1,'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas','[\"2 background\",\"5 edit\",\"file only\"]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(14,5,'Paket II',750000,30,NULL,'30 menit, maks 8 orang',NULL,NULL,1,'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas','[\"2 background\",\"7 edit\",\"Cetak 20R + Bingkai\"]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(15,5,'Paket III',950000,30,NULL,'30 menit, maks 15 orang',NULL,NULL,1,'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas','[\"3 background\",\"9 edit\",\"Cetak 16R + Bingkai\",\"Cetak 10R + Bingkai\"]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(16,5,'Paket IV',1300000,45,NULL,'45 menit, maks 20 orang',NULL,NULL,1,'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas','[\"4 background\",\"12 edit\",\"Cetak 20R\",\"16R\",\"Kolase 4R + Bingkai\"]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(17,5,'Paket V',2700000,60,NULL,'60 menit, maks 25 orang',NULL,NULL,1,'Wisudawan harus ada di setiap frame; termasuk jubah, toga, kebaya/jas','[\"Semua background\",\"20 edit\",\"Cetak 24R\",\"16R (2)\",\"Kolase 4R\"]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:54',NULL),(18,6,'Maternity 45m',900000,45,NULL,'45 menit',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(19,6,'Maternity 60m',1500000,60,NULL,'60 menit',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(20,6,'Baby 1-3 th (tanpa dekor)',500000,30,NULL,'',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(21,6,'Baby 1-3 th (dengan orang tua)',750000,45,NULL,'',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(22,6,'Mini Soulmate',650000,25,NULL,'Couple 25 menit, 1 kostum, 1 background',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(23,6,'Pas Photo Couple',250000,10,NULL,'Sesi formal & non-formal 10 menit',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(24,6,'Catalogue',150000,15,NULL,'Per item, 5 angle, 10-15 menit, 3 edit',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(25,6,'Ijazah Session',150000,30,NULL,'Mulai 150k-250k termasuk makeup & kostum',NULL,NULL,1,NULL,'[]','[]','[]','2026-02-15 12:45:03','2026-05-24 17:45:55',NULL),(28,9,'Tes',1000,60,NULL,'blablablabla',NULL,NULL,1,'tes','[]','[{\"label\":\"10 menit tambahan\",\"price\":100000,\"is_active\":true},{\"label\":\"Properti\",\"price\":50000,\"is_active\":true}]','[]','2026-02-15 15:18:26','2026-05-24 17:45:55',NULL),(29,10,'Standard Package',1500000,60,NULL,'Standard photo session',NULL,NULL,1,NULL,'[\"2 hours shoot\",\"20 edited photos\"]','[{\"label\":\"Extra print\",\"price\":50000,\"is_active\":true}]','[]','2026-04-06 23:19:10','2026-05-24 17:45:55',NULL),(30,11,'TA Test Paket 30 Menit',1000000,30,6,'Paket dummy untuk pengujian pemesanan dan pembayaran DP 10%.',NULL,NULL,1,'Data dummy pengujian.','[\"Durasi 30 menit\",\"5 foto edit\",\"File via Google Drive\"]','[{\"label\":\"Tambah waktu\\/10m\",\"price\":100000,\"unit\":\"10m\",\"is_active\":true},{\"label\":\"Tambah orang\",\"price\":50000,\"unit\":\"orang\",\"is_active\":true}]','[]','2026-05-11 17:05:19','2026-05-24 17:45:55',NULL),(31,11,'TA Test Paket 45 Menit',1500000,45,10,'Paket dummy untuk pengujian slot dan laporan.',NULL,NULL,1,'Data dummy pengujian.','[\"Durasi 45 menit\",\"10 foto edit\",\"File via Google Drive\"]','[{\"label\":\"Tambah waktu\\/10m\",\"price\":100000,\"unit\":\"10m\",\"is_active\":true}]','[]','2026-05-11 17:05:19','2026-05-24 17:45:55',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_locations`
--

LOCK TABLES `studio_locations` WRITE;
/*!40000 ALTER TABLE `studio_locations` DISABLE KEYS */;
INSERT INTO `studio_locations` VALUES (1,'Cabang 1','cabang-1','jalan antah berantah nomor entah berapalah kota padang','First station of alter studio the signature',NULL,NULL,'https://maps.app.goo.gl/uBLRE6cGFdueBToo9','[]',1,'2026-02-23 13:31:59','2026-05-24 17:45:55'),(2,'Cabang2','cabang2','jalan bawah laut nomor 29384 laut padang',NULL,NULL,NULL,'https://maps.app.goo.gl/He34m84QdroWDcot7','[]',1,'2026-02-23 13:32:55','2026-05-24 17:45:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_rooms`
--

LOCK TABLES `studio_rooms` WRITE;
/*!40000 ALTER TABLE `studio_rooms` DISABLE KEYS */;
INSERT INTO `studio_rooms` VALUES (1,1,'Studio A','kapasitas 10 - 15 Orang',NULL,1,'2026-03-12 02:25:40','2026-05-24 17:45:55'),(2,1,'Studio B','Kapasitas 20 Orang',NULL,1,'2026-03-12 02:26:08','2026-05-24 17:45:55'),(3,1,'Studio C','Kapasitas 30 Orang',NULL,1,'2026-03-12 02:26:35','2026-05-24 17:45:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Alter','admin@alter.test','08110000001','2026-05-09 22:03:35','$2y$12$0VhHgaLadkeLbt.XUnh4MOsQL.rRdWE4JsqrVFn.hRbA1rwCjXJR2','ADMIN',1,NULL,'gCZXpRJ4WZUlNHINA5jnKRw04Ud0FxhYLIczHSEwjBuyntGDQ8LElbfYhuM9','2026-02-15 12:45:01','2026-05-24 17:45:54'),(2,'Manager Alter','manager@alter.test','08110000002','2026-05-09 22:03:36','$2y$12$rjZRwjfzvItdRcq3beaTWuJqVRFYS5csWfjVOhBD3YCyjRbgZsamC','MANAGER',1,NULL,'zVYqtQF0iMGUHG8qFDitgkKEgqPPYbnXUX92fHtiJfwWYibiU7D1WDxw5OZK','2026-02-15 12:45:01','2026-05-24 17:45:54'),(3,'Fajar Photographer','photo@alter.test','08110000003','2026-05-09 22:03:36','$2y$12$yV9LllyVki/IkGEtT3Gcp.Caal1v/3PID6uUBkCh.5ZNSJMau4iU6','PHOTOGRAPHER',1,NULL,'rjQ27c1Ip24WxRRCPT7FqnWidPmnY0JVGlidd2jvckCXTbIpKH3I7T7UtUpv','2026-02-15 12:45:02','2026-05-24 17:45:54'),(4,'Rani Editor','editor@alter.test','08110000004','2026-05-09 22:03:37','$2y$12$81.PUC0amndshJ0zjOVrSORPIm7NYIrQYmlnVnVrQA6wRKqiUp0Va','EDITOR',1,NULL,'ZYFK1UfKMCft3Kngp6ZwL3k8SPu6k9QTxig2CqORnUQyv1UrBpFRJVYVS45J','2026-02-15 12:45:02','2026-05-24 17:45:54'),(5,'Client Demo','client@alter.test','08110000005','2026-05-09 22:03:37','$2y$12$TcXef3Jt93ZsdTTMlflXGufqLEqls4dpnJmpjLs3mUJrr2oiRsNnC','CLIENT',1,NULL,'lxcdrrExP049K4SruVE5Vj1hRJn4q1vvZ17p0Fn8xPd5izNN6CW5rPvznUiW','2026-02-15 12:45:03','2026-05-24 17:45:54'),(7,'Akun Dihapus','deleted-user-7-iblfywns@alter.local',NULL,NULL,'$2y$12$mCYJkazs4s7SElEQ0nqqruXupA9d/Svzmb./BuLdWf6q6INyc6FPG','CLIENT',0,NULL,NULL,'2026-03-09 13:17:31','2026-05-24 17:45:54'),(8,'Asril Tanjung','asriltanjung206@gmail.com','082188777662',NULL,'$2y$12$53kQGy30JniMXC/UTEsZoO478h2Cbp7I1qj9TJevFPmv/9WxxSijK','PHOTOGRAPHER',1,NULL,NULL,'2026-03-12 06:03:16','2026-05-24 17:45:54'),(9,'jeje','kknpandaisikek0@gmail.com','082276424',NULL,'$2y$12$JZ.plipaqMGs3zJDf8Uw9ePczMZUPDlLpZu2IA9m4GWQV940vxM9m','EDITOR',1,NULL,NULL,'2026-03-12 06:04:28','2026-05-24 17:45:54'),(10,'Brenden Hirthe','rice.burdette@example.org',NULL,'2026-04-06 23:19:10','$2y$12$1fwwSL8G7QPAxQjSCpd5QOJeKDkWHw6TXL/xt91JAuxLlvmxYXk46','CLIENT',1,NULL,'xm839Vdw4R','2026-04-06 23:19:10','2026-05-24 17:45:54'),(11,'FAJRIN PUTRA PRATAMA','fajrinputrapratama01@gmail.com',NULL,'2026-05-08 10:57:23','$2y$12$xJowQMZpzwA60usJ1yCv9ujgvLJIY3Uq3rsVHtp4/vBtsxwsWpICy','CLIENT',1,NULL,NULL,'2026-05-08 10:56:47','2026-05-24 17:45:54'),(12,'Owner Alter','owner@alter.test','08110000000','2026-05-09 22:03:35','$2y$12$e41kWjrmwD8khBB1oF6ds./8azPzYyzgaj3l3ZJNJbgJevOCSi2NW','OWNER',1,NULL,NULL,'2026-05-09 22:03:35','2026-05-24 17:45:54'),(13,'Raka Photographer','photo2@alter.test','08110000006',NULL,'$2y$12$8polLLZ4K4jX8WvDIiUcoOWCDterCydIhjQfg73cq.u0ASjfknTM2','PHOTOGRAPHER',1,NULL,NULL,'2026-05-11 17:05:18','2026-05-24 17:45:54'),(14,'Dina Editor','editor2@alter.test','08110000007',NULL,'$2y$12$buhMwzEw3oT4ShnrKMFShudBtXouP5EuX3KU.ZDVZYRMVFrN3lM5e','EDITOR',1,NULL,NULL,'2026-05-11 17:05:19','2026-05-24 17:45:54');
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

-- Dump completed on 2026-07-17 11:39:07
