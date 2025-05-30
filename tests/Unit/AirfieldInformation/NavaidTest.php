<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Airport\Navaid;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NavaidTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_new_navaid()
    {
        $navaid = factory(Navaid::class)->create();
        $this->assertInstanceOf(Navaid::class, $navaid);
        $this->assertInstanceOf(Navaid::class, Navaid::find($navaid->id));
    }

    #[Test]
    public function it_has_working_airport_relationship()
    {
        $navaid = factory(Navaid::class)->create();
        $this->assertInstanceOf(Airport::class, $navaid->airport);
    }

    #[Test]
    public function it_returns_navaid_type()
    {
        $navaid = factory(Navaid::class)->create(['type' => Navaid::TYPE_ILS]);
        $this->assertEquals('ILS', $navaid->type);
    }

    #[Test]
    public function it_returns_frequency_type()
    {
        $navaid = factory(Navaid::class)->create(['frequency_band' => Navaid::FREQUENCY_BAND_KHZ]);
        $this->assertEquals('KHz', $navaid->frequency_band);
    }
}
