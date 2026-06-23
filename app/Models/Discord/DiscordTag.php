<?php

namespace App\Models\Discord;

use App\Observers\DiscordTagObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([DiscordTagObserver::class])]
class DiscordTag extends Model
{
    use HasFactory;

    protected $table = 'discord_tags';

    protected $fillable = [
        'key',
        'title',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'key' => 'string',
            'title' => 'string',
            'value' => 'string',
        ];
    }
}
