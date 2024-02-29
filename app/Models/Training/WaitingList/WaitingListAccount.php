<?php

namespace App\Models\Training\WaitingList;

use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccount extends Pivot
{
    use SoftDeletes;

    public $table = 'training_waiting_list_account';

    public $fillable = ['added_by', 'deleted_at', 'notes', 'flags_status_summary'];

    protected $appends = ['theory_exam_passed'];

    protected $casts = [
        'flags_status_summary' => 'array',
    ];

    // 24 hours
    protected $cacheTtl = 86400;

    public function flags()
    {
        return $this->belongsToMany(
            WaitingListFlag::class,
            'training_waiting_list_account_flag',
            'waiting_list_account_id',
            'flag_id'
        )->withPivot(['marked_at', 'id'])->using(WaitingListAccountFlag::class);
    }

    public function waitingList()
    {
        return $this->belongsTo(WaitingList::class, 'list_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function addFlag(WaitingListFlag $listFlag, $value = null)
    {
        return $this->flags()->attach($listFlag, ['marked_at' => $value]);
    }

    /**
     * Mark a Flag as true.
     */
    public function markFlag(WaitingListFlag $listFlag)
    {
        // retrieve the pivot model of WaitingListAccountFlag
        $flag = $this->flags()->get()->find($listFlag)->pivot;

        $flag->mark();
    }

    public function unMarkFlag(WaitingListFlag $listFlag)
    {
        $flag = $this->flags()->get()->find($listFlag)->pivot;

        if (! $flag->value) {
            return;
        }

        $flag->unMark();
    }

    public function getCurrentStatusAttribute()
    {
        return $this->status()->first();
    }

    public function getPositionAttribute()
    {
        return $this->waitingList->accountPosition($this->account);
    }

    public function getAtcHourCheckAttribute()
    {
        return $this->atcHourCheck();
    }

    public function theoryExamPassed(): Attribute
    {
        $passed = false;

        if ($this->waitingList->department === WaitingList::ATC_DEPARTMENT) {
            try {
                $result = TheoryResult::forAccount($this->account_id);
            } catch (ModelNotFoundException) {
                return Attribute::make(
                    get: fn () => false,
                );
            }

            if ($result && $result->count()) {
                $passed = $result
                    ->where('exam', $this->waitingList->cts_theory_exam_level)
                    ->where('pass', true)->count() > 0;
            }
        }

        return Attribute::make(
            get: fn () => $passed,
        );
    }

    // public function getTheoryExamPassedAttribute(): ?bool
    // {
    //     if ($this->waitingList->department === WaitingList::PILOT_DEPARTMENT || ! $this->waitingList->cts_theory_exam_level) {
    //         return null;
    //     }

    //     $result = TheoryResult::forAccount($this->account_id);

    //     if (! $result || ! $result->count()) {
    //         return null;
    //     }

    //     return $result
    //         ->where('exam', $this->waitingList->cts_theory_exam_level)
    //         ->where('pass', true)->count() > 0;
    // }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = (string) $value;
    }

    private function cacheKey()
    {
        return "waiting-list-account:{$this->id}";
    }
}
