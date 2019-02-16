<?php

namespace App\Libraries\Storage;

abstract class StorageWrapper
{
    protected $basePath = '/storage/';
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
        return asset($this->parseFileName($fileName));
    }

    /**
     * Helper function to generate the full file name.
     *
     * @param $fileName
     * @return string
     */
    protected function parseFileName($fileName)
    {
        return $this->basePath. "/" . $fileName;
    }
}
