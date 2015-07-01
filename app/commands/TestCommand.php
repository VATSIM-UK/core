<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account;
use Models\Mship\Account\Email;
use Models\Mship\Account\State;
use Models\Mship\Qualification as QualificationData;
use Models\Mship\Account\Qualification;
use Models\Staff\Position;

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
        $position = Position::find(1);
        var_dump($position->parent);
        //echo $position . PHP_EOL;
        //echo $position->parent . PHP_EOL;
        //echo $position->children . PHP_EOL;
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
