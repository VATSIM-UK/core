<?php

namespace App\Libraries;

use Cache;
use League\Csv\Reader;

class AutoTools
{
    protected static $base_url = 'https://cert.vatsim.net/vatsimnet/admin/';

    public static function getDivisionData()
    {
        $url = sprintf(
            '%sdivdbfullwpilot.php?authid=%s&authpassword=%s&div=%s',
            self::$base_url,
            env('VATSIM_CERT_AT_USER'),
            urlencode(env('VATSIM_CERT_AT_PASS')),
            env('VATSIM_CERT_AT_DIV')
        );

        return Cache::remember('autotools_divdbfullwpilot', 60*12, function() use ($url) {
            return json_decode(json_encode(Reader::createFromString(file_get_contents($url))));
        });
    }
}
