<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSetup extends Model
{
    /** @use HasFactory<\Database\Factories\Cts\ExamSetupFactory> */
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'exam_setup';

    public $timestamps = false;

    public $guarded = [];
}
