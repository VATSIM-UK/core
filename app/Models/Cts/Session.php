<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Session extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    /**
     * CTS assigns numeric primary keys on insert; Eloquent must treat the key as incrementing
     * so create()/factory() hydrate `id` for URLs, Filament table keys, and refresh().
     */
    public $incrementing = true;

    protected $keyType = 'int';

    protected $guarded = [];

    public function mentor()
    {
        return $this->belongsTo(Member::class, 'mentor_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function reportSheets(): HasMany
    {
        return $this->hasMany(ReportSheet::class, 'seshid', 'id');
    }

    public function reportNote(): HasOne
    {
        return $this->hasOne(ReportNote::class, 'seshid', 'id');
    }

    public function studentAccount(): ?Account
    {
        $this->loadMissing('student');

        return $this->student ? Account::find($this->student->cid) : null;
    }

    public function mentorAccount(): ?Account
    {
        $this->loadMissing('mentor');

        return $this->mentor ? Account::find($this->mentor->cid) : null;
    }

    public function formattedSessionDateTime(): string
    {
        $date = Carbon::parse($this->taken_date)->format('l jS M Y');
        $from = Carbon::parse($this->taken_from)->format('H:i');
        $to = Carbon::parse($this->taken_to)->format('H:i');

        return "{$date}, {$from} - {$to}";
    }

    public function cancelReason(): HasOne
    {
        return $this->hasOne(CancelReason::class, 'sesh_id', 'id')->where('sesh_type', 'ME');
    }
}
