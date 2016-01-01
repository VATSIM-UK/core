<?php

namespace App\Libraries;

use League\Csv\Reader;

class AutoTools
{
    protected $base_url = 'https://cert.vatsim.net/vatsimnet/admin/';

    public static function getDivisionData()
    {
        $url = sprintf(
            '%sdivdbfullwpilot.php?authid=%s&authpassword=%s&div=%s',
            $base_url,
            env('VATSIM_CERT_AT_USER'),
            urlencode(env('VATSIM_CERT_AT_PASS')),
            env('VATSIM_CERT_AT_DIV')
        );

        return Reader::createFromString(file_get_contents($url));
    }
}