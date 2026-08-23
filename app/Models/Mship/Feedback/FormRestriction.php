<?php

namespace App\Models\Mship\Feedback;

use App\Enums\Feedback\FormRestrictionSubject;
use App\Enums\Feedback\FormRestrictionType;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;

class FormRestriction extends Model
{
    protected $table = 'mship_feedback_form_restrictions';

    protected $fillable = [
        'form_id',
        'restriction_group',
        'type',
        'subject',
        'minimum_value',
    ];

    protected $casts = [
        'type' => FormRestrictionType::class,
        'subject' => FormRestrictionSubject::class,
        'minimum_value' => 'integer',
        'restriction_group' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function isSatisfiedBy(Account $account): bool
    {
        return match ($this->type) {
            FormRestrictionType::Qualification => $this->accountMeetsQualification($account),
            FormRestrictionType::Hours => $this->accountMeetsHours($account),
            FormRestrictionType::AccountAge => $this->accountMeetsAge($account),
        };
    }

    private function accountMeetsQualification(Account $account): bool
    {
        $qualification = match ($this->subject) {
            FormRestrictionSubject::Atc => $account->qualification_atc,
            FormRestrictionSubject::Pilot => null,
            null => null,
        };

        if (! $qualification) {
            return false;
        }

        return $qualification->vatsim >= $this->minimum_value;
    }

    private function accountMeetsHours(Account $account): bool
    {
        $totalMinutes = match ($this->subject) {
            FormRestrictionSubject::Atc => Atc::query()
                ->where('account_id', $account->id)
                ->whereNotNull('minutes_online')
                ->sum('minutes_online'),
            FormRestrictionSubject::Pilot => 0,
            null => 0,
        };

        return ($totalMinutes / 60) >= $this->minimum_value;
    }

    private function accountMeetsAge(Account $account): bool
    {
        if (! $account->joined_at) {
            return false;
        }

        return $account->joined_at->diffInDays(now()) >= $this->minimum_value;
    }

    public function reason(): string
    {
        return match ($this->type) {
            FormRestrictionType::Qualification => "requires at least a {$this->minimumQualificationCode()} rating",
            FormRestrictionType::Hours => "requires at least {$this->minimum_value} {$this->subject->label()} hours",
            FormRestrictionType::AccountAge => "requires your account to be at least {$this->minimumAgeDescription()} old",
        };
    }

    private function minimumQualificationCode(): string
    {
        $qualification = Qualification::ofType($this->subject->value)
            ->networkValue($this->minimum_value)
            ->first();

        return $qualification->code;
    }

    private function minimumAgeDescription(): string
    {
        $days = $this->minimum_value;

        if ($days % 365 === 0 && $days >= 365) {
            $years = intdiv($days, 365);

            return $years === 1 ? '1 year' : "{$years} years";
        }

        if ($days % 30 === 0 && $days >= 30) {
            $months = intdiv($days, 30);

            return $months === 1 ? '1 month' : "{$months} months";
        }

        return $days === 1 ? '1 day' : "{$days} days";
    }
}
