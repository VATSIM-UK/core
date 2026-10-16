<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Discord;

use App\Filament\Admin\Resources\Discord\DiscordRoleRuleResource;
use App\Filament\Admin\Resources\Discord\Pages\CreateDiscordRoleRule;
use App\Filament\Admin\Resources\Discord\Pages\EditDiscordRoleRule;
use App\Filament\Admin\Resources\Discord\Pages\ListDiscordRoleRules;
use App\Jobs\Discord\SyncDiscordRoleRuleMembers;
use App\Jobs\Mship\SyncToDiscord;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class DiscordRoleRuleResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = DiscordRoleRuleResource::class;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Queue::fake();

        config([
            'services.discord.token' => 'test-token',
            'services.discord.guild_id' => '12345',
            'services.discord.base_discord_uri' => 'https://discord.com/api/v10',
        ]);

        Http::fake([
            'discord.com/api/v10/guilds/*/roles' => Http::response([
                ['id' => '111', 'name' => 'Tower Student', 'color' => 16711680],
            ]),
        ]);
    }

    #[Test]
    public function it_forbids_the_list_without_the_view_permission(): void
    {
        $this->actingAsAdminUser();

        $this->get(DiscordRoleRuleResource::getUrl('index'))->assertForbidden();
    }

    #[Test]
    public function it_allows_the_list_with_the_view_permission(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        $this->get(DiscordRoleRuleResource::getUrl('index'))->assertSuccessful();
    }

    #[Test]
    public function view_only_users_cannot_create_a_rule(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        $this->get(DiscordRoleRuleResource::getUrl('create'))->assertForbidden();
    }

    #[Test]
    public function an_editor_can_create_a_rule(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        Livewire::test(CreateDiscordRoleRule::class)
            ->fillForm([
                'name' => 'Tower Student',
                'discord_id' => '111',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discord_role_rules', [
            'name' => 'Tower Student',
            'discord_id' => 111,
        ]);
    }

    #[Test]
    public function an_editor_can_delete_a_rule(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        $rule = DiscordRoleRule::factory()->create(['name' => 'Delete me']);

        Livewire::test(EditDiscordRoleRule::class, ['record' => $rule->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('discord_role_rules', ['id' => $rule->id]);
    }

    #[Test]
    public function the_resync_action_queues_the_re_sync_job(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        $rule = DiscordRoleRule::factory()->create();

        Livewire::test(ListDiscordRoleRules::class)
            ->callTableAction('resync', $rule);

        Queue::assertPushed(SyncDiscordRoleRuleMembers::class, fn (SyncDiscordRoleRuleMembers $job) => $job->getRuleId() === $rule->id);
    }

    #[Test]
    public function the_resync_action_is_hidden_without_edit_permission(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        $rule = DiscordRoleRule::factory()->create();

        Livewire::test(ListDiscordRoleRules::class)
            ->assertTableActionHidden('resync', $rule);
    }

    #[Test]
    public function the_refresh_action_is_hidden_without_edit_permission(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        Livewire::test(ListDiscordRoleRules::class)
            ->assertActionHidden('refreshDiscordRoles');
    }

    #[Test]
    public function the_refresh_action_is_visible_with_edit_permission(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        Livewire::test(ListDiscordRoleRules::class)
            ->assertActionVisible('refreshDiscordRoles');
    }

    #[Test]
    public function a_view_only_user_cannot_delete_or_edit_a_rule(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        $rule = DiscordRoleRule::factory()->create();

        Livewire::test(ListDiscordRoleRules::class)
            ->assertTableActionHidden('delete', $rule)
            ->assertTableActionHidden('edit', $rule);
    }

    #[Test]
    public function a_view_only_user_cannot_create_a_rule_from_the_list(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        Livewire::test(ListDiscordRoleRules::class)
            ->assertActionHidden('create');
    }

    #[Test]
    public function saving_a_rule_does_not_dispatch_any_discord_sync(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        Livewire::test(CreateDiscordRoleRule::class)
            ->fillForm([
                'name' => 'No auto sync',
                'discord_id' => '999',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Queue::assertNotPushed(SyncDiscordRoleRuleMembers::class);
        Queue::assertNotPushed(SyncToDiscord::class);
    }

    #[Test]
    public function the_refresh_action_busts_the_cache_and_refetches(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        // Rendering the list warms the cache (one request).
        Livewire::test(ListDiscordRoleRules::class);

        Http::assertSentCount(1);

        Livewire::test(ListDiscordRoleRules::class)->callAction('refreshDiscordRoles');

        Http::assertSentCount(2);
    }

    #[Test]
    public function the_list_renders_the_discord_role_name(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        DiscordRoleRule::factory()->create(['discord_id' => '111']);

        Livewire::test(ListDiscordRoleRules::class)
            ->assertSee('Tower Student')
            ->assertSee('111');
    }

    #[Test]
    public function the_list_warns_when_the_discord_role_is_not_found(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view']);

        DiscordRoleRule::factory()->create(['discord_id' => '1111111111111111111']);

        Livewire::test(ListDiscordRoleRules::class)
            ->assertSee('Not found on Discord')
            ->assertSee('1111111111111111111');
    }

    #[Test]
    public function saving_an_edit_does_not_dispatch_any_discord_sync(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        $rule = DiscordRoleRule::factory()->create(['name' => 'Before']);

        Livewire::test(EditDiscordRoleRule::class, ['record' => $rule->getKey()])
            ->fillForm(['name' => 'After'])
            ->call('save')
            ->assertHasNoFormErrors();

        Queue::assertNotPushed(SyncDiscordRoleRuleMembers::class);
        Queue::assertNotPushed(SyncToDiscord::class);
    }

    #[Test]
    public function it_stores_a_discord_snowflake_without_loss_of_precision(): void
    {
        $this->actingAsAdminUser(['discord.role-rule.view', 'discord.role-rule.edit']);

        Livewire::test(CreateDiscordRoleRule::class)
            ->fillForm([
                'name' => 'Snowflake',
                'discord_id' => '1032739175948099594',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            '1032739175948099594',
            DiscordRoleRule::where('name', 'Snowflake')->first()->discord_id,
        );
    }
}
