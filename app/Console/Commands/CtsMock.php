<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tests\Database\MockCtsDatabase;

class CtsMock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cts:migrate:fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mock a fresh instance of the CTS database.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        MockCtsDatabase::destroy();
        MockCtsDatabase::create();
    }
}
