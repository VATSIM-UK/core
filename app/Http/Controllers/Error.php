<?php

namespace App\Http\Controllers;

use View;

class Error extends \App\Http\Controllers\BaseController
{
    public function getDisplay($code)
    {
        if (View::exists('error.'.$code)) {
            return $this->viewMake('error.'.$code);
        }

        return $this->viewMake('error.default');
    }
}
