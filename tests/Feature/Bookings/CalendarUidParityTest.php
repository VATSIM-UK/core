<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Services\Bookings\BookingCalendarFeedService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarUidParityTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function the_exam_email_and_feed_share_a_uid(): void
    {
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => Carbon::tomorrow()->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
        ]);

        Booking::create([
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(11, 0),
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $mail = (new ExamAcceptedStudentNotification($exam))->toMail($studentAccount);
        $feed = app(BookingCalendarFeedService::class)->forAccount($studentAccount);

        $this->assertSame('exam-'.$exam->id.'@vatsim.uk', $this->uidFrom($mail->rawAttachments[0]['data']));
        $this->assertSame($this->uidFrom($mail->rawAttachments[0]['data']), $this->uidFrom($feed));
    }

    #[Test]
    public function the_mentoring_email_and_feed_share_a_uid(): void
    {
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();

        $mentorAccount = Account::factory()->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();

        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGKK_APP',
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->toDateString(),
            'taken_from' => '13:00:00',
            'taken_to' => '15:00:00',
        ]);

        Booking::create([
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::tomorrow()->setTime(13, 0),
            'ends_at' => Carbon::tomorrow()->setTime(15, 0),
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $mail = (new MentoringSessionAcceptedStudentNotification($session))->toMail($studentAccount);
        $feed = app(BookingCalendarFeedService::class)->forAccount($studentAccount);

        $this->assertSame('session-'.$session->id.'@vatsim.uk', $this->uidFrom($mail->rawAttachments[0]['data']));
        $this->assertSame($this->uidFrom($mail->rawAttachments[0]['data']), $this->uidFrom($feed));
    }

    private function uidFrom(string $ics): ?string
    {
        preg_match('/^UID:([^\r\n]+)/m', str_replace("\r\n ", '', $ics), $matches);

        return $matches[1] ?? null;
    }
}
