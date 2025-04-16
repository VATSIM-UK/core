<?php

namespace App\Models\Mship\Concerns;

use App\Enums\QualificationTypeEnum;
use App\Events\Mship\AccountAltered;
use App\Events\Mship\Qualifications\QualificationAdded;
use App\Models\Mship\AccountQualification;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

trait HasQualifications
{
    public function qualifications()
    {
        return $this->belongsToMany(
            Qualification::class,
            'mship_account_qualification',
            'account_id',
            'qualification_id'
        )->orderBy('vatsim')
            ->using(AccountQualification::class)
            ->withPivot('deleted_at', 'id')
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    /**
     * Determine if the given qualification exists on the member account.
     *
     * @return bool
     */
    public function hasQualification(Qualification $qualification)
    {
        return $this->qualifications->filter(function ($q) use ($qualification) {
            return $q->id == $qualification->id;
        })->count() > 0;
    }

    public function hasActiveQualification(Qualification $qualification)
    {
        return $this->active_qualifications->filter(function ($q) use ($qualification) {
            return $q->id == $qualification->id;
        })->count() > 0;
    }

    /**
     * Add a qualification to the current member account.
     *
     * @return self
     */
    public function addQualification(Qualification $qualification)
    {
        if (! $this->hasQualification($qualification)) {
            $this->qualifications()->attach($qualification);
            $this->touch();
            event(new QualificationAdded($this, $qualification));
            event(new AccountAltered($this));
        }

        return $this;
    }

    public function removeQualification(Qualification $qualification)
    {
        if ($this->hasQualification($qualification)) {
            Log::info("Removing qualification {$qualification->code} from member {$this->id}");

            $memberQualificationPivot = $this->qualifications_pilot->where('code', $qualification->code)->first()->pivot;

            $memberQualificationPivot->deleted_at = now();
            $memberQualificationPivot->save();

            $this->touch();
            event(new AccountAltered($this));
        }

        return $this;
    }

    /**
     * Add qualifications to the account, calculated from the VATSIM identifiers.
     *
     * @param  int|null  $atcRating  The VATSIM ATC rating
     * @param  int|null  $pilotRating  The VATSIM pilot rating
     */
    public function updateVatsimRatings(?int $atcRating, ?int $pilotRating)
    {
        if ($atcRating === 0) {
            $this->addNetworkBan('Network ban discovered via Cert login.');
        } elseif ($atcRating > 0) {
            $this->removeNetworkBan();
            $this->addQualification(Qualification::parseVatsimATCQualification($atcRating));
        }

        if ($atcRating >= 8) {
            // This user has an admin rating but there is currently no support
            // for fetching their real rating via the VATSIM API. For
            // reference, the old AT code is below.

            //     try {
            //         $info = VatsimXML::getData($this->id, 'idstatusprat');
            //         if (isset($info->PreviousRatingInt) && $info->PreviousRatingInt > 0) {
            //             $this->addQualification(Qualification::parseVatsimATCQualification($info->PreviousRatingInt));
            //         }
            //     } catch (Exception $e) {
            //         if (strpos($e->getMessage(), 'Name or service not known') === false) {
            //             //
            //         }
            //     }
        }

        if ($pilotRating >= 0) {
            $pilotRatings = Qualification::parseVatsimPilotQualifications($pilotRating);
            foreach ($pilotRatings as $pr) {
                if (! $this->hasQualification($pr)) {
                    $this->addQualification($pr);
                }
            }
        }
    }

    public function getActiveQualificationsAttribute()
    {
        $this->load('qualifications');

        return $this->qualifications_atc_training
            ->merge($this->qualifications_pilot_training)
            ->merge($this->qualifications_admin)
            ->push($this->qualification_atc)
            ->push($this->qualification_pilot)
            ->push($this->qualification_pilot_military)
            ->filter(); // Removes nullish values
    }

    public function getQualificationsAtcAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::ATC->value;
        });
    }

    public function getQualificationAtcAttribute()
    {
        return $this->qualifications_atc->sortByDesc(function ($qualification, $key) {
            return $qualification->vatsim;
        })->first();
    }

    public function getQualificationsAtcTrainingAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::ATCTraining->value;
        });
    }

    public function getQualificationsPilotAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::Pilot->value;
        });
    }

    public function getQualificationPilotAttribute()
    {
        return $this->qualifications_pilot->sortByDesc(function ($qualification) {
            return $qualification->vatsim;
        })->first();
    }

    public function getQualificationsPilotMilitaryAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::MilitaryPilot->value;
        });
    }

    public function getQualificationPilotMilitaryAttribute()
    {
        return $this->qualifications_pilot_military->sortByDesc(function ($qualification) {
            return $qualification->vatsim;
        })->first();
    }

    public function getQualificationsPilotStringAttribute()
    {
        return optional($this->qualification_pilot)->code ?? 'None';
    }

    public function getQualificationsPilotTrainingAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::PilotTraining->value;
        });
    }

    public function getQualificationsAdminAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == QualificationTypeEnum::Admin->value;
        });
    }

    public function hasControllerRating(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->qualification_atc && $this->qualification_atc->vatsim > 1,
        );
    }
}
