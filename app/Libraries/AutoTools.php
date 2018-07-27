<?php

namespace App\Libraries;

use Bugsnag;
use Cache;
use Exception;
use League\Csv\Reader;

class AutoTools
{
    protected static $base_url = 'https://cert.vatsim.net/vatsimnet/admin/';

    public static function getDivisionData($withTimestamp = true)
    {
        $sprintUrl = '%sdivdbfullwpilot.php?authid=%s&authpassword=%s&div=%s';

        if ($withTimestamp) {
            $sprintUrl .= '&timestamp='.(time() - 60 * 119);
        }

        $url = sprintf(
            $sprintUrl,
            self::$base_url,
            env('VATSIM_CERT_AT_USER'),
            urlencode(env('VATSIM_CERT_AT_PASS')),
            env('VATSIM_CERT_AT_DIV')
        );

        $cacheName = $withTimestamp ? 'autotools_divdbfullwpilot_timestamp' : 'autotools_dividbfullwpilot_full';
        $cacheLength = $withTimestamp ? 118 : 60 * 12;

        return Cache::remember($cacheName, $cacheLength, function () use ($url) {
            try {
                $data = file_get_contents($url);
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Name or service not known') !== false) {
                    // CERT unavailable. Not our fault, so will ignore.
                    return collect();
                }
                Bugsnag::notifyException($e);

                return collect();
            }

            \Storage::put('autotools'.DIRECTORY_SEPARATOR.'divdbfullwpilot.csv', $data);

            $reader = Reader::createFromPath(storage_path('app'.DIRECTORY_SEPARATOR.'autotools'.DIRECTORY_SEPARATOR.'divdbfullwpilot.csv'), 'r');

            $keys = [
                'cid', 'rating_atc', 'rating_pilot',
                'name_first', 'name_last', 'email',
                'age_band', 'city', 'country', 'experience',
                'unknown', 'reg_date', 'region', 'division',
            ];
            $results = $reader->fetchAssoc($keys);

            $memberCollection = collect();

            foreach ($results as $r) {
                $memberCollection->push($r);
            }

            return $memberCollection;
        });
    }
}
