<?php

namespace App\Services\Storage;

use App\Exceptions\Storage\UploadLimitExceededException;
use App\Jobs\Storage\PurgeUpload;
use App\Models\Mship\Account;
use App\Models\Storage\Upload;
use App\Models\Storage\UploadCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * Validate and persist an uploaded file.
     *
     * This is the single enforcement point for category policy: size, MIME,
     * per-user file count and per-user bytes. Filament and HTTP validation
     * mirror these rules for a better experience, but never replace this.
     *
     * @param  Account|null  $owner  the account whose quota is consumed
     * @param  Model|null  $uploadable  optional domain record the file belongs to
     * @param  Account|null  $uploadedBy  the actor, when different from the owner
     */
    public function store(
        UploadedFile $file,
        UploadCategory $category,
        ?Account $owner = null,
        ?Model $uploadable = null,
        string $collection = Upload::DEFAULT_COLLECTION,
        ?Account $uploadedBy = null,
        bool $replace = false,
    ): Upload {
        $this->assertCategoryAccepts($category);

        $size = $file->getSize();

        if ($size === false) {
            throw UploadLimitExceededException::unreadableFile();
        }

        $sizeBytes = (int) $size;
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $this->assertFileSize($category, $sizeBytes);
        $this->assertMimeType($category, $mimeType);

        $disk = (string) config('uploads.disk');
        $ulid = (string) Str::ulid();
        $extension = $this->extensionFor($file);
        $directory = trim((string) config('uploads.path'), '/');
        $path = "{$directory}/{$ulid}.{$extension}";
        $visibility = $category->visibility->value;
        $originalName = $this->originalNameFor($file, $extension);
        $checksum = $this->checksumFor($file);

        Storage::disk($disk)->putFileAs($directory, $file, "{$ulid}.{$extension}", $visibility);

        try {
            $upload = DB::transaction(function () use ($category, $owner, $uploadedBy, $uploadable, $collection, $disk, $path, $ulid, $extension, $mimeType, $originalName, $checksum, $sizeBytes): Upload {
                if ($owner !== null && ($category->max_files_per_user !== null || $category->max_bytes_per_user !== null)) {
                    // Serialise quota checks for this category so concurrent uploads cannot overrun a limit.
                    $lockedCategory = UploadCategory::query()
                        ->whereKey($category->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $this->assertWithinQuota($lockedCategory, $owner, $sizeBytes);
                }

                return Upload::create([
                    'ulid' => $ulid,
                    'category_id' => $category->id,
                    'account_id' => $owner?->id,
                    'uploaded_by' => $uploadedBy?->id,
                    'uploadable_type' => $uploadable?->getMorphClass(),
                    'uploadable_id' => $uploadable?->getKey(),
                    'collection' => $collection,
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $originalName,
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'size_bytes' => $sizeBytes,
                    'checksum' => $checksum,
                    'expires_at' => $category->retention_days !== null
                        ? now()->addDays($category->retention_days)
                        : null,
                ]);
            });
        } catch (\Throwable $exception) {
            // Never leave an object in the bucket without a database row.
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        if ($replace && $uploadable !== null) {
            $this->deleteCollection($upload);
        }

        return $upload;
    }

    public function delete(Upload $upload): void
    {
        if ($upload->trashed()) {
            return;
        }

        $upload->delete();

        PurgeUpload::dispatch($upload->id, $upload->disk, $upload->path)->afterCommit();
    }

    /**
     * @return array{files: int, bytes: int}
     */
    public function usage(UploadCategory $category, Account $account): array
    {
        $query = Upload::query()
            ->where('category_id', $category->id)
            ->where('account_id', $account->id);

        return [
            'files' => (int) (clone $query)->count(),
            'bytes' => (int) (clone $query)->sum('size_bytes'),
        ];
    }

    public function assertCategoryAccepts(UploadCategory $category): void
    {
        if (! $category->is_active) {
            throw UploadLimitExceededException::categoryInactive($category);
        }
    }

    public function assertFileSize(UploadCategory $category, int $sizeBytes): void
    {
        $maxBytes = $category->maxFileSizeKb() * 1024;

        if ($sizeBytes > $maxBytes) {
            throw UploadLimitExceededException::fileTooLarge($sizeBytes, $category->maxFileSizeKb());
        }
    }

    public function assertMimeType(UploadCategory $category, string $mimeType): void
    {
        $allowed = $category->allowedMimes();

        if ($allowed !== [] && ! in_array(strtolower($mimeType), $allowed, true)) {
            throw UploadLimitExceededException::mimeNotAllowed($mimeType, $allowed);
        }
    }

    public function assertWithinQuota(UploadCategory $category, Account $owner, int $incomingBytes): void
    {
        if ($category->max_files_per_user === null && $category->max_bytes_per_user === null) {
            return;
        }

        $usage = $this->usage($category, $owner);

        if ($category->max_files_per_user !== null && $usage['files'] >= $category->max_files_per_user) {
            throw UploadLimitExceededException::tooManyFiles($category->max_files_per_user);
        }

        if ($category->max_bytes_per_user !== null && ($usage['bytes'] + $incomingBytes) > $category->max_bytes_per_user) {
            throw UploadLimitExceededException::quotaExceeded($category->max_bytes_per_user);
        }
    }

    public function deleteCollection(Upload $upload): void
    {
        if ($upload->uploadable_id === null || $upload->uploadable_type === null) {
            return;
        }

        Upload::query()
            ->where('category_id', $upload->category_id)
            ->where('uploadable_type', $upload->uploadable_type)
            ->where('uploadable_id', $upload->uploadable_id)
            ->where('collection', $upload->collection)
            ->whereKeyNot($upload->getKey())
            ->get()
            ->each(fn (Upload $previous) => $this->delete($previous));
    }

    private function extensionFor(UploadedFile $file): string
    {
        $extension = $file->extension()
            ?: $file->getClientOriginalExtension()
            ?: 'bin';

        $extension = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $extension));

        return $extension !== '' ? $extension : 'bin';
    }

    private function originalNameFor(UploadedFile $file, string $extension): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?: '';

        return Str::limit($name !== '' ? $name : "file.{$extension}", 240);
    }

    private function checksumFor(UploadedFile $file): ?string
    {
        $path = $file->getRealPath();

        if ($path === false || ! is_readable($path)) {
            return null;
        }

        $checksum = hash_file('sha256', $path);

        return $checksum === false ? null : $checksum;
    }
}
