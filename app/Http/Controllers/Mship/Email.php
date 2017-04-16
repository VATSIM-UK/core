<?php

namespace App\Http\Controllers\Mship;

use App\Models\Mship\Account;
use DB;
use App\Models\Mship\State;

class Email extends \App\Http\Controllers\BaseController
{
    public function getEmail()
    {
        return $this->viewMake('mship.email');
    }
}
