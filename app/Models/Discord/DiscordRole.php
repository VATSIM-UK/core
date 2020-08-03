<?php

namespace App\Models\Discord;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class DiscordRole extends Model
{
    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'int',
    ];

    public function role()
    {
        $this->belongsTo(Permission::class);
    }
}
