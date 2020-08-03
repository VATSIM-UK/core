<?php

namespace App\Models\Discord;

use Illuminate\Database\Eloquent\Model;

class DiscordRole extends Model
{
    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'int',
    ];
}
