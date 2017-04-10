<?php


use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteTest extends TestCase
{
    use DatabaseTransactions;

    /** Unit Testing */

    /** @test */
    public function it_can_create_a_new_application_for_a_user()
    {
        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->createVisitingTransferApplication([
            'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        ]);

        $this->assertCount(1, $account->fresh()->visitTransferApplications);
        $this->assertCount(1, $account->fresh()->visitApplications);
    }

    /** @test */
    public function it_throws_an_exception_when_attempting_to_create_a_duplicate_application()
    {
        $this->setExpectedException(\App\Modules\Visittransfer\Exceptions\Application\DuplicateApplicationException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        ]);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_attempting_to_create_an_application_for_a_division_member()
    {
        $this->setExpectedException(\App\Modules\Visittransfer\Exceptions\Application\AlreadyADivisionMemberException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('DIVISION'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        ]);
    }
}
