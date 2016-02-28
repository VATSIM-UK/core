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
            \Storage::put('app/autotools/divdbfullwpilot.csv', file_get_contents($url));

            $reader = Reader::createFromPath(storage_path("app/autotools/divdbfullwpilot.csv"));

            $keys = [
                "cid", "rating_atc", "rating_pilot",
                "name_first", "name_last", "email",
                "age_band", "city", "country", "experience",
                "unknown", "reg_date", "region", "division",
            ];
            $results = $reader->fetchAssoc($keys);

            $memberCollection = collect();

            foreach($results as $r){
                $memberCollection->push($r);
            }

            return $memberCollection;
        });
    }
}
