<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryAnswer extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'theory_answers';

    public $timestamps = false;

    protected $fillable = [
        'theory_id',
        'question_id',
        'question_no',
        'answer_given',
        'answer_correct',
        'correct',
        'submitted',
        'submitted_time',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TheoryQuestion::class, 'question_id', 'id');
    }

    public function theoryResult()
    {
        return $this->belongsTo(TheoryResult::class, 'theory_id');
    }
}
