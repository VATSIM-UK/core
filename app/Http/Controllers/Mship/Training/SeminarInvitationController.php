<?php

namespace App\Http\Controllers\Mship\Training;

use App\Enums\SeminarInvitationStatus;
use App\Http\Controllers\BaseController;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Services\Training\SeminarInvitationService;

class SeminarInvitationController extends BaseController
{
    public function accept(string $token, SeminarInvitationService $service)
    {
        $invitation = $this->findInvitation($token);

        $this->authoriseOwner($invitation);

        if ($invitation->status === SeminarInvitationStatus::Attending) {
            return view('training.seminar-invitation.result', ['result' => 'accepted', 'invitation' => $invitation]);
        }

        if (! $this->isTokenValid($invitation)) {
            return $this->expired($invitation);
        }

        $service->accept($invitation);

        return view('training.seminar-invitation.result', ['result' => 'accepted', 'invitation' => $invitation->fresh()]);
    }

    public function notInterested(string $token, SeminarInvitationService $service)
    {
        $invitation = $this->findInvitation($token);

        if (! $this->isTokenValid($invitation)) {
            return $this->expired($invitation);
        }

        $service->markNotInterested($invitation);

        return view('training.seminar-invitation.result', ['result' => 'not_interested', 'invitation' => $invitation->fresh()]);
    }

    public function cannotAttend(string $token, SeminarInvitationService $service)
    {
        $invitation = $this->findInvitation($token);

        if (! $this->isTokenValid($invitation)) {
            return $this->expired($invitation);
        }

        $service->markCannotAttend($invitation);

        return view('training.seminar-invitation.result', ['result' => 'cannot_attend', 'invitation' => $invitation->fresh()]);
    }

    private function findInvitation(string $token): SeminarInvitation
    {
        return SeminarInvitation::with(['account', 'seminar.waitingList'])->where('token', $token)->firstOrFail();
    }

    private function isTokenValid(SeminarInvitation $invitation): bool
    {
        $this->authoriseOwner($invitation);

        return $invitation->canRespond();
    }

    private function authoriseOwner(SeminarInvitation $invitation): void
    {
        abort_unless($invitation->account_id === auth()->id(), 403);
    }

    private function expired(SeminarInvitation $invitation)
    {
        return view('training.seminar-invitation.expired', compact('invitation'));
    }
}
