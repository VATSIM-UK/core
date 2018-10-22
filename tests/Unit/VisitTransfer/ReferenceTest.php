<?php

namespace Tests\Unit\VisitTransfer;

use App\Exceptions\VisitTransfer\Reference\ReferenceNotRequestedException;
use App\Exceptions\VisitTransfer\Reference\ReferenceNotUnderReviewException;
use App\Models\VisitTransfer\Reference;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReferenceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function setUp()
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function itCantSubmitItselfWhenNotRequested()
    {
        $this->expectException(ReferenceNotRequestedException::class);
        $reference = factory(Reference::class)->create();
        $text = $this->faker->realText;
        $reference->submit($text);
    }

    /** @test */
    public function itCanSubmitItself()
    {
        $reference = factory(Reference::class)->create(['status' => Reference::STATUS_REQUESTED]);
        $text = $this->faker->realText;
        $reference->submit($text);
        $this->assertEquals($text, $reference->fresh()->reference);
        $this->assertEquals(Reference::STATUS_UNDER_REVIEW, $reference->fresh()->status);
        $this->assertNotNull($reference->fresh()->submitted_at);
    }

    /** @test */
    public function itCanDeleteItself()
    {
        $reference = factory(Reference::class)->create();
        $reference->delete();
        $this->assertEquals(Reference::STATUS_DELETED, $reference->fresh()->status);
        $this->assertNotNull($reference->fresh()->deleted_at);
    }

    /** @test */
    public function itCantRejectItselfWhenNotUnderReview()
    {
        $this->expectException(ReferenceNotUnderReviewException::class);
        $reference = factory(Reference::class)->create();
        $reference->reject();
    }

    /** @test */
    public function itCanRejectItself()
    {
        $reference = factory(Reference::class)->create(['status' => Reference::STATUS_UNDER_REVIEW]);
        $reference->reject();
        $this->assertEquals(Reference::STATUS_REJECTED, $reference->fresh()->status);
    }
}
