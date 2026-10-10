<?php

namespace App\Services\Training;

use App\Enums\SeminarInvitationStatus;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\SeminarInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeminarInvitationService
{
    public function __construct(private SeminarTheoryExamReminderService $theoryExamReminders) {}

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
        $waitingList = $seminar->waitingList()->with(['waitingListAccounts.account', 'waitingListAccounts.theoryReminder'])->firstOrFail();

        foreach ($waitingList->waitingListAccounts as $waitingListAccount) {
            if ($invited >= $targetCount) {
                break;
            }

            if ($this->hasInvitationForSeminar($seminar, $waitingListAccount->account_id)) {
                continue;
            }

            if ($waitingListAccount->wasRemindedForSeminar($seminar->id)) {
                continue;
            }

            if (! $waitingListAccount->theory_exam_passed) {
                $this->theoryExamReminders->sendReminder($waitingListAccount, $seminar);

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

        $waitingListAccount = $this->resolveWaitingListAccount($seminar, $account, $waitingListAccountId);

        if ($waitingListAccount?->wasRemindedForSeminar($seminar->id)) {
            throw new \InvalidArgumentException('This student was sent a theory exam reminder for this seminar and cannot be invited.');
        }

        return DB::transaction(function () use ($seminar, $account, $waitingListAccountId): SeminarInvitation {
            $sentAt = now();
            $isShortNotice = $seminar->isShortNotice();

            $expiresAt = $sentAt->copy()->addHours($seminar->invitation_expiry_hours);
            $admissionsCloseAt = $seminar->admissionsCloseAt();
            if ($expiresAt->greaterThan($admissionsCloseAt)) {
                $expiresAt = $admissionsCloseAt;
            }

            $invitation = SeminarInvitation::create([
                'seminar_id' => $seminar->id,
                'account_id' => $account->id,
                'waiting_list_account_id' => $waitingListAccountId,
                'token' => $this->generateToken(),
                'status' => SeminarInvitationStatus::Sent->value,
                'is_short_notice' => $isShortNotice,
                'sent_at' => $sentAt,
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
                if ($invitation->is_short_notice) {
                    $invitation->update([
                        'status' => SeminarInvitationStatus::ShortNoticeNoResponse->value,
                        'responded_at' => now(),
                    ]);

                    return;
                }

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

    private function resolveWaitingListAccount(Seminar $seminar, Account $account, ?int $waitingListAccountId): ?WaitingListAccount
    {
        if ($waitingListAccountId) {
            return WaitingListAccount::query()->whereKey($waitingListAccountId)->first();
        }

        return WaitingListAccount::query()
            ->where('list_id', $seminar->waiting_list_id)
            ->where('account_id', $account->id)
            ->first();
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
