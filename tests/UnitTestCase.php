<?php

namespace Tests;

abstract class UnitTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->seedLegacyTables();
    }

    protected function seedLegacyTables()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        $this->dropLegacyTables();

        MockCtsDatabase::create();
    }

    protected function dropLegacyTables()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        MockCtsDatabase::destroy();
    }
}
