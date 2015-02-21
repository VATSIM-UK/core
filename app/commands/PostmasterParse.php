<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Sys\Postmaster\Queue;
use \Cache;

class PostmasterParse extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Postmaster:Parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse any new, queued emails.';

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
        // Get all new, queued emails.
        $newQueued = Queue::pending()->orderBy("priority", "DESC")->orderBy("updated_at", "ASC")->limit(100)->get();

        foreach($newQueued as $q){
            $q->parseAndSave();
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
