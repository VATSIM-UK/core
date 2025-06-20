<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class TheoryQuestion extends Model
{
    protected $table = 'theory_questions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'level', 'question', 'option_1', 'option_2', 'option_3', 'option_4', 'answer', 'add_by', 'add_date', 'edit_by', 'edit_date', 'deleted', 'status',
    ];
}
