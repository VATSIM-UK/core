<?php

namespace App\Models\Mship\Concerns;

use App\Events\Mship\AccountAltered;
use App\Events\Mship\Qualifications\QualificationAdded;
use App\Models\Mship\AccountQualification;
use App\Models\Mship\Qualification;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use VatsimXML;

trait HasQualifications
{
    public function qualifications()
    {
        return $this->belongsToMany(
            Qualification::class,
            'mship_account_qualification',
            'account_id',
            'qualification_id'
        )->using(AccountQualification::class)
            ->wherePivot('deleted_at', '=', null)
            ->withTimestamps();
    }

    /**
     * Determine if the given qualification exists on the member account.
     *
     * @param Qualification $qualification
     *
     * @return bool
     */
    public function hasQualification(Qualification $qualification)
    {
        return $this->qualifications->filter(function ($q) use ($qualification) {
            return $q->id == $qualification->id;
        })->count() > 0;
    }

    /**
     * Add a qualification to the current member account.
     *
     * @param Qualification $qualification
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

    /**
     * Add qualifications to the account, calculated from the VATSIM identifiers.
     *
     * @param int $atcRating The VATSIM ATC rating
     * @param int $pilotRating The VATSIM pilot rating
     */
    public function updateVatsimRatings(int $atcRating, int $pilotRating)
    {
        if ($atcRating === 0) {
            $this->addNetworkBan('Network ban discovered via Cert login.');
        } elseif ($atcRating > 0) {
            $this->removeNetworkBan();
            $this->addQualification(Qualification::parseVatsimATCQualification($atcRating));
        }

        if ($atcRating >= 8) {
            try {
                $info = VatsimXML::getData($this->id, 'idstatusprat');
                if (isset($info->PreviousRatingInt) && $info->PreviousRatingInt > 0) {
                    $this->addQualification(Qualification::parseVatsimATCQualification($info->PreviousRatingInt));
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Name or service not known') === false) {
                    Bugsnag::notifyException($e);
                }
            }
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
            ->push($this->qualification_pilot);
    }

    public function getQualificationAtcAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'atc';
        })->sortByDesc(function ($qualification, $key) {
            return $qualification->vatsim;
        })->first();
    }

    public function getQualificationsAtcAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'atc';
        });
    }

    public function getQualificationsAtcTrainingAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'training_atc';
        });
    }

    public function getQualificationsPilotAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'pilot';
        });
    }

    public function getQualificationPilotAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'pilot';
        })->sortByDesc(function ($qualification) {
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
            return $qual->type == 'training_pilot';
        });
    }

    public function getQualificationsAdminAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'admin';
        });
    }
}
