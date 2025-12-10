<?php

namespace App\Console\Commands\Development;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
