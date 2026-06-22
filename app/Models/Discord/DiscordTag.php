<?php

namespace App\Models\Discord;

use Illuminate\Database\Eloquent\Model;

class DiscordTag extends Model
{
    protected $table = 'discord_tags';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'key' => 'string',
            'value' => 'string',
        ];
    }
}
