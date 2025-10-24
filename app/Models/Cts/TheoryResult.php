<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    protected $casts = [
        'started' => 'datetime',
        'expires' => 'datetime',
        'submitted_time' => 'datetime',
    ];

    public $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'cid');
    }

    public function resultHuman(): string
    {
        return $this->pass === 1 ? 'Passed' : 'Failed';
    }

    /**
     * Get result for the internal Core account_id, also their CId
     * and handle the logic where a member_id in CTS is different to that
     * of the account_id in Core.
     */
    public static function forAccount(int $account_id): ?Collection
    {
        try {
            $memberId = Member::where('cid', $account_id)->firstOrFail()->id;
        } catch (ModelNotFoundException) {
            Log::warning("No member found for account_id {$account_id}. Likely sync problems.");

            return null;
        }

        // return the first part of a query to get results for a given member.
        // providing a member_id is found, otherwise return null.

        return self::where('student_id', $memberId)->get();
    }

    public function answers()
    {
        return $this->hasMany(TheoryAnswer::class, 'theory_id');
    }

    /**
     * Gets the option text for selected option
     * Helpful when answer in theory_questions is a number that we need to relate to an option
     */
    public function getOptionText($question, $optionNumber): string
    {
        if (! $question || ! $optionNumber) {
            return 'Unknown';
        }

        $options = [
            1 => $question->option_1 ?? 'Unknown',
            2 => $question->option_2 ?? 'Unknown',
            3 => $question->option_3 ?? 'Unknown',
            4 => $question->option_4 ?? 'Unknown',
        ];

        return $options[$optionNumber] ?? 'Unknown';
    }
}
