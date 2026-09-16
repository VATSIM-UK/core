<?php

namespace Tests\Feature\Livewire;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LivewireLayoutTest extends TestCase
{
    #[Test]
    public function full_page_livewire_components_render_the_configured_layout(): void
    {
        $response = $this->get(route('mship.waiting-lists.retention.fail'));

        $response->assertOk();

        // resources/views/components/layouts/app.blade.php is the only layout that
        // renders this favicon link; if Livewire falls back to `layouts::app` the
        // request 500s with "View [layouts::app] not found".
        $response->assertSee('images/favicon.png', false);
    }
}
