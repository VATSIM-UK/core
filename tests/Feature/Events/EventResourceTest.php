<?php

namespace Tests\Feature\Events;

use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use App\Models\Permission;
use Livewire\Livewire;
use Tests\TestCase;

class EventResourceTest extends TestCase
{
    private function userWithPermission(string $permission): Account
    {
        $user = Account::factory()->create();
        $user->givePermissionTo('admin.access');
        $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));

        return $user;
    }

    public function test_view_permission_can_access_list(): void
    {
        $this->actingAs($this->userWithPermission('events.view'))
            ->get('/admin/events')
            ->assertOk();
    }

    public function test_user_without_permission_cannot_access_list(): void
    {
        $user = Account::factory()->create();
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get('/admin/events')
            ->assertForbidden();
    }

    public function test_manage_permission_can_create_event(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'name' => 'Test event',
                'start' => '2026-09-01 18:00:00',
                'end' => '2026-09-01 21:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('events', ['name' => 'Test event']);
    }

    public function test_new_event_defaults_to_draft(): void
    {
        Event::factory()->create();

        $this->actingAs($this->userWithPermission('events.view'))
            ->get('/admin/events')
            ->assertSee('Draft');
    }

    public function test_publish_action_publishes_event(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $event = Event::factory()->create(['published_at' => null, 'banner_created' => false]);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->callAction('publish')
            ->assertNotified();

        $this->assertNotNull($event->fresh()->published_at);
    }
}
