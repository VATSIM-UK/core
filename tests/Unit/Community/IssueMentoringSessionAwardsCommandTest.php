<?php

namespace Tests\Unit\Community;

use Mockery as m;
use Tests\TestCase;
use Alawrence\Ipboard\Facades\Ipboard;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\Cts\SessionRepository;

class IssueMentoringSessionAwardsCommandTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     *
     * @group Community
     */
    public function can_issue_awards_to_all_mentors_that_had_sessions_in_last_28_days()
    {
        $sessionRepoMock = m::mock(SessionRepository::class)
                            ->shouldReceive('mentorIdsForSessionsInLast28Days')
                            ->once()
                            ->andReturn([
                                980234,
                                1234567,
                            ]);

        Ipboard::shouldReceive('awardBadge')
               ->with(980234, 1)
               ->once()
               ->andReturn(true);

        Ipboard::shouldReceive('awardBadge')
               ->with(1234567, 1)
               ->once()
               ->andReturn(true);

        $this->app->instance(SessionRepository::class, $sessionRepoMock->getMock());

        Artisan::call('community:badges:mentoring28');
    }

    /**
     * Ref: https://github.com/laravel/framework/blob/5.6/tests/Session/SessionTableCommandTest.php
     *
     * @param       $command
     * @param array $input
     *
     * @return
     */
    protected function runCommand($command, $input = [])
    {
        return $command->run(
            new \Symfony\Component\Console\Input\ArrayInput($input),
            new \Symfony\Component\Console\Output\NullOutput
        );
    }
}