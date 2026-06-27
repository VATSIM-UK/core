<?php

declare(strict_types=1);

namespace Tests\Unit\Training;

use App\Enums\FieldScore;
use App\Models\Cts\Member;
use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\TrainingProgressCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingProgressCalculatorTest extends TestCase
{
    use DatabaseTransactions;

    private Account $studentAccount;

    private Member $studentMember;

    private TrainingPosition $trainingPosition;

    private TrainingPlace $trainingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->generateCTSInternalID($this->studentAccount->id),
            'cid' => $this->studentAccount->id,
        ]);

        $this->trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP'],
        ]);

        $this->trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $this->studentAccount->id,
            'training_position_id' => $this->trainingPosition->id,
            'created_at' => Carbon::now()->subDays(30),
        ]);
    }

    #[Test]
    public function returns_zero_percentage_when_student_has_no_filed_sessions(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => null,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame(0, $result['percentage']);
        $this->assertEmpty($result['sessionIds']);
    }

    #[Test]
    public function excludes_sessions_taken_before_the_training_place_was_created(): void
    {
        $this->trainingPlace->update(['created_at' => Carbon::now()->subDays(10)]);

        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertEmpty($result['sessionIds']);
    }

    #[Test]
    public function excludes_sessions_taken_after_a_soft_deleted_training_place(): void
    {
        $this->trainingPlace->update(['created_at' => Carbon::now()->subDays(30)]);
        $this->trainingPlace->delete();
        $this->trainingPlace->update(['deleted_at' => Carbon::now()->subDays(5)]);

        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace->withTrashed()->find($this->trainingPlace->id)))->calculate();

        $this->assertEmpty($result['sessionIds']);
    }

    #[Test]
    public function excludes_sessions_for_positions_not_belonging_to_the_training_position(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGKK_TWR',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertEmpty($result['sessionIds']);
    }

    #[Test]
    public function collects_session_ids_ordered_ascending_by_taken_date(): void
    {
        $older = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $newer = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame([$older->id, $newer->id], $result['sessionIds']);
    }

    #[Test]
    public function latest_session_id_is_the_most_recently_taken_session(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ]);

        $latest = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame($latest->id, $result['latestSessionId']);
    }

    #[Test]
    public function best_score_is_the_highest_score_across_all_sessions_for_a_field(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        $secondSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'progress_sheet_id' => $session->progress_sheet_id,
        ]);

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::COVERED,
        ]);

        ReportSheet::factory()->create([
            'seshid' => $secondSession->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::GOOD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame(FieldScore::GOOD, $result['categories'][0]['fields'][0]['best_score']);
    }

    #[Test]
    public function score_does_not_regress_when_a_later_session_has_a_lower_score(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        $secondSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'progress_sheet_id' => $session->progress_sheet_id,
        ]);

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::TEST_STANDARD,
        ]);

        ReportSheet::factory()->create([
            'seshid' => $secondSession->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::DEVELOPING,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame(FieldScore::TEST_STANDARD, $result['categories'][0]['fields'][0]['best_score']);
    }

    #[Test]
    public function overall_percentage_is_zero_when_no_fields_have_been_assessed(): void
    {
        $this->scaffoldSessionWithField();

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame(0, $result['percentage']);
    }

    #[Test]
    public function overall_percentage_is_100_when_all_fields_are_at_test_standard(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::TEST_STANDARD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertSame(FieldScore::TEST_STANDARD->toPercentage(), $result['percentage']);
        $this->assertSame(FieldScore::TEST_STANDARD->toPercentage(), $result['categories'][0]['percentage']);
    }

    #[Test]
    public function category_percentage_is_capped_at_100(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::TEST_STANDARD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertLessThanOrEqual(100, $result['categories'][0]['percentage']);
    }

    #[Test]
    public function overall_percentage_reflects_partial_progress_across_multiple_fields(): void
    {
        $progSheetId = 99;

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => $progSheetId,
        ]);

        $category = ProgSheetCategory::factory()->create(['prog_sheet_id' => $progSheetId]);

        $fieldA = ProgSheetField::factory()->create([
            'catId' => $category->catId,
            'disabled' => 0,
        ]);

        $fieldB = ProgSheetField::factory()->create([
            'catId' => $category->catId,
            'disabled' => 0,
        ]);

        $score = FieldScore::TEST_STANDARD;

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $fieldA->field_id,
            'field_score' => $score,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $fieldCount = 2;
        $expected = (int) round(($score->toPercentage() / ($fieldCount * 100)) * 100);

        $this->assertSame($expected, $result['percentage']);
    }

    #[Test]
    public function uses_prog_sheet_categories_when_session_has_a_progress_sheet_id(): void
    {
        $progSheetId = 42;

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => $progSheetId,
        ]);

        $category = ProgSheetCategory::factory()->create([
            'prog_sheet_id' => $progSheetId,
            'catName' => 'Airspace Awareness',
        ]);

        ProgSheetField::factory()->create([
            'catId' => $category->catId,
            'disabled' => 0,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertCount(1, $result['categories']);
        $this->assertSame('Airspace Awareness', $result['categories'][0]['name']);
    }

    #[Test]
    public function disabled_fields_are_excluded_from_prog_sheet_categories(): void
    {
        $progSheetId = 43;

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => $progSheetId,
        ]);

        $category = ProgSheetCategory::factory()->create(['prog_sheet_id' => $progSheetId]);

        ProgSheetField::factory()->create(['catId' => $category->catId, 'disabled' => 1]);
        $activeField = ProgSheetField::factory()->create(['catId' => $category->catId, 'disabled' => 0]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertCount(1, $result['categories'][0]['fields']);
        $this->assertSame($activeField->field_id, $result['categories'][0]['fields'][0]['field_id']);
    }

    #[Test]
    public function falls_back_to_resolving_categories_from_scored_field_ids_when_no_progress_sheet_id(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => 0,
        ]);

        $category = ProgSheetCategory::factory()->create(['catName' => 'Radar Separation']);
        $field = ProgSheetField::factory()->create(['catId' => $category->catId, 'disabled' => 0]);

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::GOOD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $categoryNames = array_column($result['categories'], 'name');
        $this->assertContains('Radar Separation', $categoryNames);
    }

    #[Test]
    public function fields_without_a_category_are_grouped_under_uncategorised(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => 0,
        ]);

        $field = ProgSheetField::factory()->create(['catId' => 0, 'disabled' => 0]);

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::DEVELOPING,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $categoryNames = array_column($result['categories'], 'name');
        $this->assertContains('Uncategorised', $categoryNames);
    }

    #[Test]
    public function calculate_is_memoised_and_does_not_requery_the_database_on_subsequent_calls(): void
    {
        $this->scaffoldSessionWithField();

        $calculator = new TrainingProgressCalculator($this->trainingPlace);

        $first = $calculator->calculate();
        $second = $calculator->calculate();

        $this->assertSame($first, $second);
    }

    #[Test]
    public function result_always_contains_required_keys(): void
    {
        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $this->assertArrayHasKey('percentage', $result);
        $this->assertArrayHasKey('sessionIds', $result);
        $this->assertArrayHasKey('latestSessionId', $result);
        $this->assertArrayHasKey('categories', $result);
    }

    #[Test]
    public function each_category_entry_contains_required_keys(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::GOOD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $cat = $result['categories'][0];
        $this->assertArrayHasKey('name', $cat);
        $this->assertArrayHasKey('percentage', $cat);
        $this->assertArrayHasKey('fields', $cat);
    }

    #[Test]
    public function each_field_entry_contains_required_keys(): void
    {
        [$session, $category, $field] = $this->scaffoldSessionWithField();

        ReportSheet::factory()->create([
            'seshid' => $session->id,
            'field_id' => $field->field_id,
            'field_score' => FieldScore::GOOD,
        ]);

        $result = (new TrainingProgressCalculator($this->trainingPlace))->calculate();

        $fieldEntry = $result['categories'][0]['fields'][0];
        $this->assertArrayHasKey('name', $fieldEntry);
        $this->assertArrayHasKey('field_id', $fieldEntry);
        $this->assertArrayHasKey('best_score', $fieldEntry);
        $this->assertArrayHasKey('best_score_label', $fieldEntry);
        $this->assertArrayHasKey('best_score_color', $fieldEntry);
    }

    private function scaffoldSessionWithField(): array
    {
        $progSheet = ProgSheet::factory()->create();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'filed' => now(),
            'taken_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'progress_sheet_id' => $progSheet->prog_sheet_id,
        ]);

        $category = ProgSheetCategory::factory()->create([
            'prog_sheet_id' => $progSheet->prog_sheet_id,
        ]);

        $field = ProgSheetField::factory()->create([
            'catId' => $category->catId,
            'disabled' => 0,
        ]);

        return [$session, $category, $field];
    }
}
