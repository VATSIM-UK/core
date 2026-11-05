<?php

namespace App\Models\Training;

use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryExamResult extends Model
{
    use HasFactory;

    protected $table = 'training_theory_results';

    protected $guarded = [];

    protected $casts = [
        'exam_datetime' => 'datetime',
    ];

    public const PASS = 'P';

    public const FAIL = 'F';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
