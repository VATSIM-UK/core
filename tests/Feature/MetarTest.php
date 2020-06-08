<?php

use Illuminate\Support\Facades\Http;

class MetarTest extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
    }

    public function testItReturnsMetar()
    {
        $metar = 'EGKK 122050Z 21004KT CAVOK 09/02 Q1017';
        Http::fake([
            'http://metar.vatsim.net/*' => Http::response($metar, 200),
        ]);

        $response = $this->get(route('api.metar', ['EGKK']))->content();
        $this->assertEquals($metar, $response);
    }

    public function testItHandlesDowntimeGracefully()
    {
        Http::fake([
            'http://metar.vatsim.net/*' => Http::response('', 502),
        ]);

        $response = $this->get(route('api.metar', ['EGKK']))->content();
        $this->assertEquals('METAR UNAVAILABLE', $response);
    }
}
