<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;

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
        if ($this->can('use-permission', 'feedback/own')) {
            return [];
        }

        return [$this->id];
    }
}
