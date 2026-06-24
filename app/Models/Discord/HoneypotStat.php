<?php

namespace App\Models\Discord;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Discord\HoneypotStat.
 *
 * @property int $id
 * @property string $account_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Discord\HoneypotStat whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Discord\HoneypotStat whereId($value)
 *
 * @mixin \Eloquent
 */
class HoneypotStat extends Model
{
    use HasFactory;

    protected $table = 'honeypot_stats';

    protected $fillable = ['account_id'];

    public $timestamps = false;
}
