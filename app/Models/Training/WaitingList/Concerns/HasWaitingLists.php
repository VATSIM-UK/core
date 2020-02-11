<?php

namespace App\Models\Training\WaitingList\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;

trait HasWaitingLists
{
    public function waitingLists()
    {
        return $this->belongsToMany(WaitingList::class, 'training_waiting_list_account',
            'account_id', 'list_id')->using(WaitingListAccount::class)->withPivot(['id']);
    }

    public function authorisedDepartments()
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
}
