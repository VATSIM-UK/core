<?php

namespace App\Http\Controllers\Mship\Training;

use App\Enums\SeminarInvitationStatus;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Services\Training\SeminarInvitationService;

class SeminarInvitationController extends \App\Http\Controllers\BaseController
{
    public function accept(string $token, SeminarInvitationService $service)
    {
        $invitation = $this->findInvitation($token);

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
        if ($invitation->account_id !== auth()->id()) {
            abort(403);
        }

        return $invitation->canRespond();
    }

    private function expired(SeminarInvitation $invitation)
    {
        return view('training.seminar-invitation.expired', compact('invitation'));
    }
}
