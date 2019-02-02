<?php

namespace App\Libraries\Storage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

abstract class StorageWrapper
{
    protected $basePath = '/';
    protected $disk = 'public';

    /**
     * Store an image on the disk.
     *
     * @param CoreUploadedFile $image
     * @return false|string
     */
    public function store(CoreUploadedFile $image)
    {
        return $image->storeAs($this->basePath,
            "{$image->getFullFileName()}.{$image->getClientOriginalExtension()}", ['disk' => $this->disk]);
    }

    /**
     * Retrieve an image from the disk.
     *
     * @param $fileName
     * @return bool|string
     */
    public function retrieve($fileName)
    {
        try {
            $file = Storage::disk($this->disk)->get($this->parseFileName($fileName));
        } catch (FileNotFoundException $e) {
            return false;
        }

        return $file;
    }

    /**
     * Helper function to generate the full file name.
     *
     * @param $fileName
     * @return string
     */
    protected function parseFileName($fileName)
    {
        return $this->disk. $fileName;
    }
}