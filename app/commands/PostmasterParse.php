<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Sys\Postmaster\Queue;
use \Cache;
use Models\Sys\Timeline\Entry;

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
        $newQueued = Queue::pending()
                          ->orderBy("priority", "DESC")
                          ->orderBy("updated_at", "ASC")
                          ->limit($this->argument("number_to_process"))
                          ->get();

        foreach($newQueued as $q){
            try {
                $q->parseAndSave();
                print "Parsed, ".$q->postmaster_queue_id;
            } catch(Exception $e){
                $q->status = Queue::STATUS_PARSE_ERROR;
                $q->save();

                Entry::log("SYS_POSTMASTER_ERROR_PARSE", $q->recipient, $q, $e);
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array("number_to_process", InputArgument::OPTIONAL, "The number of emails to process in a single run.", 100),
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
