<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExaminerRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExaminerRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var ExaminerRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(ExaminerRepository::class);
    }

    #[Test]
    public function it_can_return_a_list_of_atc_examiners()
    {
        $account = Account::factory()->create();
        $account->assignRole('ATC Examiner (TWR)');
        $member = Member::factory()->forAccount($account)->create();

        $examiners = $this->subjectUnderTest->getAtcExaminers();

        $this->assertContains($account->id, $examiners->all());
        $this->assertNotEquals($member->id, $account->id);
    }

    #[Test]
    public function it_can_return_a_list_of_pilot_examiners()
    {
        $account = Account::factory()->create();
        $account->assignRole('Pilot Examiner (P1)');
        Member::factory()->forAccount($account)->create();

        $examiners = $this->subjectUnderTest->getPilotExaminers();

        $this->assertEquals($account->id, $examiners->first());
    }

    #[Test]
    public function it_returns_the_cts_member_id_for_examiner_details()
    {
        $account = Account::factory()->create();
        $account->assignRole('ATC Examiner (APP)');
        $member = Member::factory()->forAccount($account)->create();

        $examiners = $this->subjectUnderTest->getExaminerDetailsByScope('app');

        $this->assertSame([
            'cid' => $account->id,
            'name' => $account->name,
            'id' => $member->id,
        ], $examiners->first());
    }

    #[Test]
    public function it_excludes_an_examiner_account_without_a_cts_member()
    {
        $account = Account::factory()->create();
        $account->assignRole('ATC Examiner (APP)');

        $examiners = $this->subjectUnderTest->getExaminerDetailsByScope('app');

        $this->assertCount(0, $examiners);
    }

    #[Test]
    public function it_rejects_an_unknown_examiner_scope()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->subjectUnderTest->getExaminerDetailsByScope('unknown');
    }
}
