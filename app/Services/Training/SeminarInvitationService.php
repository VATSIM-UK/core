<?php

namespace App\Services\Training;

use App\Enums\SeminarInvitationStatus;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Notifications\Training\SeminarInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeminarInvitationService
{
    public function topUpAutomaticInvitations(Seminar $seminar): int
    {
        if (! $seminar->automatic_invitations_enabled || $seminar->isSendingCutoffReached()) {
            return 0;
        }

        $target = $seminar->spacesRemaining();

        if ($target <= 0) {
            return 0;
        }

        return $this->inviteNextEligible($seminar, $target);
    }

    public function inviteNextEligible(Seminar $seminar, int $targetCount): int
    {
        if ($seminar->isSendingCutoffReached()) {
            throw new \InvalidArgumentException('Seminar admissions are closed.');
        }

        $invited = 0;
        $waitingList = $seminar->waitingList()->with('waitingListAccounts.account')->firstOrFail();

        foreach ($waitingList->waitingListAccounts as $waitingListAccount) {
            if ($invited >= $targetCount) {
                break;
            }

            if (! $waitingListAccount->theory_exam_passed) {
                // Eventually will fire CTS Theory Exam reminder email and logic
                continue;
            }

            if ($this->hasInvitationForSeminar($seminar, $waitingListAccount->account_id)) {
                continue;
            }

            $this->createInvitation($seminar, $waitingListAccount->account, $waitingListAccount->id);
            $invited++;
        }

        return $invited;
    }

    public function createInvitation(Seminar $seminar, Account $account, ?int $waitingListAccountId = null): SeminarInvitation
    {
        if ($seminar->isSendingCutoffReached()) {
            throw new \InvalidArgumentException('Seminar admissions are closed.');
        }

        if ($seminar->spacesRemaining() <= 0) {
            throw new \InvalidArgumentException('Seminar has reached its invite capacity.');
        }

        $existing = SeminarInvitation::query()
            ->where('seminar_id', $seminar->id)
            ->where('account_id', $account->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($seminar, $account, $waitingListAccountId): SeminarInvitation {
            $expiresAt = now()->addDays($seminar->invitation_expiry_days);
            $seminarStart = $seminar->startsAt();
            if ($expiresAt->greaterThan($seminarStart)) {
                $expiresAt = $seminarStart;
            }

            $invitation = SeminarInvitation::create([
                'seminar_id' => $seminar->id,
                'account_id' => $account->id,
                'waiting_list_account_id' => $waitingListAccountId,
                'token' => $this->generateToken(),
                'status' => SeminarInvitationStatus::Sent->value,
                'sent_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            $account->notify(new SeminarInvitationNotification($invitation));

            return $invitation;
        });
    }

    public function accept(SeminarInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation): void {
            $invitation->update([
                'status' => SeminarInvitationStatus::Attending->value,
                'responded_at' => now(),
            ]);

            SeminarAttendee::firstOrCreate(
                [
                    'seminar_id' => $invitation->seminar_id,
                    'account_id' => $invitation->account_id,
                ],
                [
                    'invitation_id' => $invitation->id,
                    'added_by' => $invitation->account_id,
                    'added_at' => now(),
                ]
            );
        });
    }

    public function markNotInterested(SeminarInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation): void {
            $invitation->update([
                'status' => SeminarInvitationStatus::NotInterested->value,
                'responded_at' => now(),
            ]);

            $this->removeFromWaitingList(
                $invitation,
                RemovalReason::SeminarNotInterested
            );
        });

        $this->topUpAutomaticInvitations($invitation->seminar);
    }

    public function markCannotAttend(SeminarInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation): void {
            $invitation->update([
                'status' => SeminarInvitationStatus::CannotAttend->value,
                'responded_at' => now(),
            ]);

            $cannotAttendCount = $invitation->account
                ->cannotAttendSeminarCountForWaitingList($invitation->seminar->waitingList);

            if ($cannotAttendCount < 2) {
                return;
            }

            $invitation->update([
                'status' => SeminarInvitationStatus::RemovedTwoCannotAttend->value,
            ]);

            $this->removeFromWaitingList(
                $invitation,
                RemovalReason::SeminarTwoCannotAttend
            );
        });

        $this->topUpAutomaticInvitations($invitation->seminar);
    }

    public function expireUnrespondedInvitations(): int
    {
        $expired = 0;
        $pendingInvitations = SeminarInvitation::query()
            ->where('status', SeminarInvitationStatus::Sent->value)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($pendingInvitations as $invitation) {
            DB::transaction(function () use ($invitation): void {
                $invitation->update([
                    'status' => SeminarInvitationStatus::RemovedNoResponse->value,
                    'responded_at' => now(),
                ]);

                $this->removeFromWaitingList($invitation, RemovalReason::SeminarNoResponse);
            });
            $this->topUpAutomaticInvitations($invitation->seminar);
            $expired++;
        }

        return $expired;
    }

    private function removeFromWaitingList(SeminarInvitation $invitation, RemovalReason $reason): void
    {
        $waitingList = $invitation->seminar->waitingList;
        $account = $invitation->account;

        if (! $waitingList || ! $waitingList->includesAccount($account->id)) {
            return;
        }

        $waitingList->removeFromWaitingList(
            $account,
            new Removal($reason, null)
        );
    }

    private function hasInvitationForSeminar(Seminar $seminar, int $accountId): bool
    {
        return $seminar->invitations()->where('account_id', $accountId)->exists();
    }

    private function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (SeminarInvitation::where('token', $token)->exists());

        return $token;
    }
}
