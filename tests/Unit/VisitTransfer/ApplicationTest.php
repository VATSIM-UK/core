<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\VisitTransfer\Application;
use App\Notifications\ApplicationAccepted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** Unit Testing */

    /** @test */
    public function testItCanCreateANewApplicationForAUser()
    {
        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
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
            'type' => Application::TYPE_VISIT,
        ]);

        $account->fresh()->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
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
            'type' => Application::TYPE_VISIT,
        ]);
    }

    /** @test */
    public function itSendsAcceptanceEmailToTrainingTeam()
    {
        Notification::fake();
        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $facility = factory(\App\Models\VisitTransfer\Facility::class, 'atc_visit')->create();

        $application = $account->fresh()->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
            'facility_id' => $facility->id,
            'training_team' => $facility->training_team,
            'status' => Application::STATUS_UNDER_REVIEW,
        ]);

        $application->accept();

        Notification::assertSentTo($facility, ApplicationAccepted::class, function ($notification, $channels) use ($application, $facility) {
            $mail = $notification->toMail($facility);
            $view = View::make($mail->view, $mail->viewData)->render();

            $this->assertContains('Dear ATC Training Team,', $view);

            return $notification->application->id == $application->id;
        });
    }
}
