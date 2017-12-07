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

    /**
     * @param $id
     * @return FlightImage|null
     */
    public static function find($id)
    {
        $image = null;

        $flightImages = Storage::drive('public')->files('smartcars/exercises');
        foreach ($flightImages as $flightImage) {
            if (starts_with($flightImage, "smartcars/exercises/$id.")) {
                $image = $flightImage;
                break;
            }
        }

        if ($image) {
            return new self($image);
        }

        return null;
    }

    public function asset()
    {
        return asset("storage/$this->path");
    }
}
