<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamBooking extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'exam_book';

    public $timestamps = false;

    public $guarded = [];

    public final const int FINISHED_FLAG = 1;

    public final const int NOT_FINISHED_FLAG = 0;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function studentAccount()
    {
        return Account::find($this->student->cid);
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

    public function endDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse("{$this->taken_date} {$this->taken_to}")->format('Y-m-d H:i'),
        );
    }

    public function studentQualification(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Qualification::ofType('atc')->where('vatsim', $this->student_rating)->first()
        );
    }

    #[Scope]
    protected function conductable()
    {
        return $this->where('taken', 1)
            ->where('finished', self::NOT_FINISHED_FLAG);
    }
}
