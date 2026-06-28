<?php

namespace Tests\Unit\Admin\ATCTraining;

use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\RemovalReason;
use App\Services\Admin\ATCTrainingStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ATCTrainingStatsTest extends TestCase
{
    private Carbon $startDate;

    private Carbon $endDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startDate = Carbon::parse('2026-01-01');
        $this->endDate = Carbon::parse('2026-04-01');
    }

    // completedMentoringSessionsByTG
    #[Test]
    public function it_groups_sessions_by_tg_via_callsign_mapping()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR'],
        ]);
        TrainingPosition::factory()->create([
            'category' => 'S3 Training',
            'cts_positions' => ['EGLL_N_APP'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-01',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGLL_N_APP',
            'taken_date' => '2026-02-15',
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertCount(3, $result);
        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 2], $result);
    }

    #[Test]
    public function it_aggregates_multiple_sessions_on_same_position_into_one_tg()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-14',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-20',
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 3], $result);
        $this->assertContains(['name' => 'Total', 'value' => 3], $result);
    }

    #[Test]
    public function it_groups_multiple_positions_under_same_tg()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR', 'EGBB_TWR'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGBB_TWR',
            'taken_date' => '2026-02-14',
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 2], $result);
        $this->assertContains(['name' => 'Total', 'value' => 2], $result);
    }

    #[Test]
    public function it_excludes_cancelled_sessions()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-14',
            'cancelled_datetime' => '2026-02-10 12:00:00',
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_excludes_no_show_sessions()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-14',
            'noShow' => 1,
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_only_counts_sessions_within_date_range()
    {
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGKK_TWR'],
        ]);

        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-05-01',
        ]);

        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_returns_only_total_when_no_sessions_match()
    {
        $result = ATCTrainingStats::completedMentoringSessionsByTG($this->startDate, $this->endDate);

        $this->assertCount(1, $result);
        $this->assertContains(['name' => 'Total', 'value' => 0], $result);
    }

    // examsConductedByTG

    #[Test]
    public function it_maps_exam_types_to_correct_tgs()
    {
        PracticalResult::factory()->create([
            'exam' => 'OBS',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'APP',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'CTR',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'OBS to S1 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'C1 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 4], $result);
    }

    #[Test]
    public function it_aggregates_multiple_passes_of_same_exam_type()
    {
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-01-10',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-02-14',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-03-20',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 3], $result);
        $this->assertContains(['name' => 'Total', 'value' => 3], $result);
    }

    #[Test]
    public function it_counts_all_exams_regardless_of_result()
    {
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'F',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'APP',
            'result' => 'S',
            'date' => '2026-02-01',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 2], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 3], $result);
    }

    #[Test]
    public function it_excludes_pilot_exams()
    {
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'P2',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'P3',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertNotContains(['name' => 'P1', 'value' => 1], $result);
        $this->assertNotContains(['name' => 'P2', 'value' => 1], $result);
        $this->assertNotContains(['name' => 'P3', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_only_counts_exams_within_date_range()
    {
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'TWR',
            'result' => 'P',
            'date' => '2026-05-01',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_uses_raw_exam_name_when_no_tg_mapping_exists()
    {
        PracticalResult::factory()->create([
            'exam' => 'OBS',
            'result' => 'P',
            'date' => '2026-02-01',
        ]);

        $result = ATCTrainingStats::examsConductedByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'OBS to S1 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    // ratingUpgradesByTG
    private function makeQual(string $code, int $vatsim): Qualification
    {
        return Qualification::firstOrCreate(
            ['code' => $code],
            ['type' => 'atc', 'vatsim' => $vatsim, 'name_small' => $code, 'name_long' => $code, 'name_grp' => $code]
        );
    }

    #[Test]
    public function it_maps_qualification_codes_to_correct_tgs()
    {
        $account = Account::factory()->create();
        $s1 = $this->makeQual('S1', 2);
        $s2 = $this->makeQual('S2', 3);
        $s3 = $this->makeQual('S3', 4);
        $c1 = $this->makeQual('C1', 5);

        DB::table('mship_account_qualification')->insert([
            ['account_id' => $account->id, 'qualification_id' => $s1->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
            ['account_id' => $account->id, 'qualification_id' => $s2->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
            ['account_id' => $account->id, 'qualification_id' => $s3->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
            ['account_id' => $account->id, 'qualification_id' => $c1->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
        ]);

        $result = ATCTrainingStats::ratingUpgradesByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'OBS to S1 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'C1 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 4], $result);
    }

    #[Test]
    public function it_only_counts_atc_qualifications()
    {
        $account = Account::factory()->create();
        $s2 = $this->makeQual('S2', 3);
        $p1 = Qualification::factory()->pilot()->create();

        DB::table('mship_account_qualification')->insert([
            ['account_id' => $account->id, 'qualification_id' => $s2->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
            ['account_id' => $account->id, 'qualification_id' => $p1->id, 'created_at' => '2026-02-01', 'updated_at' => '2026-02-01'],
        ]);

        $result = ATCTrainingStats::ratingUpgradesByTG($this->startDate, $this->endDate);

        $this->assertCount(2, $result);
        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_only_counts_upgrades_within_date_range()
    {
        $account = Account::factory()->create();
        $s2 = $this->makeQual('S2', 3);

        DB::table('mship_account_qualification')->insert([
            ['account_id' => $account->id, 'qualification_id' => $s2->id, 'created_at' => '2026-05-01', 'updated_at' => '2026-05-01'],
        ]);

        $result = ATCTrainingStats::ratingUpgradesByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'Total', 'value' => 0], $result);
    }

    // heathrowEndorsementsIssued

    #[Test]
    public function it_counts_heathrow_endorsements_by_type()
    {
        $account = Account::factory()->create();
        $twr = PositionGroup::factory()->create(['name' => 'Heathrow (TWR)']);
        $app = PositionGroup::factory()->create(['name' => 'Heathrow (APP)']);
        $gnd = PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $military = PositionGroup::factory()->create(['name' => 'Military (TWR)']);

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $twr->id,
            'created_at' => '2026-02-01',
        ]);
        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $app->id,
            'created_at' => '2026-02-01',
        ]);
        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $gnd->id,
            'created_at' => '2026-02-01',
        ]);
        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $military->id,
            'created_at' => '2026-02-01',
        ]);

        $result = ATCTrainingStats::heathrowEndorsementsIssued($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'Heathrow (TWR)', 'value' => 1], $result);
        $this->assertContains(['name' => 'Heathrow (APP)', 'value' => 1], $result);
        $this->assertContains(['name' => 'Heathrow (GND)', 'value' => 1], $result);
        $this->assertNotContains(['name' => 'Military (TWR)', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 3], $result);
    }

    #[Test]
    public function it_counts_multiple_issuances_of_same_type()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $twr = PositionGroup::factory()->create(['name' => 'Heathrow (TWR)']);

        Endorsement::factory()->create([
            'account_id' => $account1->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $twr->id,
            'created_at' => '2026-02-01',
        ]);
        Endorsement::factory()->create([
            'account_id' => $account2->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $twr->id,
            'created_at' => '2026-02-15',
        ]);

        $result = ATCTrainingStats::heathrowEndorsementsIssued($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'Heathrow (TWR)', 'value' => 2], $result);
        $this->assertContains(['name' => 'Total', 'value' => 2], $result);
    }

    #[Test]
    public function it_only_counts_endorsements_within_date_range()
    {
        $account = Account::factory()->create();
        $twr = PositionGroup::factory()->create(['name' => 'Heathrow (TWR)']);

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $twr->id,
            'created_at' => '2026-05-01',
        ]);

        $result = ATCTrainingStats::heathrowEndorsementsIssued($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'Total', 'value' => 0], $result);
    }

    #[Test]
    public function it_returns_total_only_when_no_heathrow_endorsements_exist()
    {
        PositionGroup::factory()->create(['name' => 'Military (TWR)']);

        $result = ATCTrainingStats::heathrowEndorsementsIssued($this->startDate, $this->endDate);

        $this->assertCount(1, $result);
        $this->assertContains(['name' => 'Total', 'value' => 0], $result);
    }

    // atcWaitingListCounts

    #[Test]
    public function it_counts_active_members_on_each_atc_waiting_list()
    {
        $listA = WaitingList::factory()->create(['name' => 'ATC List A', 'department' => 'atc']);
        $listB = WaitingList::factory()->create(['name' => 'ATC List B', 'department' => 'atc']);

        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();
        $acc3 = Account::factory()->create();

        $listA->addToWaitingList($acc1, $this->privacc);
        $listA->addToWaitingList($acc2, $this->privacc);
        $listB->addToWaitingList($acc3, $this->privacc);

        $result = ATCTrainingStats::atcWaitingListCounts();

        $this->assertContains(['name' => 'ATC List A', 'value' => 2], $result);
        $this->assertContains(['name' => 'ATC List B', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 3], $result);
    }

    #[Test]
    public function it_excludes_pilot_waiting_lists()
    {
        WaitingList::factory()->create(['name' => 'ATC List', 'department' => 'atc']);
        WaitingList::factory()->create(['name' => 'Pilot List', 'department' => 'pilot']);

        $result = ATCTrainingStats::atcWaitingListCounts();

        $names = array_column($result, 'name');
        $this->assertContains('ATC List', $names);
        $this->assertNotContains('Pilot List', $names);
    }

    #[Test]
    public function it_excludes_removed_members()
    {
        $list = WaitingList::factory()->create(['name' => 'ATC List', 'department' => 'atc']);
        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();

        $list->addToWaitingList($acc1, $this->privacc);
        $list->addToWaitingList($acc2, $this->privacc);

        $wla = $list->waitingListAccounts()->where('account_id', $acc2->id)->first();
        $wla->delete();

        $result = ATCTrainingStats::atcWaitingListCounts();

        $this->assertContains(['name' => 'ATC List', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    // atcWaitingListRemovals

    #[Test]
    public function it_counts_inactivity_removals_per_list()
    {
        $listA = WaitingList::factory()->create(['name' => 'ATC List A', 'department' => 'atc']);
        $listB = WaitingList::factory()->create(['name' => 'ATC List B', 'department' => 'atc']);
        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();
        $acc3 = Account::factory()->create();

        $now = now();

        DB::table('training_waiting_list_account')->insert([
            ['list_id' => $listA->id, 'account_id' => $acc1->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => $now, 'created_at' => '2026-01-01', 'updated_at' => $now],
            ['list_id' => $listA->id, 'account_id' => $acc2->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => $now, 'created_at' => '2026-01-01', 'updated_at' => $now],
            ['list_id' => $listB->id, 'account_id' => $acc3->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => $now, 'created_at' => '2026-01-01', 'updated_at' => $now],
        ]);

        $result = ATCTrainingStats::atcWaitingListRemovals(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

        $this->assertContains(['name' => 'ATC List A', 'value' => 2], $result, 'Result: '.json_encode($result));
        $this->assertContains(['name' => 'ATC List B', 'value' => 1], $result, 'Result: '.json_encode($result));
    }

    #[Test]
    public function it_excludes_other_removal_types()
    {
        $listA = WaitingList::factory()->create(['name' => 'ATC List', 'department' => 'atc']);
        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();

        $now = now();

        DB::table('training_waiting_list_account')->insert([
            ['list_id' => $listA->id, 'account_id' => $acc1->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => $now, 'created_at' => '2026-01-01', 'updated_at' => $now],
            ['list_id' => $listA->id, 'account_id' => $acc2->id, 'removal_type' => RemovalReason::FailedRetention->value, 'deleted_at' => $now, 'created_at' => '2026-01-01', 'updated_at' => $now],
        ]);

        $result = ATCTrainingStats::atcWaitingListRemovals(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

        $this->assertContains(['name' => 'ATC List', 'value' => 1], $result, 'Result: '.json_encode($result));
        $this->assertContains(['name' => 'Total', 'value' => 1], $result, 'Result: '.json_encode($result));
    }

    #[Test]
    public function it_only_counts_removals_within_date_range()
    {
        $list = WaitingList::factory()->create(['name' => 'ATC List', 'department' => 'atc']);
        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();

        DB::table('training_waiting_list_account')->insert([
            ['list_id' => $list->id, 'account_id' => $acc1->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => '2026-02-01', 'created_at' => '2026-01-01', 'updated_at' => '2026-02-01'],
            ['list_id' => $list->id, 'account_id' => $acc2->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => '2026-06-01', 'created_at' => '2026-01-01', 'updated_at' => '2026-06-01'],
        ]);

        $result = ATCTrainingStats::atcWaitingListRemovals(Carbon::parse('2026-01-01'), Carbon::parse('2026-04-01'));

        $this->assertContains(['name' => 'ATC List', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_excludes_pilot_list_removals()
    {
        $atcList = WaitingList::factory()->create(['name' => 'ATC List', 'department' => 'atc']);
        $pilotList = WaitingList::factory()->create(['name' => 'Pilot List', 'department' => 'pilot']);
        $acc1 = Account::factory()->create();
        $acc2 = Account::factory()->create();

        DB::table('training_waiting_list_account')->insert([
            ['list_id' => $atcList->id, 'account_id' => $acc1->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => '2026-02-01', 'created_at' => '2026-01-01', 'updated_at' => '2026-02-01'],
            ['list_id' => $pilotList->id, 'account_id' => $acc2->id, 'removal_type' => RemovalReason::Inactivity->value, 'deleted_at' => '2026-02-15', 'created_at' => '2026-01-01', 'updated_at' => '2026-02-15'],
        ]);

        $result = ATCTrainingStats::atcWaitingListRemovals(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

        $names = array_column($result, 'name');
        $this->assertContains('ATC List', $names);
        $this->assertNotContains('Pilot List', $names);
    }

    // mentorsByTG

    #[Test]
    public function it_counts_mentors_who_mentored_per_tg()
    {
        $account1 = Account::factory()->create(['id' => 100001]);
        $account2 = Account::factory()->create(['id' => 100002]);
        Member::factory()->create(['id' => 100001, 'cid' => 100001]);
        Member::factory()->create(['id' => 100002, 'cid' => 100002]);

        $tpS2 = TrainingPosition::factory()->create(['category' => 'S2 Training', 'cts_positions' => ['EGKK_TWR']]);
        $tpS3 = TrainingPosition::factory()->create(['category' => 'S3 Training', 'cts_positions' => ['EGLL_N_APP']]);

        MentorTrainingPosition::create([
            'account_id' => $account1->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tpS2->id,
            'created_by' => $account1->id,
        ]);
        MentorTrainingPosition::create([
            'account_id' => $account2->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tpS3->id,
            'created_by' => $account2->id,
        ]);

        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-01',
        ]);
        Session::factory()->accepted()->create([
            'mentor_id' => 100002,
            'position' => 'EGLL_N_APP',
            'taken_date' => '2026-02-15',
        ]);

        $result = ATCTrainingStats::mentorsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 2], $result);
    }

    #[Test]
    public function it_counts_mentor_once_even_with_multiple_sessions_in_same_tg()
    {
        $account = Account::factory()->create(['id' => 100001]);
        Member::factory()->create(['id' => 100001, 'cid' => 100001]);

        $tp = TrainingPosition::factory()->create(['category' => 'S2 Training', 'cts_positions' => ['EGKK_TWR']]);

        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tp->id,
            'created_by' => $account->id,
        ]);

        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-01-10',
        ]);
        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-14',
        ]);
        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-03-20',
        ]);

        $result = ATCTrainingStats::mentorsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_only_counts_mentors_with_sessions_in_date_range()
    {
        $account1 = Account::factory()->create(['id' => 100001]);
        $account2 = Account::factory()->create(['id' => 100002]);
        Member::factory()->create(['id' => 100001, 'cid' => 100001]);
        Member::factory()->create(['id' => 100002, 'cid' => 100002]);

        $tp = TrainingPosition::factory()->create(['category' => 'S2 Training', 'cts_positions' => ['EGKK_TWR']]);

        MentorTrainingPosition::create([
            'account_id' => $account1->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tp->id,
            'created_by' => $account1->id,
        ]);
        MentorTrainingPosition::create([
            'account_id' => $account2->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tp->id,
            'created_by' => $account2->id,
        ]);

        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-01',
        ]);
        Session::factory()->accepted()->create([
            'mentor_id' => 100002,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-05-01',
        ]);

        $result = ATCTrainingStats::mentorsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 1], $result);
    }

    #[Test]
    public function it_excludes_cancelled_and_no_show_sessions()
    {
        $account = Account::factory()->create(['id' => 100001]);
        Member::factory()->create(['id' => 100001, 'cid' => 100001]);

        $tp = TrainingPosition::factory()->create(['category' => 'S2 Training', 'cts_positions' => ['EGKK_TWR']]);

        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tp->id,
            'created_by' => $account->id,
        ]);

        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-01',
            'cancelled_datetime' => '2026-01-15 12:00:00',
        ]);

        $result = ATCTrainingStats::mentorsByTG($this->startDate, $this->endDate);

        $s2Entry = collect($result)->firstWhere('name', 'S2 Training');
        $this->assertNull($s2Entry);
        $this->assertContains(['name' => 'Total', 'value' => 0], $result);
    }

    #[Test]
    public function it_handles_mentor_in_multiple_tgs()
    {
        $account = Account::factory()->create(['id' => 100001]);
        Member::factory()->create(['id' => 100001, 'cid' => 100001]);

        $tpS2 = TrainingPosition::factory()->create(['category' => 'S2 Training', 'cts_positions' => ['EGKK_TWR']]);
        $tpS3 = TrainingPosition::factory()->create(['category' => 'S3 Training', 'cts_positions' => ['EGLL_N_APP']]);

        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tpS2->id,
            'created_by' => $account->id,
        ]);
        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $tpS3->id,
            'created_by' => $account->id,
        ]);

        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-02-01',
        ]);
        Session::factory()->accepted()->create([
            'mentor_id' => 100001,
            'position' => 'EGLL_N_APP',
            'taken_date' => '2026-02-15',
        ]);

        $result = ATCTrainingStats::mentorsByTG($this->startDate, $this->endDate);

        $this->assertContains(['name' => 'S2 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'S3 Training', 'value' => 1], $result);
        $this->assertContains(['name' => 'Total', 'value' => 2], $result);
    }
}
