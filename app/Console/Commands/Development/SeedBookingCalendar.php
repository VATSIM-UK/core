<?php

declare(strict_types=1);

namespace App\Console\Commands\Development;

use Database\Seeders\BookingCalendarSeeder;
use Illuminate\Console\Command;

class SeedBookingCalendar extends Command
{
    protected $signature = 'bookings:seed-calendar {--user= : CID of the existing account that will own the seeded bookings}';

    protected $description = 'Seed standard bookings for today, owned by an existing account, to exercise the bookings calendar locally';

    public function handle(): int
    {
        $cid = $this->option('user');

        if (! $cid) {
            $this->error('The --user option is required (CID of the account that will own the seeded bookings).');

            return self::FAILURE;
        }

        (new BookingCalendarSeeder)
            ->setContainer($this->laravel)
            ->setCommand($this)
            ->run((int) $cid);

        return self::SUCCESS;
    }
}
