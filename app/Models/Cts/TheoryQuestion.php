<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheoryQuestion extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'theory_questions';

    public $timestamps = false;

    protected $fillable = [
        'level',
        'question',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'answer',
        'add_by',
        'add_date',
        'edit_by',
        'edit_date',
        'deleted',
        'status',
    ];

    public function answers(): string
    {
        return $this->hasMany(TheoryAnswer::class, 'question_id');
    }
}
