<?php

namespace App\Console\Commands\Development;

use Illuminate\Console\Command;

class SuperSeeder extends Command
{
    protected $signature = 'db:super-seed';

    protected $description = 'Seends all the table with realistic-looking data for development purposes.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting super seeder...');
    }
}
