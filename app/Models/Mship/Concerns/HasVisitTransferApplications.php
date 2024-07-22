<?php

namespace App\Models\Mship\Concerns;

use App\Exceptions\VisitTransferLegacy\Application\DuplicateApplicationException;
use App\Models\VisitTransferLegacy\Application;

trait HasVisitTransferApplications
{
    /**
     * Fetch all related visiting/transfer applications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitTransferApplications()
    {
        return $this->hasMany(\App\Models\VisitTransferLegacy\Application::class)->orderBy('created_at', 'DESC');
    }

    public function visitApplications()
    {
        return $this->visitTransferApplications()->where('type', '=', Application::TYPE_VISIT);
    }

    public function transferApplications()
    {
        return $this->visitTransferApplications()->where('type', '=', Application::TYPE_TRANSFER);
    }

    public function getVisitTransferCurrentAttribute()
    {
        return $this->visitTransferApplications()->open()->latest()->first();
    }

    public function createVisitingTransferApplication(array $attributes)
    {
        $this->guardAgainstDivisionMemberVisitingTransferApplication();
        $this->guardAgainstDuplicateVisitingTransferApplications();

        $application = new Application($attributes);

        return $this->visitTransferApplications()->save($application);
    }

    private function guardAgainstDivisionMemberVisitingTransferApplication()
    {
        if ($this->hasState('DIVISION')) {
            throw new \App\Exceptions\VisitTransferLegacy\Application\AlreadyADivisionMemberException($this);
        }
    }

    private function guardAgainstDuplicateVisitingTransferApplications()
    {
        if ($this->hasOpenVisitingTransferApplication()) {
            throw new DuplicateApplicationException($this);
        }
    }

    public function hasOpenVisitingTransferApplication()
    {
        return $this->visitTransferApplications->contains(function ($application, $key) {
            return in_array(
                $application->status,
                \App\Models\VisitTransferLegacy\Application::$APPLICATION_IS_CONSIDERED_OPEN
            );
        });
    }

    public function visitTransferReferee()
    {
        return $this->hasMany(\App\Models\VisitTransferLegacy\Reference::class);
    }

    public function getVisitTransferRefereePendingAttribute()
    {
        return $this->visitTransferReferee->filter(function ($ref) {
            return $ref->is_requested;
        })->sortBy(function ($ref) {
            return $ref->application->submitted_at;
        });
    }
}
