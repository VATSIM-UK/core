<?php

namespace App\Libraries;

class UKCP
{

    /** @var string */
    private $apiKey;

    /**
     * UKCP constructor.
     */
    public function __construct()
    {
        $this->apiKey = config('services.ukcp.key');
    }
}
