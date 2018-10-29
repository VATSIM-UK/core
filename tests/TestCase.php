<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Cts\MockCtsDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /* @var Carbon */
    protected $knownDate;

    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.url' => 'http://'.config('app.url')]);

        Carbon::setTestNow();
        $this->knownDate = Carbon::now();

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
