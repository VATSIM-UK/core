<?php

namespace Tests\Feature\Training\Exams;

use App\Models\Cts\ExaminerSettings;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExaminerRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamRequestsTableSecondaryExaminerScopingTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_gets_obs_examiners_for_obs_scope()
    {
        // Create test examiners
        $obsExaminer = $this->createExaminerWithSettings(['OBS' => 1]);
        $twrExaminer = $this->createExaminerWithSettings(['S1' => 1]);
        $appExaminer = $this->createExaminerWithSettings(['S2' => 1]);

        $repository = new ExaminerRepository;
        $obsExaminers = $repository->getExaminerDetailsByScope('obs');

        // Only OBS examiner should be returned
        $examinerIds = $obsExaminers->pluck('id')->toArray();
        $this->assertContains($obsExaminer->id, $examinerIds, 'OBS examiner should be in OBS scope');
        $this->assertNotContains($twrExaminer->id, $examinerIds, 'TWR examiner should not be in OBS scope');
        $this->assertNotContains($appExaminer->id, $examinerIds, 'APP examiner should not be in OBS scope');
    }

    #[Test]
    public function it_gets_twr_examiners_for_twr_scope()
    {
        // Create test examiners
        $obsExaminer = $this->createExaminerWithSettings(['OBS' => 1]);
        $twrExaminer = $this->createExaminerWithSettings(['S1' => 1]);
        $appExaminer = $this->createExaminerWithSettings(['S2' => 1]);

        $repository = new ExaminerRepository;
        $twrExaminers = $repository->getExaminerDetailsByScope('twr');

        // Only TWR examiner should be returned
        $examinerIds = $twrExaminers->pluck('id')->toArray();
        $this->assertNotContains($obsExaminer->id, $examinerIds, 'OBS examiner should not be in TWR scope');
        $this->assertContains($twrExaminer->id, $examinerIds, 'TWR examiner should be in TWR scope');
        $this->assertNotContains($appExaminer->id, $examinerIds, 'APP examiner should not be in TWR scope');
    }

    #[Test]
    public function it_gets_app_examiners_for_app_scope()
    {
        // Create test examiners
        $twrExaminer = $this->createExaminerWithSettings(['S1' => 1]);
        $appExaminer = $this->createExaminerWithSettings(['S2' => 1]);
        $ctrExaminer = $this->createExaminerWithSettings(['S3' => 1]);

        $repository = new ExaminerRepository;
        $appExaminers = $repository->getExaminerDetailsByScope('app');

        // Only APP examiner should be returned
        $examinerIds = $appExaminers->pluck('id')->toArray();
        $this->assertNotContains($twrExaminer->id, $examinerIds, 'TWR examiner should not be in APP scope');
        $this->assertContains($appExaminer->id, $examinerIds, 'APP examiner should be in APP scope');
        $this->assertNotContains($ctrExaminer->id, $examinerIds, 'CTR examiner should not be in APP scope');
    }

    #[Test]
    public function it_gets_ctr_examiners_for_ctr_scope()
    {
        // Create test examiners
        $appExaminer = $this->createExaminerWithSettings(['S2' => 1]);
        $ctrExaminer = $this->createExaminerWithSettings(['S3' => 1]);

        $repository = new ExaminerRepository;
        $ctrExaminers = $repository->getExaminerDetailsByScope('ctr');

        // Only CTR examiner should be returned
        $examinerIds = $ctrExaminers->pluck('id')->toArray();
        $this->assertNotContains($appExaminer->id, $examinerIds, 'APP examiner should not be in CTR scope');
        $this->assertContains($ctrExaminer->id, $examinerIds, 'CTR examiner should be in CTR scope');
    }

    /**
     * Helper method to create an examiner with specific settings
     */
    private function createExaminerWithSettings(array $settings): Member
    {
        $examinerAccount = Account::factory()->create();
        $examinerMember = Member::factory()->create([
            'id' => $examinerAccount->id,
            'cid' => $examinerAccount->id,
            'examiner' => true,
        ]);

        ExaminerSettings::create(array_merge([
            'memberID' => $examinerMember->id,
            'OBS' => 0,
            'S1' => 0,
            'S2' => 0,
            'S3' => 0,
            'P1' => 0,
            'P2' => 0,
            'P3' => 0,
            'P4' => 0,
            'P5' => 0,
            'lastUpdated' => now(),
            'updatedBy' => 0,
        ], $settings));

        return $examinerMember;
    }
}
