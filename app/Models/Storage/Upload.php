<?php

namespace App\Models\Storage;

use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Stored file metadata; URLs expose the ULID, never the primary key.
 */
class Upload extends Model
{
    use SoftDeletes;

    public const DEFAULT_COLLECTION = 'default';

    protected $table = 'sys_storage_upload';

    protected $fillable = [
        'ulid',
        'category_id',
        'account_id',
        'uploaded_by',
        'uploadable_type',
        'uploadable_id',
        'collection',
        'disk',
        'path',
        'original_name',
        'extension',
        'mime_type',
        'size_bytes',
        'checksum',
        'expires_at',
        'purged_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'expires_at' => 'datetime',
        'purged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Upload $upload): void {
            $upload->ulid ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(UploadCategory::class, 'category_id');
    }

    /**
     * The account whose quota this upload counts against.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'uploaded_by');
    }

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPublic(): bool
    {
        return (bool) $this->category?->isPublic();
    }

    /**
     * Stable application URL. Private uploads redirect to a short-lived
     * presigned S3 URL after the policy check; public uploads redirect to
     * the object's direct URL.
     */
    public function url(): string
    {
        return route('storage.files.show', $this);
    }

    public function downloadUrl(): string
    {
        return route('storage.files.show', ['upload' => $this, 'download' => 1]);
    }
}
