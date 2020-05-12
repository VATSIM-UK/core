<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class MetarTest extends \Tests\TestCase
{
    public function testItReturnsMetar()
    {
        $metar = 'EGKK 122050Z 21004KT CAVOK 09/02 Q1017';
        Http::fake([
            'http://metar.vatsim.net/metar.php*' => Http::response($metar, 200)
        ]);

        $response = $this->get(route('api.metar', ['EGKK']))->content();
        $this->assertEquals($metar, $response);
    }

    public function testItHandlesDowntimeGracefully()
    {
        Http::fake([
            'http://metar.vatsim.net/metar.php*' => Http::response('', 502)
        ]);

        $response = $this->get(route('api.metar', ['EGKK']))->content();
        $this->assertEquals('METAR UNAVAILABLE', $response);
    }
}
