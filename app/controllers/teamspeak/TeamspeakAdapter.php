<?php

namespace Controllers\Teamspeak;

use TeamSpeak3 as TSAPI;

class TeamspeakAdapter {

    public static function run() {
        return TSAPI::factory("serverquery://".$_ENV['ts.user'].":".$_ENV['ts.pass']
            ."@".$_ENV['ts.host'].":".$_ENV['ts.query.port']."/?nickname=".urlencode($_ENV['ts.nickname'])."&server_port=".$_ENV['ts.port']."#no_query_clients");
    }

}