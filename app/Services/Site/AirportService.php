<?php

namespace App\Services\Site;

use App\Libraries\UKCP;
use App\Models\Airport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AirportService
{
    public function __construct(private UKCP $ukcp) {}

    public function getAirportIndex(): Collection
    {
        return Airport::uk()->orderBy('name')->get()->split(2);
    }

    public function getStandStatus(Airport $airport): mixed
    {
        return $this->ukcp->getStandStatus(Str::upper($airport->icao));
    }
}
