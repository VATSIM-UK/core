<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrackAudioControllerTest extends Tests\TestCase
{
    public function test_it_redirects_to_cached_latest_trackaudio_release(): void
    {
        Cache::put('trackaudio_latest_version', '1.2.3', now()->addMinutes(5));

        $response = $this->get(route('api.trackaudio.latest'));

        $response->assertRedirect('https://github.com/pierr3/TrackAudio/releases/tag/1.2.3');
    }

    public function test_it_fetches_latest_trackaudio_release_and_redirects(): void
    {
        Cache::forget('trackaudio_latest_version');
        Http::fake([
            'raw.githubusercontent.com/*' => Http::response("2.0.1\n", 200),
        ]);

        $response = $this->get(route('api.trackaudio.latest'));

        $response->assertRedirect('https://github.com/pierr3/TrackAudio/releases/tag/2.0.1');
        $this->assertSame('2.0.1', Cache::get('trackaudio_latest_version'));
    }
}
