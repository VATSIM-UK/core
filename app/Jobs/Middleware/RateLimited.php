<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;

class RateLimited
{
    private $key;

    /** @var int */
    private $allow;

    /** @var int */
    private $every;

    /** @var int */
    private $retryAfter;

    public function __construct(string $key = null, ?int $allow = 50, ?int $every = 10, ?int $retryAfter = 10)
    {
        $this->key = $key ?? 'rate_limited_queue_job';
        $this->allow = $allow ?? 50;
        $this->every = $every ?? 10;
        $this->retryAfter = $retryAfter ?? 10;
    }

    public function handle($job, $next)
    {
        try {
            Redis::throttle($this->key)
                ->allow($this->allow)
                ->every($this->every)
                ->then(function () use ($job, $next) {
                    // Lock obtained...

                    $next($job);
                }, function () use ($job) {
                    // Could not obtain lock...

                    $job->release($this->retryAfter);
                });
        } catch (ConnectionException $exception) {
            // Redis probably not installed. We will send the job anyway
            $next($job);
        }
    }
}
