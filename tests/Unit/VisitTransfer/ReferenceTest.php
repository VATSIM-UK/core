<?php

namespace Tests\Unit\VisitTransferLegacy;

use App\Exceptions\VisitTransferLegacy\Reference\ReferenceNotRequestedException;
use App\Exceptions\VisitTransferLegacy\Reference\ReferenceNotUnderReviewException;
use App\Models\VisitTransferLegacy\Reference;
use Faker\Provider\Base;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReferenceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function setUp(): void
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

    /** @test */
    public function itReportsStatisticsCorrectly()
    {
        $referenceTypes = [
            'statisticTotal' => collect(Reference::$REFERENCE_IS_PENDING)->merge(Reference::$REFERENCE_IS_SUBMITTED)->unique()->all(),
            'statisticRequested' => [Reference::STATUS_REQUESTED],
            'statisticSubmitted' => Reference::$REFERENCE_IS_SUBMITTED,
            'statisticUnderReview' => [Reference::STATUS_UNDER_REVIEW],
            'statisticAccepted' => [Reference::STATUS_ACCEPTED],
            'statisticRejected' => [Reference::STATUS_REJECTED],
        ];

        // Check initially zero

        foreach ($referenceTypes as $function => $status) {
            $this->assertEquals(0, Reference::$function());
        }

        // Create some references

        factory(Reference::class, 20)->create([
            'status' => function () use ($referenceTypes) {
                return Base::randomElement(Base::randomElement($referenceTypes));
            },
        ]);

        // Test
        Cache::flush();

        foreach ($referenceTypes as $function => $status) {
            $this->assertEquals(Reference::statusIn($status)->count(), Reference::$function());
        }

        // Assert that the values were cached
        Cache::shouldReceive('remember')
            ->times(6);

        foreach ($referenceTypes as $function => $status) {
            Reference::$function();
        }
    }
}
