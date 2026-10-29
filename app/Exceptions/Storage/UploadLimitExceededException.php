<?php

namespace App\Exceptions\Storage;

use App\Models\Storage\UploadCategory;
use RuntimeException;

/**
 * Raised when an upload breaches a category policy; messages are user-safe.
 */
class UploadLimitExceededException extends RuntimeException
{
    public static function categoryInactive(UploadCategory $category): self
    {
        return new self("The '{$category->name}' upload category is not accepting files.");
    }

    public static function unreadableFile(): self
    {
        return new self('The uploaded file could not be read.');
    }

    public static function fileTooLarge(int $sizeBytes, int $maxKilobytes): self
    {
        return new self(sprintf(
            'This file is %s; the maximum allowed size is %s.',
            self::humanSize($sizeBytes),
            self::humanSize($maxKilobytes * 1024),
        ));
    }

    /**
     * @param  array<int, string>  $allowed
     */
    public static function mimeNotAllowed(string $mimeType, array $allowed): self
    {
        return new self(sprintf(
            'Files of type %s are not allowed. Allowed types: %s.',
            $mimeType,
            implode(', ', $allowed),
        ));
    }

    public static function tooManyFiles(int $maxFiles): self
    {
        return new self("You can upload at most {$maxFiles} file(s) in this category.");
    }

    public static function quotaExceeded(int $maxBytes): self
    {
        return new self('You have reached the storage limit for this category ('.self::humanSize($maxBytes).').');
    }

    private static function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
