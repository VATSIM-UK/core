<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MetarTest extends Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_returns_metar()
    {
        $metar = 'EGKK 122050Z 21004KT CAVOK 09/02 Q1017';
        Http::fake([
            'https://metar.vatsim.net/*' => Http::response($metar, 200),
        ]);

        $response = $this->get(route('api.metar', ['EGKK']));
        $this->assertEquals($metar, $response->content());
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    public function test_it_returns_plaintext_when_the_client_prefers_it()
    {
        $metar = 'EGKK 122050Z 21004KT CAVOK 09/02 Q1017';
        Http::fake([
            'https://metar.vatsim.net/*' => Http::response($metar, 200),
        ]);

        $response = $this->get(route('api.metar', ['EGKK']), ['Accept' => 'text/plain']);
        $this->assertEquals($metar, $response->content());
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    public function test_it_rejects_invalid_icao_codes_without_calling_upstream()
    {
        Http::fake();

        $this->get(route('api.metar', ['EGKKX']))->assertNotFound();
        $this->get(route('api.metar', ['1234']))->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_it_handles_downtime_gracefully()
    {
        Http::fake([
            'https://metar.vatsim.net/*' => Http::response('', 502),
        ]);

        $response = $this->get(route('api.metar', ['EGKK']))->content();
        $this->assertEquals('METAR UNAVAILABLE', $response);
    }
}
