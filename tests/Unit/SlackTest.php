<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use SlackUser;
use Tests\TestCase;

class SlackTest extends TestCase
{
    public function testSlackApiGivesInvalidAuth()
    {
        $users = SlackUser::lists();
        $this->assertEquals('invalid_auth', $users->error);
        $this->assertFalse($users->ok);
    }

    public function testItLogsWhenSlackCredentialsIncorrect()
    {
        Artisan::call('slack:manager');
        $this->assertEquals("Slack credentials invalid!\n", Artisan::output());
    }
}
