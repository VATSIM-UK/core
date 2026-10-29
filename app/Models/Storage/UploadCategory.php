<?php

namespace App\Models\Storage;

use App\Enums\StorageVisibility;
use App\Jobs\Storage\RecalculateUploadExpiry;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Database-defined upload policy; consumers reference categories by slug.
 */
class UploadCategory extends Model
{
    use SoftDeletes;

    protected $table = 'sys_storage_category';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'visibility',
        'max_file_size_kb',
        'allowed_mimes',
        'max_files_per_user',
        'max_bytes_per_user',
        'retention_days',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'visibility' => StorageVisibility::class,
        'allowed_mimes' => 'array',
        'max_file_size_kb' => 'integer',
        'max_files_per_user' => 'integer',
        'max_bytes_per_user' => 'integer',
        'retention_days' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updated(function (UploadCategory $category): void {
            if ($category->wasChanged('retention_days')) {
                RecalculateUploadExpiry::dispatch($category->id, $category->retention_days);
            }
        });

        static::deleting(function (UploadCategory $category): void {
            if ($category->isForceDeleting()) {
                return;
            }

            // Free the slug so a replacement category can be created.
            $category->forceFill([
                'slug' => Str::limit($category->slug, 60, '').'-deleted-'.Str::lower((string) Str::ulid()),
            ])->saveQuietly();
        });
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class, 'category_id');
    }

    public function isPublic(): bool
    {
        return $this->visibility === StorageVisibility::Public;
    }

    public function maxFileSizeKb(): int
    {
        return $this->max_file_size_kb ?? (int) config('uploads.max_file_size_kb');
    }

    /**
     * @return array<int, string>
     */
    public function allowedMimes(): array
    {
        return array_values(array_filter(
            array_map('strtolower', $this->allowed_mimes ?? []),
        ));
    }
}
