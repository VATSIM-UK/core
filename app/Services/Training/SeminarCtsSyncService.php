<?php

namespace App\Services\Training;

use App\Models\Cts\GroupSession;
use App\Models\Cts\GroupSessionStudent;
use App\Models\Cts\Member;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;

class SeminarCtsSyncService
{
    private const CTS_GROUP_SESSION_NAME_LIMIT = 60;

    private const CTS_GROUP_SESSION_DESCRIPTION_LIMIT = 60;

    // Temporary logic to sync core seminars with old CTS seminars
    public function syncSeminar(Seminar $seminar): void
    {
        $leaderMemberId = Member::query()
            ->where('cid', $seminar->created_by)
            ->value('id') ?? 0;

        $groupSession = GroupSession::query()->updateOrCreate(
            ['group_session_id' => $seminar->cts_group_session_id],
            [
                'rts_id' => 14, // Hardcoded OBS->S1 id
                'name' => mb_substr($seminar->name, 0, self::CTS_GROUP_SESSION_NAME_LIMIT),
                'description' => mb_substr($seminar->name, 0, self::CTS_GROUP_SESSION_DESCRIPTION_LIMIT),
                'date' => $seminar->date->format('Y-m-d'),
                'from' => $seminar->from,
                'to' => $seminar->to,
                'min_target_rating' => 0,
                'max_target_rating' => 12, // Allow any ratings for the time being
                'min_mentor_rating' => 0,
                'max_mentors' => 10,
                'max_students' => $seminar->capacity,
                'sequence_cutoff' => 999,
                'members_only' => 1,
                'leader_id' => $leaderMemberId,
                'prepared' => 0,
                'completed' => 0,
            ]
        );

        if (! $seminar->cts_group_session_id) {
            $seminar->forceFill(['cts_group_session_id' => $groupSession->group_session_id])->saveQuietly();
        }
    }

    public function syncAttendee(SeminarAttendee $attendee): void
    {
        $seminar = $attendee->seminar;
        if (! $seminar->cts_group_session_id) {
            return;
        }

        $memberId = Member::query()
            ->where('cid', $attendee->account_id)
            ->value('id');

        if (! $memberId) {
            return;
        }

        $groupSessionStudent = GroupSessionStudent::query()->firstOrCreate([
            'group_session_id' => $seminar->cts_group_session_id,
            'member_id' => $memberId,
        ], [
            'addedBy' => 'CORE',
            'signup_date' => now(),
        ]);

        $attendee->forceFill([
            'cts_group_sessions_student_id' => $groupSessionStudent->group_sessions_student_id,
        ])->saveQuietly();
    }
}
