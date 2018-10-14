<?php

namespace App\Console\Commands\Atc;

use Illuminate\Support\Facades\DB;

class TGNCInterestCts
{
    public function getUsers()
    {
        $results = DB::connection('cts')->select('CALL TGNCInterest()');

        return array_pluck($results, 'cid');
    }
}
