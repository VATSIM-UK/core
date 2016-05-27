<?php

namespace App\Libraries;

use TeamSpeak3 as TSAPI;

class Teamspeak
{
    public static function run($nickname = 'VATSIM UK TeamSpeak Bot')
    {
        return TSAPI::factory(
            sprintf(
                'serverquery://%s:%s@%s:%s/?nickname=%s&server_port=%s#no_query_clients',
                env('TS_USER'),
                env('TS_PASS'),
                env('TS_HOST'),
                env('TS_QUERY_PORT'),
                urlencode($nickname),
                env('TS_PORT')
            )
        );
    }
}
