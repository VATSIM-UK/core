<?php


namespace Tests\Integration;

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApplicationTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    /** Application Testing */

    /** @test */
    public function itDisplaysTheDashboardToTheMember()
    {
    }

    /** @test */
    public function itDisplaysTheStartNewVisitingApplicationButtonToANoneDivisionMember()
    {
    }

    /** @test */
    public function itDoesNotDisplayTheStartNewVisitingApplicationButtonToADivisionMember()
    {
    }

    /** @test */
    public function itDoesNotDisplayTheNewApplicationButtonsToThoseWithAnOpenApplication()
    {
    }

    /** @test */
    public function itDisplaysTheStartNewTransferringApplicationButtonToANoneDivisionMember()
    {
    }

    /** @test */
    public function itDoesNotDisplayTheStartNewTransferringButtonToADivisionMember()
    {
    }
}
