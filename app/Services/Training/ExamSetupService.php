<?php

namespace App\Services\Training;

use App\Enums\PilotExamType;
use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Repositories\Cts\ExamResultRepository;
use App\Repositories\Cts\SessionRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExamSetupService
{
    public function __construct(
        protected ExamResultRepository $examResults = new ExamResultRepository,
        protected SessionRepository $sessions = new SessionRepository,
        protected ExamForwardingService $forwarding = new ExamForwardingService,
    ) {}

    // TWR -> CTR
    public function twrToCtrPositionOptions(): array
    {
        return Position::where('callsign', 'NOT LIKE', '%ATIS%')
            ->orderBy('callsign')
            ->pluck('callsign', 'id')
            ->toArray();
    }

    public function twrToCtrStudentOptions(?int $positionId): array
    {
        if (! $positionId) {
            return [];
        }

        $position = Position::find($positionId);
        if (! $position) {
            return [];
        }

        $recentPassedStudentIds = $this->examResults
            ->getRecentPassedExamsOfType($position->examLevel, daysConsideredRecent: 180)
            ->pluck('student_id');

        $pendingStudentIds = $this->examResults
            ->getPendingExamsOfType($position->examLevel, daysConsideredRecent: 180)
            ->pluck('student_id');

        return $this->generateStudentOptions(
            positionCallsign: $position->callsign,
            daysConsideredRecent: 180,
            recentPassedStudentIds: $recentPassedStudentIds,
            pendingStudentIds: $pendingStudentIds
        )->toArray();
    }

    public function setupTwrToCtr(int $positionId, int $studentId, int $forwardedByUserId): void
    {
        $trainingPosition = TrainingPosition::where('position_id', $positionId)->firstOrFail();
        $ctsMember = Member::where('id', $studentId)->firstOrFail();
        $examType = $trainingPosition->position->examLevel;

        $this->guardAgainstPendingExam($ctsMember, $examType);

        $this->forwarding->forwardForExam($ctsMember, $trainingPosition, $forwardedByUserId);
        $this->forwarding->notifySuccess($trainingPosition->exam_callsign ?? $trainingPosition->position->callsign);
    }

    // OBS
    public function obsPositionOptions(): array
    {
        return CtsPosition::where('callsign', 'LIKE', 'OBS_%_PT3')
            ->orderBy('callsign')
            ->pluck('callsign', 'id')
            ->toArray();
    }

    public function obsStudentOptions(?int $positionId): array
    {
        if (! $positionId) {
            return [];
        }

        $position = CtsPosition::find($positionId);
        if (! $position) {
            return [];
        }

        // PT3 selected in the dropdown - work out the matching PT2 position
        // (OBS_XX_PT3 -> OBS_XX_PT2) so we can find recent students.
        $pt2Position = CtsPosition::where('callsign', 'LIKE', Str::replaceLast('PT3', 'PT2', $position->callsign))->first();

        if (! $pt2Position) {
            return [];
        }

        $recentPassedStudentIds = $this->examResults
            ->getRecentPassedExamsOfType('OBS', daysConsideredRecent: 180)
            ->pluck('student_id');

        $pendingStudentIds = $this->examResults
            ->getPendingExamsOfType('OBS', daysConsideredRecent: 180)
            ->pluck('student_id');

        return $this->generateStudentOptions(
            positionCallsign: $pt2Position->callsign,
            daysConsideredRecent: 180,
            recentPassedStudentIds: $recentPassedStudentIds,
            pendingStudentIds: $pendingStudentIds
        )->toArray();
    }

    public function setupObs(int $positionId, int $studentId): void
    {
        $position = CtsPosition::findOrFail($positionId);
        $trainingPosition = TrainingPosition::whereJsonContains('cts_positions', $position->callsign)->firstOrFail();
        $ctsMember = Member::where('id', $studentId)->firstOrFail();

        $this->guardAgainstPendingExam($ctsMember, 'OBS');

        $this->forwarding->forwardForObsExam($ctsMember, $trainingPosition);
        $this->forwarding->notifySuccess($trainingPosition->exam_callsign ?? $trainingPosition->position->callsign);
    }

    // Pilot
    public function pilotExamTypeOptions(): array
    {
        return collect(PilotExamType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function pilotStudentSearchResults(string $search, ?string $examType): array
    {
        if (! $examType) {
            return [];
        }

        $prerequisiteRating = PilotExamType::from($examType)->prerequisiteQualification();

        $passedStudentIds = $this->examResults
            // Pilot exams pre 2020 should be ignored
            ->getPassedExamsOfType($examType, since: Carbon::parse('2020-01-01'))
            ->pluck('student_id');

        $pendingStudentIds = $this->examResults
            ->getPendingExamsOfType($examType, daysConsideredRecent: 180)
            ->pluck('student_id');

        $members = TrainingMemberAccountSearch::membersMatchingSearch($search, 25);

        if ($members->isEmpty()) {
            return [];
        }

        $eligibleCids = Account::whereIn('id', $members->pluck('cid'))
            ->whereHas('qualifications', function ($q) use ($prerequisiteRating) {
                // Students must hold either the previous rating or hold a
                // Flight Examiner (P6) rating to be forwarded for any pilot exam.
                $q->where('type', 'pilot')
                    ->where(function ($q) use ($prerequisiteRating) {
                        $q->where('code', $prerequisiteRating)
                            ->orWhere('code', 'FE');
                    });
            })
            ->pluck('id');

        return $members
            ->whereIn('cid', $eligibleCids)
            ->whereNotIn('id', $passedStudentIds)
            ->whereNotIn('id', $pendingStudentIds)
            ->take(25)
            ->mapWithKeys(fn ($member) => [
                $member->id => "{$member->name} ({$member->cid})",
            ])
            ->toArray();
    }

    public function pilotStudentLabel(int|string $value): ?string
    {
        return Member::find($value)?->name;
    }

    public function twrToCtrStudentLabel(int|string $value): ?string
    {
        return Member::find($value)?->name;
    }

    public function obsStudentLabel(int|string $value): ?string
    {
        return Member::find($value)?->name;
    }

    public function setupPilot(string $examType, int $studentId, int $forwardedByUserId): void
    {
        $ctsMember = Member::where('id', $studentId)->firstOrFail();

        $this->guardAgainstPendingExam($ctsMember, $examType);

        $this->forwarding->forwardForPilotExam($ctsMember, $examType, $forwardedByUserId);
        $this->forwarding->notifySuccess(PilotExamType::from($examType)->label());
    }

    public function guardAgainstPendingExam(Member $ctsMember, string $examType): void
    {
        if ($this->examResults->studentHasPendingExam($examType, $ctsMember->id)) {
            throw new PendingExamExistsException("This student already has a pending {$examType} exam.");
        }
    }

    protected function generateStudentOptions(
        string $positionCallsign,
        int $daysConsideredRecent,
        Collection $recentPassedStudentIds,
        ?Collection $pendingStudentIds = null
    ): Collection {
        $recentCompletedSessions = $this->sessions->getRecentCompletedSessionsForPosition(
            $positionCallsign,
            daysConsideredRecent: $daysConsideredRecent
        );

        return $recentCompletedSessions
            ->map(fn ($session) => [
                'cts_student_id' => $session->student_id,
                'name' => $session->student->name,
                'cid' => $session->student->cid,
            ])
            ->reject(fn ($student) => $recentPassedStudentIds->contains($student['cts_student_id'])
                || ($pendingStudentIds && $pendingStudentIds->contains($student['cts_student_id']))
            )
            ->mapWithKeys(fn ($student) => [$student['cts_student_id'] => "{$student['name']} ({$student['cid']})"]);
    }
}
