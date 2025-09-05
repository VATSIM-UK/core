<?php

namespace Tests\Database;

use Illuminate\Support\Facades\DB;

class MockCtsDatabase
{
    public static function create()
    {
        DB::connection('cts')->statement("SET SESSION sql_mode='NO_ZERO_IN_DATE';");

        DB::connection('cts')->statement(
            "CREATE TABLE `members` (
              `old_rts_id` smallint(5) unsigned NOT NULL,
              `id` bigint(7) NOT NULL,
              `home_rts_id` tinyint(2) NOT NULL DEFAULT 0,
              `cid` mediumint(7) unsigned DEFAULT 0,
              `name` varchar(50) NOT NULL DEFAULT '',
              `email` varchar(50) NOT NULL DEFAULT '',
              `password` varchar(32) NOT NULL DEFAULT '',
              `rating` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `prating` tinyint(1) NOT NULL DEFAULT 0,
              `disabled` enum('0','1') NOT NULL DEFAULT '0',
              `visiting` tinyint(1) unsigned DEFAULT 0,
              `visit_from` varchar(100) DEFAULT NULL,
              `visit_may_control` varchar(150) DEFAULT NULL,
              `visit_requested` mediumtext DEFAULT NULL,
              `visit_ptd` enum('0','1') NOT NULL DEFAULT '0',
              `ageband` tinyint(1) unsigned DEFAULT NULL,
              `country` char(2) DEFAULT NULL,
              `experience` enum('P','A','B','N') DEFAULT NULL,
              `bt_start_hour` smallint(2) NOT NULL DEFAULT 18,
              `bt_start_min` smallint(2) NOT NULL DEFAULT 30,
              `bt_end_hour` smallint(2) NOT NULL DEFAULT 21,
              `bt_end_min` smallint(2) NOT NULL DEFAULT 30,
              `examiner` tinyint(1) unsigned DEFAULT 0,
              `examiner_app` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `examiner_p_app` tinyint(1) NOT NULL DEFAULT 0,
              `admin` tinyint(1) unsigned DEFAULT 0,
              `admin_rts` tinyint(1) NOT NULL DEFAULT 0,
              `admin_rtsi` tinyint(1) NOT NULL DEFAULT 0,
              `admin_ex` tinyint(1) NOT NULL DEFAULT 0,
              `ins` int(1) NOT NULL DEFAULT 0,
              `joined` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `joined_div` datetime NOT NULL,
              `last_cert_check` datetime DEFAULT NULL,
              `verified` tinyint(1) unsigned DEFAULT 0,
              `deleted` tinyint(1) unsigned DEFAULT 0,
              `updated` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `cid` (`cid`),
              KEY `deleted` (`deleted`),
              KEY `name` (`name`),
              KEY `rating` (`rating`),
              KEY `prating` (`prating`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `bookings` (
              `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
              `date` date NOT NULL DEFAULT '0000-00-00',
              `from` time NOT NULL DEFAULT '00:00:00',
              `to` time NOT NULL DEFAULT '00:00:00',
              `position` varchar(12) NOT NULL DEFAULT '',
              `member_id` int(7) unsigned NOT NULL DEFAULT 0,
              `type` char(2) NOT NULL DEFAULT '',
              `type_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
              `groupID` mediumint(8) DEFAULT NULL,
              `time_booked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `local_id` bigint(50) NOT NULL,
              `eurobook_id` bigint(50) unsigned DEFAULT NULL,
              `eurobook_import` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `date` (`date`)
            ) ENGINE=InnoDB AUTO_INCREMENT=96258 DEFAULT CHARSET=utf8mb4;"
        );

        DB::connection('cts')->statement(
            "create table events
            (
                id smallint(5) unsigned auto_increment
                    primary key,
                event varchar(100) default '' not null,
                date date default '0000-00-00' not null,
                `from` time default '00:00:00' not null,
                `to` time default '00:00:00' not null,
                image enum('0', 'jpeg', 'jpg', 'gif', 'png') default '0' null,
                text longtext not null,
                tagline varchar(100) not null,
                thread varchar(150) null,
                add_by int(7) unsigned default 0 not null,
                add_date datetime default '0000-00-00 00:00:00' not null,
                gone int(1) default 0 not null,
                priority int(1) default 1 not null
            ) charset=utf8mb4;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `memberships`
            (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `rts_id` smallint(5) unsigned NOT NULL DEFAULT 0,
              `member_id` int(7) unsigned NOT NULL DEFAULT 0,
              `type` enum('','H','A','V') NOT NULL DEFAULT '',
              `rtsm` tinyint(1) unsigned DEFAULT 0,
              `rtsi` tinyint(1) unsigned DEFAULT 0,
              `hidden` enum('0','1') NOT NULL DEFAULT '0',
              `sequence` smallint(3) NOT NULL DEFAULT 999,
              `other` tinyint(1) NOT NULL DEFAULT 0,
              `pending` tinyint(1) unsigned DEFAULT 0,
              `joined` date DEFAULT NULL,
              `confirmed` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `member_id` (`member_id`),
              KEY `rts_id` (`rts_id`)
            ) charset=utf8mb4;"
        );

        DB::connection('cts')->statement(
            'CREATE TABLE `validations` (
                  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                  `position_id` smallint(5) unsigned NOT NULL DEFAULT 0,
                  `member_id` int(7) unsigned NOT NULL DEFAULT 0,
                  `awarded_by` int(7) unsigned NOT NULL DEFAULT 0,
                  `awarded_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                  PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8mb4;'
        );

        DB::connection('cts')->statement(
            'CREATE TABLE `validations_p` (
                  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                  `position` varchar(30) NOT NULL DEFAULT \'\',
                  `rts` smallint(5) DEFAULT NULL,
                  `min_rating` tinyint(1) NOT NULL DEFAULT 3,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `positions` (
                `id` smallint unsigned NOT NULL AUTO_INCREMENT,
                `rts_id` smallint unsigned NOT NULL DEFAULT '0',
                `callsign` varchar(20) NOT NULL DEFAULT '',
                `rating` tinyint unsigned NOT NULL DEFAULT '0',
                `auto_rating` tinyint(1) NOT NULL DEFAULT '0',
                `vis_roster` tinyint(1) NOT NULL DEFAULT '1',
                `anon_requests` tinyint(1) DEFAULT '0',
                `prog_sheet_id` smallint unsigned DEFAULT '0',
                `prog_sheet_assign_by` int unsigned DEFAULT '0',
                PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=684 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `position_validations` (
                `member_id` int NOT NULL DEFAULT '0',
                `position_id` smallint NOT NULL DEFAULT '0',
                `status` smallint NOT NULL DEFAULT '0',
                `changed_by` int NOT NULL DEFAULT '0',
                `date_changed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`member_id`,`position_id`),
                KEY `position_id` (`position_id`),
                KEY `status` (`status`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `examinerSettings` (
                `id` int NOT NULL AUTO_INCREMENT,
                `memberID` int NOT NULL,
                `OBS` tinyint(1) NOT NULL DEFAULT '0',
                `S1` int NOT NULL DEFAULT '0',
                `S2` int NOT NULL DEFAULT '0',
                `S3` int NOT NULL DEFAULT '0',
                `P1` tinyint(1) NOT NULL DEFAULT '0',
                `P2` tinyint(1) NOT NULL,
                `P3` tinyint NOT NULL DEFAULT '0',
                `P4` tinyint(1) NOT NULL DEFAULT '0',
                `P5` tinyint(1) NOT NULL DEFAULT '0',
                `OBStrain` enum('0','1') DEFAULT NULL,
                `S1train` enum('0','1') NOT NULL,
                `S2train` enum('0','1') NOT NULL,
                `S3train` enum('0','1') NOT NULL,
                `P1train` enum('0','1') NOT NULL,
                `P2train` enum('0','1') NOT NULL,
                `P3train` enum('0','1') NOT NULL DEFAULT '0',
                `P4train` enum('0','1') NOT NULL DEFAULT '0',
                `P5train` enum('0','1') NOT NULL DEFAULT '0',
                `lastUpdated` datetime NOT NULL,
                `updatedBy` int NOT NULL,
                PRIMARY KEY (`id`)
              );"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `theory_results` (
                `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
                `exam` char(2) NOT NULL DEFAULT '',
                `student_id` int unsigned NOT NULL DEFAULT '0',
                `questions` tinyint unsigned NOT NULL DEFAULT '0',
                `time_mins` tinyint unsigned NOT NULL DEFAULT '0',
                `passmark` tinyint unsigned NOT NULL DEFAULT '0',
                `correct` tinyint unsigned NOT NULL DEFAULT '0',
                `pass` tinyint unsigned NOT NULL DEFAULT '0',
                `started` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `expires` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                `submitted` tinyint(1) NOT NULL DEFAULT '0',
                `submitted_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                `upgraded` tinyint unsigned DEFAULT '0',
                `upgraded_by` int unsigned DEFAULT '0',
                `upgraded_date` datetime DEFAULT NULL,
                `old_type` tinyint NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
              );"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `practical_results` (
                `id` smallint unsigned NOT NULL AUTO_INCREMENT,
                `examid` smallint unsigned NOT NULL DEFAULT '0',
                `student_id` int unsigned NOT NULL DEFAULT '0',
                `exam` enum('P1','P2','P3','P4','P5','P6','P7','P8','P9','OBS','TWR','APP','CTR','S3','C1','C3') NOT NULL DEFAULT 'TWR',
                `notes` longtext,
                `result` char(1) NOT NULL DEFAULT '',
                `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `cert_upgrade` tinyint unsigned DEFAULT '0',
                `upgrade_by` int unsigned DEFAULT '0',
                `upgrade_date` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
              );"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `sessions` (
              `id` smallint unsigned NOT NULL AUTO_INCREMENT,
              `rts_id` smallint unsigned NOT NULL DEFAULT '0',
              `position` varchar(20) NOT NULL DEFAULT '',
              `progress_sheet_id` mediumint NOT NULL,
              `student_id` int unsigned NOT NULL DEFAULT '0',
              `student_rating` tinyint unsigned NOT NULL DEFAULT '0',
              `date_1` date DEFAULT NULL,
              `from_1` time DEFAULT NULL,
              `to_1` time DEFAULT NULL,
              `date_2` date DEFAULT NULL,
              `from_2` time DEFAULT NULL,
              `to_2` time DEFAULT NULL,
              `date_3` date DEFAULT NULL,
              `from_3` time DEFAULT NULL,
              `to_3` time DEFAULT NULL,
              `taken` tinyint unsigned DEFAULT '0',
              `mentor_id` int unsigned DEFAULT NULL,
              `mentor_rating` tinyint unsigned DEFAULT NULL,
              `taken_date` date DEFAULT NULL,
              `taken_from` time DEFAULT NULL,
              `taken_to` time DEFAULT NULL,
              `request_time` datetime DEFAULT NULL,
              `taken_time` datetime DEFAULT NULL,
              `book_done` tinyint unsigned DEFAULT '0',
              `session_done` tinyint unsigned DEFAULT '0',
              `noShow` int NOT NULL DEFAULT '0',
              `cancelled_datetime` datetime DEFAULT NULL,
              `no_avail_count` smallint NOT NULL DEFAULT '0',
              `filed` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `rts_id` (`rts_id`),
              KEY `position` (`position`),
              KEY `progress_sheet_id` (`progress_sheet_id`),
              KEY `student_id` (`student_id`),
              KEY `mentor_id` (`mentor_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=62862 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `rts` (
              `id` smallint unsigned NOT NULL AUTO_INCREMENT,
              `forum_mentor_group` int NOT NULL,
              `name` varchar(30) NOT NULL DEFAULT '',
              `helpdesk` tinyint(1) NOT NULL,
              `email` varchar(50) DEFAULT NULL,
              `opened_by` int unsigned NOT NULL DEFAULT '0',
              `opened_date` date NOT NULL DEFAULT '0000-00-00',
              `edit_by` int unsigned DEFAULT NULL,
              `edit_date` datetime DEFAULT NULL,
              `full` smallint NOT NULL DEFAULT '0',
              `cfg_sequence` tinyint(1) NOT NULL,
              `allow_home` tinyint(1) NOT NULL DEFAULT '1',
              `allow_visit` tinyint(1) NOT NULL DEFAULT '0',
              `min_home_rating` tinyint NOT NULL,
              `allow_area` tinyint(1) NOT NULL DEFAULT '0',
              `min_area_rating` tinyint NOT NULL DEFAULT '12',
              `min_visit_rating` tinyint NOT NULL DEFAULT '0',
              `start_count` smallint NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `exam_book` (
          `id` smallint unsigned NOT NULL AUTO_INCREMENT,
          `rts_id` smallint unsigned NOT NULL DEFAULT '0',
          `student_id` int unsigned NOT NULL DEFAULT '0',
          `student_rating` tinyint unsigned NOT NULL DEFAULT '0',
          `exam` enum('P1','P2','P3','P4','P5','P6','P7','P8','P9','OBS','TWR','APP','CTR','S3','C1','C3') NOT NULL DEFAULT 'TWR',
          `position_1` varchar(10) NOT NULL DEFAULT '',
          `position_2` varchar(10) DEFAULT NULL,
          `date_1` date DEFAULT NULL,
          `from_1` time DEFAULT NULL,
          `to_1` time DEFAULT NULL,
          `date_2` date DEFAULT NULL,
          `from_2` time DEFAULT NULL,
          `to_2` time DEFAULT NULL,
          `date_3` date DEFAULT NULL,
          `from_3` time DEFAULT NULL,
          `to_3` time DEFAULT NULL,
          `taken` tinyint unsigned DEFAULT '0',
          `taken_date` date DEFAULT NULL,
          `taken_from` time DEFAULT NULL,
          `taken_to` time DEFAULT NULL,
          `exmr_id` int unsigned DEFAULT NULL,
          `exmr_rating` tinyint unsigned DEFAULT NULL,
          `time_book` datetime DEFAULT NULL,
          `time_taken` datetime DEFAULT NULL,
          `book_done` tinyint unsigned DEFAULT '0',
          `second_examiner_req` tinyint unsigned DEFAULT '0',
          `pass` tinyint unsigned DEFAULT '0',
          `finished` tinyint unsigned DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `student_id` (`student_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2739 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

        DB::connection('cts')->statement(
            "CREATE TABLE `practical_examiners` (
              `id` smallint unsigned NOT NULL AUTO_INCREMENT,
              `examid` smallint unsigned NOT NULL DEFAULT '0',
              `senior` int unsigned NOT NULL DEFAULT '0',
              `other` int unsigned DEFAULT '0',
              `trainee` int unsigned DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=3010 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `exam_criteria` (
            `id` smallint unsigned NOT NULL AUTO_INCREMENT,
            `exam` enum('OBS','TWR','APP','CTR','P1','P2','P3','P4','P5','P6','P7','P8','P9') NOT NULL DEFAULT 'TWR',
            `criteria` longtext NOT NULL,
            `deleted` tinyint unsigned DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `practical_criteria_assess` (
              `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
              `examid` smallint unsigned NOT NULL DEFAULT '0',
              `criteria_id` smallint unsigned NOT NULL DEFAULT '0',
              `result` char(1) NOT NULL DEFAULT '',
              `addnotes` tinyint unsigned DEFAULT '0',
              `notes` longtext NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=38933 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );

        DB::connection('cts')->statement(
            "CREATE TABLE `exam_setup` (
                `id` smallint unsigned NOT NULL AUTO_INCREMENT,
                `rts_id` smallint unsigned NOT NULL DEFAULT '0',
                `student_id` int unsigned NOT NULL DEFAULT '0',
                `position_1` varchar(10) DEFAULT NULL,
                `position_2` varchar(10) DEFAULT NULL,
                `exam` char(3) NOT NULL DEFAULT '',
                `setup_by` int unsigned NOT NULL DEFAULT '0',
                `setup_date` datetime DEFAULT NULL,
                `response` tinyint unsigned DEFAULT '0',
                `deny_reason` longtext,
                `dealt_by` int unsigned DEFAULT '0',
                `dealt_date` datetime DEFAULT NULL,
                `bookid` mediumint NOT NULL DEFAULT '0',
                `booked` smallint unsigned DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
              ) ENGINE=InnoDB AUTO_INCREMENT=2344 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
        );
    }

    public static function destroy()
    {
        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `members`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `bookings`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `events`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `memberships`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `validations`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `validations_p`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `positions`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `position_validations`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `examinerSettings`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `theory_results`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `practical_results`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `sessions`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `rts`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `exam_book`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `practical_examiners`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `exam_criteria`;'
        );

        DB::connection('cts')->statement(
            'DROP TABLE IF EXISTS `practical_criteria_assess`;'
        );
    }
}
