<?php

namespace App\Models\Mship\Concerns;

trait HasNovaPermissions
{
    public function waitingListDepartments()
    {
        $departments = [];
        if ($this->hasRole('privacc')) {
            $departments = ['atc', 'pilot'];
        }
        if ($this->checkPermissionTo('waitingLists/atc/view', 'web')) {
            array_push($departments, 'atc');
        }
        if ($this->checkPermissionTo('waitingLists/pilot/view', 'web')) {
            array_push($departments, 'pilot');
        }

        return $departments;
    }

    public function hiddenFeedbackUsers()
    {
        if ($this->can('use-permission', 'feedback/own') || $this->can('use-permission', 'feedback.view-own')) {
            return [];
        }

        return [$this->id];
    }
}
