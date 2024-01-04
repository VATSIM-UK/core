<?php

namespace App\Models;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Roster
 *
 * @property-read Account|null $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Roster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Roster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Roster query()
 *
 * @mixin \Eloquent
 */
class Roster extends Model
{
    use HasFactory;

    protected $table = 'roster';

    protected static function booted(): void
    {
        // Only return users that are on the roster
        // and are still within the UK.
        static::addGlobalScope('eligibleState', function (Builder $builder) {
            $builder->whereHas('account', function ($query) {
                $query->whereHas('states', function ($query) {
                    $query->whereIn('mship_state.code', ['DIVISION', 'VISITING', 'TRANSFERRING']);
                });
            });
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function remove()
    {
        // Notify that they were removed (database and email)
        $this->delete();
    }
}
