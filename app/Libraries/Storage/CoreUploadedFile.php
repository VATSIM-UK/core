<?php

namespace App\Libraries\Storage;

use Illuminate\Http\UploadedFile;

class CoreUploadedFile extends UploadedFile
{
    public function __construct(UploadedFile $uploadedFile)
    {
        parent::__construct($uploadedFile->path(), $uploadedFile->getClientOriginalName());
    }

    public function getFullFileName()
    {
        return sha1("{$this->getClientOriginalName()}.{$this->getClientOriginalExtension()}");
    }
}
