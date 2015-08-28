<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\State;
use App\Models\Mship\Qualification as QualificationData;
use App\Models\Mship\Account\Qualification;
use App\Models\Staff\Position;

class TestCommand extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'TC';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test area for Anthony.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        //
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
        );
    }

}
