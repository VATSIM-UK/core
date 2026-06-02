<?php

namespace Tests\Unit\Services\Training\Mentoring;

use App\Libraries\Discord;
use App\Models\Cts\Session;
use App\Services\Training\MentoringAnnouncementService;
use App\Services\Training\MentorPermissionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringAnnouncementServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeService(?MentorPermissionService $permissionService = null): MentoringAnnouncementService
    {
        return new MentoringAnnouncementService(new Discord, $permissionService ?? $this->createMock(MentorPermissionService::class));
    }

    private function makeSession(array $attributes = []): Session
    {
        return Session::factory()->make(array_merge([
            'mentor_id' => 100,
            'position' => 'EGLL_TWR',
            'taken_date' => now()->addDay()->format('Y-m-d'),
            'taken_from' => '12:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
        ], $attributes));
    }

    private function makeAtcPermissionService(string $category = 'S2 Training'): MentorPermissionService
    {
        $mock = $this->createMock(MentorPermissionService::class);
        $mock->method('resolveCategoryForCtsCallsign')->willReturn($category);

        return $mock;
    }

    #[Test]
    public function it_disallows_posting_announcements_for_filed_sessions(): void
    {
        $session = $this->makeSession(['filed' => now()]);
        $this->assertFalse($this->makeService()->canPostAnnouncement($session, 100));
    }

    #[Test]
    public function it_disallows_posting_announcements_for_cancelled_sessions(): void
    {
        $session = $this->makeSession(['cancelled_datetime' => now()]);
        $this->assertFalse($this->makeService()->canPostAnnouncement($session, 100));
    }

    #[Test]
    public function it_disallows_posting_announcements_for_past_sessions(): void
    {
        $session = $this->makeSession([
            'taken_date' => now()->subDay()->format('Y-m-d'),
            'taken_from' => '12:00:00',
        ]);

        $this->assertFalse($this->makeService()->canPostAnnouncement($session, 100));
    }

    #[Test]
    public function it_disallows_posting_announcements_for_non_mentors(): void
    {
        $session = $this->makeSession(['mentor_id' => 100]);
        $this->assertFalse($this->makeService()->canPostAnnouncement($session, 999));
    }

    #[Test]
    public function it_allows_posting_announcements_for_the_assigned_mentor(): void
    {
        $session = $this->makeSession(['mentor_id' => 100]);
        $this->assertTrue($this->makeService()->canPostAnnouncement($session, 100));
    }

    #[Test]
    public function it_builds_atc_message_with_no_mentions_and_no_notes(): void
    {
        config()->set('training.discord.mentoring_pilot_role_id', '111');
        config()->set('training.discord.mentoring_controller_role_id', '222');

        $service = $this->makeService($this->makeAtcPermissionService());
        $session = $this->makeSession(['position' => 'EGPH_TWR', 'taken_date' => '2026-01-11', 'taken_from' => '12:00:00']);

        $message = $service->buildMessage($session, ['ping_pilot' => false, 'ping_controller' => false, 'notes' => '']);

        $this->assertStringContainsString('ATC Mentoring Session', $message);
        $this->assertStringContainsString('EGPH_TWR', $message);
        $this->assertStringNotContainsString('<@&111>', $message);
        $this->assertStringNotContainsString('<@&222>', $message);
        $this->assertStringNotContainsString('**Notes:**', $message);
    }

    #[Test]
    public function it_builds_atc_message_with_mentions_and_notes(): void
    {
        config()->set('training.discord.mentoring_pilot_role_id', '111');
        config()->set('training.discord.mentoring_controller_role_id', '222');

        $service = $this->makeService($this->makeAtcPermissionService());
        $session = $this->makeSession(['position' => 'EGPH_TWR', 'taken_date' => '2026-01-11', 'taken_from' => '12:00:00']);

        $message = $service->buildMessage($session, ['ping_pilot' => true, 'ping_controller' => true, 'notes' => '  Some notes here  ']);

        $this->assertStringContainsString('<@&111>', $message);
        $this->assertStringContainsString('<@&222>', $message);
        $this->assertStringContainsString("**Notes:**\nSome notes here", $message);
    }

    #[Test]
    public function it_builds_pilot_message_for_pilot_category_sessions(): void
    {
        $mock = $this->createMock(MentorPermissionService::class);
        $mock->method('resolveCategoryForCtsCallsign')->willReturn('P1 Training');

        $session = $this->makeSession(['position' => 'P1_PPL(A)', 'taken_date' => '2026-01-11', 'taken_from' => '12:00:00']);

        $message = $this->makeService($mock)->buildMessage($session, ['ping_pilot' => false, 'ping_controller' => false, 'notes' => '']);

        $this->assertStringContainsString('Pilot Mentoring Session', $message);
        $this->assertStringNotContainsString('ATC Mentoring Session', $message);
    }

    #[Test]
    public function it_includes_discord_timestamps_in_the_message(): void
    {
        $service = $this->makeService($this->makeAtcPermissionService());
        $session = $this->makeSession(['taken_date' => '2026-01-11', 'taken_from' => '12:00:00']);

        $unix = CarbonImmutable::parse('2026-01-11 12:00:00')->utc()->getTimestamp();

        $message = $service->buildMessage($session, ['ping_pilot' => false, 'ping_controller' => false, 'notes' => '']);

        $this->assertStringContainsString("<t:{$unix}:F>", $message);
        $this->assertStringContainsString("<t:{$unix}:R>", $message);
    }

    #[Test]
    public function it_only_pings_pilot_role_when_only_pilot_selected(): void
    {
        config()->set('training.discord.mentoring_pilot_role_id', '111');
        config()->set('training.discord.mentoring_controller_role_id', '222');

        $message = $this->makeService($this->makeAtcPermissionService())->buildMessage(
            $this->makeSession(),
            ['ping_pilot' => true, 'ping_controller' => false, 'notes' => ''],
        );

        $this->assertStringContainsString('<@&111>', $message);
        $this->assertStringNotContainsString('<@&222>', $message);
    }

    #[Test]
    public function it_only_pings_controller_role_when_only_controller_selected(): void
    {
        config()->set('training.discord.mentoring_pilot_role_id', '111');
        config()->set('training.discord.mentoring_controller_role_id', '222');

        $message = $this->makeService($this->makeAtcPermissionService())->buildMessage(
            $this->makeSession(),
            ['ping_pilot' => false, 'ping_controller' => true, 'notes' => ''],
        );

        $this->assertStringNotContainsString('<@&111>', $message);
        $this->assertStringContainsString('<@&222>', $message);
    }
}
