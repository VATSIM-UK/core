<?php

namespace Tests\Unit\VisitTransfer;

use App\Exceptions\VisitTransfer\Reference\ReferenceNotRequestedException;
use App\Exceptions\VisitTransfer\Reference\ReferenceNotUnderReviewException;
use App\Models\VisitTransfer\Reference;
use Faker\Provider\Base;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReferenceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    #[Test]
    public function it_cant_submit_itself_when_not_requested()
    {
        $this->expectException(ReferenceNotRequestedException::class);
        $reference = Reference::factory()->create();
        $text = $this->faker->realText;
        $reference->submit($text);
    }

    #[Test]
    public function it_can_submit_itself()
    {
        $reference = Reference::factory()->create(['status' => Reference::STATUS_REQUESTED]);
        $text = $this->faker->realText;
        $reference->submit($text);
        $this->assertEquals($text, $reference->fresh()->reference);
        $this->assertEquals(Reference::STATUS_UNDER_REVIEW, $reference->fresh()->status);
        $this->assertNotNull($reference->fresh()->submitted_at);
    }

    #[Test]
    public function it_can_delete_itself()
    {
        $reference = Reference::factory()->create();
        $reference->delete();
        $this->assertEquals(Reference::STATUS_DELETED, $reference->fresh()->status);
        $this->assertNotNull($reference->fresh()->deleted_at);
    }

    #[Test]
    public function it_cant_reject_itself_when_not_under_review()
    {
        $this->expectException(ReferenceNotUnderReviewException::class);
        $reference = Reference::factory()->create();
        $reference->reject();
    }

    #[Test]
    public function it_can_reject_itself()
    {
        $reference = Reference::factory()->create(['status' => Reference::STATUS_UNDER_REVIEW]);
        $reference->reject();
        $this->assertEquals(Reference::STATUS_REJECTED, $reference->fresh()->status);
    }

    #[Test]
    public function it_reports_statistics_correctly()
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

        Reference::factory()->count(20)->create([
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
