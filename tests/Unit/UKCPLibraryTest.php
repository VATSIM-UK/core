<?php

namespace Tests\Unit;

use App\Libraries\UKCP;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UKCPLibraryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now()->addMinutes(30));
    }

    protected function tearDown(): void
    {
        Cache::forget('UKCP_STAND_STATUS_EGLL');
        parent::tearDown();
    }

    #[Test]
    public function it_can_delete_tokens()
    {
        $currentTokenID = '1234567891234abcd';

        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('delete')
                ->andReturn(new Response(200, [], true));
        })->makePartial();

        $ukcp = resolve(UKCP::class);
        $this->assertTrue($ukcp->deleteToken($currentTokenID, $this->user));
    }

    public function test_it_returns_cached_stand_status()
    {
        $ukcp = $this->app->get(UKCP::class);
        Cache::put('UKCP_STAND_STATUS_EGLL', ['stands' => ['foo' => 'bar']], 60);
        $this->assertEquals(['foo' => 'bar'], $ukcp->getStandStatus('EGLL'));
    }

    public function test_it_caches_sorted_stand_status()
    {
        $expiry = Carbon::now()->addMinutes(5);
        $this->mock(Client::class, function (MockInterface $mock) use ($expiry) {
            $mock->shouldReceive('get')
                ->with('https://ukcp.vatsim.uk/api/stand/status?airfield=EGLL', ['timeout' => 8])
                ->andReturn(
                    new Response(200, [], json_encode([
                        'refresh_at' => $expiry,
                        'stands' => [
                            [
                                'identifier' => '1',
                            ],
                            [
                                'identifier' => '12',
                            ],
                            [
                                'identifier' => '2',
                            ],
                        ],
                    ]))
                );
        });

        $expectedData = [
            'refresh_at' => $expiry,
            'stands' => [
                [
                    'identifier' => '1',
                ],
                [
                    'identifier' => '2',
                ],
                [
                    'identifier' => '12',
                ],
            ],
        ];

        $ukcp = $this->app->get(UKCP::class);
        $this->assertEquals($expectedData['stands'], $ukcp->getStandStatus('EGLL'));
        $this->assertEquals($expectedData, Cache::get('UKCP_STAND_STATUS_EGLL'));
    }

    public function test_it_returns_empty_if_client_throws()
    {
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->with('https://ukcp.vatsim.uk/api/stand/status?airfield=EGLL', ['timeout' => 8])
                ->andThrow(
                    new ClientException(
                        'Bang',
                        new Request('GET', 'https://ukcp.vatsim.uk/api/v1/stand/status?airfield=EGLL'),
                        new Response(500, [], 'Bang')
                    )
                );
        });

        $ukcp = $this->app->get(UKCP::class);
        $this->assertEquals([], $ukcp->getStandStatus('EGLL'));
        $this->assertNull(Cache::get('UKCP_STAND_STATUS_EGLL'));
    }

    #[Test]
    public function it_can_fetch_controller_positions_v2_dependency(): void
    {
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->with('https://ukcp.vatsim.uk/api/controller-positions-v2', [
                    'headers' => [
                        'Authorization' => 'Bearer '.config('services.ukcp.key'),
                    ],
                    'timeout' => 15,
                ])
                ->andReturn(
                    new Response(200, [], json_encode([
                        [
                            'id' => 1,
                            'callsign' => 'LON_S_CTR',
                            'frequency' => 129.425,
                            'top_down' => ['EGLL', 'EGKK', 'EGLF'],
                        ],
                        [
                            'id' => 2,
                            'callsign' => 'EGLL_TWR',
                            'frequency' => 118.500,
                            'top_down' => [],
                        ],
                        [
                            'id' => 3,
                            'callsign' => 'LON_CTR',
                            'frequency' => 127.830,
                            'top_down' => ['EGLL', 'EGKK', 'EGCC', 'EGBB'],
                        ],
                    ]))
                );
        });

        $ukcp = $this->app->get(UKCP::class);
        $positions = $ukcp->getControllerPositionsV2Dependency();

        $this->assertInstanceOf(Collection::class, $positions);
        $this->assertCount(3, $positions);

        $lonS = $positions->firstWhere('id', 1);
        $this->assertEquals('LON_S_CTR', $lonS->callsign);
        $this->assertEquals(129.425, $lonS->frequency);
        $this->assertEquals(['EGLL', 'EGKK', 'EGLF'], $lonS->top_down);

        $egllTwr = $positions->firstWhere('id', 2);
        $this->assertEquals('EGLL_TWR', $egllTwr->callsign);
        $this->assertEquals([], $egllTwr->top_down);
    }
}
