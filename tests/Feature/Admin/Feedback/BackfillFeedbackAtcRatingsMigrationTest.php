<?php

namespace Tests\Feature\Admin\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Qualification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackfillFeedbackAtcRatingsMigrationTest extends TestCase
{
    use DatabaseTransactions;

    private const REJECT_REASON = 'Rejected automatically: the controller was not rated as a controller at the time of this submission and therefore cannot be the correct recipient.';

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_08_09_000002_backfill_feedback_atc_ratings.php');
        $migration->up();
    }

    private function atcForm(): ?Form
    {
        return Form::where('slug', 'atc')->first();
    }

    private function createQualification(int $vatsim, ?string $code = null): Qualification
    {
        return Qualification::factory()->create([
            'type' => 'atc',
            'vatsim' => $vatsim,
            'code' => $code,
        ]);
    }

    private function attachQualification(Account $account, Qualification $qualification, Carbon $awardedAt): void
    {
        $account->qualifications()->attach($qualification->id, [
            'created_at' => $awardedAt,
            'updated_at' => $awardedAt,
        ]);
    }

    private function createFeedback(Form $form, Account $account, Carbon $createdAt): Feedback
    {
        $feedback = factory(Feedback::class)->create([
            'form_id' => $form->id,
            'account_id' => $account->id,
        ]);

        $feedback->timestamps = false;
        $feedback->created_at = $createdAt;
        $feedback->save();

        return $feedback;
    }

    #[Test]
    public function it_backfills_rating_from_qualification_history(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $qualification = $this->createQualification(3);
        $account = Account::factory()->create();
        $this->attachQualification($account, $qualification, now()->subYears(2));

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));

        $this->runMigration();

        $this->assertDatabaseHas('mship_feedback', [
            'id' => $feedback->id,
            'account_atc_qualification_id' => $qualification->id,
        ]);
        $this->assertNull($feedback->fresh()->deleted_at);
    }

    #[Test]
    public function it_picks_the_highest_qualification_held_at_the_time(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $second = $this->createQualification(3, 'Z2');
        $third = $this->createQualification(4, 'Z3');
        $account = Account::factory()->create();
        $this->attachQualification($account, $second, now()->subYears(2));
        $this->attachQualification($account, $third, now()->subYears(1));

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));

        $this->runMigration();

        $this->assertDatabaseHas('mship_feedback', [
            'id' => $feedback->id,
            'account_atc_qualification_id' => $third->id,
        ]);
    }

    #[Test]
    public function it_ignores_qualifications_awarded_after_the_feedback(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $second = $this->createQualification(3, 'Z2');
        $third = $this->createQualification(4, 'Z3');
        $account = Account::factory()->create();
        $this->attachQualification($account, $second, now()->subYears(2));
        $this->attachQualification($account, $third, now()->subMonths(3));

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));

        $this->runMigration();

        $this->assertDatabaseHas('mship_feedback', [
            'id' => $feedback->id,
            'account_atc_qualification_id' => $second->id,
        ]);
    }

    #[Test]
    public function it_ignores_the_pivot_deleted_at_when_qualifications_are_not_removed_on_upgrade(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $qualification = $this->createQualification(3);
        $account = Account::factory()->create();
        $this->attachQualification($account, $qualification, now()->subYears(2));
        $account->qualifications()->updateExistingPivot($qualification->id, ['deleted_at' => now()]);

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));

        $this->runMigration();

        $feedback = $feedback->fresh();

        $this->assertSame($qualification->id, $feedback->account_atc_qualification_id);
        $this->assertNull($feedback->deleted_at);
    }

    #[Test]
    public function it_rejects_feedback_where_the_recipient_was_not_rated_at_the_time(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $observer = $this->createQualification(1);
        $account = Account::factory()->create();
        $this->attachQualification($account, $observer, now()->subYears(2));

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));

        $this->runMigration();

        $feedback = $feedback->fresh();

        $this->assertNotNull($feedback->deleted_at);
        $this->assertNull($feedback->account_atc_qualification_id);
        $this->assertSame(self::REJECT_REASON, $feedback->reject_reason);
    }

    #[Test]
    public function it_skips_feedback_that_already_has_a_rating(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $qualification = $this->createQualification(3);
        $account = Account::factory()->create();
        $this->attachQualification($account, $qualification, now()->subYears(2));

        $feedback = factory(Feedback::class)->create([
            'form_id' => $form->id,
            'account_id' => $account->id,
            'account_atc_qualification_id' => $qualification->id,
        ]);

        $this->runMigration();

        $feedback = $feedback->fresh();

        $this->assertSame($qualification->id, $feedback->account_atc_qualification_id);
        $this->assertNull($feedback->deleted_at);
    }

    #[Test]
    public function it_skips_feedback_that_was_already_rejected(): void
    {
        $form = $this->atcForm();
        if (! $form) {
            $this->markTestSkipped('ATC feedback form not seeded');
        }

        $qualification = $this->createQualification(3);
        $account = Account::factory()->create();
        $this->attachQualification($account, $qualification, now()->subYears(2));

        $feedback = $this->createFeedback($form, $account, now()->subMonths(6));
        $feedback->markRejected(null, 'Manual rejection');

        $this->runMigration();

        $feedback = $feedback->fresh();

        $this->assertNotNull($feedback->deleted_at);
        $this->assertSame('Manual rejection', $feedback->reject_reason);
    }
}
