<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Libraries\Discord;
use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarMentorAnnouncementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarMentorAnnouncementServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_sends_the_message_to_the_mentors_discord_channel(): void
    {
        $seminar = Seminar::factory()->create([
            'name' => 'OBS to S1 Seminar',
            'date' => now()->addWeeks(2)->format('Y-m-d'),
            'from' => '19:00',
            'to' => '21:00',
        ]);

        $discord = $this->mock(Discord::class);
        $discord->shouldReceive('sendMessageToChannel')
            ->once()
            ->with(
                Seminar::MENTORS_DISCORD_CHANNEL_ID,
                ['embeds' => [app(SeminarMentorAnnouncementService::class)->buildEmbed($seminar)]]
            );

        app(SeminarMentorAnnouncementService::class)->announceToMentors($seminar);
    }
}
