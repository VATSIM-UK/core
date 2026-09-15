<?php

namespace App\Models\Training\WaitingList;

use App\Models\Atc\PositionGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListFlag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'position_group_id',
        'moodle_course_idnumber',
        'display_in_table',
    ];

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

    public function isManual(): bool
    {
        return is_null($this->position_group_id) && is_null($this->moodle_course_idnumber);
    }
}
