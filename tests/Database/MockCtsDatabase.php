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
    }
}
