<?php

namespace Controllers\Teamspeak;

use TeamSpeak3 as TSAPI;

class TeamspeakAdapter {

    public static function run() {
        return TSAPI::factory("serverquery://".$_ENV['ts.user'].":".$_ENV['ts.pass']."@".$_ENV['ts.host'].":".$_ENV['ts.query.port']."/?server_port=".$_ENV['ts.port']);
    }

}