<?php

namespace App\Libraries\Storage;

use Illuminate\Support\Facades\Storage;

abstract class StorageWrapper
{
    protected $basePath = '/storage/';

    protected $disk = 'public';

    /**
     * Store an image on the disk.
     *
     * @return false|string
     */
    public function store(CoreUploadedFile $image)
    {
        return $image->storeAs($this->basePath,
            "{$image->getFullFileName()}.{$image->getClientOriginalExtension()}", ['disk' => $this->disk]);
    }

    /**
     * Generates a public url to the image.
     *
     * @return bool|string
     */
    public function retrieve($fileName)
    {
        return url('/').Storage::url($this->parseFileName($fileName));
    }

    /**
     * Deletes the image file.
     *
     * @return bool
     */
    public function delete($fileName)
    {
        return Storage::disk($this->disk)->delete($this->parseFileName($fileName));
    }

    /**
     * Helper function to generate the full file name.
     *
     * @return string
     */
    protected function parseFileName($fileName)
    {
        return $this->basePath.'/'.$fileName;
    }
}
