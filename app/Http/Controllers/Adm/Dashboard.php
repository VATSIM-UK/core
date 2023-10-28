<?php

namespace App\Http\Controllers\Adm;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Email as AccountEmail;
use Cache;
use Illuminate\Support\Facades\Request;
use Redirect;

class Dashboard extends \App\Http\Controllers\Adm\AdmController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->viewMake('adm.dashboard');
    }
}
