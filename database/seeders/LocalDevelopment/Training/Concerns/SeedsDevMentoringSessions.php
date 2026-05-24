<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Cts\CancelReason;
use App\Models\Cts\Member;
use App\Models\Cts\Session;

/**
 * Idempotent CTS mentoring session fixtures for local development personas.
 */
trait SeedsDevMentoringSessions
{
    /**
     * Completed, cancelled, and no-show sessions tied to a training place's CTS position.
     */
    protected function seedDevMentoringHistory(Member $student, Member $mentor, string $position): void
    {
        $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'taken_date' => now()->subDays(56)->format('Y-m-d'),
                'session_done' => 1,
            ],
            [
                'mentor_id' => $mentor->id,
                'mentor_rating' => 3,
                'taken' => 1,
                'taken_time' => now()->subDays(56),
                'taken_from' => '18:00:00',
                'taken_to' => '20:00:00',
                'cancelled_datetime' => null,
                'noShow' => 0,
                'request_time' => now()->subDays(63),
            ],
        );

        $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'taken_date' => now()->subDays(28)->format('Y-m-d'),
                'session_done' => 1,
                'mentor_id' => $mentor->id,
            ],
            [
                'mentor_rating' => 3,
                'taken' => 1,
                'taken_time' => now()->subDays(28),
                'taken_from' => '19:30:00',
                'taken_to' => '21:30:00',
                'cancelled_datetime' => null,
                'noShow' => 0,
                'request_time' => now()->subDays(35),
            ],
        );

        $cancelled = $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'taken_date' => now()->subDays(10)->format('Y-m-d'),
                'cancelled_datetime' => now()->subDays(9),
            ],
            [
                'mentor_id' => $mentor->id,
                'mentor_rating' => 3,
                'taken' => 1,
                'session_done' => 0,
                'taken_time' => now()->subDays(10),
                'taken_from' => '14:00:00',
                'taken_to' => '16:00:00',
                'noShow' => 0,
                'request_time' => now()->subDays(17),
            ],
        );

        CancelReason::query()->updateOrCreate(
            [
                'sesh_id' => $cancelled->id,
                'sesh_type' => 'ME',
            ],
            [
                'reason' => 'Dev seed: student cancelled mentoring session.',
                'used' => 0,
                'reason_by' => $student->cid,
                'date' => now()->subDays(9),
            ],
        );

        $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'taken_date' => now()->subDays(21)->format('Y-m-d'),
                'noShow' => 1,
            ],
            [
                'mentor_id' => $mentor->id,
                'mentor_rating' => 3,
                'taken' => 1,
                'session_done' => 0,
                'taken_time' => now()->subDays(21),
                'taken_from' => '20:00:00',
                'taken_to' => '22:00:00',
                'cancelled_datetime' => null,
                'request_time' => now()->subDays(28),
            ],
        );

        $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'taken_date' => now()->addDays(7)->format('Y-m-d'),
                'session_done' => 0,
                'mentor_id' => $mentor->id,
            ],
            [
                'mentor_rating' => 3,
                'taken' => 1,
                'taken_time' => now()->addDays(7),
                'taken_from' => '18:00:00',
                'taken_to' => '20:00:00',
                'cancelled_datetime' => null,
                'noShow' => 0,
                'request_time' => now()->subDays(3),
            ],
        );
    }

    /**
     * Open mentoring request awaiting mentor acceptance.
     */
    protected function seedDevMentoringPendingRequest(Member $student, string $position): void
    {
        $this->upsertDevMentoringSession(
            [
                'student_id' => $student->id,
                'position' => $position,
                'session_done' => 0,
                'mentor_id' => null,
            ],
            [
                'taken' => 0,
                'request_time' => now()->subDay(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $match
     * @param  array<string, mixed>  $attributes
     */
    private function upsertDevMentoringSession(array $match, array $attributes): Session
    {
        return Session::query()->updateOrCreate(
            $match,
            array_merge([
                'rts_id' => 1,
                'student_rating' => 1,
                'progress_sheet_id' => 0,
            ], $attributes),
        );
    }
}
