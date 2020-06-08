<?php

namespace App\Policies;

use Session;

class Policy
{
    protected function allow()
    {
        return true;
    }

    protected function deny($reason = null)
    {
        if (! is_null($reason)) {
            Session::flash('authorization.error', $reason);
        }

        return false;
    }
}
