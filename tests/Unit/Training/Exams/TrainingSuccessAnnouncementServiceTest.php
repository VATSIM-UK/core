<?php

namespace Tests\Unit\Services\Training;

use App\Libraries\Discord;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Services\Training\TrainingSuccessesAnnouncementService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingSuccessAnnouncementServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_builds_correct_exam_message_when_member_not_in_discord()
    {
        $service = new TrainingSuccessesAnnouncementService(new Discord);
        $student = Member::factory()->create();
        $account = Account::factory()->create(['id' => $student->cid]);

        $examBooking = ExamBooking::factory()->make([
            'start_date' => '2026-01-11T12:00:00Z',
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
        ]);

        $examBooking->setRelation('student', $student);
        $examBooking->setRelation('studentAccount', $account);

        $message = $service->buildExamPassedMessage($examBooking);
        $unix = CarbonImmutable::parse($examBooking->start_date)->utc()->getTimestamp();

        $this->assertStringContainsString('Please join us in congratulating', $message);
        $this->assertStringContainsString('S2', $message);
        $this->assertStringContainsString("<t:{$unix}:R>", $message);
    }

    #[Test]
    public function it_builds_correct_exam_message_when_member_in_discord()
    {
        $service = new TrainingSuccessesAnnouncementService(new Discord);
        $student = Member::factory()->create();
        $account = Account::factory()->create(['id' => $student->cid, 'discord_id' => '123456789012345678']);

        $examBooking = ExamBooking::factory()->make([
            'start_date' => '2026-01-11T12:00:00Z',
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
        ]);

        $examBooking->setRelation('student', $student);
        $examBooking->setRelation('studentAccount', $account);

        $message = $service->buildExamPassedMessage($examBooking);
        $unix = CarbonImmutable::parse($examBooking->start_date)->utc()->getTimestamp();

        $this->assertStringContainsString('Please join us in congratulating', $message);
        $this->assertStringContainsString('S2', $message);
        $this->assertStringContainsString('<@123456789012345678>', $message);
        $this->assertStringContainsString("<t:{$unix}:R>", $message);
    }

    #[Test]
    public function it_builds_correct_endorsement_message_when_member_not_in_discord()
    {
        $service = new TrainingSuccessesAnnouncementService(new Discord);
        $account = Account::factory()->create(['discord_id' => null]);
        $positionGroup = PositionGroup::factory()->create(['name' => 'Heathrow Ground']);

        $message = $service->buildTierEndorsementMessage($account, $positionGroup);

        $this->assertStringContainsString('Please join us in congratulating', $message);
        $this->assertStringContainsString('Heathrow Ground', $message);
        $this->assertStringContainsString($account->name, $message);
        $this->assertStringNotContainsString('<@', $message);
    }

    #[Test]
    public function it_builds_correct_endorsement_message_when_member_in_discord()
    {
        $service = new TrainingSuccessesAnnouncementService(new Discord);
        $account = Account::factory()->create(['discord_id' => '123456789012345678']);
        $positionGroup = PositionGroup::factory()->create(['name' => 'Heathrow Ground']);

        $message = $service->buildTierEndorsementMessage($account, $positionGroup);

        $this->assertStringContainsString('Please join us in congratulating', $message);
        $this->assertStringContainsString('Heathrow Ground', $message);
        $this->assertStringContainsString('<@123456789012345678>', $message);
    }
}
