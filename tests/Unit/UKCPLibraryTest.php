<?php

namespace Tests\Unit;

use App\Libraries\UKCP;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class UKCPLibraryTest extends TestCase
{
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
}
