<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarMentorAnnouncementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarMentorAnnouncementServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_sends_the_message_to_the_mentors_discord_channel(): void
    {
        Http::fake([
            'discord.com/api/v10/channels/705818534885720176/messages' => Http::response(['id' => '123'], 200),
        ]);

        $seminar = Seminar::factory()->create([
            'name' => 'OBS to S1 Seminar',
            'date' => now()->addWeeks(2)->format('Y-m-d'),
            'from' => '19:00',
            'to' => '21:00',
        ]);

        app(SeminarMentorAnnouncementService::class)->announceToMentors($seminar);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/channels/705818534885720176/messages');
        });
    }
}
