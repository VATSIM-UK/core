<?php

namespace Tests\Unit;

use App\Libraries\UKCP;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Hamcrest\Core\IsEqual;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class UKCPLibraryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now()->addMinutes(30));
    }

    /** @test */
    public function itCanCreateAToken()
    {
        $token = json_encode([
            'api-url' => 'http://awebaddress.test',
            'api-key' => '1234',
        ]);

        $this->mock(Client::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('get')
                ->andReturn(
                    new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                        'id' => $this->user->id,
                        'tokens' => [],
                    ])),
                    new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                        'id' => $this->user->id,
                        'tokens' => [
                            ['id' => '1234abc', 'revoked' => false],
                        ],
                    ])));

            $mock->shouldReceive('post')
                ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], $token));
        })->makePartial();

        Storage::fake('local');

        $ukcp = resolve(UKCP::class);
        $ukcp->createTokenFor($this->user);

        Storage::disk('local')->assertExists(UKCP::getPathForToken('1234abc', $this->user));
    }

    /** @test */
    public function itCanDeleteTokens()
    {
        $currentTokenID = '1234567891234abcd';

        // Put in a fake existing token
        Storage::fake('local');
        Storage::disk('local')->put(UKCP::getPathForToken($currentTokenID, $this->user), '');

        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('delete')
                ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], true));
        })->makePartial();

        $ukcp = resolve(UKCP::class);
        $ukcp->deleteToken($currentTokenID, $this->user);

        Storage::disk('local')->assertMissing(UKCP::getPathForToken($currentTokenID, $this->user));
    }

    public function testItReturnsCachedStandStatus()
    {
        $ukcp = $this->app->get(UKCP::class);
        Cache::put('UKCP_STAND_STATUS_EGLL', ['foo' => 'bar'], 60);
        $this->assertEquals(['foo' => 'bar'], $ukcp->getStandStatus('EGLL'));
    }

    public function testItCachesSortedStandStatus()
    {
        $now = Carbon::now();
        $this->mock(Client::class, function (MockInterface $mock) use ($now) {
            $mock->shouldReceive('get')
                ->with('https://ukcp.vatsim.uk/api/stand/status?airfield=EGLL')
                ->andReturn(
                    new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                        'refresh_at' => $now,
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
            [
                'identifier' => '1',
            ],
            [
                'identifier' => '2',
            ],
            [
                'identifier' => '12',
            ],
        ];

        Cache::shouldReceive('get')
            ->andReturn(null);
        Cache::shouldReceive('put')
            ->with('UKCP_STAND_STATUS_EGLL', $expectedData, IsEqual::equalTo($now))
            ->once();

        $ukcp = $this->app->get(UKCP::class);
        $this->assertEquals($expectedData, $ukcp->getStandStatus('EGLL'));
    }

    public function testItReturnsEmptyIfClientThrows()
    {
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->with('https://ukcp.vatsim.uk/api/stand/status?airfield=EGLL')
                ->andThrow(
                    new ClientException(
                        'Bang',
                        new Request('GET', 'https://ukcp.vatsim.uk/api/v1/stand/status?airfield=EGLL'),
                        new Response(500, [], 'Bang')
                    )
                );
        });

        Cache::shouldReceive('get')
            ->andReturn(null);
        Cache::shouldReceive('put')
            ->never();
        $ukcp = $this->app->get(UKCP::class);
        $this->assertEquals([], $ukcp->getStandStatus('EGLL'));
    }
}
