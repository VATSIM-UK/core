<?php

namespace Tests\Unit\Services\Training;

use App\Libraries\Discord;
use App\Models\Cts\ExamBooking;
use App\Services\Training\ExamAnnouncementService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamAnnouncementServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function setExaminers(ExamBooking $examBooking, int $senior, int $other, int $trainee): void
    {
        $examBooking->setRelation('examiners', (object) [
            'senior' => $senior,
            'other' => $other,
            'trainee' => $trainee,
        ]);
    }

    #[Test]
    public function it_disallows_posting_announcements_for_finished_exams()
    {
        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'finished' => ExamBooking::FINISHED_FLAG,
        ]);

        $this->setExaminers($examBooking, 10, 20, 30);

        $this->assertFalse($service->canPostAnnouncement($examBooking, 10));
        $this->assertFalse($service->canPostAnnouncement($examBooking, 20));
        $this->assertFalse($service->canPostAnnouncement($examBooking, 30));
    }

    #[Test]
    public function it_allows_posting_announcements_for_senior_examiner()
    {
        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'finished' => 0,
        ]);

        $this->setExaminers($examBooking, 123, 456, 789);

        $this->assertTrue($service->canPostAnnouncement($examBooking, 123));
    }

    #[Test]
    public function it_allows_posting_announcements_for_other_examiner()
    {
        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'finished' => 0,
        ]);

        $this->setExaminers($examBooking, 123, 456, 789);

        $this->assertTrue($service->canPostAnnouncement($examBooking, 456));
    }

    #[Test]
    public function it_allows_posting_announcements_for_trainee_examiner()
    {
        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'finished' => 0,
        ]);

        $this->setExaminers($examBooking, 123, 456, 789);

        $this->assertTrue($service->canPostAnnouncement($examBooking, 789));
    }

    #[Test]
    public function it_disallows_posting_announcements_for_non_examiners()
    {
        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'finished' => 0,
        ]);

        $this->setExaminers($examBooking, 123, 456, 789);

        $this->assertFalse($service->canPostAnnouncement($examBooking, 999));
    }

    #[Test]
    public function it_builds_message_with_no_mentions_and_no_notes_when_none_selected()
    {
        config()->set('training.discord.exam_pilot_role_id', '111');
        config()->set('training.discord.exam_controller_role_id', '222');

        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'exam' => 'S2',
            'position_1' => 'EGPH_TWR',
            'start_date' => '2026-01-11T12:00:00Z',
        ]);

        $unix = CarbonImmutable::parse('2026-01-11T12:00:00Z')->utc()->getTimestamp();

        $message = $service->buildMessage($examBooking, [
            'ping_exam_pilot' => false,
            'ping_exam_controller' => false,
            'notes' => '',
        ]);

        $this->assertStringNotContainsString('<@&111>', $message);
        $this->assertStringNotContainsString('<@&222>', $message);
        $this->assertStringNotContainsString('**Notes:**', $message);
    }

    #[Test]
    public function it_builds_message_with_mentions_and_notes_when_selected()
    {
        config()->set('training.discord.exam_pilot_role_id', '111');
        config()->set('training.discord.exam_controller_role_id', '222');

        $discord = new Discord;
        $service = new ExamAnnouncementService($discord);

        $examBooking = ExamBooking::factory()->make([
            'exam' => 'C1',
            'position_1' => 'LON_S_CTR',
            'start_date' => '2026-01-11T12:00:00Z',
        ]);

        $message = $service->buildMessage($examBooking, [
            'ping_exam_pilot' => true,
            'ping_exam_controller' => true,
            'notes' => "  NOTES GO HERE  \n",
        ]);

        $this->assertStringContainsString('<@&111>', $message);
        $this->assertStringContainsString('<@&222>', $message);

        $this->assertStringContainsString("**Notes:**\nNOTES GO HERE", $message);
    }
}
