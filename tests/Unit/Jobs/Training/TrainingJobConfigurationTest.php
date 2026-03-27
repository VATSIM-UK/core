<?php

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\ActionWaitingListRetentionCheckRemoval;
use App\Jobs\Training\SendWaitingListRetentionCheck;
use App\Jobs\Training\UpdateAccountWaitingListEligibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class TrainingJobConfigurationTest extends TestCase
{
    use DatabaseTransactions;
    use WaitingListTestHelper;

    #[Test]
    public function update_eligibility_job_has_expected_queue_configuration_and_middleware(): void
    {
        $job = new UpdateAccountWaitingListEligibility($this->user);

        $this->assertSame(3, $job->tries);
        $this->assertSame(15, $job->backoff);
        $this->assertSame('training-eligibility', $job->queue);

        $middleware = $job->middleware();

        $this->assertCount(2, $middleware);
        $this->assertInstanceOf(RateLimitedWithRedis::class, $middleware[0]);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[1]);
    }

    #[Test]
    public function send_retention_check_job_has_expected_queue_configuration_and_middleware(): void
    {
        $waitingList = $this->createList();
        $waitingListAccount = $waitingList->addToWaitingList($this->user, $this->privacc);

        $job = new SendWaitingListRetentionCheck($waitingListAccount);

        $this->assertSame(3, $job->tries);
        $this->assertSame(30, $job->backoff);
        $this->assertSame('training-retention', $job->queue);

        $middleware = $job->middleware();

        $this->assertCount(2, $middleware);
        $this->assertInstanceOf(RateLimitedWithRedis::class, $middleware[0]);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[1]);
    }

    #[Test]
    public function action_retention_removal_job_has_expected_queue_configuration_and_middleware(): void
    {
        $waitingList = $this->createList();
        $waitingListAccount = $waitingList->addToWaitingList($this->user, $this->privacc);
        $retentionCheck = $waitingListAccount->retentionChecks()->create([
            'status' => 'pending',
            'token' => 'test-token',
            'expires_at' => now()->addDays(7),
            'email_sent_at' => now(),
        ]);

        $job = new ActionWaitingListRetentionCheckRemoval($retentionCheck);

        $this->assertSame(3, $job->tries);
        $this->assertSame(30, $job->backoff);
        $this->assertSame('training-retention', $job->queue);

        $middleware = $job->middleware();

        $this->assertCount(2, $middleware);
        $this->assertInstanceOf(RateLimitedWithRedis::class, $middleware[0]);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[1]);
    }
}
