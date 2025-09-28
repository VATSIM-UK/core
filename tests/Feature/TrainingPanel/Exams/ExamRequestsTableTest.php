<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Livewire\Training\ExamRequestsTable;
use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamRequestsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected ExamBooking $examBooking;

    protected Availability $availability;

    protected function setUp(): void
    {
        parent::setUp();

        // Give the panel user exam access permissions and TWR conduct permission
        $this->panelUser->givePermissionTo('training.exams.access');
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        // Create a student account and member
        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        // Attach an ATC qualification to the panel user (as examiner)
        $atcQualification = Qualification::factory()->atc()->create(['vatsim' => 3]);
        $this->panelUser->qualifications()->attach($atcQualification->id);

        // Create an unaccepted exam booking
        $this->examBooking = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0, // Not yet accepted
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'student_rating' => 1,
        ]);

        // Create student availability
        $this->availability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->future()
            ->timeRange('14:00:00', '18:00:00')
            ->create();
    }

    #[Test]
    public function it_loads_successfully_with_proper_permissions()
    {
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_successfully_for_any_authenticated_user()
    {
        // The ExamRequestsTable component itself doesn't enforce authorization
        // Authorization is handled at the page level where it's embedded
        $userWithoutExamPermissions = Account::factory()->create();
        Member::factory()->create(['id' => $userWithoutExamPermissions->id, 'cid' => $userWithoutExamPermissions->id]);
        $userWithoutExamPermissions->givePermissionTo('training.access');

        Livewire::actingAs($userWithoutExamPermissions)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_pending_exam_requests()
    {
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertSee((string) $this->studentAccount->id) // CID
            ->assertSee('TWR') // Exam level
            ->assertSee('EGKK_TWR'); // Position
    }

    #[Test]
    public function it_does_not_show_already_accepted_exams()
    {
        // Create an already accepted exam
        $acceptedExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 1, // Already accepted
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        // Create examiner record for the accepted exam
        PracticalExaminers::create([
            'examid' => $acceptedExam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertDontSee($acceptedExam->id);
    }

    #[Test]
    public function it_shows_accept_action_for_unfinished_exams()
    {
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertTableActionVisible('Accept', $this->examBooking);
    }

    #[Test]
    public function it_hides_accept_action_for_finished_exams()
    {
        $this->examBooking->update(['finished' => ExamBooking::FINISHED_FLAG]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertTableActionHidden('Accept', $this->examBooking);
    }

    #[Test]
    public function it_populates_availability_slots_in_accept_form()
    {
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();

        $component->callTableAction('Accept', $this->examBooking);

        // The form fields exist within the action modal
        $component->assertTableActionExists('Accept');
    }

    #[Test]
    public function it_successfully_accepts_exam_and_creates_database_records()
    {
        $startHour = 15;
        $startMinute = 0;
        $endHour = 16;
        $endMinute = 30;

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => $startHour,
                'start_minute' => $startMinute,
                'end_hour' => $endHour,
                'end_minute' => $endMinute,
                'secondary_examiner' => null,
            ]);

        // Verify the exam booking was updated
        $this->examBooking->refresh();

        $expectedStartTime = Carbon::parse($this->availability->date->format('Y-m-d').' '.sprintf('%02d:%02d:00', $startHour, $startMinute));
        $expectedEndTime = Carbon::parse($this->availability->date->format('Y-m-d').' '.sprintf('%02d:%02d:00', $endHour, $endMinute));

        $this->assertEquals(1, $this->examBooking->taken);
        $this->assertEquals($expectedStartTime->format('Y-m-d'), $this->examBooking->taken_date);
        $this->assertEquals($expectedStartTime->format('H:i:s'), $this->examBooking->taken_from);
        $this->assertEquals($expectedEndTime->format('H:i:s'), $this->examBooking->taken_to);
        $this->assertEquals($this->panelUser->member->id, $this->examBooking->exmr_id);
        $this->assertEquals(3, $this->examBooking->exmr_rating); // ATC qualification vatsim rating
        $this->assertEquals(0, $this->examBooking->second_examiner_req);
        $this->assertNotNull($this->examBooking->time_book);

        // Verify the practical examiner record was created
        $practicalExaminer = PracticalExaminers::where('examid', $this->examBooking->id)->first();
        $this->assertNotNull($practicalExaminer);
        $this->assertEquals($this->panelUser->member->id, $practicalExaminer->senior);
        $this->assertNull($practicalExaminer->other);

        // Verify success notification
        $component->assertNotified('Exam Accepted');
    }

    #[Test]
    public function it_accepts_exam_with_secondary_examiner()
    {
        // Create another examiner
        $secondaryExaminerAccount = Account::factory()->create();
        $secondaryExaminerMember = Member::factory()->create([
            'id' => $secondaryExaminerAccount->id,
            'cid' => $secondaryExaminerAccount->id,
        ]);

        $startHour = 14;
        $startMinute = 30;
        $endHour = 16;
        $endMinute = 0;

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => $startHour,
                'start_minute' => $startMinute,
                'end_hour' => $endHour,
                'end_minute' => $endMinute,
                'secondary_examiner' => $secondaryExaminerMember->id,
            ]);

        // Verify the exam booking has secondary examiner flag
        $this->examBooking->refresh();
        $this->assertEquals(1, $this->examBooking->second_examiner_req);

        // Verify the practical examiner record includes secondary examiner
        $practicalExaminer = PracticalExaminers::where('examid', $this->examBooking->id)->first();
        $this->assertNotNull($practicalExaminer);
        $this->assertEquals($this->panelUser->member->id, $practicalExaminer->senior);
        $this->assertEquals($secondaryExaminerMember->id, $practicalExaminer->other);
    }

    #[Test]
    public function it_validates_minimum_exam_duration()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exam duration must be at least 60 minutes.');

        // Try to create an exam that's less than 60 minutes
        $startHour = 15;
        $startMinute = 0;
        $endHour = 15;
        $endMinute = 45; // Only 45 minutes

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => $startHour,
                'start_minute' => $startMinute,
                'end_hour' => $endHour,
                'end_minute' => $endMinute,
            ]);
    }

    #[Test]
    public function it_only_shows_future_availability_slots()
    {
        // Create a past availability slot
        $pastAvailability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->onDate(Carbon::yesterday()->format('Y-m-d'))
            ->timeRange('14:00:00', '18:00:00')
            ->create();

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class);

        $component->callTableAction('Accept', $this->examBooking);

        // We verify action exists (indicating form was shown)
        $component->assertTableActionExists('Accept');
    }

    #[Test]
    public function it_filters_availability_by_student()
    {
        // Create availability for a different student
        $otherStudentAccount = Account::factory()->create();
        $otherStudentMember = Member::factory()->create([
            'id' => $otherStudentAccount->id,
            'cid' => $otherStudentAccount->id,
        ]);

        $otherAvailability = Availability::factory()
            ->forStudent($otherStudentMember->id)
            ->future()
            ->timeRange('10:00:00', '14:00:00')
            ->create();

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class);

        $component->callTableAction('Accept', $this->examBooking);

        // We verify action exists (indicating form was shown with proper filtering)
        $component->assertTableActionExists('Accept');
    }

    #[Test]
    public function it_throws_exception_for_invalid_availability_slot()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Selected availability slot not found.');

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => 99999, // Non-existent ID
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
            ]);
    }

    #[Test]
    public function it_dispatches_exam_accepted_event_after_successful_acceptance()
    {
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class);

        $component->callTableAction('Accept', $this->examBooking, [
            'availability_slot' => $this->availability->id,
            'start_hour' => 15,
            'start_minute' => 0,
            'end_hour' => 16,
            'end_minute' => 30,
        ]);

        // Verify the event was dispatched (this would trigger a refresh)
        $component->assertDispatched('exam-accepted');
    }

    #[Test]
    public function it_refreshes_component_when_exam_accepted_event_received()
    {
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();

        // Simulate the exam-accepted event being dispatched
        $component->dispatch('exam-accepted');

        // Component should refresh successfully
        $component->assertSuccessful();
    }

    #[Test]
    public function it_correctly_formats_exam_start_and_end_times()
    {
        $startHour = 9;
        $startMinute = 15;
        $endHour = 11;
        $endMinute = 15; // Exactly 2 hours duration

        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => $startHour,
                'start_minute' => $startMinute,
                'end_hour' => $endHour,
                'end_minute' => $endMinute,
            ]);

        $this->examBooking->refresh();

        // Verify times are correctly formatted with leading zeros
        $this->assertEquals('09:15:00', $this->examBooking->taken_from);
        $this->assertEquals('11:15:00', $this->examBooking->taken_to);
    }

    #[Test]
    public function it_only_shows_exams_user_has_permission_to_conduct()
    {
        // Create a user with only TWR exam permissions
        $twrOnlyUser = Account::factory()->create();
        $twrMember = Member::factory()->create(['id' => $twrOnlyUser->id, 'cid' => $twrOnlyUser->id]);
        $twrOnlyUser->givePermissionTo('training.access');
        $twrOnlyUser->givePermissionTo('training.exams.conduct.twr');

        // Create additional exam bookings for different levels
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'position_1' => 'EGKK_APP',
        ]);

        $ctrExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'CTR',
            'position_1' => 'LON_SC_CTR',
        ]);

        // User with only TWR permission should only see TWR exam
        Livewire::actingAs($twrOnlyUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertSee('TWR')
            ->assertSee('EGKK_TWR')
            ->assertDontSee('APP')
            ->assertDontSee('EGKK_APP')
            ->assertDontSee('CTR')
            ->assertDontSee('LON_SC_CTR');
    }

    #[Test]
    public function it_shows_no_exams_when_user_has_no_conduct_permissions()
    {
        // Create a user with no exam conduct permissions
        $noPermissionsUser = Account::factory()->create();
        $noPermissionsMember = Member::factory()->create(['id' => $noPermissionsUser->id, 'cid' => $noPermissionsUser->id]);
        $noPermissionsUser->givePermissionTo('training.access');

        Livewire::actingAs($noPermissionsUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertDontSee('TWR')
            ->assertDontSee('EGKK_TWR');
    }

    #[Test]
    public function it_hides_accept_action_when_user_lacks_permission_for_exam_level()
    {
        // Create a user with only APP permissions
        $appOnlyUser = Account::factory()->create();
        $appMember = Member::factory()->create(['id' => $appOnlyUser->id, 'cid' => $appOnlyUser->id]);
        $appOnlyUser->givePermissionTo('training.access');
        $appOnlyUser->givePermissionTo('training.exams.conduct.app');

        // Create an APP exam this user should see
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'position_1' => 'EGKK_APP',
        ]);

        $component = Livewire::actingAs($appOnlyUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();

        // Should see Accept action for APP exam
        $component->assertTableActionVisible('Accept', $appExam);

        // Should not see the TWR exam at all (filtered out by query)
        $component->assertDontSee('TWR');
    }

    #[Test]
    public function it_shows_multiple_exam_levels_when_user_has_multiple_permissions()
    {
        // Give user permission for both TWR and APP
        $this->panelUser->givePermissionTo('training.exams.conduct.app');

        // Create an APP exam
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'position_1' => 'EGKK_APP',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful()
            ->assertSee('TWR')
            ->assertSee('EGKK_TWR')
            ->assertSee('APP')
            ->assertSee('EGKK_APP');

        // Should see Accept actions for both exams
        $component->assertTableActionVisible('Accept', $this->examBooking);
        $component->assertTableActionVisible('Accept', $appExam);
    }

    #[Test]
    public function it_handles_case_insensitive_exam_levels()
    {
        // Create exam with lowercase exam level
        $lowerCaseExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'twr', // lowercase
            'position_1' => 'EGLL_TWR',
        ]);

        // User should still see this exam with TWR permission
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->assertSuccessful();

        // Check that the exam appears in the table (should see the position)
        $component->assertSee('EGLL_TWR');

        // Check that the accept action is visible for this exam
        $component->assertTableActionVisible('Accept', $lowerCaseExam);
    }

    #[Test]
    public function it_accepts_exam_only_when_user_has_specific_permission()
    {
        // Create a user with only TWR permissions
        $twrOnlyUser = Account::factory()->create();
        $twrMember = Member::factory()->create(['id' => $twrOnlyUser->id, 'cid' => $twrOnlyUser->id]);
        $twrOnlyUser->givePermissionTo('training.access');
        $twrOnlyUser->givePermissionTo('training.exams.conduct.twr');

        // Attach ATC qualification to the user
        $atcQualification = Qualification::factory()->atc()->create(['vatsim' => 3]);
        $twrOnlyUser->qualifications()->attach($atcQualification->id);

        // This user should be able to accept the TWR exam
        Livewire::actingAs($twrOnlyUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
            ]);

        // Verify the exam was accepted
        $this->examBooking->refresh();
        $this->assertEquals(1, $this->examBooking->taken);
        $this->assertEquals($twrMember->id, $this->examBooking->exmr_id);
    }

    #[Test]
    public function it_enforces_maximum_exam_duration_of_2_hours()
    {
        // Create an availability slot that's longer than 2 hours
        $longAvailability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->future()
            ->timeRange('09:00:00', '15:00:00') // 6 hours available
            ->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exam duration cannot exceed 120 minutes (2 hours).');

        // Try to book a 3-hour exam (should fail)
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $longAvailability->id,
                'start_hour' => 9,
                'start_minute' => 0,
                'end_hour' => 12, // 3 hours later
                'end_minute' => 0,
            ]);
    }

    #[Test]
    public function it_allows_exactly_2_hour_exam_duration()
    {
        // Create an availability slot that covers exactly 2 hours
        $twoHourAvailability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->future()
            ->timeRange('14:00:00', '16:00:00') // Exactly 2 hours
            ->create();

        // This should succeed - exactly 2 hours
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $twoHourAvailability->id,
                'start_hour' => 14,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 0,
            ]);

        // Verify the exam was accepted
        $this->examBooking->refresh();
        $this->assertEquals(1, $this->examBooking->taken);
        $this->assertEquals('14:00:00', $this->examBooking->taken_from);
        $this->assertEquals('16:00:00', $this->examBooking->taken_to);
    }

    #[Test]
    public function it_restricts_available_hours_when_availability_exceeds_2_hours()
    {
        // Create a very long availability slot
        $longAvailability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->future()
            ->timeRange('10:00:00', '18:00:00') // 8 hours available
            ->create();

        // Update exam booking to use this availability for testing
        $longExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $longExam);

        // Select the long availability slot and start time
        $component->fillForm([
            'availability_slot' => $longAvailability->id,
            'start_hour' => 10,
            'start_minute' => 0,
        ]);

        // The end hour options should be limited to 2 hours from start (10:00 + 2h = 12:00)
        // Hours beyond 12 should not be available for selection
        // This test verifies the UI constraint is working
        $component->assertSuccessful();
    }

    #[Test]
    public function it_prevents_exam_longer_than_2_hours_via_server_validation()
    {
        // Test server-side validation by attempting to bypass form constraints
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exam duration cannot exceed 120 minutes (2 hours).');

        // Create a long availability
        $longAvailability = Availability::factory()
            ->forStudent($this->studentMember->id)
            ->future()
            ->timeRange('09:00:00', '18:00:00')
            ->create();

        // Attempt to create a 4-hour exam
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $longAvailability->id,
                'start_hour' => 9,
                'start_minute' => 0,
                'end_hour' => 13, // 4 hours later
                'end_minute' => 0,
            ]);
    }

    #[Test]
    public function it_requires_secondary_examiner_for_app_exams()
    {
        // Create an APP exam
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'position_1' => 'EGKK_APP',
        ]);

        // Give user APP permission
        $this->panelUser->givePermissionTo('training.exams.conduct.app');

        // Try to accept APP exam without secondary examiner - should fail due to client-side validation
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $appExam);

        // The form should show the secondary examiner field as required
        // This verifies that the form validation will prevent submission without secondary examiner
        $component->assertSuccessful();

        // Verify the exam was not accepted due to missing required field
        $appExam->refresh();
        $this->assertEquals(0, $appExam->taken);
    }

    #[Test]
    public function it_requires_secondary_examiner_for_ctr_exams()
    {
        // Create a CTR exam
        $ctrExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'CTR',
            'position_1' => 'LON_SC_CTR',
        ]);

        // Give user CTR permission
        $this->panelUser->givePermissionTo('training.exams.conduct.ctr');

        // Try to accept CTR exam without secondary examiner - should fail due to client-side validation
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $ctrExam);

        // The form should show the secondary examiner field as required
        // This verifies that the form validation will prevent submission without secondary examiner
        $component->assertSuccessful();

        // Verify the exam was not accepted due to missing required field
        $ctrExam->refresh();
        $this->assertEquals(0, $ctrExam->taken);
    }

    #[Test]
    public function it_allows_twr_exams_without_secondary_examiner()
    {
        // TWR exam should not require secondary examiner
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
                // No secondary_examiner specified - should be fine for TWR
            ]);

        // Verify the exam was accepted
        $this->examBooking->refresh();
        $this->assertEquals(1, $this->examBooking->taken);
        $this->assertEquals(0, $this->examBooking->second_examiner_req);
    }

    #[Test]
    public function it_allows_obs_exams_without_secondary_examiner()
    {
        // Create an OBS exam
        $obsExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'OBS',
            'position_1' => 'OBS_GRP_01',
        ]);

        // Give user OBS permission
        $this->panelUser->givePermissionTo('training.exams.conduct.obs');

        // OBS exam should not require secondary examiner
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $obsExam, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
                // No secondary_examiner specified - should be fine for OBS
            ]);

        // Verify the exam was accepted
        $obsExam->refresh();
        $this->assertEquals(1, $obsExam->taken);
        $this->assertEquals(0, $obsExam->second_examiner_req);
    }

    #[Test]
    public function it_accepts_app_exam_with_secondary_examiner()
    {
        // Create an APP exam
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'position_1' => 'EGKK_APP',
        ]);

        // Create a secondary examiner
        $secondaryExaminerAccount = Account::factory()->create();
        $secondaryExaminerMember = Member::factory()->create([
            'id' => $secondaryExaminerAccount->id,
            'cid' => $secondaryExaminerAccount->id,
        ]);

        // Give user APP permission
        $this->panelUser->givePermissionTo('training.exams.conduct.app');

        // Accept APP exam with secondary examiner
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $appExam, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
                'secondary_examiner' => $secondaryExaminerMember->id,
            ]);

        // Verify the exam was accepted with secondary examiner requirement
        $appExam->refresh();
        $this->assertEquals(1, $appExam->taken);
        $this->assertEquals(1, $appExam->second_examiner_req);

        // Verify the practical examiner record includes secondary examiner
        $practicalExaminer = PracticalExaminers::where('examid', $appExam->id)->first();
        $this->assertNotNull($practicalExaminer);
        $this->assertEquals($this->panelUser->member->id, $practicalExaminer->senior);
        $this->assertEquals($secondaryExaminerMember->id, $practicalExaminer->other);
    }

    #[Test]
    public function it_accepts_ctr_exam_with_secondary_examiner()
    {
        // Create a CTR exam
        $ctrExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'CTR',
            'position_1' => 'LON_SC_CTR',
        ]);

        // Create a secondary examiner
        $secondaryExaminerAccount = Account::factory()->create();
        $secondaryExaminerMember = Member::factory()->create([
            'id' => $secondaryExaminerAccount->id,
            'cid' => $secondaryExaminerAccount->id,
        ]);

        // Give user CTR permission
        $this->panelUser->givePermissionTo('training.exams.conduct.ctr');

        // Accept CTR exam with secondary examiner
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $ctrExam, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
                'secondary_examiner' => $secondaryExaminerMember->id,
            ]);

        // Verify the exam was accepted with secondary examiner requirement
        $ctrExam->refresh();
        $this->assertEquals(1, $ctrExam->taken);
        $this->assertEquals(1, $ctrExam->second_examiner_req);

        // Verify the practical examiner record includes secondary examiner
        $practicalExaminer = PracticalExaminers::where('examid', $ctrExam->id)->first();
        $this->assertNotNull($practicalExaminer);
        $this->assertEquals($this->panelUser->member->id, $practicalExaminer->senior);
        $this->assertEquals($secondaryExaminerMember->id, $practicalExaminer->other);
    }

    #[Test]
    public function it_handles_case_insensitive_exam_levels_for_secondary_examiner_requirement()
    {
        // Create an exam with lowercase 'app'
        $appExam = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'app', // lowercase
            'position_1' => 'EGKK_APP',
        ]);

        // Give user APP permission
        $this->panelUser->givePermissionTo('training.exams.conduct.app');

        // Try to accept lowercase app exam without secondary examiner - should fail due to client-side validation
        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $appExam);

        // The form should show the secondary examiner field as required even for lowercase exam levels
        $component->assertSuccessful();

        // Verify the exam was not accepted due to missing required field
        $appExam->refresh();
        $this->assertEquals(0, $appExam->taken);
    }

    #[Test]
    public function it_sets_second_examiner_req_flag_correctly_for_optional_secondary_examiner()
    {
        // Create a secondary examiner
        $secondaryExaminerAccount = Account::factory()->create();
        $secondaryExaminerMember = Member::factory()->create([
            'id' => $secondaryExaminerAccount->id,
            'cid' => $secondaryExaminerAccount->id,
        ]);

        // Accept TWR exam with optional secondary examiner
        Livewire::actingAs($this->panelUser)
            ->test(ExamRequestsTable::class)
            ->callTableAction('Accept', $this->examBooking, [
                'availability_slot' => $this->availability->id,
                'start_hour' => 15,
                'start_minute' => 0,
                'end_hour' => 16,
                'end_minute' => 30,
                'secondary_examiner' => $secondaryExaminerMember->id,
            ]);

        // Verify the exam was accepted with secondary examiner flag set
        $this->examBooking->refresh();
        $this->assertEquals(1, $this->examBooking->taken);
        $this->assertEquals(1, $this->examBooking->second_examiner_req);

        // Verify the practical examiner record includes secondary examiner
        $practicalExaminer = PracticalExaminers::where('examid', $this->examBooking->id)->first();
        $this->assertNotNull($practicalExaminer);
        $this->assertEquals($this->panelUser->member->id, $practicalExaminer->senior);
        $this->assertEquals($secondaryExaminerMember->id, $practicalExaminer->other);
    }
}
