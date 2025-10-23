<?php

namespace Tests\Feature\Site;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPagesTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_s1_syllabus()
    {
        $this->get(route('sire.training.s1-syllabus'))->assertOk();
    }
}