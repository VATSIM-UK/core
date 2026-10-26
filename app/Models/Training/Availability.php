<?php

namespace App\Models\Training;

use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    /** Student rows; the legacy mentor type is unused here. */
    public const TYPE_STUDENT = 'S';

    // Transitional: the model has moved to the Training namespace, the table has not.
    protected $connection = 'cts';

    protected $table = 'availability';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'from' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_STUDENT,
    ];

    // Temporary handling for `to` being set to "24:00" from legacy CTS system
    protected function to(): Attribute
    {
        return Attribute::make(get: fn ($value) => $value ? Carbon::parse(str_replace('24:00', '23:45', $value)) : null);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.$this->from->format('H:i:s'), 'UTC');
    }

    /**
     * A `to` earlier than `from` is how a slot running past midnight is stored, so the end
     * rolls forward a day.
     */
    public function endsAt(): Carbon
    {
        $end = Carbon::parse($this->date->format('Y-m-d').' '.$this->to->format('H:i:s'), 'UTC');

        if ($this->to->format('H:i:s') < $this->from->format('H:i:s')) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function bounds(): array
    {
        return [$this->startsAt(), $this->endsAt()];
    }

    public function crossesMidnight(): bool
    {
        $end = $this->endsAt();

        return ! $this->startsAt()->isSameDay($end)
            && ! $end->equalTo($end->copy()->startOfDay());
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeStudentOnly(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_STUDENT);
    }

    public function scopeStartingBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
    }

    public function scopeStartingFrom(Builder $query, Carbon $day): Builder
    {
        return $query->where('date', '>=', $day->toDateString());
    }

    public function scopeEndingAfter(Builder $query, Carbon $instant): Builder
    {
        return $query->where(function (Builder $query) use ($instant): void {
            $query->whereDate('date', '>', $instant->toDateString())
                ->orWhere(function (Builder $query) use ($instant): void {
                    $query->whereDate('date', $instant->toDateString())
                        ->whereTime('to', '>', $instant->format('H:i:s'));
                });
        });
    }

    public function scopeOnDate(Builder $query, Carbon $day): Builder
    {
        return $query->whereDate('date', $day);
    }

    public function scopeOrderedByStart(Builder $query): Builder
    {
        return $query->orderBy('date')->orderBy('from');
    }

    public static function storeSlot(int $studentId, Carbon $start, Carbon $end): self
    {
        $row = new static;
        $row->student_id = $studentId;
        $row->type = self::TYPE_STUDENT;
        $row->setBounds($start, $end);
        $row->save();

        return $row;
    }

    public function updateSlot(Carbon $start, Carbon $end): bool
    {
        $this->setBounds($start, $end);

        return $this->save();
    }

    private function setBounds(Carbon $start, Carbon $end): void
    {
        $start = $start->copy()->utc();
        $end = $end->copy()->utc();

        $this->date = $start->toDateString();
        $this->from = $start->format('H:i:s');
        $this->to = $end->format('H:i:s');
    }
}
