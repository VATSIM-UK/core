<?php

namespace Tests\Feature\TrainingPanel\EmailSettings;

use App\Enums\EmailType;
use App\Filament\Training\Pages\EmailSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class EmailSettingsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private function buildFormData(array $overrides = []): array
    {
        $data = [];
        foreach (EmailType::cases() as $type) {
            $data[$type->value] = true;
        }

        return array_merge($data, $overrides);
    }

    #[Test]
    public function it_renders_for_authenticated_user(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_defaults_every_checkbox_to_checked_for_new_users(): void
    {
        $this->assertCount(0, $this->panelUser->fresh()->emailSettings);

        $component = Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class);

        foreach (EmailType::cases() as $type) {
            $component->assertSet("data.{$type->value}", true);
        }
    }

    #[Test]
    public function it_pre_fills_disabled_types_as_unchecked(): void
    {
        $this->panelUser->setEmailEnabled(EmailType::ExamAccepted, false);
        $this->panelUser->setEmailEnabled(EmailType::ExamCancelled, false);

        $component = Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class);

        $component->assertSet('data.exam_accepted', false);
        $component->assertSet('data.exam_cancelled', false);
        $component->assertSet('data.session_accepted_by_mentor', true);
    }

    #[Test]
    public function submitting_with_all_checked_clears_all_rows(): void
    {
        $this->panelUser->setEmailEnabled(EmailType::ExamAccepted, false);
        $this->panelUser->setEmailEnabled(EmailType::SessionAcceptedByMentor, false);
        $this->assertCount(2, $this->panelUser->fresh()->emailSettings);

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(0, $this->panelUser->fresh()->emailSettings);
    }

    #[Test]
    public function submitting_with_one_unchecked_creates_one_row(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData(['exam_accepted' => false]))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(1, $this->panelUser->fresh()->emailSettings);
        $this->assertDatabaseHas('mship_email_settings', [
            'account_id' => $this->panelUser->id,
            'email_type' => 'exam_accepted',
            'enabled' => false,
        ]);
    }

    #[Test]
    public function submitting_then_resubmitting_all_checked_removes_rows(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData(['exam_accepted' => false, 'exam_cancelled' => false]))
            ->call('save');

        $this->assertCount(2, $this->panelUser->fresh()->emailSettings);

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData())
            ->call('save');

        $this->assertCount(0, $this->panelUser->fresh()->emailSettings);
    }

    #[Test]
    public function submitting_a_known_active_type_persists_correctly(): void
    {
        $formData = $this->buildFormData();
        $formData['session_cancelled_by_mentor'] = false;

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $formData)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mship_email_settings', [
            'account_id' => $this->panelUser->id,
            'email_type' => 'session_cancelled_by_mentor',
            'enabled' => false,
        ]);

        $this->assertDatabaseMissing('mship_email_settings', [
            'account_id' => $this->panelUser->id,
            'email_type' => 'exam_accepted',
        ]);
    }

    #[Test]
    public function it_persists_only_falses_even_after_multiple_saves(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData(['exam_accepted' => false, 'session_accepted_by_mentor' => false]))
            ->call('save');

        $this->assertCount(2, $this->panelUser->fresh()->emailSettings);

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData([
                'exam_accepted' => false,
                'session_accepted_by_mentor' => false,
                'session_cancelled_by_mentor' => false,
            ]))
            ->call('save');

        $this->assertCount(3, $this->panelUser->fresh()->emailSettings);
    }

    #[Test]
    public function re_enabling_a_previously_disabled_type_deletes_its_row(): void
    {
        $this->panelUser->setEmailEnabled(EmailType::ExamAccepted, false);
        $this->assertCount(1, $this->panelUser->fresh()->emailSettings);

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $this->buildFormData(['exam_accepted' => true]))
            ->call('save');

        $this->assertCount(0, $this->panelUser->fresh()->emailSettings);
    }

    #[Test]
    public function saving_only_persists_data_for_active_enum_cases(): void
    {
        $formData = $this->buildFormData();
        $formData['not_a_real_type'] = false;

        Livewire::actingAs($this->panelUser)
            ->test(EmailSettings::class)
            ->set('data', $formData)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(0, $this->panelUser->fresh()->emailSettings);
    }
}
