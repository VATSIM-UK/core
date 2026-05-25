<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\FieldScore;
use App\Models\Cts\CancelReason;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\MentoringReportFiled;
use App\Notifications\Training\StudentMentoringNoShow;
use App\Repositories\Cts\MentoringReportRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MentoringReportService
{
    public function __construct(
        private readonly MentoringReportRepository $repository,
        private readonly MentorPermissionService $mentorPermissionService,
    ) {}

    /**
     * @param  array<int, array{score: FieldScore, notes: ?string}>  $criteriaData
     */
    public function saveDraft(Session $session, array $criteriaData, ?string $additionalComments): void
    {
        DB::connection('cts')->transaction(function () use ($session, $criteriaData, $additionalComments): void {
            foreach ($criteriaData as $fieldId => $data) {
                $this->repository->upsertCriterion(
                    $session,
                    (int) $fieldId,
                    $data['score'],
                    $data['notes'],
                );
            }

            if ($additionalComments !== null) {
                $this->repository->upsertAdditionalComments($session, $additionalComments);
            }
        });
    }

    public function submit(Session $session): void
    {
        $this->assertCanConduct($session);

        $fields = $this->repository->getCriteriaFieldsForSession($session);
        $existingScores = $this->repository->getExistingScoresForSession($session);

        $unscoredFields = $fields->filter(function ($field) use ($existingScores) {
            $score = $existingScores[$field->field_id] ?? FieldScore::NOT_SCORED;

            return $score === FieldScore::NOT_SCORED;
        });

        if ($unscoredFields->isNotEmpty()) {
            throw ValidationException::withMessages([
                'criteria' => 'You must select a score for every criteria before submitting the report.',
            ]);
        }

        DB::connection('cts')->transaction(function () use ($session): void {
            $this->repository->fileSession($session);
        });

        $studentAccount = $session->student?->account;

        if ($studentAccount) {
            $studentAccount->notify(new MentoringReportFiled($session));
        }
    }

    public function canMarkNoShow(Session $session): bool
    {
        if ($session->session_done || $session->filed || $session->cancelled_datetime || $session->noShow) {
            return false;
        }

        $sessionStart = Carbon::parse("{$session->taken_date} {$session->taken_from}");
        $delayMinutes = config('training.mentoring.no_show_delay_minutes', 5);

        return now()->greaterThanOrEqualTo($sessionStart->copy()->addMinutes($delayMinutes));
    }

    public function wasBookedWithShortNotice(Session $session): bool
    {
        if (! $session->taken_time) {
            return false;
        }

        $sessionStart = Carbon::parse("{$session->taken_date} {$session->taken_from}");
        $bookedAt = Carbon::parse($session->taken_time);
        $shortNoticeHours = config('training.mentoring.short_notice_hours', 24);

        return $bookedAt->diffInHours($sessionStart, false) < $shortNoticeHours;
    }

    public function markNoShow(Session $session, bool $studentConfirmedDiscordOnShortNotice): void
    {
        $this->assertCanConduct($session);

        if (! $this->canMarkNoShow($session)) {
            throw ValidationException::withMessages([
                'no_show' => 'This session cannot be marked as a no-show yet.',
            ]);
        }

        if ($this->wasBookedWithShortNotice($session) && ! $studentConfirmedDiscordOnShortNotice) {
            $this->cancelSessionAsMentor(
                $session,
                'Session cancelled: student did not confirm attendance via Discord for a short-notice booking.',
            );

            return;
        }

        DB::connection('cts')->transaction(function () use ($session): void {
            $previousScores = $this->repository->getPreviousScoresForStudent($session);
            $fields = $this->repository->getCriteriaFieldsForSession($session);

            foreach ($fields as $field) {
                $score = $previousScores[$field->field_id] ?? FieldScore::NOT_APPLICABLE;

                $this->repository->upsertCriterion(
                    $session,
                    $field->field_id,
                    $score,
                    null,
                );
            }

            $this->repository->upsertAdditionalComments($session, null);
            $this->repository->fileSession($session, noShow: true);
        });

        $this->notifyTgisOfNoShow($session);
    }

    public function cancelSessionAsMentor(Session $session, string $reason): void
    {
        $this->assertCanConduct($session);

        DB::connection('cts')->transaction(function () use ($session, $reason): void {
            $mentorId = $session->mentor_id;

            CancelReason::create([
                'sesh_id' => $session->id,
                'sesh_type' => 'ME',
                'reason' => $reason,
                'reason_by' => $mentorId,
                'date' => now(),
            ]);

            $primaryPosition = $this->repository->getPrimaryPosition($session->position);

            $session->update([
                'position' => $primaryPosition,
                'taken' => 0,
                'mentor_id' => null,
                'mentor_rating' => null,
                'taken_date' => null,
                'taken_from' => null,
                'taken_to' => null,
                'taken_time' => null,
            ]);
        });
    }

    /**
     * @return array<int, FieldScore>
     */
    public function getPreviousScores(Session $session): array
    {
        return $this->repository->getPreviousScoresForStudent($session);
    }

    /**
     * @return array<int, FieldScore>
     */
    public function getBestScores(Session $session): array
    {
        return $this->repository->getBestScoresForStudent($session);
    }

    private function assertCanConduct(Session $session): void
    {
        if ($session->session_done || $session->filed) {
            throw ValidationException::withMessages([
                'session' => 'This session report has already been filed.',
            ]);
        }

        if ($session->cancelled_datetime) {
            throw ValidationException::withMessages([
                'session' => 'This session has been cancelled.',
            ]);
        }
    }

    private function notifyTgisOfNoShow(Session $session): void
    {
        $category = $this->resolveCategoryForPosition($session->position);

        if (! $category) {
            return;
        }

        $permissionName = 'training.mentors.manage.'.MentorPermissionService::categoryType($category);

        $recipients = Account::permission($permissionName)->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new StudentMentoringNoShow($session));
        }
    }

    private function resolveCategoryForPosition(string $position): ?string
    {
        foreach (array_merge(MentorPermissionService::atcCategories(), MentorPermissionService::pilotCategories()) as $category) {
            if (in_array($position, $this->mentorPermissionService->getAllCtsCallsignsForCategory($category), true)) {
                return $category;
            }
        }

        return null;
    }
}
