<?php

namespace App\Models\Smartcars;

use Storage;

class FlightImage
{
    protected $path;

    public function __construct($imagePath = null)
    {
        $this->path = $imagePath;
    }

    /**
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
    }

    public function asset()
    {
        return asset("storage/$this->path");
    }

    public function delete()
    {
        Storage::disk('public')->delete($this->path);
    }
}
