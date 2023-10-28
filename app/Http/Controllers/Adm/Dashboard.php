<?php

namespace App\Http\Controllers\Adm;

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
