<?php

namespace Tests\Unit\VisitTransfer;

use App\Events\VisitTransfer\ApplicationWithdrawn;
use App\Exceptions\VisitTransfer\Application\ApplicationCannotBeWithdrawnException;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicationWithdrawalTest extends TestCase
{
    public static function withdrawableApplications(): iterable
    {
        foreach ([Application::TYPE_VISIT, Application::TYPE_TRANSFER] as $type) {
            foreach ([Application::STATUS_IN_PROGRESS, Application::STATUS_SUBMITTED] as $status) {
                foreach (['none', 'finite', 'unlimited', 'no-training'] as $facility) {
                    yield "$type-$status-$facility" => [$type, $status, $facility];
                }
            }
        }
    }

    #[Test]
    #[DataProvider('withdrawableApplications')]
    public function withdrawal_permanently_removes_the_application_and_releases_capacity(int $type, int $status, string $facilityKind): void
    {
        Event::fake([ApplicationWithdrawn::class]);
        $facility = $facilityKind === 'none' ? null : Facility::factory()->createQuietly([
            'training_required' => $facilityKind !== 'no-training',
            'training_spaces' => $facilityKind === 'unlimited' ? null : 3,
        ]);
        $application = $this->application(['type' => $type, 'status' => $status, 'facility_id' => $facility?->id]);
        $publicId = $application->public_id;

        $application->withdraw();

        $this->assertDatabaseMissing('vt_application', ['id' => $application->id]);
        $this->assertNull(Application::withTrashed()->find($application->id));
        $this->assertFalse($application->account->visitTransferApplications()->whereKey($application->id)->exists());
        if ($facility) {
            $this->assertSame(match ($facilityKind) {
                'unlimited' => null,
                'no-training' => 3,
                default => 4,
            }, $facility->fresh()->training_spaces);
        }
        Event::assertDispatchedTimes(ApplicationWithdrawn::class, 1);
        Event::assertDispatched(ApplicationWithdrawn::class, fn ($event) => $event->application->public_id === $publicId
            && $event->application->status === Application::STATUS_WITHDRAWN);
    }

    public static function nonWithdrawableStatuses(): iterable
    {
        foreach ([15, 16, 50, 60, 70, 90, 93, 96, 99] as $status) {
            yield (string) $status => [$status];
        }
    }

    #[Test]
    #[DataProvider('nonWithdrawableStatuses')]
    public function non_withdrawable_applications_are_preserved(int $status): void
    {
        Event::fake([ApplicationWithdrawn::class]);
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $application = $this->application(['status' => $status, 'facility_id' => $facility->id]);

        try {
            $application->withdraw();
            $this->fail('Withdrawal should reject this status.');
        } catch (ApplicationCannotBeWithdrawnException) {
            $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => $status]);
            $this->assertSame(3, $facility->fresh()->training_spaces);
            Event::assertNotDispatched(ApplicationWithdrawn::class);
        }
    }

    #[Test]
    public function stale_models_cannot_withdraw_an_application_that_has_progressed(): void
    {
        $application = $this->application();
        DB::table('vt_application')->where('id', $application->id)->update(['status' => Application::STATUS_ACCEPTED]);

        $this->expectException(ApplicationCannotBeWithdrawnException::class);
        $application->withdraw();
    }

    #[Test]
    public function stale_models_cannot_release_capacity_a_second_time(): void
    {
        Event::fake([ApplicationWithdrawn::class]);
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $application = $this->application(['facility_id' => $facility->id]);
        $stale = $application->fresh();
        $application->withdraw();

        foreach ([$application, $stale] as $copy) {
            try {
                $copy->withdraw();
                $this->fail('A deleted application cannot be withdrawn again.');
            } catch (ApplicationCannotBeWithdrawnException) {
                $this->assertSame(4, $facility->fresh()->training_spaces);
            }
        }
        Event::assertDispatchedTimes(ApplicationWithdrawn::class, 1);
    }

    #[Test]
    public function deletion_failure_rolls_back_status_and_capacity(): void
    {
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $application = $this->application(['facility_id' => $facility->id]);
        Application::forceDeleting(function () {
            throw new \RuntimeException('Deletion failed');
        });

        try {
            $application->withdraw();
            $this->fail('The deletion failure must propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Deletion failed', $exception->getMessage());
            $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => Application::STATUS_SUBMITTED, 'deleted_at' => null]);
            $this->assertSame(3, $facility->fresh()->training_spaces);
        }
    }

    public static function cancelledModelEvents(): iterable
    {
        yield 'status save' => ['saving'];
        yield 'permanent deletion' => ['forceDeleting'];
    }

    #[Test]
    #[DataProvider('cancelledModelEvents')]
    public function cancelled_model_operations_roll_back_withdrawal(string $modelEvent): void
    {
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $application = $this->application(['facility_id' => $facility->id]);
        Application::$modelEvent(fn () => false);

        try {
            $application->withdraw();
            $this->fail('A cancelled model operation must fail withdrawal.');
        } catch (ApplicationCannotBeWithdrawnException) {
            $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => Application::STATUS_SUBMITTED, 'deleted_at' => null]);
            $this->assertSame(3, $facility->fresh()->training_spaces);
        }
    }

    #[Test]
    public function cancelled_capacity_update_rolls_back_withdrawal(): void
    {
        Event::fake([ApplicationWithdrawn::class]);
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $application = $this->application(['facility_id' => $facility->id]);
        Facility::updating(fn () => false);

        try {
            $application->withdraw();
            $this->fail('A cancelled capacity update must fail withdrawal.');
        } catch (ApplicationCannotBeWithdrawnException) {
            $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => Application::STATUS_SUBMITTED, 'deleted_at' => null]);
            $this->assertSame(3, $facility->fresh()->training_spaces);
            Event::assertNotDispatched(ApplicationWithdrawn::class);
        }
    }

    private function application(array $attributes = []): Application
    {
        return Application::factory()->createQuietly(array_merge([
            'account_id' => Account::factory()->createQuietly()->id,
            'type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
            'status' => Application::STATUS_SUBMITTED,
        ], $attributes));
    }
}
