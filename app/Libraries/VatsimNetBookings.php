<?php

declare(strict_types=1);

namespace App\Libraries;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatsimNetBookings
{
    private string $url;

    private string $key;

    public function __construct()
    {
        $this->url = (string) config('services.vatsim-net.bookings.url');
        $this->key = (string) config('services.vatsim-net.bookings.key');
    }

    public function create(array $payload): int
    {
        $response = $this->client()->post('booking', $payload);

        $this->logFailure('create', $response->status(), $response->body());

        $response->throw();

        $id = (int) $response->json('id');

        if ($id <= 0) {
            throw new \RuntimeException('VATSIM.net booking create returned an invalid id.');
        }

        return $id;
    }

    public function update(int $remoteId, array $payload): void
    {
        $response = $this->client()->put("booking/{$remoteId}", $payload);

        $this->logFailure('update', $response->status(), $response->body());

        $response->throw();
    }

    public function delete(int $remoteId): void
    {
        $response = $this->client()->delete("booking/{$remoteId}");

        $this->logFailure('delete', $response->status(), $response->body());

        $response->throw();
    }

    private function client()
    {
        return Http::baseUrl($this->url)
            ->withToken($this->key)
            ->acceptJson()
            ->asJson();
    }

    private function logFailure(string $method, int $status, string $body): void
    {
        if ($status >= 400) {
            Log::warning('VATSIM.net booking request failed', [
                'method' => $method,
                'status' => $status,
                'body' => $body,
            ]);
        }
    }
}
