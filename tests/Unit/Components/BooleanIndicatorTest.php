<?php

namespace Tests\Unit\Components;

use Tests\TestCase;

class BooleanIndicatorTest extends TestCase
{
    public function test_it_renders_correctly()
    {
        $view = $this->blade(
            '<x-boolean-indicator :value="true" />'
        );

        $view->assertSee('tick_mark_circle');

        $view = $this->blade(
            '<x-boolean-indicator :value="false" />'
        );

        $view->assertSee('cross_mark_circle');
    }
}
