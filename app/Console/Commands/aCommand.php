<?php

use Illuminate\Console\Command;

class aCommand extends Command {
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        \Auth::loginUsingId(707070);

        parent::__construct();
    }
}
