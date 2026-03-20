<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\VatsimLoginService;
use Tests\TestCase;

class VatsimLoginServiceTest extends TestCase
{
    public function test_has_required_resource_owner_fields_accepts_boolean_true_token_valid(): void
    {
        $service = new VatsimLoginService;

        $resourceOwner = (object) [
            'data' => (object) [
                'cid' => 1234567,
                'personal' => (object) [
                    'name_first' => 'Test',
                    'name_last' => 'User',
                    'email' => 'test@example.com',
                ],
                'vatsim' => (object) [
                    'rating' => (object) ['id' => 1],
                    'pilotrating' => (object) ['id' => 0],
                    'division' => (object) ['id' => 'GBR'],
                    'region' => (object) ['id' => 'EUR'],
                ],
                'oauth' => (object) [
                    'token_valid' => true,
                ],
            ],
        ];

        $this->assertTrue($service->hasRequiredResourceOwnerFields($resourceOwner));
    }

    public function test_has_required_resource_owner_fields_accepts_string_true_token_valid(): void
    {
        $service = new VatsimLoginService;

        $resourceOwner = (object) [
            'data' => (object) [
                'cid' => 1234567,
                'personal' => (object) [
                    'name_first' => 'Test',
                    'name_last' => 'User',
                    'email' => 'test@example.com',
                ],
                'vatsim' => (object) [
                    'rating' => (object) ['id' => 1],
                    'pilotrating' => (object) ['id' => 0],
                    'division' => (object) ['id' => 'GBR'],
                    'region' => (object) ['id' => 'EUR'],
                ],
                'oauth' => (object) [
                    'token_valid' => 'true',
                ],
            ],
        ];

        $this->assertTrue($service->hasRequiredResourceOwnerFields($resourceOwner));
    }
}
