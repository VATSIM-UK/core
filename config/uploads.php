<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Uploads Disk
    |--------------------------------------------------------------------------
    |
    | Every upload is stored on this disk. It is configured in
    | config/filesystems.php (S3 by default; set UPLOADS_DRIVER=local for
    | development) and is deliberately not configurable per category.
    |
    */

    'disk' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | Upload Path
    |--------------------------------------------------------------------------
    |
    | All objects are written under this single key prefix. The ULID in the
    | filename keeps them unique; category and owner live on the database
    | row rather than the key.
    |
    */

    'path' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | Default File Size Limit
    |--------------------------------------------------------------------------
    |
    | Used when a category does not define its own limit. Categories are
    | stored in the database, so this is only a fallback.
    |
    */

    'max_file_size_kb' => (int) env('UPLOADS_MAX_FILE_SIZE_KB', 10240),

    /*
    |--------------------------------------------------------------------------
    | Temporary URL Lifetime
    |--------------------------------------------------------------------------
    |
    | How long a presigned S3 URL handed to a browser remains valid. The
    | application route stays stable; only the redirect target expires.
    |
    */

    'temporary_url_ttl_minutes' => (int) env('UPLOADS_TEMP_URL_TTL_MINUTES', 5),

];
