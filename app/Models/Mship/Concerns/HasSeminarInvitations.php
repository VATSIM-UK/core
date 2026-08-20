<?php

namespace App\Models\Mship\Concerns;

use App\Enums\SeminarInvitationStatus;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSeminarInvitations
{
    public function seminarInvitations(): HasMany
    {
        return $this->hasMany(SeminarInvitation::class, 'account_id', 'id');
    }

    public function cannotAttendSeminarCountForWaitingList(WaitingList $waitingList): int
    {
        return $this->seminarInvitations()
            ->where('status', SeminarInvitationStatus::CannotAttend->value)
            ->whereHas('seminar', fn ($query) => $query->where('waiting_list_id', $waitingList->id))
            ->count();
    }
}
