<?php

namespace App\Libraries\Storage;

class FteStorageWrapper extends StorageWrapper
{
    protected $basePath = "smartcars/exercises";

    protected static $disk = 'public';
}