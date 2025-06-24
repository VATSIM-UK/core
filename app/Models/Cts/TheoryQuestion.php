<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class TheoryQuestion extends Model
{
    protected $connection = 'cts';

    protected $table = 'theory_questions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'level', 'question', 'option_1', 'option_2', 'option_3', 'option_4', 'answer', 'add_by', 'add_date', 'edit_by', 'edit_date', 'deleted', 'status',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $userId = auth()->id();

            $model->level = 'S1';
            $model->add_by = $userId;
            $model->add_date = now();
            $model->edit_by = $userId;
            $model->edit_date = now();
        });

        static::updating(function ($model) {
            $model->edit_by = auth()->id();
            $model->edit_date = now();
        });

    }
}
