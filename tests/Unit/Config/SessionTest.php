<?php

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionTest extends TestCase
{
    #[Test]
    public function the_session_cookie_is_host_only_when_session_domain_is_configured()
    {
        $_ENV['SESSION_DOMAIN'] = '.vatsim.uk';
        $_SERVER['SESSION_DOMAIN'] = '.vatsim.uk';

        try {
            $config = require config_path('session.php');
        } finally {
            unset($_ENV['SESSION_DOMAIN'], $_SERVER['SESSION_DOMAIN']);
        }

        $this->assertNull($config['domain']);
    }
}
