<?php

namespace Tests\Feature\API;

use App\Services\Api\DTO\ApiServiceResult;
use App\Services\Api\PositionValidationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidationTest extends TestCase
{
#[Test]
public function it_delegates_validation_to_service_with_supplied_position(): void
{
    $this->mock(PositionValidationService::class, function ($mock) {
        $mock->shouldReceive('validatePosition')
            ->once()
            ->with('EGLL_TWR')
            ->andReturn(new ApiServiceResult(200, [
                'status' => ['position' => 'Heathrow Tower'],
                'validated_members' => [
                    ['id' => 1234567],
                ],
            ]));
    });

    $this->getJson(route('api.validations', ['position' => 'EGLL_TWR']))
        ->assertOk()
        ->assertExactJson([
            'status' => ['position' => 'Heathrow Tower'],
            'validated_members' => [
                ['id' => 1234567],
            ],
        ]);
}

#[Test]
public function it_returns400_when_service_reports_missing_position(): void
{
    $this->mock(PositionValidationService::class, function ($mock) {
        $mock->shouldReceive('validatePosition')
            ->once()
            ->with(null)
            ->andReturn(new ApiServiceResult(400, [
                'status' => '400',
                'message' => 'No position was supplied.',
            ]));
    });

    $this->getJson(route('api.validations'))
        ->assertStatus(400)
        ->assertExactJson([
            'status' => '400',
            'message' => 'No position was supplied.',
        ]);
}

#[Test]
public function it_returns404_when_service_reports_unknown_position(): void
{
    $this->mock(PositionValidationService::class, function ($mock) {
        $mock->shouldReceive('validatePosition')
            ->once()
            ->with('EGXX_TWR')
            ->andReturn(new ApiServiceResult(404, [
                'status' => '404',
                'message' => 'Position not found.',
            ]));
    });

    $this->getJson(route('api.validations', ['position' => 'EGXX_TWR']))
        ->assertStatus(404)
        ->assertExactJson([
            'status' => '404',
            'message' => 'Position not found.',
        ]);
}
}
