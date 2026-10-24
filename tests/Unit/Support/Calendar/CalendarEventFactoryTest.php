<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Calendar;

use App\Enums\CalendarViewer;
use App\Filament\Training\Pages\Exam\Exams;
use App\Filament\Training\Pages\Mentor\Mentoring;
use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Filament\Training\Pages\MyTraining\MyExams;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Support\Calendar\CalendarEventFactory;
use Closure;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarEventFactoryTest extends TestCase
{
    #[Test]
    public function it_describes_a_mentoring_session_for_the_student(): void
    {
        $event = CalendarEventFactory::forSession($this->makeSession(), CalendarViewer::Student, $this->names());

        $this->assertSame('EGLL_TWR - Mentoring session', $event->summary);
        $this->assertSame(
            "Position: EGLL_TWR\nMentor: John Smith\n\nManage: ".MyAvailability::getUrl(panel: 'training'),
            $event->description,
        );
        $this->assertSame('EGLL_TWR', $event->location);
        $this->assertSame(MyAvailability::getUrl(panel: 'training'), $event->url);
    }

    #[Test]
    public function it_describes_a_mentoring_session_for_the_mentor(): void
    {
        $event = CalendarEventFactory::forSession($this->makeSession(), CalendarViewer::Instructor, $this->names());

        $this->assertSame(
            "Position: EGLL_TWR\nStudent: Anna Brown (1234567)\nMentor: John Smith\n\nManage: ".Mentoring::getUrl(panel: 'training'),
            $event->description,
        );
    }

    #[Test]
    public function it_adds_the_note_before_the_manage_link(): void
    {
        $event = CalendarEventFactory::forSession($this->makeSession(), CalendarViewer::Student, $this->names(), 'Rescheduled from earlier.');

        $this->assertStringEndsWith("Mentor: John Smith\nRescheduled from earlier.\n\nManage: ".MyAvailability::getUrl(panel: 'training'), $event->description);
    }

    #[Test]
    public function it_ends_an_overnight_session_on_the_next_day(): void
    {
        $session = $this->makeSession()->forceFill(['taken_from' => '23:00:00', 'taken_to' => '01:00:00']);

        $event = CalendarEventFactory::forSession($session, CalendarViewer::Student, $this->names());

        $this->assertSame('2026-11-02 23:00:00', $event->startsAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-03 01:00:00', $event->endsAt->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_describes_an_exam_for_the_student(): void
    {
        $event = CalendarEventFactory::forExam($this->makeExam(), CalendarViewer::Student, $this->names());

        $this->assertSame('EGLL_TWR - Practical exam', $event->summary);
        $this->assertSame(
            "Position: EGLL_TWR\nExam: TWR\nExaminer: John Smith\n\nManage: ".MyExams::getUrl(panel: 'training'),
            $event->description,
        );
    }

    #[Test]
    public function it_describes_an_exam_for_the_examiner(): void
    {
        $event = CalendarEventFactory::forExam($this->makeExam(), CalendarViewer::Instructor, $this->names());

        $this->assertSame(
            "Position: EGLL_TWR\nExam: TWR\nStudent: Anna Brown (1234567)\nExaminer: John Smith\n\nManage: ".Exams::getUrl(panel: 'training'),
            $event->description,
        );
    }

    #[Test]
    public function it_leaves_out_an_examiner_that_is_not_assigned_yet(): void
    {
        $exam = $this->makeExam()->setRelation('examiners', null);

        $event = CalendarEventFactory::forExam($exam, CalendarViewer::Student, $this->names());

        $this->assertStringNotContainsString('Examiner', $event->description);
    }

    #[Test]
    public function it_links_a_standard_booking_to_the_calendar(): void
    {
        $booking = (new Booking)->forceFill([
            'id' => 4821,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => '2026-11-03 17:00:00',
            'ends_at' => '2026-11-03 19:00:00',
        ]);
        $booking->setRelation('ctsBooking', null)->setRelation('position', (object) ['callsign' => 'EGLL_TWR']);

        $event = CalendarEventFactory::forBooking($booking);
        $url = route('site.bookings.calendar', ['booking_id' => 4821]);

        $this->assertSame('EGLL_TWR - ATC booking', $event->summary);
        $this->assertSame("Position: EGLL_TWR\n\nManage: {$url}", $event->description);
        $this->assertSame($url, $event->url);
    }

    private function names(): Closure
    {
        return fn (Member $member): ?string => [1234567 => 'Anna Brown', 7654321 => 'John Smith'][(int) $member->cid] ?? null;
    }

    private function makeSession(): Session
    {
        $session = (new Session)->forceFill([
            'id' => 812,
            'position' => 'EGLL_TWR',
            'taken_date' => '2026-11-02',
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        return $session
            ->setRelation('student', (new Member)->forceFill(['cid' => 1234567]))
            ->setRelation('mentor', (new Member)->forceFill(['cid' => 7654321]));
    }

    private function makeExam(): ExamBooking
    {
        $exam = (new ExamBooking)->forceFill([
            'id' => 95,
            'exam' => 'TWR',
            'position_1' => 'EGLL_TWR',
            'taken_date' => '2026-11-09',
            'taken_from' => '19:00:00',
            'taken_to' => '21:00:00',
        ]);

        return $exam
            ->setRelation('student', (new Member)->forceFill(['cid' => 1234567]))
            ->setRelation('examiners', (new PracticalExaminers)->setRelation('primaryExaminer', (new Member)->forceFill(['cid' => 7654321])));
    }
}
