<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Repositories\Cts\ExamResultRepository;
use App\Services\Training\ExamHistoryService;
use Mockery;
use Tests\TestCase;

class ExamHistoryServiceTest extends TestCase
{
    public function test_get_types_to_show_returns_only_levels_user_can_conduct(): void
    {
        $repository = Mockery::mock(ExamResultRepository::class);
        $service = new ExamHistoryService($repository);

        $user = Mockery::mock(Account::class);
        $user->shouldReceive('can')->with('training.exams.conduct.obs')->andReturn(true);
        $user->shouldReceive('can')->with('training.exams.conduct.twr')->andReturn(false);
        $user->shouldReceive('can')->with('training.exams.conduct.app')->andReturn(true);
        $user->shouldReceive('can')->with('training.exams.conduct.ctr')->andReturn(false);

        $this->assertSame(['obs', 'app'], $service->getTypesToShow($user)->all());
    }

    public function test_get_result_badge_color_maps_known_exam_results(): void
    {
        $repository = Mockery::mock(ExamResultRepository::class);
        $service = new ExamHistoryService($repository);

        $this->assertSame('success', $service->getResultBadgeColor('Passed'));
        $this->assertSame('danger', $service->getResultBadgeColor('Failed'));
        $this->assertSame('warning', $service->getResultBadgeColor('Incomplete'));
        $this->assertSame('gray', $service->getResultBadgeColor('Unknown'));
    }
}
