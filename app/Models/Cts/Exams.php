<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exams extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'exam_book';

    public $timestamps = false;

    public $incrementing = false;

    public function examiner()
    {
        return $this->belongsTo(Member::class, 'exmr_id', 'id');
    }  
}