<?php

namespace App\Models\Cts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamBooking extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'exam_book';

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function examiners(): HasOne
    {
        return $this->hasOne(PracticalExaminers::class, 'examid', 'id');
    }

    public function startDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse("{$this->taken_date} {$this->taken_from}")->format('Y-m-d H:i'),
        );
    }

    #[Scope]
    protected function conductable()
    {
        return $this->where('taken', 1)
            ->where('finished', 0);
    }
}
