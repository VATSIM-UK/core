<?php

namespace App\Http\Controllers\Site;

use App\Services\Site\MetarService;

class MetarController
{
    public function __construct(private MetarService $metarService) {}

    public function get($airportIcao)
    {
        return $this->metarService->get((string) $airportIcao);
    }
}
