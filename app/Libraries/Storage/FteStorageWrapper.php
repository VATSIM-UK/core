<?php

namespace App\Libraries\Storage;

class FteStorageWrapper extends StorageWrapper
{
    protected $basePath = "storage/smartcars/exercises";

    protected $disk = 'public';
}
