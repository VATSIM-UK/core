<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Artisan;
use SlackUser;
use Tests\TestCase;

class SlackTest extends TestCase
{
    /** @test */
    public function itSlackApiGivesInvalidAuth()
    {
        $users = SlackUser::lists();
        $this->assertEquals('invalid_auth', $users->error);
        $this->assertFalse($users->ok);
    }

    /** @test */
    public function itLogsWhenSlackCredentialsIncorrect()
    {
        Artisan::call('slack:manager');
        $this->assertEquals("Slack credentials invalid!\n", Artisan::output());
    }
}
