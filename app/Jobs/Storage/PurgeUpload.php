<?php

namespace App\Jobs\Storage;

use App\Models\Storage\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes the object and hard-deletes its row. Primitive properties are used
 * so the job survives a row another worker already removed.
 */
class PurgeUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $uploadId,
        public string $disk,
        public string $path,
    ) {}

    public function handle(): void
    {
        Storage::disk($this->disk)->delete($this->path);

        Upload::withTrashed()->whereKey($this->uploadId)->forceDelete();
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to purge upload', [
            'upload_id' => $this->uploadId,
            'disk' => $this->disk,
            'path' => $this->path,
            'exception' => $exception,
        ]);
    }
}
