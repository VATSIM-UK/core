<?php

use Illuminate\Console\Command;
use \Session;

class aCommand extends Command {
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        Session::set("auth_adm_account", 707070);

        parent::__construct();
    }
}
