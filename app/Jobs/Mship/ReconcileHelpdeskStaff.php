<?php

declare(strict_types=1);

namespace App\Jobs\Mship;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Services\Helpdesk\HelpdeskStaffService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReconcileHelpdeskStaff extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly int $accountId) {}

    public function handle(HelpdeskStaffService $service): void
    {
        $service->reconcile($this->accountId);

        Log::info('Member sync completed', [
            'service' => 'helpdesk-staff',
            'account_id' => $this->accountId,
        ]);
    }

    protected function logJobContext(): array
    {
        return ['account_id' => $this->accountId];
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("helpdesk-staff-{$this->accountId}"))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }
}
