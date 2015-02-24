<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Sys\Postmaster\Queue;
use \Cache;

class PostmasterDispatch extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Postmaster:Dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch any parsed, queued emails.';

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
        // Get all parsed, queued emails.
        $unsent = Queue::parsed()->orderBy("priority", "DESC")->orderBy("updated_at", "ASC")->limit(25)->get();

        foreach($unsent as $q){
            $q->dispatch();
        }
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
