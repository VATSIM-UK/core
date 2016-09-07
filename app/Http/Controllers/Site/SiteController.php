<?php

namespace App\Http\Controllers\Site;

use Auth;
use Session;
use Route;
use View;
use App\Models\Mship\Account;
use Request;

class SiteController extends \App\Http\Controllers\BaseController
{
    protected $_template = "sitev2";
}
