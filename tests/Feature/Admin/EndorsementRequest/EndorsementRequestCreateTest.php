<?php

namespace Tests\Feature\Admin\EndorsementRequest;

use Tests\TestCase;

class EndorsementRequestCreateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
