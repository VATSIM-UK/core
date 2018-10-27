<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
    }
}
