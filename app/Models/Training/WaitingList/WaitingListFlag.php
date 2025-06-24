<?php

namespace App\Models\Training\WaitingList;

use Database\Factories\Training\WaitingList\WaitingListFlagFactory;
use App\Models\Atc\PositionGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListFlag extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'training_waiting_list_flags';

    protected $casts = [
        'display_in_table' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (self $flag) {
            $flag->waitingListAccounts()->detach();
        });
    }

    public function waitingListAccounts()
    {
        return $this->belongsToMany(
            WaitingListAccount::class,
            'training_waiting_list_account_flag',
            'flag_id',
            'waiting_list_account_id'
        )->withPivot(['marked_at'])->using(WaitingListAccountFlag::class);
    }

    public function positionGroup()
    {
        return $this->belongsTo(PositionGroup::class);
    }
}
