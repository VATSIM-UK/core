<?php

namespace Tests\Feature\VisitTransfer;

use App\Events\VisitTransfer\ApplicationWithdrawn;
use App\Models\Mship\Account;
use App\Models\Sys\Activity;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurgeWithdrawnApplicationsTest extends TestCase
{
    #[Test]
    public function migration_deletes_only_withdrawn_rows_without_side_effects(): void
    {
        $account = Account::factory()->createQuietly();
        $facility = Facility::factory()->createQuietly(['training_required' => true, 'training_spaces' => 3]);
        $applications = collect([10, 15, 16, 30, 50, 60, 70, 90, 93, 96, 99])->mapWithKeys(function ($status) use ($account, $facility) {
            return [$status => Application::factory()->createQuietly([
                'account_id' => $account->id,
                'facility_id' => $facility->id,
                'type' => Application::TYPE_VISIT,
                'training_team' => 'atc',
                'status' => $status,
            ])];
        });
        $trashed = Application::factory()->createQuietly([
            'account_id' => $account->id,
            'type' => Application::TYPE_TRANSFER,
            'training_team' => 'atc',
            'status' => Application::STATUS_WITHDRAWN,
            'deleted_at' => now(),
        ]);
        DB::table('vt_application')->insert(array_fill(0, 501, [
            'account_id' => $account->id,
            'facility_id' => $facility->id,
            'type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
            'status' => Application::STATUS_WITHDRAWN,
        ]));
        $note = $account->addNote('visittransfer', 'Keep this historical note.', $account, $applications[15]);
        $activity = Activity::createQuietly([
            'subject_id' => $applications[15]->id,
            'subject_type' => Application::class,
            'action' => 'updated',
        ]);
        $noteBefore = DB::table($note->getTable())->where('id', $note->id)->first();
        $activityBefore = DB::table($activity->getTable())->where('id', $activity->id)->first();
        $activitiesBefore = Activity::count();
        Event::fake([ApplicationWithdrawn::class]);

        $migration = require database_path('migrations/2026_11_01_000000_purge_withdrawn_vt_applications.php');
        $migration->up();
        $this->assertDatabaseMissing('vt_application', ['id' => $applications[15]->id]);
        $this->assertDatabaseMissing('vt_application', ['id' => $trashed->id]);
        $this->assertSame(0, DB::table('vt_application')->where('status', Application::STATUS_WITHDRAWN)->count());
        foreach ($applications->except(15) as $application) {
            $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => $application->status]);
        }
        $migration->up();
        $migration->down();
        $this->assertSame(0, DB::table('vt_application')->where('status', Application::STATUS_WITHDRAWN)->count());
        $this->assertSame(3, $facility->fresh()->training_spaces);
        $this->assertEquals($noteBefore, DB::table($note->getTable())->where('id', $note->id)->first());
        $this->assertEquals($activityBefore, DB::table($activity->getTable())->where('id', $activity->id)->first());
        $this->assertNull($activity->fresh()->subject);
        $this->assertSame([], $activity->fresh()->extra_data);
        $this->assertSame($activitiesBefore, Activity::count());
        Event::assertNotDispatched(ApplicationWithdrawn::class);
        Notification::assertNothingSent();
    }
}
