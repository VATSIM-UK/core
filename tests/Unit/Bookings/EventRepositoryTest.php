<?php

namespace Tests\Unit\Bookings;

use App\Models\Cts\Booking;
use App\Models\Cts\Event;
use App\Repositories\Cts\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EventRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private $repository;

    protected function setUp():void
    {
        parent::setUp();

        $this->repository = resolve(EventRepository::class);
    }

    /** @test */
    public function itCanReturnTodaysEvents()
    {
        factory(Event::class, 2)->create(['date' => Carbon::now()->addDays(5)->toDateString()]);

        $eventTodayOne = factory(Event::class)->create([
            'date' => Carbon::now()->toDateString(),
            'from' => Carbon::now()->toTimeString(),
        ]);

        $eventTodayTwo = factory(Event::class)->create([
            'date' => Carbon::now()->toDateString(),
            'from' => Carbon::now()->addHour()->toTimeString(),
        ]);

        $events = $this->repository->getTodaysEvents();

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertCount(2, $events);
        $this->assertEquals($events->first()->event, $eventTodayOne->event);
        $this->assertEquals($events->last()->event, $eventTodayTwo->event);
    }
}
