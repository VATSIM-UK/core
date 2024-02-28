<?php

namespace App\Models\Atc;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Endorseable
{
    public function name(): Attribute;

    public function description(): Attribute;
}
