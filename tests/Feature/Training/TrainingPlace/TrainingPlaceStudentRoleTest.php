<?php

declare(strict_types=1);

namespace Tests\Feature\Training\TrainingPlace;

use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceStudentRoleTest extends TestCase
{
    #[Test]
    public function it_assigns_the_student_role_when_a_training_place_is_created_and_removes_it_when_deleted(): void
    {
        $student = $this->student();
        $position = $this->trainingPosition('OBS to S1 Training');

        $place = $this->place($student, $position);

        $this->assertTrue($student->fresh()->hasRole('OBS Students'));

        $place->delete();

        $this->assertFalse($student->fresh()->hasRole('OBS Students'));
    }

    #[Test]
    public function it_keeps_a_shared_heathrow_student_role_while_another_heathrow_place_remains(): void
    {
        $student = $this->student();
        $ground = $this->place($student, $this->trainingPosition('Heathrow GMC'));
        $this->place($student, $this->trainingPosition('Heathrow AIR'));

        $this->assertTrue($student->fresh()->hasRole('ATC Students (Heathrow)'));

        $ground->delete();

        $this->assertTrue($student->fresh()->hasRole('ATC Students (Heathrow)'));
    }

    #[Test]
    public function it_assigns_pilot_and_tfp_student_roles_from_the_qualification(): void
    {
        $student = $this->student();
        $ppl = $this->placeForQualification($student, 'PPL');
        $this->placeForQualification($student, 'TFP');

        $student = $student->fresh();
        $this->assertTrue($student->hasRole('Pilot Students'));
        $this->assertTrue($student->hasRole('TFP Student'));

        $ppl->delete();

        $student = $student->fresh();
        $this->assertFalse($student->hasRole('Pilot Students'));
        $this->assertTrue($student->hasRole('TFP Student'));
    }

    #[Test]
    public function it_resyncs_the_student_role_when_the_trainable_changes(): void
    {
        $student = $this->student();
        $place = $this->place($student, $this->trainingPosition('OBS to S1 Training'));

        $this->assertTrue($student->fresh()->hasRole('OBS Students'));

        $tower = $this->trainingPosition('S2 Training');
        $place->update([
            'trainable_type' => TrainingPosition::class,
            'trainable_id' => $tower->id,
        ]);

        $student = $student->fresh();
        $this->assertFalse($student->hasRole('OBS Students'));
        $this->assertTrue($student->hasRole('ATC Students (TWR)'));
    }

    #[Test]
    public function it_restores_the_student_role_when_the_training_place_is_restored(): void
    {
        $student = $this->student();
        $place = $this->place($student, $this->trainingPosition('S3 Training'));

        $place->delete();
        $this->assertFalse($student->fresh()->hasRole('ATC Students (APP)'));

        $place->restore();

        $this->assertTrue($student->fresh()->hasRole('ATC Students (APP)'));
    }

    #[Test]
    public function it_does_not_assign_a_student_role_when_the_training_position_has_no_category(): void
    {
        $student = $this->student();
        $position = TrainingPosition::factory()->create(['category' => null]);

        $this->place($student, $position);

        $student = $student->fresh();

        foreach (MentorPermissionService::studentRoleNames() as $roleName) {
            $this->assertFalse($student->hasRole($roleName));
        }
    }

    private function student(): Account
    {
        $student = Account::factory()->create();
        Member::factory()->forAccount($student)->create();

        return $student;
    }

    private function trainingPosition(string $category): TrainingPosition
    {
        return TrainingPosition::factory()->create([
            'category' => $category,
        ]);
    }

    private function place(Account $student, TrainingPosition $position): TrainingPlace
    {
        return TrainingPlace::factory()
            ->forTrainingPosition($position)
            ->create(['account_id' => $student->id]);
    }

    private function placeForQualification(Account $student, string $code): TrainingPlace
    {
        $callsign = MentorPermissionService::QUALIFICATION_CTS_POSITION_MAP[$code];
        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        $qualification = Qualification::firstWhere('code', $code)
            ?? Qualification::factory()->create([
                'code' => $code,
                'type' => $code === 'TFP' ? 'pilot_virtual' : 'pilot',
                'vatsim' => 0,
            ]);

        return TrainingPlace::factory()
            ->forQualification($qualification)
            ->create(['account_id' => $student->id]);
    }
}
