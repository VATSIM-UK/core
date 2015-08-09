<?php

namespace Controllers\Teamspeak;

use TeamSpeak3 as TSAPI;

class TeamspeakAdapter {

    public static function run($nickname = 'VATSIM UK TeamSpeak Bot') {
        return TSAPI::factory(
            'serverquery://' . env('TS_USER') . ':' . env('TS_PASS')
            . '@' . env('TS_HOST') . ':' . env('TS_QUERY_PORT')
            . '/?nickname=' . urlencode($nickname)
            . '&server_port=' . env('TS_PORT') . '#no_query_clients'
        );
    }

}
