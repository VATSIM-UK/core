<?php

    namespace Tests\Unit\Airport;

    use App\Models\Airport;
    use App\Models\Airport\Navaid;
    use Tests\TestCase;
    use Illuminate\Foundation\Testing\WithFaker;
    use Illuminate\Foundation\Testing\RefreshDatabase;

    class NavaidTest extends TestCase
    {
        use RefreshDatabase;

        /** @test */
        public function itCanCreateANewNavaid()
        {
            $navaid = factory(Navaid::class)->create();
            $this->assertInstanceOf(Navaid::class, $navaid);
            $this->assertNotNull(Navaid::find($navaid->id));
        }

        /** @test */
        public function itReturnsNavaidType()
        {
            $navaid = factory(Navaid::class)->create(['type' => Navaid::TYPE_ILS]);
            $this->assertEquals("ILS", $navaid->type);
        }

        /** @test */
        public function itHasWorkingAirportRelationship()
        {
            $navaid = factory(Navaid::class)->create();
            $this->assertInstanceOf(Airport::class, $navaid->airport);
        }
    }
