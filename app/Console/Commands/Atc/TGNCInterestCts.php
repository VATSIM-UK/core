<?php

namespace App\Console\Commands\Atc;

use Illuminate\Support\Facades\DB;

class TGNCInterestCts
{
    public static function getUsers()
    {
        $results = DB::connection('cts')->select('CALL TGNCInterest()');

        return array_pluck($results, 'cid');
    }
}
