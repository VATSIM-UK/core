<?php

namespace Tests\Unit;

use App\Libraries\UKCP;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
                ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], true));
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
                    new \GuzzleHttp\Psr7\Response(200, [], json_encode([
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
}
