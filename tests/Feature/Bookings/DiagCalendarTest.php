<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiagCalendarTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function diagnostic_mount_directly(): void
    {
        try {
            $html = app('livewire')->mount(Calendar::class, []);
            fwrite(STDERR, 'MOUNT OK len='.strlen($html)."\n");
            fwrite(STDERR, "has 'Bookings Calendar': ".(str_contains($html, 'Bookings Calendar') ? 'yes' : 'no')."\n");
            fwrite(STDERR, "has 'Woopsie': ".(str_contains($html, 'Woopsie') ? 'yes' : 'no')."\n");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'MOUNT EXCEPTION: '.get_class($e).': '.$e->getMessage()."\n");
            fwrite(STDERR, '  at '.str_replace("\n", "\n  at ", $e->getTraceAsString())."\n");
        }

        $this->assertTrue(true);
    }
}
