<?php

namespace App\Notifications\Contracts;

use App\Enums\EmailType;

interface HasEmailType
{
    public function getEmailType(): EmailType;
}
