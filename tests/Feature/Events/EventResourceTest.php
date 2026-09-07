<?php

namespace Tests\Feature\Events;

use App\Enums\EventChecklistItem;
use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use App\Models\Permission;
use Filament\Forms\Components\Select;
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

    public function test_event_times_must_be_at_15_minute_intervals(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'name' => 'Test event',
                'start' => '2026-09-01 18:07:00',
                'end' => '2026-09-01 21:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['start']);
    }

    public function test_new_event_defaults_to_draft(): void
    {
        Event::factory()->create();

        $this->actingAs($this->userWithPermission('events.view'))
            ->get('/admin/events')
            ->assertSee('Draft');
    }

    public function test_checklist_is_not_available_when_creating_an_event(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));

        Livewire::test(CreateEvent::class)
            ->assertFormFieldHidden('checklist');
    }

    public function test_checklist_is_available_when_editing_an_event(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $event = Event::factory()->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormFieldVisible('checklist');
    }

    public function test_checklist_is_hydrated_from_completions(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::BannerCreated)
            ->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormSet(['checklist' => ['banner_created']]);
    }

    public function test_ticking_a_checklist_item_saves_without_pressing_save(): void
    {
        $user = $this->userWithPermission('events.manage');
        $this->actingAs($user);
        $event = Event::factory()->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm(['checklist' => ['eoi_published']]);

        $this->assertDatabaseHas('event_checklist_completions', [
            'event_id' => $event->id,
            'item' => 'eoi_published',
            'account_id' => $user->id,
        ]);
    }

    public function test_unticking_a_checklist_item_removes_it(): void
    {
        $user = $this->userWithPermission('events.manage');
        $this->actingAs($user);
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::EoiPublished, $user)
            ->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm(['checklist' => []]);

        $this->assertDatabaseMissing('event_checklist_completions', [
            'event_id' => $event->id,
            'item' => 'eoi_published',
        ]);
    }

    public function test_details_are_locked_once_the_event_is_published(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $event = Event::factory()->published()->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormFieldDisabled('name')
            ->assertFormFieldDisabled('start')
            ->assertFormFieldDisabled('end')
            ->assertFormFieldDisabled('positions')
            ->assertFormFieldEnabled('tagline')
            ->assertFormFieldEnabled('rostered');
    }

    public function test_details_are_editable_while_the_event_is_a_draft(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $event = Event::factory()->create(['published_at' => null]);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormFieldEnabled('name')
            ->assertFormFieldEnabled('start')
            ->assertFormFieldEnabled('end');
    }

    public function test_manager_options_only_include_accounts_with_admin_access(): void
    {
        $this->actingAs($this->userWithPermission('events.manage'));
        $staff = $this->userWithPermission('events.view');
        $member = Account::factory()->create();
        $event = Event::factory()->create();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormFieldExists('manager_id', function (Select $field) use ($staff, $member): bool {
                $options = $field->getOptions();

                return array_key_exists($staff->id, $options)
                    && ! array_key_exists($member->id, $options);
            });
    }

    public function test_publish_action_publishes_event(): void
    {
        $user = $this->userWithPermission('events.manage');
        $this->actingAs($user);
        $event = Event::factory()->create(['published_at' => null]);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->callAction('publish')
            ->assertNotified();

        $fresh = $event->fresh();
        $this->assertNotNull($fresh->published_at);
        $this->assertEquals($user->id, $fresh->published_by);
    }

    public function test_republish_action_updates_the_published_timestamp(): void
    {
        $user = $this->userWithPermission('events.manage');
        $this->actingAs($user);
        $originallyPublishedAt = now()->subDays(3);
        $event = Event::factory()->create(['published_at' => $originallyPublishedAt]);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->callAction('publish');

        $this->assertTrue($event->fresh()->published_at->greaterThan($originallyPublishedAt));
    }
}
