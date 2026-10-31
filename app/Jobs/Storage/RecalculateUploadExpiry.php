<?php

namespace App\Jobs\Storage;

use App\Models\Storage\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateUploadExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $categoryId,
        public ?int $retentionDays,
    ) {}

    public function handle(): void
    {
        Upload::withTrashed()
            ->where('category_id', $this->categoryId)
            ->whereNull('purged_at')
            ->chunkById(200, function ($uploads): void {
                foreach ($uploads as $upload) {
                    $expiresAt = $this->retentionDays === null
                        ? null
                        : $upload->created_at->copy()->addDays($this->retentionDays);

                    $upload->forceFill(['expires_at' => $expiresAt])->saveQuietly();
                }
            });
    }
}
