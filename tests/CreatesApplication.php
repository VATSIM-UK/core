<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Tests\Database\MockCtsDatabase;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        MockCtsDatabase::ensureCreated();

        return $app;
    }
}
