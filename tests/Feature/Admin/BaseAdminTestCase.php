<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

abstract class BaseAdminTestCase extends TestCase
{
    protected function actingAsSuperUser()
    {
        $this->actingAs($this->privacc);
    }
}
