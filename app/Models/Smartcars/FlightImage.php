<?php

namespace App\Models\Smartcars;

use Illuminate\Support\Arr;
use Storage;

class FlightImage
{
    protected $path;

    public function __construct($imagePath = null)
    {
        $this->path = $imagePath;
    }

    public static function find($id)
    {
        $flightImages = Storage::drive('public')->files('smartcars/exercises');
        $imageMatches = preg_grep('/^smartcars\/exercises\/'.$id.'\.[A-Za-z0-9]*$/', $flightImages);

        $imagePath = Arr::first($imageMatches);
        if ($imagePath) {
            return new self($imagePath);
        }

        return null;
    }

    public function asset()
    {
        return asset("storage/$this->path");
    }
}
