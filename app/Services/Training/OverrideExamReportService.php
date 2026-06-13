<?php

namespace App\Services\Training;

use App\Enums\ExamResultEnum;
use App\Filament\Training\Concerns\InteractsWithCtsRichEditorNotes;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExamAssessmentRepository;

class OverrideExamReportService
{
    use InteractsWithCtsRichEditorNotes;

    public function __construct(
        private readonly ExamAssessmentRepository $examAssessmentRepository,
        private readonly ExamResubmissionService $examResubmissionService,
    ) {}

    public function handle(PracticalResult $practicalResult, array $data, Account $actor): bool
    {
        $newResult = ExamResultEnum::from($data['exam_result']);
        $reason = trim((string) ($data['reason'] ?? ''));
        $account = $practicalResult->examBooking->student->account;

        $updates = [];
        $resultChanged = $this->applyResultOverride($practicalResult, $newResult, $reason, $account, $actor, $updates);
        $additionalCommentsChanged = $this->applyAdditionalCommentsOverride($practicalResult, $data['additional_comments'] ?? null, $reason, $account, $actor, $updates);
        $criteriaChanged = $this->applyCriteriaOverrides($practicalResult, $data['criteria_updates'] ?? [], $reason, $account, $actor);

        if ($updates !== []) {
            $practicalResult->update($updates);
        }

        if ($resultChanged) {
            $this->examResubmissionService->handle(
                examBooking: $practicalResult->examBooking,
                result: $newResult->value,
                userId: $actor->id,
            );
        }

        $hasChanges = $resultChanged || $additionalCommentsChanged || $criteriaChanged;

        return $hasChanges;
    }

    private function applyResultOverride(PracticalResult $practicalResult, ExamResultEnum $newResult, string $reason, Account $account, Account $actor, array &$updates): bool
    {
        if ($practicalResult->result === $newResult->value) {
            return false;
        }

        $oldResultHuman = ExamResultEnum::tryFrom($practicalResult->result)?->human() ?? $practicalResult->resultHuman();
        $updates['result'] = $newResult->value;

        $this->addTrainingNote(
            account: $account,
            actor: $actor,
            content: "Exam result for {$practicalResult->examBooking->exam} exam overridden from {$oldResultHuman} to {$newResult->human()}. Reason: {$reason}",
        );

        return true;
    }

    private function applyAdditionalCommentsOverride(PracticalResult $practicalResult, mixed $newComments, string $reason, Account $account, Account $actor, array &$updates): bool
    {
        $oldNotes = $this->normalizedNotes($practicalResult->notes);
        $newNotes = $this->normalizedNotes($newComments);

        if ($oldNotes === $newNotes) {
            return false;
        }

        $updates['notes'] = $newNotes;

        $this->addTrainingNote(
            account: $account,
            actor: $actor,
            content: "Additional comments updated for {$practicalResult->examBooking->exam} exam. Reason: {$reason}",
        );

        return true;
    }

    private function applyCriteriaOverrides(PracticalResult $practicalResult, array $criteriaUpdates, string $reason, Account $account, Account $actor): bool
    {
        if ($criteriaUpdates === []) {
            return false;
        }

        $existingAssessments = ExamCriteriaAssessment::where('examid', $practicalResult->examid)
            ->get()
            ->keyBy('criteria_id');

        $criteriaIds = array_map('intval', array_keys($criteriaUpdates));
        $criteriaNames = ExamCriteria::whereIn('id', $criteriaIds)->pluck('criteria', 'id');
        $gradeLabels = ExamCriteriaAssessment::gradeDropdownOptions();
        $hasCriteriaChanges = false;

        foreach ($criteriaUpdates as $criteriaId => $update) {
            $criteriaId = (int) $criteriaId;
            $assessment = $existingAssessments->get($criteriaId);
            $oldGrade = $assessment?->result ?? ExamCriteriaAssessment::NOT_ASSESSED;
            $newGrade = $update['grade'] ?? $oldGrade;
            $oldNotes = $this->normalizedNotes($assessment?->notes);
            $newNotes = $this->normalizedNotes($update['notes'] ?? null);
            $hasGradeChange = $oldGrade !== $newGrade;
            $hasNotesChange = $oldNotes !== $newNotes;

            if (! $hasGradeChange && ! $hasNotesChange) {
                continue;
            }

            $this->examAssessmentRepository->upsertExamCriteriaAssessment(
                examId: $practicalResult->examid,
                criteriaId: $criteriaId,
                grade: $newGrade,
                comments: $newNotes !== '' ? $newNotes : null,
            );

            $criteriaName = $criteriaNames->get($criteriaId) ?? "Criteria #{$criteriaId}";
            $changeReason = trim((string) ($update['change_comments'] ?? ''));
            $changeReasonText = $changeReason !== '' ? $changeReason : 'No reason provided.';

            if ($hasGradeChange) {
                $oldGradeLabel = $gradeLabels[$oldGrade] ?? $oldGrade;
                $newGradeLabel = $gradeLabels[$newGrade] ?? $newGrade;

                $this->addTrainingNote(
                    account: $account,
                    actor: $actor,
                    content: "'{$criteriaName}' updated from {$oldGradeLabel} to {$newGradeLabel}. Reason: {$changeReasonText}",
                );
            } elseif ($hasNotesChange) {
                $this->addTrainingNote(
                    account: $account,
                    actor: $actor,
                    content: "'{$criteriaName}' comments updated. Reason: {$changeReasonText}",
                );
            }

            $hasCriteriaChanges = true;
        }

        return $hasCriteriaChanges;
    }

    private function addTrainingNote(Account $account, Account $actor, string $content): void
    {
        $account->addNote(
            noteType: 'training',
            noteContent: $content,
            writer: $actor,
        );
    }

    private function normalizedNotes(mixed $notes): string
    {
        return $this->ctsRichContentNotesForCts($notes) ?? '';
    }
}
