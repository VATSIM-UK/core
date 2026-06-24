<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel;

use App\Services\TimezoneService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TimezoneDetectTest extends TestCase
{
    private TimezoneService $tzService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tzService = app(TimezoneService::class);

        session()->forget(TimezoneService::SESSION_KEY);
        session()->forget(TimezoneService::SESSION_BROWSER_KEY);
    }

    #[Test]
    public function it_stores_browser_timezone(): void
    {
        $this->actingAs($this->user);

        $this->post(route('training.timezone.detect'), [
            'timezone' => 'America/New_York',
        ])->assertSuccessful()->assertJson(['success' => true]);

        $this->assertEquals('America/New_York', $this->tzService->getBrowserTimezone());
    }

    #[Test]
    public function it_auto_activates_timezone_on_first_visit(): void
    {
        $this->actingAs($this->user);

        // Session key should not exist yet
        $this->assertFalse(session()->has(TimezoneService::SESSION_KEY));

        $this->post(route('training.timezone.detect'), [
            'timezone' => 'Europe/Paris',
        ])->assertSuccessful();

        // Should auto-activate since nothing was chosen before
        $this->assertEquals('Europe/Paris', $this->tzService->getTimezone());
    }

    #[Test]
    public function it_does_not_overwrite_existing_timezone_choice(): void
    {
        $this->actingAs($this->user);

        // User already chose a timezone
        $this->tzService->setTimezone('Asia/Tokyo');

        $this->post(route('training.timezone.detect'), [
            'timezone' => 'America/Chicago',
        ])->assertSuccessful();

        // Should NOT overwrite the explicit choice
        $this->assertEquals('Asia/Tokyo', $this->tzService->getTimezone());
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->post(route('training.timezone.detect'), [
            'timezone' => 'UTC',
        ])->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_validates_timezone_is_required(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('training.timezone.detect'), [
            // missing timezone
        ]);

        $response->assertSessionHasErrors('timezone');
    }
}
