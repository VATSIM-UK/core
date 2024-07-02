<?php

namespace App\Models\Training\WaitingList;

use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $list_id
 * @property int $account_id
 * @property int|null $added_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property array|null $flags_status_summary
 * @property-read Account|null $account
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training\WaitingList\WaitingListFlag> $flags
 * @property-read int|null $flags_count
 * @property-read mixed $atc_hour_check
 * @property-read mixed $position
 * @property-read mixed $theory_exam_passed
 * @property-read WaitingList|null $waitingList
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereFlagsStatusSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingListAccount withoutTrashed()
 *
 * @mixin \Eloquent
 */
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

    public function waitingList(): BelongsTo
    {
        return $this->belongsTo(WaitingList::class, 'list_id');
    }

    public function account(): BelongsTo
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

    public function position(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->waitingList->positionOf($this)
        );
    }

    public function getAtcHourCheckAttribute()
    {
        return $this->atcHourCheck();
    }

    public function theoryExamPassed(): Attribute
    {
        $passed = false;

        if ($this->waitingList?->department === WaitingList::ATC_DEPARTMENT) {
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

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = (string) $value;
    }

    private function cacheKey()
    {
        return "waiting-list-account:{$this->id}";
    }
}
