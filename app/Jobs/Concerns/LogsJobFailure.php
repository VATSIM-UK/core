<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

trait LogsJobFailure
{
    /**
     * Extra context merged into lifecycle log entries. Override per job.
     */
    protected function logJobContext(): array
    {
        return [];
    }

    public function failed(Throwable $e): void
    {
        Log::error('Job failed', [
            'job' => static::class,
            'exception' => $e,
        ] + $this->logJobContext());
    }
}
