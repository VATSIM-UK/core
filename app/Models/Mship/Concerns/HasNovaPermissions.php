<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;

trait HasNovaPermissions
{
    public function waitingLists()
    {
        return $this->belongsToMany(WaitingList::class, 'training_waiting_list_account',
            'account_id', 'list_id')->using(WaitingListAccount::class)->withPivot(['id', 'deleted_at']);
    }

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
