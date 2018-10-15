<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

abstract class UnitTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->seedLegacyTables();
    }

    protected function seedLegacyTables()
    {
        if (!method_exists($this, 'refreshDatabase')) {
            return;
        }

        $this->dropLegacyTables();

        DB::connection('cts')->statement("CREATE TABLE `sessions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rts_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `position` varchar(12) NOT NULL DEFAULT '',
  `progress_sheet_id` mediumint(9) NOT NULL,
  `student_id` int(7) unsigned NOT NULL DEFAULT 0,
  `student_rating` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `date_1` date DEFAULT NULL,
  `from_1` time DEFAULT NULL,
  `to_1` time DEFAULT NULL,
  `date_2` date DEFAULT NULL,
  `from_2` time DEFAULT NULL,
  `to_2` time DEFAULT NULL,
  `date_3` date DEFAULT NULL,
  `from_3` time DEFAULT NULL,
  `to_3` time DEFAULT NULL,
  `taken` tinyint(1) unsigned DEFAULT 0,
  `mentor_id` int(7) unsigned DEFAULT NULL,
  `mentor_rating` tinyint(1) unsigned DEFAULT NULL,
  `taken_date` date DEFAULT NULL,
  `taken_from` time DEFAULT NULL,
  `taken_to` time DEFAULT NULL,
  `request_time` datetime DEFAULT NULL,
  `taken_time` datetime DEFAULT NULL,
  `book_done` tinyint(1) unsigned DEFAULT 0,
  `session_done` tinyint(1) unsigned DEFAULT 0,
  `noShow` int(1) NOT NULL DEFAULT 0,
  `cancelled_datetime` datetime DEFAULT NULL,
  `no_avail_count` smallint(6) NOT NULL DEFAULT 0,
  `filed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");
    }

    protected function dropLegacyTables()
    {
        if (!method_exists($this, 'refreshDatabase')) {
            return;
        }

        DB::connection('cts')->statement("
            DROP TABLE IF EXISTS `sessions`;
        ");
    }
}
