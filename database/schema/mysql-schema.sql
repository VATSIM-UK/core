/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_access_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `accessor_account_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loggable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loggable_id` bigint unsigned NOT NULL,
  `action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_access_logs_loggable_type_loggable_id_index` (`loggable_type`,`loggable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `admin_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_access_logs` ENABLE KEYS */;
DROP TABLE IF EXISTS `airport_navaids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airport_navaids` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `airport_id` int unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heading` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ident` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequency` decimal(6,3) NOT NULL,
  `frequency_band` smallint unsigned NOT NULL,
  `remarks` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `airport_navaids` DISABLE KEYS */;
/*!40000 ALTER TABLE `airport_navaids` ENABLE KEYS */;
DROP TABLE IF EXISTS `airport_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airport_positions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `airport_id` int unsigned NOT NULL,
  `position_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `airport_positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `airport_positions` ENABLE KEYS */;
DROP TABLE IF EXISTS `airport_procedures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airport_procedures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `airport_id` int unsigned NOT NULL,
  `runway_id` int unsigned DEFAULT NULL,
  `type` tinyint unsigned NOT NULL,
  `ident` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_fix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_altitude` int DEFAULT NULL,
  `final_altitude` int DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `airport_procedures` DISABLE KEYS */;
/*!40000 ALTER TABLE `airport_procedures` ENABLE KEYS */;
DROP TABLE IF EXISTS `airport_runways`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airport_runways` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `airport_id` int unsigned NOT NULL,
  `ident` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `heading` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int unsigned NOT NULL,
  `length` int unsigned NOT NULL,
  `surface_type` smallint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `airport_runways` DISABLE KEYS */;
/*!40000 ALTER TABLE `airport_runways` ENABLE KEYS */;
DROP TABLE IF EXISTS `airports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `icao` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iata` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fir_type` tinyint unsigned DEFAULT NULL,
  `major` tinyint(1) NOT NULL DEFAULT '0',
  `latitude` double(12,8) DEFAULT NULL,
  `longitude` double(12,8) DEFAULT NULL,
  `elevation` mediumint DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `departure_procedures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `arrival_procedures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vfr_procedures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `other_information` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `airports_ident_unique` (`icao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `airports` DISABLE KEYS */;
/*!40000 ALTER TABLE `airports` ENABLE KEYS */;
DROP TABLE IF EXISTS `api_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_request` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `api_account_id` int unsigned NOT NULL,
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_full` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_code` int unsigned DEFAULT NULL,
  `response_full` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_request_api_account_id_foreign` (`api_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `api_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_request` ENABLE KEYS */;
DROP TABLE IF EXISTS `community_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_membership` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_membership_group_id_foreign` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `community_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_membership` ENABLE KEYS */;
DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contacts_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,'ATC_TRAINING','ATC Training','atc-team@vatsim.uk'),(2,'PILOT_TRAINING','Pilot Training','pilot-team@vatsim.uk'),(3,'PRIVACC','Privileged Access','privileged-access@vatsim.uk');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
DROP TABLE IF EXISTS `discord_role_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discord_role_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discord_id` bigint NOT NULL,
  `permission_id` int DEFAULT NULL,
  `qualification_id` int unsigned DEFAULT NULL,
  `state_id` int unsigned DEFAULT NULL,
  `cts_may_control_contains` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endorsable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endorsable_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discord_role_rules_endorsable_type_endorsable_id_index` (`endorsable_type`,`endorsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `discord_role_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `discord_role_rules` ENABLE KEYS */;
DROP TABLE IF EXISTS `endorsement_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `endorsement_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `endorsable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `endorsable_id` bigint unsigned NOT NULL,
  `requested_by` int unsigned NOT NULL,
  `actioned_at` timestamp NULL DEFAULT NULL,
  `actioned_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actioned_by` int unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `endorsement_requests_endorsable_type_endorsable_id_index` (`endorsable_type`,`endorsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `endorsement_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `endorsement_requests` ENABLE KEYS */;
DROP TABLE IF EXISTS `jobs_failed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs_failed` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `exception` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jobs_failed_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `jobs_failed` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs_failed` ENABLE KEYS */;
DROP TABLE IF EXISTS `jobs_pending`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs_pending` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_pending_queue_reserved_at_index` (`queue`,`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `jobs_pending` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs_pending` ENABLE KEYS */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `monitored_scheduled_task_log_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitored_scheduled_task_log_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `monitored_scheduled_task_id` bigint unsigned NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_scheduled_task_id` (`monitored_scheduled_task_id`),
  CONSTRAINT `fk_scheduled_task_id` FOREIGN KEY (`monitored_scheduled_task_id`) REFERENCES `monitored_scheduled_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `monitored_scheduled_task_log_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `monitored_scheduled_task_log_items` ENABLE KEYS */;
DROP TABLE IF EXISTS `monitored_scheduled_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitored_scheduled_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cron_expression` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timezone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ping_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_started_at` datetime DEFAULT NULL,
  `last_finished_at` datetime DEFAULT NULL,
  `last_failed_at` datetime DEFAULT NULL,
  `last_skipped_at` datetime DEFAULT NULL,
  `registered_on_oh_dear_at` datetime DEFAULT NULL,
  `last_pinged_at` datetime DEFAULT NULL,
  `grace_time_in_minutes` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `monitored_scheduled_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `monitored_scheduled_tasks` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account` (
  `id` int unsigned NOT NULL,
  `name_first` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_last` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_set_at` timestamp NULL DEFAULT NULL,
  `password_expires_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discord_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discord_access_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `discord_refresh_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vatsim_access_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vatsim_refresh_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vatsim_token_expires` bigint unsigned DEFAULT NULL,
  `gender` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` smallint unsigned NOT NULL DEFAULT '0',
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
--   `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
  `debug` tinyint(1) NOT NULL DEFAULT '0',
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cert_checked_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mship_account_discord_id_unique` (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_ban` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `banned_by` int unsigned DEFAULT NULL,
  `type` smallint unsigned NOT NULL,
  `reason_id` int unsigned DEFAULT NULL,
  `reason_extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` timestamp NULL DEFAULT NULL,
  `period_finish` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `repealed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_ban_account_id_foreign` (`account_id`),
  KEY `mship_account_ban_banned_by_foreign` (`banned_by`),
  KEY `mship_account_ban_reason_id_foreign` (`reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_ban` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_ban` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_email` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int unsigned NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_email_account_id_foreign` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_email` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_endorsement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_endorsement` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `endorsable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `endorsable_id` bigint unsigned NOT NULL,
  `endorsement_request_id` int unsigned DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_endorsement_endorsable_type_endorsable_id_index` (`endorsable_type`,`endorsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_endorsement` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_endorsement` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_note` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `note_type_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `writer_id` int unsigned DEFAULT NULL,
  `attachment_id` int unsigned DEFAULT NULL,
  `attachment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_note_note_type_id_foreign` (`note_type_id`),
  KEY `mship_account_note_account_id_foreign` (`account_id`),
  KEY `mship_account_note_writer_id_foreign` (`writer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_note` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_permission` (
  `permission_id` int unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `mship_account_permission_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_permission` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_qualification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_qualification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `qualification_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_qualification_account_id_foreign` (`account_id`),
  KEY `mship_account_qualification_qualification_id_foreign` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_qualification` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_qualification` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_role` (
  `role_id` int unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `mship_account_role_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_role` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_account_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_account_state` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `state_id` int unsigned NOT NULL,
  `division` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_account_state_account_id_foreign` (`account_id`),
  KEY `mship_account_state_state_id_foreign` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_account_state` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_account_state` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_ban_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_ban_reason` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_amount` smallint unsigned NOT NULL,
  `period_unit` enum('M','H','D') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_ban_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_ban_reason` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_feedback` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `form_id` bigint unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `submitter_account_id` int unsigned NOT NULL,
  `actioned_at` timestamp NULL DEFAULT NULL,
  `actioned_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `actioned_by_id` int unsigned DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sent_by_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_feedback_form_id_foreign` (`form_id`),
  KEY `mship_feedback_account_id_foreign` (`account_id`),
  KEY `mship_feedback_submitter_account_id_foreign` (`submitter_account_id`),
  KEY `mship_feedback_actioned_by_id_foreign` (`actioned_by_id`),
  KEY `mship_feedback_sent_by_id_foreign` (`sent_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_feedback` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_feedback_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_feedback_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `feedback_id` bigint unsigned NOT NULL,
  `question_id` int unsigned NOT NULL,
  `response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `mship_feedback_answers_feedback_id_foreign` (`feedback_id`),
  KEY `mship_feedback_answers_question_id_foreign` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_feedback_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_feedback_answers` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_feedback_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_feedback_forms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_id` int unsigned DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `targeted` tinyint(1) NOT NULL DEFAULT '1',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_feedback_forms_contact_id_foreign` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_feedback_forms` DISABLE KEYS */;
INSERT INTO `mship_feedback_forms` VALUES (1,'ATC Feedback','atc',1,1,1,1,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(2,'Pilot Feedback','pilot',2,1,1,1,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL);
/*!40000 ALTER TABLE `mship_feedback_forms` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_feedback_question_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_feedback_question_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rules` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_uses` int unsigned NOT NULL DEFAULT '0',
  `requires_value` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_feedback_question_types` DISABLE KEYS */;
INSERT INTO `mship_feedback_question_types` VALUES (1,'userlookup','<input class=\"form-control\" name=\"%1$s\" type=\"text\" id=\"%1$s\" value=\"%2$s\" placeholder=\"Enter the user\'s CID e.g 1234567\">','integer|exists:mship_account,id',1,0),(2,'text','<input class=\"form-control\" name=\"%1$s\" type=\"text\" value=\"%2$s\" id=\"%1$s\">',NULL,0,0),(3,'textarea','<textarea class=\"form-control\" name=\"%1$s\" cols=\"50\" rows=\"10\" id=\"%1$s\">%2$s</textarea>',NULL,0,0),(4,'radio','<input name=\"%1$s\" type=\"radio\" style=\"margin-left: 20px;\" value=\"%4$s\" id=\"%1$s\" %5$s> %3$s',NULL,0,1),(5,'datetime','<input class=\"form-control datetimepickercustom\" name=\"%1$s\" type=\"text\" value=\"%2$s\" id=\"%1$s\">','date',0,0);
/*!40000 ALTER TABLE `mship_feedback_question_types` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_feedback_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_feedback_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int unsigned NOT NULL,
  `form_id` bigint unsigned NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `required` tinyint(1) NOT NULL,
  `sequence` int unsigned NOT NULL,
  `permanent` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mship_feedback_questions_type_id_foreign` (`type_id`),
  KEY `mship_feedback_questions_form_id_foreign` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_feedback_questions` DISABLE KEYS */;
INSERT INTO `mship_feedback_questions` VALUES (1,1,1,'usercid','CID of the member you are leaving feedback for.',NULL,1,1,1,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(2,1,2,'usercid','CID of the member you are leaving feedback for.',NULL,1,1,1,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(3,5,1,'datetime2','Date & time of the event.',NULL,1,2,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(4,5,2,'datetime2','Date & time of the event.',NULL,1,2,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(5,2,1,'callsign3','What was their callsign?',NULL,1,3,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(6,2,2,'callsign3','What was their callsign?',NULL,1,3,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(7,4,1,'professional4','The service they provided was professional and well delivered.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,4,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(8,4,2,'professional4','The service they provided was professional and well delivered.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,4,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(9,4,1,'competent5','They were competent.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,5,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(10,4,2,'competent5','They were competent.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,5,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(11,4,1,'helpful6','They were helpful and provided all of the information required.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,6,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(12,4,2,'helpful6','They were helpful and provided all of the information required.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,6,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(13,4,1,'enjoyed7','I enjoyed controlling alongside them.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,7,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(14,4,2,'enjoyed7','I enjoyed flying alongside them.','{\"values\":[\"Strongly disagree\",\"Disagree\",\"Neither Agree nor Disagree\",\"Agree\",\"Strongly Agree\"]}',1,7,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(15,4,1,'overallopinion8','Overall Opinion','{\"values\":[\"Terrible\",\" Poor\",\" Neither Poor nor Good\",\"Good\",\"Excellent\"]}',1,8,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(16,4,2,'overallopinion8','Overall Opinion','{\"values\":[\"Terrible\",\" Poor\",\" Neither Poor nor Good\",\"Good\",\"Excellent\"]}',1,8,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(17,3,1,'report9','Do you have any further comments? Please make comments detailed, specific and avoid identifying yourself',NULL,1,9,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL),(18,3,2,'report9','Do you have any further comments? Please make comments detailed, specific and avoid identifying yourself',NULL,1,9,0,'2025-03-11 20:43:31','2025-03-11 20:43:31',NULL);
/*!40000 ALTER TABLE `mship_feedback_questions` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_note_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_note_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `colour_code` enum('default','info','success','danger','warning') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_note_type` DISABLE KEYS */;
INSERT INTO `mship_note_type` VALUES (1,'System Generated','default',0,1,1,'default','2025-03-11 20:43:30','2025-03-11 20:43:30',NULL),(2,'General','',1,0,0,'info','2025-03-11 20:43:30','2025-03-11 20:43:30',NULL),(3,'Discipline','discipline',1,1,0,'danger','2025-03-11 20:43:30','2025-03-11 20:43:30',NULL),(4,'Visiting &amp; Transfer','visittransfer',1,1,0,'info','2025-03-11 20:43:31','2025-03-11 20:43:31',NULL);
/*!40000 ALTER TABLE `mship_note_type` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_oauth_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_oauth_emails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_email_id` bigint unsigned DEFAULT NULL,
  `sso_account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_oauth_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `mship_oauth_emails` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_permission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_permission` DISABLE KEYS */;
INSERT INTO `mship_permission` VALUES (2,'adm/dashboard','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(3,'adm/search','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(4,'adm/mship','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(6,'adm/mship/account/*','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(7,'adm/mship/account/list','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(8,'adm/mship/account/datachanges','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(9,'adm/mship/account/datachanges/view','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(10,'adm/mship/account/*/flag/view','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(11,'adm/mship/account/*/flags','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(12,'adm/mship/account/*/roles','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(13,'adm/mship/account/*/roles/attach','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(14,'adm/mship/account/*/roles/*/detach','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(15,'adm/mship/account/*/note/create','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(16,'adm/mship/account/*/note/view','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(17,'adm/mship/account/*/note/filter','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(18,'adm/mship/account/*/notes','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(19,'adm/mship/account/*/receivedEmails','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(20,'adm/mship/account/*/security','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(21,'adm/mship/account/*/security/change','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(22,'adm/mship/account/*/security/enable','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(23,'adm/mship/account/*/security/reset','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(24,'adm/mship/account/*/security/view','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(25,'adm/mship/account/*/sentEmails','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(26,'adm/mship/account/*/timeline','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(27,'adm/mship/account/email/view','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(28,'adm/mship/account/*/impersonate','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(29,'adm/mship/permission','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(30,'adm/mship/permission/create','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(31,'adm/mship/permission/list','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(32,'adm/mship/permission/*/update','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(33,'adm/mship/permission/*/delete','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(34,'adm/mship/permission/*/delete','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(35,'adm/mship/permission/attach','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(36,'adm/mship/role','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(37,'adm/mship/role/*/delete','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(38,'adm/mship/role/*/update','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(39,'adm/mship/role/create','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(40,'adm/mship/role/list','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(41,'adm/sys/timeline/mship','web','2015-02-27 22:23:51','2015-02-27 22:23:51'),(42,'adm/mship/role/default','web','2015-02-27 23:12:57','2015-02-27 23:12:57'),(43,'adm','web','2015-02-27 23:20:05','2015-02-27 23:20:05'),(44,'adm/mship/account/own','web','2015-03-03 00:13:22','2015-03-03 00:13:22'),(45,'teamspeak/servergroup/serveradmin','web','2015-03-12 21:37:27','2015-03-12 21:37:27'),(46,'teamspeak/idle/extended','web','2015-03-12 21:37:27','2015-03-12 21:37:27'),(47,'teamspeak/idle/permanent','web','2015-03-12 21:37:27','2015-03-12 21:37:27'),(48,'adm/mship/account/*/bans','web','2015-12-17 23:34:51','2018-10-08 17:15:41'),(49,'adm/mship/account/*/ban/add','web','2015-12-17 23:34:51','2015-12-17 23:34:51'),(50,'adm/mship/ban/*/modify','web','2015-12-17 23:34:51','2016-01-14 00:28:13'),(51,'adm/mship/account/*/ban/view','web','2015-12-17 23:34:51','2015-12-17 23:34:51'),(52,'adm/mship/ban/*/repeal','web','2015-12-17 23:34:51','2016-01-14 00:28:13'),(53,'teamspeak/servergroup/divisionstaff','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(54,'teamspeak/servergroup/webstaff','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(55,'teamspeak/servergroup/rtsm','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(56,'teamspeak/servergroup/leadmentor','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(57,'teamspeak/servergroup/atcstaff','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(58,'teamspeak/servergroup/ptdstaff','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(59,'teamspeak/servergroup/member','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(60,'teamspeak/channel/essex','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(61,'teamspeak/channel/heathrow','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(62,'teamspeak/channel/egtt','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(63,'teamspeak/channel/northern','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(64,'teamspeak/channel/scottish','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(65,'teamspeak/channel/serts','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(66,'teamspeak/channel/swrts','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(67,'teamspeak/channel/military','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(68,'teamspeak/channel/pilot','web','2016-07-31 18:04:36','2016-07-31 18:04:36'),(70,'adm/system/module','web','2016-07-31 18:04:37','2016-07-31 18:04:37'),(71,'adm/system/module/*/enable','web','2016-07-31 18:04:37','2016-07-31 18:04:37'),(72,'adm/system/module/*/disable','web','2016-07-31 18:04:37','2016-07-31 18:04:37'),(73,'adm/mship/account/all','web','2016-08-01 15:24:25','2016-08-01 15:24:25'),(74,'adm/mship/account/active','web','2016-08-01 15:24:25','2016-08-01 15:24:25'),(75,'adm/mship/account/division','web','2016-08-01 15:24:25','2016-08-01 15:24:25'),(76,'adm/mship/account/nondivision','web','2016-08-01 15:24:25','2016-08-01 15:24:25'),(77,'adm/visit-transfer','web','2016-08-22 21:51:20','2017-01-02 16:50:08'),(78,'adm/visit-transfer/dashboard','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(79,'adm/visit-transfer/facility','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(80,'adm/visit-transfer/facility/create','web','2016-08-22 21:51:20','2016-09-02 11:18:02'),(81,'adm/visit-transfer/facility/*/update','web','2016-08-22 21:51:20','2016-09-02 12:24:10'),(82,'adm/visit-transfer/application','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(83,'adm/visit-transfer/application/open','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(84,'adm/visit-transfer/application/closed','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(85,'adm/visit-transfer/application/review','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(86,'adm/visit-transfer/application/accepted','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(87,'adm/visit-transfer/application/completed','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(88,'adm/visit-transfer/application/*','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(89,'adm/visit-transfer/application/*/accept','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(90,'adm/visit-transfer/application/*/reject','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(91,'adm/visit-transfer/reference','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(92,'adm/visit-transfer/reference/pending-submission','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(93,'adm/visit-transfer/reference/submitted','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(94,'adm/visit-transfer/reference/under-review','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(95,'adm/visit-transfer/reference/accepted','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(96,'adm/visit-transfer/reference/rejected','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(97,'adm/visit-transfer/reference/*','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(98,'adm/visit-transfer/reference/*/accept','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(99,'adm/visit-transfer/reference/*/reject','web','2016-08-22 21:51:20','2016-08-22 21:51:20'),(100,'adm/visit-transfer/facility/*/update','web','2016-09-02 11:18:54','2016-09-02 18:34:31'),(101,'adm/visit-transfer/application/*/check/met','web','2016-09-02 17:42:06','2016-09-02 18:34:21'),(102,'adm/visit-transfer/application/*/check/not-met','web','2016-09-16 21:25:24','2016-09-16 21:25:24'),(103,'adm/mship/account/*/bans/*','web','2016-09-19 20:41:31','2016-09-19 20:44:39'),(104,'adm/visit-trasnfer/facility/*/update','web','2016-12-10 14:54:13','2016-12-10 14:54:13'),(105,'adm/mship/account/*/datachanges','web','2016-12-13 19:28:05','2016-12-13 19:28:05'),(106,'adm/mship/account/*/datachanges/view','web','2016-12-13 19:29:17','2016-12-13 19:29:17'),(108,'teamspeak/servergroup/divisioninstructor','web','2017-02-12 21:18:22','2017-02-12 21:18:22'),(110,'adm/mship/ban/*/comment','web','2017-02-26 15:21:16','2017-02-26 15:22:54'),(111,'teamspeak/servergroup/globalmoderator','web','2017-03-08 18:49:20','2017-03-08 18:49:20'),(112,'teamspeak/servergroup/bogecfounder','web','2017-06-11 17:24:18','2017-06-11 17:24:18'),(123,'teamspeak/servergroup/marketingstaff','web','2017-07-31 21:24:03','2018-04-05 16:41:50'),(128,'teamspeak/servergroup/communitymanager','web','2017-12-01 20:50:50','2017-12-01 20:50:50'),(130,'adm/visit-transfer/application/*/complete','web','2018-01-12 20:39:06','2018-01-12 20:39:42'),(135,'adm/mship/bans','web','2018-02-26 23:59:53','2018-02-26 23:59:53'),(136,'adm/smartcars','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(137,'adm/smartcars/aircraft','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(138,'adm/smartcars/aircraft/create','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(139,'adm/smartcars/aircraft/update','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(140,'adm/smartcars/aircraft/delete','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(141,'adm/smartcars/airports','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(142,'adm/smartcars/airports/create','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(143,'adm/smartcars/airports/update','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(144,'adm/smartcars/airports/delete','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(145,'adm/smartcars/exercises','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(146,'adm/smartcars/exercises/create','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(147,'adm/smartcars/exercises/update','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(148,'adm/smartcars/exercises/delete','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(149,'adm/smartcars/flights','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(150,'adm/smartcars/flights/override','web','2018-02-28 21:05:11','2018-02-28 21:05:11'),(154,'teamspeak/servergroup/tgncmanager','web','2018-04-05 16:48:56','2018-04-05 16:48:56'),(160,'adm/atc','web','2018-06-05 20:51:54','2018-06-05 20:51:54'),(161,'adm/atc/endorsement','web','2018-06-05 20:51:54','2018-06-05 20:51:54'),(163,'adm/ops','web','2018-08-01 19:40:35','2018-08-01 19:40:35'),(164,'adm/ops/qstats','web','2018-08-01 19:40:35','2018-08-01 19:40:35'),(165,'adm/visit-transfer/hours/*','web','2018-08-01 19:46:35','2018-08-01 19:48:17'),(166,'adm/visit-transfer/hours','web','2018-08-01 19:48:12','2018-08-01 19:48:12'),(167,'teamspeak/servergroup/atcmentor','web','2018-08-05 14:20:16','2018-08-05 14:20:16'),(168,'teamspeak/servergroup/ptdmentor','web','2018-08-05 14:32:57','2018-08-05 14:32:57'),(171,'adm/visit-transfer/application/review','web','2018-08-16 19:49:31','2018-08-16 19:49:31'),(172,'adm/visit-transfer/application/open','web','2018-08-16 19:51:03','2018-08-16 19:51:03'),(173,'adm/mship/ban','web','2018-10-08 17:22:36','2018-10-08 17:22:36'),(175,'nova','web',NULL,NULL),(176,'waitingLists/create','web',NULL,NULL),(177,'waitingLists/atc/view','web',NULL,NULL),(181,'waitingLists/atc/update','web',NULL,NULL),(183,'waitingLists/pilot/view','web',NULL,NULL),(187,'waitingLists/pilot/update','web',NULL,NULL),(189,'waitingLists/addAccounts','web',NULL,NULL),(190,'waitingLists/addFlags','web',NULL,NULL),(191,'feedback','web',NULL,NULL),(192,'feedback/submitter','web',NULL,NULL),(193,'feedback/action','web',NULL,NULL),(194,'feedback/own','web',NULL,NULL),(195,'feedback/view/atc','web',NULL,NULL),(196,'feedback/view/pilot','web',NULL,NULL),(197,'feedback/view/group','web',NULL,NULL),(198,'feedback/view/atcmentor','web',NULL,NULL),(199,'feedback/view/eve','web',NULL,NULL),(200,'feedback/view/live','web',NULL,NULL),(201,'waitingLists/addAccountsAdmin','web',NULL,NULL),(202,'telescope','web',NULL,NULL),(203,'horizon','web',NULL,NULL),(204,'discord/member','web',NULL,NULL),(205,'discord/dsg','web',NULL,NULL),(206,'discord/web','web',NULL,NULL),(207,'discord/moderator','web',NULL,NULL),(208,'discord/memberservices','web',NULL,NULL),(209,'discord/marketing','web',NULL,NULL),(210,'discord/trainingmanager','web',NULL,NULL),(211,'discord/atc/divisioninstructor','web',NULL,NULL),(212,'discord/atc/appinstructor','web',NULL,NULL),(213,'discord/atc/twrinstructor','web',NULL,NULL),(214,'discord/atc/ncinstructor','web',NULL,NULL),(215,'discord/atc/examiner','web',NULL,NULL),(216,'discord/atc/mentor/s1','web',NULL,NULL),(217,'discord/atc/mentor/s2','web',NULL,NULL),(218,'discord/atc/mentor/s3','web',NULL,NULL),(219,'discord/atc/mentor/c1','web',NULL,NULL),(220,'discord/atc/mentor/heathrow','web',NULL,NULL),(221,'discord/pilot/examiner','web',NULL,NULL),(222,'discord/pilot/instructor','web',NULL,NULL),(223,'discord/pilot/mentor','web',NULL,NULL),(224,'discord/graphics','web',NULL,NULL),(225,'discord/rostering','web',NULL,NULL),(226,'discord/livestreaming','web',NULL,NULL),(227,'discord/gatwick-students','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(228,'discord/atc/student/s2','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(229,'discord/atc/student/s3','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(230,'discord/atc/student/c1','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(231,'discord/pilot/student/tfp','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(232,'discord/pilot/mentor/p1','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(233,'discord/pilot/mentor/p2','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(234,'discord/heathrow-endorsed-ground','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(235,'discord/heathrow-endorsed-tower','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(236,'discord/heathrow-endorsed-approach','web','2025-03-11 20:43:33','2025-03-11 20:43:33'),(237,'admin/beta','web','2025-03-11 20:43:33','2025-03-11 20:43:33');
/*!40000 ALTER TABLE `mship_permission` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_qualification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_qualification` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('pilot','pilot_military','atc','training_atc','training_pilot','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_small` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_long` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_grp` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vatsim` smallint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mship_qualification_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_qualification` DISABLE KEYS */;
INSERT INTO `mship_qualification` VALUES (1,'OBS','atc','OBS','Observer','Observer',1),(2,'S1','atc','STU','Student 1','Ground Controller',2),(3,'S2','atc','STU2','Student 2','Tower Controller',3),(4,'S3','atc','STU+','Student 3','Approach Controller',4),(5,'C1','atc','CTR','Controller 1','Area Controller',5),(6,'C2','atc','CTR+','Senior Controller','Senior Controller',6),(7,'C3','atc','CTR+','Senior Controller','Senior Controller',7),(8,'I1','training_atc','INS','Instructor','Instructor',8),(9,'I2','training_atc','INS+','Senior Instructor','Senior Instructor',9),(10,'I3','training_atc','INS+','Senior Instructor','Senior Instructor',10),(11,'SUP','admin','SUP','Supervisor','Network Supervisor',11),(12,'ADM','admin','ADM','Administrator','Network Administrator',12),(13,'PPL','pilot','PPL','Private Pilot License','Online Pilot',1),(14,'IR','pilot','IR','Instrument Rating','Flight Fundamentals',3),(15,'CMEL','pilot','CMEL','Commercial Multi-Engine License','VFR Pilot',7),(16,'ATPL','pilot','ATPL','Airline Transport Pilot License','IFR Pilot',15),(17,'P6','pilot','P6','P6','P6',32),(18,'P7','pilot','P7','P7','P7',64),(19,'P8','pilot','P8','P8','P8',128),(20,'P9','pilot','P9','P9','Pilot Flight Instructor',256),(21,'P5','pilot','P5','P5','P5',16),(22,'P0','pilot','P0','No Pilot Rating','P0',0),(23,'FI','pilot','FI','Flight Instructor','Flight Instructor',31),(24,'FE','pilot','FE','Flight Examiner','Flight Examiner',63),(25,'M0','pilot_military','M0','M0','No military pilot rating',0),(26,'M1','pilot_military','M1','M1','Military Pilot License',1),(27,'M2','pilot_military','M2','M2','Military Instrument Rating',3),(28,'M3','pilot_military','M3','M3','Military Multi-Engine Rating',7),(29,'M4','pilot_military','M4','M4','Military Mission Ready Pilot',15);
/*!40000 ALTER TABLE `mship_qualification` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `session_timeout` int unsigned DEFAULT NULL,
  `password_mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `password_lifetime` int unsigned NOT NULL DEFAULT '180',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_role` DISABLE KEYS */;
INSERT INTO `mship_role` VALUES (1,'member','web',1,NULL,0,180,'2025-03-11 20:43:32','2025-03-11 20:43:32'),(2,'privacc','web',0,NULL,0,180,'2025-03-11 20:43:32','2025-03-11 20:43:32'),(3,'Gatwick GND Students','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(4,'ATC Students (TWR)','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(5,'ATC Students (APP)','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(6,'ATC Students (ENR)','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(7,'TFP Student','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(8,'P1 Mentor','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(9,'P2 Mentor','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(10,'Heathrow Endorsed Ground','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(11,'Heathrow Endorsed Tower','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33'),(12,'Heathrow Endorsed Approach','web',0,NULL,0,180,'2025-03-11 20:43:33','2025-03-11 20:43:33');
/*!40000 ALTER TABLE `mship_role` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_role_permission` (
  `permission_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `mship_role_permission_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_role_permission` DISABLE KEYS */;
INSERT INTO `mship_role_permission` VALUES (204,1),(228,4),(229,5),(230,6),(231,7),(232,8),(233,9),(234,10),(235,11),(236,12);
/*!40000 ALTER TABLE `mship_role_permission` ENABLE KEYS */;
DROP TABLE IF EXISTS `mship_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mship_state` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('perm','temp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `division` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delete_all_temps` tinyint(1) NOT NULL DEFAULT '0',
  `priority` smallint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mship_state_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mship_state` DISABLE KEYS */;
INSERT INTO `mship_state` VALUES (1,'TRANSFERRING','temp','Transferring','[]','[]',0,20,NULL,NULL),(2,'VISITING','temp','Visiting','[]','[]',0,30,NULL,NULL),(3,'DIVISION','perm','Division','[\"GBR\"]','[\"EUR\"]',1,0,NULL,NULL),(4,'REGION','perm','Region','[\"*\"]','[\"EUR\"]',0,40,NULL,NULL),(5,'INTERNATIONAL','perm','International','[\"*\"]','[\"*\"]',0,70,NULL,NULL),(6,'UNKNOWN','perm','Unknown','[\"*\"]','[\"*\"]',0,99,NULL,NULL);
/*!40000 ALTER TABLE `mship_state` ENABLE KEYS */;
DROP TABLE IF EXISTS `networkdata_atc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `networkdata_atc` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `callsign` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequency` double(6,3) unsigned DEFAULT NULL,
  `qualification_id` int unsigned NOT NULL,
  `facility_type` tinyint unsigned DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `minutes_online` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `networkdata_atc_qualification_id_foreign` (`qualification_id`),
  KEY `networkdata_atc_callsign_index` (`callsign`),
  KEY `networkdata_atc_connected_at_index` (`connected_at`),
  KEY `networkdata_atc_disconnected_at_index` (`disconnected_at`),
  KEY `networkdata_atc_by_date_callsign` (`account_id`,`disconnected_at`,`callsign`),
  KEY `networkdata_atc_by_callsign_date` (`account_id`,`callsign`,`disconnected_at`),
  KEY `networkdata_endorsement_check_query` (`account_id`,`callsign`,`connected_at`,`qualification_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `networkdata_atc` DISABLE KEYS */;
/*!40000 ALTER TABLE `networkdata_atc` ENABLE KEYS */;
DROP TABLE IF EXISTS `networkdata_pilots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `networkdata_pilots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `callsign` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `flight_type` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `departure_airport` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `arrival_airport` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternative_airport` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aircraft` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cruise_altitude` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cruise_tas` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_latitude` double(12,8) DEFAULT NULL,
  `current_longitude` double(12,8) DEFAULT NULL,
  `current_altitude` mediumint DEFAULT NULL,
  `current_groundspeed` int unsigned DEFAULT NULL,
  `current_heading` smallint unsigned DEFAULT NULL,
  `departed_at` timestamp NULL DEFAULT NULL,
  `arrived_at` timestamp NULL DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `minutes_online` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `networkdata_pilots_account_id_foreign` (`account_id`),
  KEY `networkdata_pilots_departure_airport_arrival_airport_index` (`departure_airport`,`arrival_airport`),
  KEY `networkdata_pilots_departure_airport_index` (`departure_airport`),
  KEY `networkdata_pilots_arrival_airport_index` (`arrival_airport`),
  KEY `networkdata_pilots_connected_at_index` (`connected_at`),
  KEY `networkdata_pilots_disconnected_at_index` (`disconnected_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `networkdata_pilots` DISABLE KEYS */;
/*!40000 ALTER TABLE `networkdata_pilots` ENABLE KEYS */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned NOT NULL,
  `client_id` int unsigned NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_codes` ENABLE KEYS */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `oauth_personal_access_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_personal_access_clients` ENABLE KEYS */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`,`token`),
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
DROP TABLE IF EXISTS `position_group_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `position_group_conditions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `position_group_id` int NOT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `positions` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `required_hours` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int NOT NULL,
  `within_months` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `required_qualification` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `position_group_conditions` DISABLE KEYS */;
INSERT INTO `position_group_conditions` VALUES (1,1,NULL,'[\"EGCC_%\",\"EGPH_%\",\"EGSS_%\",\"EGGP_%\"]','10',1,3,'2025-03-11 20:43:32','2025-03-11 20:43:32',NULL),(2,1,NULL,'[\"EGPF_%\",\"EGBB_%\",\"EGGD_%\",\"EGGW_%\"]','10',1,3,'2025-03-11 20:43:32','2025-03-11 20:43:32',NULL),(3,1,NULL,'[\"EGJJ_%\",\"EGAA_%\",\"EGNT_%\",\"EGNX_%\"]','5',1,3,'2025-03-11 20:43:32','2025-03-11 20:43:32',NULL);
/*!40000 ALTER TABLE `position_group_conditions` ENABLE KEYS */;
DROP TABLE IF EXISTS `position_group_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `position_group_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `position_group_id` int unsigned NOT NULL,
  `position_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `position_group_positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `position_group_positions` ENABLE KEYS */;
DROP TABLE IF EXISTS `position_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `position_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maximum_atc_qualification_id` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `position_groups` DISABLE KEYS */;
INSERT INTO `position_groups` VALUES (1,'Gatwick S1 (DEL/GND)',NULL,NULL,'2025-03-11 20:43:32','2025-03-11 20:43:32',NULL);
/*!40000 ALTER TABLE `position_groups` ENABLE KEYS */;
DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `callsign` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequency` decimal(6,3) NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `sub_station` tinyint(1) NOT NULL DEFAULT '0',
  `temporarily_endorsable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
DROP TABLE IF EXISTS `roster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roster` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roster_account_id_unique` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `roster` DISABLE KEYS */;
/*!40000 ALTER TABLE `roster` ENABLE KEYS */;
DROP TABLE IF EXISTS `roster_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roster_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `original_created_at` timestamp NOT NULL,
  `original_updated_at` timestamp NOT NULL,
  `removed_by` int unsigned DEFAULT NULL,
  `roster_update_id` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `roster_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `roster_history` ENABLE KEYS */;
DROP TABLE IF EXISTS `roster_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roster_updates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period_start` datetime NOT NULL,
  `period_end` datetime NOT NULL,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `roster_updates` DISABLE KEYS */;
/*!40000 ALTER TABLE `roster_updates` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_aircraft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_aircraft` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `icao` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `range_nm` int NOT NULL,
  `weight_kg` int NOT NULL,
  `cruise_altitude` int NOT NULL,
  `max_passengers` int NOT NULL,
  `max_cargo_kg` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_aircraft` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_aircraft` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_airport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_airport` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `icao` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` double(12,8) NOT NULL,
  `longitude` double(12,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `smartcars_airport_icao_unique` (`icao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_airport` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_airport` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_bid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_bid` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `flight_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_bid_flight_id_foreign` (`flight_id`),
  KEY `smartcars_bid_account_id_foreign` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_bid` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_bid` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_flight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_flight` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `flightnum` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `departure_id` int unsigned NOT NULL,
  `arrival_id` int unsigned NOT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aircraft_id` int unsigned NOT NULL,
  `cruise_altitude` int NOT NULL DEFAULT '0',
  `distance` double(8,2) NOT NULL DEFAULT '0.00',
  `flight_time` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_flight_departure_id_foreign` (`departure_id`),
  KEY `smartcars_flight_arrival_id_foreign` (`arrival_id`),
  KEY `smartcars_flight_aircraft_id_foreign` (`aircraft_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_flight` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_flight` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_flight_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_flight_criteria` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `flight_id` int unsigned NOT NULL,
  `order` int unsigned NOT NULL,
  `p1_latitude` double(12,8) DEFAULT NULL,
  `p1_longitude` double(12,8) DEFAULT NULL,
  `p2_latitude` double(12,8) DEFAULT NULL,
  `p2_longitude` double(12,8) DEFAULT NULL,
  `p3_latitude` double(12,8) DEFAULT NULL,
  `p3_longitude` double(12,8) DEFAULT NULL,
  `p4_latitude` double(12,8) DEFAULT NULL,
  `p4_longitude` double(12,8) DEFAULT NULL,
  `min_altitude` int DEFAULT NULL,
  `max_altitude` int DEFAULT NULL,
  `min_groundspeed` smallint DEFAULT NULL,
  `max_groundspeed` smallint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_flight_criteria_flight_id_foreign` (`flight_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_flight_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_flight_criteria` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_flight_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_flight_resources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `flight_id` int unsigned NOT NULL,
  `type` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_flight_resources` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_flight_resources` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_pirep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_pirep` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` int unsigned NOT NULL,
  `aircraft_id` int unsigned NOT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `flight_time` time NOT NULL,
  `landing_rate` int NOT NULL,
  `comments` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fuel_used` double(8,2) NOT NULL,
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '2',
  `passed` tinyint(1) DEFAULT NULL,
  `pass_reason` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failed_at` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_pirep_bid_id_foreign` (`bid_id`),
  KEY `smartcars_pirep_aircraft_id_foreign` (`aircraft_id`),
  KEY `smartcars_pirep_failed_at_foreign` (`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_pirep` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_pirep` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_posrep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_posrep` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` int unsigned NOT NULL,
  `aircraft_id` int unsigned NOT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `altitude` int NOT NULL,
  `heading_mag` smallint NOT NULL,
  `heading_true` smallint NOT NULL,
  `latitude` double(12,8) NOT NULL,
  `longitude` double(12,8) NOT NULL,
  `groundspeed` smallint NOT NULL,
  `distance_remaining` int NOT NULL,
  `phase` smallint NOT NULL,
  `time_departure` time DEFAULT NULL,
  `time_remaining` time DEFAULT NULL,
  `time_arrival` time DEFAULT NULL,
  `network` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_posrep_bid_id_foreign` (`bid_id`),
  KEY `smartcars_posrep_aircraft_id_foreign` (`aircraft_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_posrep` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_posrep` ENABLE KEYS */;
DROP TABLE IF EXISTS `smartcars_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smartcars_session` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smartcars_session_account_id_foreign` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `smartcars_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `smartcars_session` ENABLE KEYS */;
DROP TABLE IF EXISTS `staff_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `staff_services` DISABLE KEYS */;
INSERT INTO `staff_services` VALUES (1,'Community (Forum)','2025-03-11 20:43:30','2025-03-11 20:43:30'),(2,'Core','2025-03-11 20:43:30','2025-03-11 20:43:30'),(3,'Events','2025-03-11 20:43:30','2025-03-11 20:43:30'),(4,'Helpdesk','2025-03-11 20:43:30','2025-03-11 20:43:30'),(5,'Moodle','2025-03-11 20:43:30','2025-03-11 20:43:30'),(6,'RTS System','2025-03-11 20:43:30','2025-03-11 20:43:30'),(7,'Servers','2025-03-11 20:43:30','2025-03-11 20:43:30'),(8,'Status','2025-03-11 20:43:30','2025-03-11 20:43:30'),(9,'Website','2025-03-11 20:43:30','2025-03-11 20:43:30');
/*!40000 ALTER TABLE `staff_services` ENABLE KEYS */;
DROP TABLE IF EXISTS `sys_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_activity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` int unsigned DEFAULT NULL,
  `subject_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_activity_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `sys_activity_actor_id_foreign` (`actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sys_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_activity` ENABLE KEYS */;
DROP TABLE IF EXISTS `sys_data_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_data_change` (
  `data_change_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `data_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_old` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_new` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`data_change_id`),
  KEY `sys_data_change_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sys_data_change` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_data_change` ENABLE KEYS */;
DROP TABLE IF EXISTS `sys_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_notification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` smallint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `effective_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sys_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_notification` ENABLE KEYS */;
DROP TABLE IF EXISTS `sys_notification_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_notification_read` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sys_notification_read_notification_id_account_id_unique` (`notification_id`,`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sys_notification_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_notification_read` ENABLE KEYS */;
DROP TABLE IF EXISTS `sys_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_token` (
  `token_id` int unsigned NOT NULL AUTO_INCREMENT,
  `related_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_id` bigint unsigned NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `sys_token_code_unique` (`code`),
  KEY `sys_token_related_type_related_id_index` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sys_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_token` ENABLE KEYS */;
DROP TABLE IF EXISTS `teamspeak_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teamspeak_channel` (
  `id` smallint unsigned NOT NULL,
  `parent_id` smallint unsigned DEFAULT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `protected` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teamspeak_channel_parent_id_foreign` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `teamspeak_channel` DISABLE KEYS */;
/*!40000 ALTER TABLE `teamspeak_channel` ENABLE KEYS */;
DROP TABLE IF EXISTS `teamspeak_channel_group_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teamspeak_channel_group_permission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` smallint unsigned NOT NULL,
  `channelgroup_id` smallint unsigned NOT NULL,
  `permission_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teamspeak_channel_group_permission_channel_id_unique` (`channel_id`),
  KEY `teamspeak_channel_group_permission_channelgroup_id_foreign` (`channelgroup_id`),
  KEY `teamspeak_channel_group_permission_permission_id_foreign` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `teamspeak_channel_group_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `teamspeak_channel_group_permission` ENABLE KEYS */;
DROP TABLE IF EXISTS `teamspeak_confirmation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teamspeak_confirmation` (
  `registration_id` int unsigned NOT NULL,
  `privilege_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `teamspeak_confirmation` DISABLE KEYS */;
/*!40000 ALTER TABLE `teamspeak_confirmation` ENABLE KEYS */;
DROP TABLE IF EXISTS `teamspeak_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teamspeak_group` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `dbid` smallint unsigned NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 's',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `permission_id` int unsigned DEFAULT NULL,
  `qualification_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teamspeak_group_permission_id_foreign` (`permission_id`),
  KEY `teamspeak_group_qualification_id_foreign` (`qualification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `teamspeak_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `teamspeak_group` ENABLE KEYS */;
DROP TABLE IF EXISTS `teamspeak_registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teamspeak_registration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `registration_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_os` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dbid` smallint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teamspeak_registration_account_id_index` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `teamspeak_registration` DISABLE KEYS */;
/*!40000 ALTER TABLE `teamspeak_registration` ENABLE KEYS */;
DROP TABLE IF EXISTS `telescope_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries` (
  `sequence` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_hash` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`),
  UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  KEY `telescope_entries_batch_id_index` (`batch_id`),
  KEY `telescope_entries_family_hash_index` (`family_hash`),
  KEY `telescope_entries_created_at_index` (`created_at`),
  KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `telescope_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `telescope_entries` ENABLE KEYS */;
DROP TABLE IF EXISTS `telescope_entries_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`entry_uuid`),
  KEY `telescope_entries_tags_entry_uuid_tag_index` (`entry_uuid`,`tag`),
  KEY `telescope_entries_tags_tag_index` (`tag`),
  CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `telescope_entries_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `telescope_entries_tags` ENABLE KEYS */;
DROP TABLE IF EXISTS `telescope_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_monitoring` (
  `tag` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `telescope_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `telescope_monitoring` ENABLE KEYS */;
DROP TABLE IF EXISTS `training_waiting_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_waiting_list` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `home_members_only` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `cts_theory_exam_level` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_toggles` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `training_waiting_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_waiting_list` ENABLE KEYS */;
DROP TABLE IF EXISTS `training_waiting_list_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_waiting_list_account` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `flags_status_summary` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `training_waiting_list_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_waiting_list_account` ENABLE KEYS */;
DROP TABLE IF EXISTS `training_waiting_list_account_flag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_waiting_list_account_flag` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `waiting_list_account_id` int unsigned NOT NULL,
  `flag_id` int unsigned NOT NULL,
  `marked_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `training_waiting_list_account_flag` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_waiting_list_account_flag` ENABLE KEYS */;
DROP TABLE IF EXISTS `training_waiting_list_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_waiting_list_flags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int unsigned DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_value` tinyint(1) NOT NULL DEFAULT '0',
  `position_group_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `training_waiting_list_flags` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_waiting_list_flags` ENABLE KEYS */;
DROP TABLE IF EXISTS `vt_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vt_application` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint NOT NULL DEFAULT '10',
  `training_team` enum('atc','pilot') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int unsigned NOT NULL,
  `facility_id` int unsigned DEFAULT NULL,
  `training_required` tinyint(1) NOT NULL DEFAULT '0',
  `statement_required` tinyint(1) NOT NULL DEFAULT '0',
  `references_required` smallint NOT NULL DEFAULT '0',
  `should_perform_checks` tinyint(1) NOT NULL DEFAULT '0',
  `check_outcome_90_day` tinyint(1) DEFAULT NULL,
  `check_outcome_50_hours` tinyint(1) DEFAULT NULL,
  `will_auto_accept` tinyint(1) NOT NULL DEFAULT '0',
  `statement` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` smallint NOT NULL DEFAULT '10',
  `status_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vt_application_account_id_foreign` (`account_id`),
  KEY `vt_application_facility_id_foreign` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `vt_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `vt_application` ENABLE KEYS */;
DROP TABLE IF EXISTS `vt_facility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vt_facility` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_transfer` tinyint(1) NOT NULL DEFAULT '0',
  `can_visit` tinyint(1) NOT NULL DEFAULT '0',
  `training_required` tinyint(1) NOT NULL DEFAULT '0',
  `training_team` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `training_spaces` int unsigned DEFAULT '0',
  `stage_statement_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `stage_reference_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `stage_reference_quantity` smallint NOT NULL DEFAULT '1',
  `stage_checks` tinyint(1) NOT NULL DEFAULT '1',
  `auto_acceptance` tinyint(1) NOT NULL DEFAULT '0',
  `open` tinyint(1) NOT NULL DEFAULT '0',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `vt_facility` DISABLE KEYS */;
/*!40000 ALTER TABLE `vt_facility` ENABLE KEYS */;
DROP TABLE IF EXISTS `vt_facility_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vt_facility_email` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` int unsigned NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vt_facility_email_facility_id_foreign` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `vt_facility_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `vt_facility_email` ENABLE KEYS */;
DROP TABLE IF EXISTS `vt_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vt_reference` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `application_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `email` varchar(85) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relationship` varchar(85) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` smallint NOT NULL DEFAULT '10',
  `status_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contacted_at` timestamp NULL DEFAULT NULL,
  `reminded_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vt_reference_application_id_foreign` (`application_id`),
  KEY `vt_reference_account_id_foreign` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `vt_reference` DISABLE KEYS */;
/*!40000 ALTER TABLE `vt_reference` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2015_01_01_000000_initial_mship',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2015_01_01_000010_initial_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2015_01_01_000020_initial_sso',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2015_01_01_000030_initial_teamspeak',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2015_01_01_000040_initial_messages',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2015_01_01_000050_initial_statistic',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2015_05_03_204155_short_url',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2015_06_24_202137_staff_management',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2015_06_24_205753_staff_management_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2015_08_29_203828_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2015_08_29_204330_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2015_12_23_160400_create_atc_statistics_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2015_12_31_033900_email_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2016_02_06_155232_create_sys_activity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2016_02_20_012422_nullable_email_verified_times',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2016_02_20_023640_change_mship_account_email_primary_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2016_02_20_043629_move_primary_email_to_account_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2016_02_20_230237_add_missing_pilot_ratings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2016_02_23_002546_remove_mship_account_template_field',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2016_02_23_223008_change_account_id_to_just_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2016_02_23_223009_add_member_nickname_to_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2016_02_27_190508_migrate_security_passwords_to_mship_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2016_04_30_182200_visiting_transfer_initial_setup',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2016_05_16_062436_mship_qualification_improvements',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2016_05_16_165602_id_refactoring',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2016_05_17_004857_remove_unnecessary_soft_deletes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2016_05_31_194857_teamspeak_refactoring',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2016_06_01_000001_create_oauth_auth_codes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2016_06_01_000002_create_oauth_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2016_06_01_000003_create_oauth_refresh_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2016_06_01_000004_create_oauth_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2016_06_01_000005_create_oauth_personal_access_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2016_06_08_021718_add_email_view_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2016_06_09_002409_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2016_06_19_185800_caffeinated_modules_introduction',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2016_06_19_194500_add_missing_p5_rating',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2016_08_01_161511_add_missing_admin_membership_account_list_filter_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2016_08_23_214643_differentiate_between_pilot_and_atc_facilities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2016_08_23_225700_allow_infinity_training_spaces',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2016_08_29_165600_copy_facility_settings_to_application',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2016_08_29_235959_add_50hour_check_and_90day_check',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2016_08_31_163346_move_mship_state_definitions_to_database',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2016_09_01_232732_add_expires_at_column_to_applications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2016_09_02_115754_laravel_5_3_upgrade',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2016_10_09_220424_initial_session',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2016_10_10_172817_create_airport_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2016_10_10_174323_create_aircraft_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2016_10_10_175733_create_flight_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2016_10_10_182625_create_bid_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2016_10_10_201749_create_posrep_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2016_10_10_203516_create_pirep_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2016_11_16_231348_DeletedOldUnexpiredAtcSessions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2016_11_16_232012_StoreFrequencyOfAtcSession',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2016_12_05_190309_add_time_spent_online_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2016_12_06_225347_create_community_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2016_12_06_225400_create_community_memberships_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2016_12_10_135203_resolve_missing_vt_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2016_12_19_141500_fix_production_group_relationships',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2016_12_19_165638_delete_statistic_atc_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2016_12_21_161430_create_api_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2016_12_21_163528_create_api_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2017_01_17_212055_increase_length_of_mship_role_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2017_02_10_232137_add_request_ip_and_used_at_to_sso_tokens',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2017_02_26_110639_create_vt_facility_email',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2017_03_11_124105_add_ipv6_support',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2017_03_31_211222_create_feedback_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2017_04_06_224417_make_permission_names_larger',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2017_04_11_020921_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2017_04_11_190712_create_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2017_04_30_213730_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2017_05_04_042131_add_oauth_clients_to_emails',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2017_05_06_014424_remove_session_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2017_05_13_125804_create_email_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2017_05_13_205836_create_basic_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2017_05_24_192753_vt_facilities_public_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2017_06_30_171219_remove_qualification_timestamps',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2017_07_01_152542_make_userid_input_placeholder_grammatically_correct',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2017_07_11_204600_strict_mode',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2017_07_12_143016_create_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2017_07_12_145028_remove_mship_account_email_setting_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2017_07_15_021424_remove_api_account_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2017_07_16_031531_add_contact_to_feedback_forms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2017_07_18_012554_rename_status_to_activity',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2017_07_20_162221_add_custom_feedback_forms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2017_08_20_013846_create_networkdata_pilots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2017_08_20_213819_create_airports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2017_08_24_023743_allow_nullable_note_attachments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2017_08_25_232236_add_smartcars_flight_metadata',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2017_08_25_233309_add_smartcars_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2017_08_27_172044_increase_vt_column_lengths',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2017_09_19_225558_remove_columns_from_sys_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2017_09_19_232941_drop_teamspeak_bans',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2017_09_28_214134_lengthen_pilot_groundspeed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2017_10_13_004446_lengthen_division',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2017_10_27_184914_add_adm_usr_detail_feedback_perm',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2017_11_21_192224_fix_feedback_optional_questions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2017_12_11_204212_create_smartcars_flight_criteria',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2017_12_11_222301_add_pirep_passed_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2017_12_18_162225_convert_tables_to_utf8mb4',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2017_12_18_220629_posrep_lat_lon_adjustment',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2017_12_20_010930_smartcars_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2017_12_25_104817_create_flight_resources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2017_12_27_025012_fix_vt_facility_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2018_01_04_231938_allow_null_smartcars_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2018_01_12_215157_increase_groundspeed_size',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2018_02_10_164157_create_endorsements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2018_02_10_180736_add_kkgnd_endorsement',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2018_02_26_233358_add_mship_bans_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2018_02_27_094211_normalise_smartcars_permission_names',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2018_02_27_202930_modify_flight_time_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2018_02_28_201745_add_privacc_contact',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2018_03_01_211706_feedback_slug_length_and_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2018_03_07_194806_fix_teamspeak_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2018_03_13_182129_add_endorsement_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2018_04_20_195659_add_failed_posrep_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2018_06_10_193326_add_created_at_to_read_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2018_07_10_204054_add_feedback_sent',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2018_07_10_204055_add_feedback_send_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2018_07_21_223400_add_qstats_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2018_08_06_110205_create_airfield_information_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2018_08_11_211410_add_heading_column_to_pilot_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2018_10_26_110747_add_own_feedback_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2018_11_03_172113_delete_old_permissions_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2018_11_03_172114_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2018_11_03_192650_add_legacy_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2018_11_10_175100_remove_all_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2018_12_31_122052_add_network_data_indices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2019_01_14_221646_add_index_to_network_data_timestamps',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2019_02_02_115521_add_image_field_to_fte',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2019_06_30_201947_refactor_endorsement_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2019_07_20_085104_add_waiting_list_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2019_10_13_164450_create_sys_config_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2019_10_13_171328_add_notice_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2020_01_31_180100_add_waiting_list_action_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2020_02_01_095731_create_nova_feedback_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2020_02_01_225045_remove_unused_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2020_02_08_121218_remove_position_column_from_waiting_lists',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2020_02_11_222649_drop_waiting_list_staff_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2020_02_13_190400_create_add_student_admin_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2020_03_20_121721_increase_slack_id_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2020_04_18_142515_add_connect_columns_to_mship_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2020_04_30_215425_add_discord_id_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2020_05_10_195144_create_telescope_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2020_05_10_210000_create_telescope_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2020_05_11_184510_add_horizon_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2020_06_13_120105_create_discord_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2020_06_13_171913_add_missing_primary_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2020_08_03_170100_add_discord_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2020_08_04_201114_add_discord_oauth_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2020_08_10_164240_remove_slack_id_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2020_08_10_164413_change_order_of_discord_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2020_08_18_173102_add_p0_rating',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2020_10_03_175748_remove_outdated_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2020_11_14_174551_add_uuid_to_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2020_12_07_163307_create_schedule_monitor_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2021_05_22_092019_add_any_or_all_flag_to_waiting_list_flags',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2021_08_05_165824_add_required_qualification_column_to_endorsement_criteria',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2021_11_07_195238_modify_network_callsigns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2022_08_11_165740_add_gatwick_students_role',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2022_08_19_183038_rename_gatwick_gnd_students_role',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2022_11_20_200656_add_atc_training_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2022_12_13_131128_index_networkdata_for_account_disconnected_and_callsign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2022_12_14_103127_add_cts_exam_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2023_01_28_221016_add_tfp_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2023_01_28_230310_rename_discord_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2023_02_26_201345_pilot_mentor_role_split',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2023_03_24_165127_waiting_lists_home_members_only_flag',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2023_05_14_102302_add_pilot_instructor_ratings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2023_06_05_193357_heathrow_endorsement_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2023_06_07_184051_add_military_pilot_ratings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2023_06_19_170417_add_feature_toggles_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2023_06_24_150343_add_admin_beta_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2023_06_25_104653_update_pilot_rating_bitmasks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2023_07_04_193928_database_columns_for_checks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2023_11_15_184316_create_admin_access_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2023_12_07_190336_create_roster_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2024_01_04_201826_create_mship_account_endorsement_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2024_01_04_203458_create_endorsement_stations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2024_01_04_210059_rename_tables_for_endorsements',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2024_01_28_123419_add_temp_endorseable_flag_to_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2024_01_29_192559_create_endorsement_requests_table_and_polymorphic_endorsements',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2024_02_15_193128_amend_mship_endorsements_for_nullable_created_by',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2024_02_26_170552_add_maximum_qualification_to_position_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2024_02_28_161526_add_soft_deletes_to_position_groups',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2024_02_28_162223_add_soft_deletes_to_training_waiting_list_flags',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2024_02_29_173742_remove_waiting_list_removed_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2024_03_31_170305_create_roster_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2024_06_26_185221_add_endorseable_to_discord_role_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2024_10_21_073152_update_feedback_form_comments_question',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2025_01_07_114512_create_roster_updates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2025_01_07_120305_add_roster_update_id_to_roster_history',1);
