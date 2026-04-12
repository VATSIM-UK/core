<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrackAudioController

{
    public function getLatest(Request $request)
    {
        $latestVersion = Cache::get('trackaudio_latest_version');

        if ($latestVersion == null) {
            $latestVersion = $this->fetchLatestVersion();

            if ($latestVersion) {
                Cache::put('trackaudio_latest_version', $latestVersion, now()->addHours(24));
            }
        }

        if ($latestVersion) {
            return new RedirectResponse("https://github.com/pierr3/TrackAudio/releases/tag/{$latestVersion}");
        } else {
            return new RedirectResponse("https://github.com/pierr3/TrackAudio/releases");
        }

    }

    private function fetchLatestVersion()
    {
        try {
            $response = Http::get('https://raw.githubusercontent.com/pierr3/TrackAudio/refs/heads/main/MANDATORY_VERSION');

            if (! $response->successful()) {
                return null;
            }

            return trim($response->body());

        } catch (\Exception $e) {
            \Log::error('Failed to fetch latest TrackAudio version: '.$e->getMessage());

            return null;
        }
    }
}
