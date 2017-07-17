<?php


namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use DatabaseTransactions;

    /** Unit Testing */

    /** @test */
    public function testItCanCreateANewApplicationForAUser()
    {
        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        ]);

        $this->assertCount(1, $account->fresh()->visitTransferApplications);
        $this->assertCount(1, $account->fresh()->visitApplications);
    }

    /** @test */
    public function testItThrowsAnExceptionWhenAttemptingToCreateADuplicateApplication()
    {
        $this->expectException(\App\Exceptions\VisitTransfer\Application\DuplicateApplicationException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        ]);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        ]);
    }

    /** @test */
    public function testItThrowsAnExceptionWhenAttemptingToCreateAnApplicationForADivisionMember()
    {
        $this->expectException(\App\Exceptions\VisitTransfer\Application\AlreadyADivisionMemberException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('DIVISION'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        ]);
    }
}
