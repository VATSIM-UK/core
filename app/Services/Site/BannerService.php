<?php

namespace App\Services\Site;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BannerService
{
    public function generateBannerUrl(): string
    {
        $key = 'CORE_BANNER_URL';

        if ($url = Cache::get($key)) {
            return $url;
        }

        $timeSegment = $this->resolveTimeSegment(Carbon::now());

        $dir = public_path('images/banner/'.$timeSegment);
        $images = $this->loadImages($dir);
        if ($images === []) {
            return $this->fallbackBannerUrl();
        }

        $url = asset("images/banner/$timeSegment/".$images[array_rand($images)]);
        Cache::put($key, $url, 60 * 60);

        return $url;
    }


    /**
     * @return array<int, string>
     */
    private function loadImages(string $directory): array
    {
        return array_values(array_diff(scandir($directory), ['.', '..']));
    }

    private function fallbackBannerUrl(): string
    {
        return asset('images/banner/fallback.jpg');
    }

    private function resolveTimeSegment(Carbon $time): string
    {
        return match (true) {
            $time->hour < 7 => 'night',
            $time->hour < 9 => 'morning',
            $time->hour < 17 => 'day',
            $time->hour < 21 => 'evening',
            default => 'night',
        };
    }
}
