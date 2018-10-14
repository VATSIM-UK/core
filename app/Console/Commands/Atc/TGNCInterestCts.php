<?php

namespace App\Console\Commands\Atc;
use Illuminate\Support\Facades\DB;

class TGNCInterestCts
{
    public static function getUsers()
    {
        $results = DB::connection('cts')->select("CALL TGNCInterest()");

        $results = collect($results)->each(function ($item, $key) {
            // flatten the collection
        });

        return $results;
    }
}
