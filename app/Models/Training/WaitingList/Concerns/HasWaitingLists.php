<?php

namespace App\Models\Training\WaitingList\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Support\Arr;

trait HasWaitingLists
{
    public function waitingLists()
    {
        return $this->belongsToMany(WaitingList::class, 'training_waiting_list_account',
            'account_id', 'list_id')->using(WaitingListAccount::class)->withPivot(['id', 'position']);
    }

    public function authorisedDepartments()
    {
        $departments = [];
        if ($this->hasRole('privacc')) {
            $departments = ['atc', 'pilot'];
        }
        if ($this->checkPermissionTo('waitingLists/atc/base')) {
            Arr::add($departments, 0, 'atc');
        }
        if ($this->checkPermissionTo('waitingLists/pilot/base')) {
            Arr::add($departments, 1, 'pilot');
        }

        return $departments;
    }
}
