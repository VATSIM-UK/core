<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Feedback\Form;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = Form::find(1);
    }

    /** @test */
    public function testItRedirectsFromFeedbackFormSelectorAsGuest()
    {
        $this->get(route('mship.feedback.new'))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheFeedbackFormSelector()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new'))
            ->assertSuccessful();
    }

    /** @test */
    public function testItRedirectsFromFeedbackFormAsGuest()
    {
        $this->get(route('mship.feedback.new.form', $this->form->slug))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheFeedbackForm()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new.form', $this->form->slug))
            ->assertSuccessful();
    }

    /** @test */
    public function testItFillsUserCidInAtcForm()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $request = $this->actingAs($this->user, 'web')
            ->call('GET', route('mship.feedback.new.form', $form->slug), ['cid' => 'mycidishere']);

        $request->assertSuccessful();
        $request->assertSee('mycidishere');
    }

    public function testItRedirectsToAtcFeedback()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $request = $this->actingAs($this->user, 'web')
            ->call('GET', route('mship.feedback.redirect.atc'), ['cid' => 'mycidishere']);

        $request->assertRedirect(route('mship.feedback.new.form', [$form->slug, 'cid' => 'mycidishere']));
    }

    //    /** @test */
    //    public function testItAllowsSubmission()
    //    {
    //        //
    //    }
    //
    //    /** @test */
    //    public function testItAllowsCreationOfFormWithPermission()
    //    {
    //        //
    //    }
    //
    //    /** @test */
    //    public function testItAllowsViewingOfSubmissionWithPermission()
    //    {
    //        //
    //    }
    //
    //    /** @test */
    //    public function testItDoesNotAllowViewingOfSubmissionWithoutPermission()
    //    {
    //        //
    //    }
}
