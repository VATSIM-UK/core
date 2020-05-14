<?php

namespace Tests\Unit\Sys;

use App\Models\Sys\Config;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itRespondsAppropriatelyToNoConfig()
    {
        $invalidKey = sys_config('does-not-exist');

        $this->assertNull($invalidKey);
    }

    /** @test */
    public function itDoesNotAllowConfigKeysToBeCreated()
    {
        $this->expectException(QueryException::class);

        Config::create([
            'key'    => 'key',
            'value'  => 'value',
            'active' => 1,
        ]);
    }

    /** @test */
    public function itFindsConfigKeysByKey()
    {
        $key = Config::forceCreate([
            'key'    => 'key',
            'value'  => 'value',
            'active' => 1,
        ]);

        $this->assertEquals(Config::find('key')->value('value'), 'value');
    }

    /** @test */
    public function itOnlyReturnsActiveConfigKeys()
    {
        Config::forceCreate([
            'key'    => 'active',
            'value'  => 'active-value',
            'active' => 1,
        ]);

        Config::forceCreate([
            'key'    => 'inactive',
            'value'  => 'inactive-value',
            'active' => 0,
        ]);

        $this->assertNotNull(Config::find('active'));
        $this->assertNull(Config::find('inactive'));
    }

    /** @test */
    public function theHelperFunctionReturnsTheValue()
    {
        Config::forceCreate([
            'key'    => 'key',
            'value'  => 'value',
            'active' => 1,
        ]);

        $this->assertEquals(sys_config('key'), 'value');
    }
}
