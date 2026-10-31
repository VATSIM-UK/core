<?php

declare(strict_types=1);

namespace Tests\Feature\Helpdesk;

use App\Models\Mship\RoleHelpdeskStaffSetting;
use App\Services\Helpdesk\HelpdeskStaffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HelpdeskStaffServiceTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = config('database.connections.mysql.database');
        $connection = config('database.connections.mysql');
        config([
            'database.connections.helpdesk_test' => $connection,
            'services.helpdesk.connection' => 'helpdesk_test',
            'services.helpdesk.database' => $this->database,
        ]);
        DB::purge('helpdesk_test');

        $this->user->forceFill([
            'name_first' => 'Test',
            'name_last' => 'User',
        ])->saveQuietly();

        $this->createHelpdeskTables();
        $this->clearHelpdeskTables();
    }

    protected function tearDown(): void
    {
        $this->clearHelpdeskTables();
        DB::purge('helpdesk_test');

        parent::tearDown();
    }

    #[Test]
    public function it_creates_and_reconciles_a_staff_account(): void
    {
        $role = $this->mappedRole(priority: 10);
        $this->assignRoleDirectly($role);

        app(HelpdeskStaffService::class)->reconcile($this->user);

        $staff = DB::connection('helpdesk_test')->table('ost_staff')->first();

        $this->assertSame("s{$this->user->id}", $staff->username);
        $this->assertSame('test.user@vatsim.uk', $staff->email);
        $this->assertSame(10, $staff->dept_id);
        $this->assertSame(30, $staff->role_id);
        $this->assertNull($staff->passwd);
        $this->assertDatabaseHas('mship_helpdesk_staff_account', [
            'account_id' => $this->user->id,
            'helpdesk_staff_id' => $staff->staff_id,
        ]);
        $this->assertSame(
            [['team_id' => 20, 'staff_id' => $staff->staff_id]],
            DB::connection('helpdesk_test')->table('ost_team_member')
                ->get(['team_id', 'staff_id'])
                ->map(fn ($row): array => (array) $row)
                ->all(),
        );
        $this->assertDatabaseHas("{$this->database}.ost_staff_dept_access", [
            'staff_id' => $staff->staff_id,
            'dept_id' => 40,
            'role_id' => 50,
        ], 'helpdesk_test');
    }

    #[Test]
    public function it_adopts_and_updates_an_existing_staff_account(): void
    {
        $role = $this->mappedRole(priority: 10);
        $this->assignRoleDirectly($role);

        DB::connection('helpdesk_test')->table('ost_staff')->insert([
            'staff_id' => 99,
            'dept_id' => 1,
            'role_id' => 1,
            'username' => "s{$this->user->id}",
            'firstname' => 'Old',
            'lastname' => 'Name',
            'email' => 'old@example.com',
            'signature' => '',
            'created' => now(),
            'updated' => now(),
        ]);

        app(HelpdeskStaffService::class)->reconcile($this->user);

        $this->assertSame(1, DB::connection('helpdesk_test')->table('ost_staff')->count());
        $this->assertDatabaseHas("{$this->database}.ost_staff", [
            'staff_id' => 99,
            'username' => "s{$this->user->id}",
            'firstname' => $this->user->name_first,
            'lastname' => $this->user->name_last,
            'email' => 'test.user@vatsim.uk',
            'dept_id' => 10,
            'role_id' => 30,
        ], 'helpdesk_test');
        $this->assertDatabaseHas('mship_helpdesk_staff_account', [
            'account_id' => $this->user->id,
            'helpdesk_staff_id' => 99,
        ]);
    }

    #[Test]
    public function it_uses_the_highest_priority_mapping_and_deletes_like_osticket(): void
    {
        $lower = $this->mappedRole(priority: 1);
        $higher = $this->mappedRole(priority: 100, departmentId: 11, teamId: 21, roleId: 31);
        $this->assignRoleDirectly($lower);
        $this->assignRoleDirectly($higher);

        $service = app(HelpdeskStaffService::class);
        $service->reconcile($this->user);

        $staff = DB::connection('helpdesk_test')->table('ost_staff')->first();
        $this->assertSame(11, $staff->dept_id);
        $this->assertSame(31, $staff->role_id);

        DB::connection('helpdesk_test')->table('ost_ticket')->insert([
            'ticket_id' => 1,
            'staff_id' => $staff->staff_id,
        ]);
        DB::connection('helpdesk_test')->table('ost_thread_entry')->insert([
            'id' => 1,
            'staff_id' => $staff->staff_id,
            'poster' => 'Original',
        ]);
        DB::table('mship_account_role')
            ->where('model_type', get_class($this->user))
            ->where('model_id', $this->user->id)
            ->whereIn('role_id', [$lower->id, $higher->id])
            ->delete();

        $service->reconcile($this->user);

        $this->assertDatabaseMissing("{$this->database}.ost_staff", ['staff_id' => $staff->staff_id], 'helpdesk_test');
        $this->assertDatabaseHas("{$this->database}.ost_ticket", ['ticket_id' => 1, 'staff_id' => 0], 'helpdesk_test');
        $this->assertDatabaseHas("{$this->database}.ost_thread_entry", [
            'id' => 1,
            'staff_id' => 0,
            'poster' => "{$this->user->name_first} {$this->user->name_last}",
        ], 'helpdesk_test');
        $this->assertDatabaseMissing('mship_helpdesk_staff_account', ['account_id' => $this->user->id]);
        $this->assertSame(0, DB::connection('helpdesk_test')->table('ost_team_member')->count());
        $this->assertSame(0, DB::connection('helpdesk_test')->table('ost_staff_dept_access')->count());
    }

    private function mappedRole(
        int $priority,
        int $departmentId = 10,
        int $teamId = 20,
        int $roleId = 30,
    ): Role {
        $role = Role::query()->create([
            'name' => fake()->unique()->word(),
            'guard_name' => 'web',
        ]);

        RoleHelpdeskStaffSetting::withoutEvents(function () use ($departmentId, $priority, $role, $roleId, $teamId): void {
            $setting = RoleHelpdeskStaffSetting::query()->create([
                'role_id' => $role->id,
                'helpdesk_department_id' => $departmentId,
                'helpdesk_team_id' => $teamId,
                'helpdesk_role_id' => $roleId,
                'priority' => $priority,
            ]);
            $setting->extendedAccess()->create([
                'helpdesk_department_id' => 40,
                'helpdesk_role_id' => 50,
            ]);
        });

        return $role;
    }

    private function assignRoleDirectly(Role $role): void
    {
        DB::table('mship_account_role')->insert([
            'role_id' => $role->id,
            'model_type' => get_class($this->user),
            'model_id' => $this->user->id,
        ]);
    }

    private function createHelpdeskTables(): void
    {
        $schema = Schema::connection('helpdesk_test');

        if (! $schema->hasTable('ost_staff')) {
            $schema->create('ost_staff', function (Blueprint $table): void {
                $table->increments('staff_id');
                $table->unsignedInteger('dept_id')->default(0);
                $table->unsignedInteger('role_id')->default(0);
                $table->string('username', 32)->unique();
                $table->string('firstname', 32)->nullable();
                $table->string('lastname', 32)->nullable();
                $table->string('passwd', 128)->nullable();
                $table->string('backend', 32)->nullable();
                $table->string('email')->nullable();
                $table->text('signature');
                $table->boolean('isactive')->default(true);
                $table->timestamp('created')->nullable();
                $table->timestamp('updated')->nullable();
            });
            $schema->create('ost_team_member', function (Blueprint $table): void {
                $table->unsignedInteger('team_id');
                $table->unsignedInteger('staff_id');
                $table->unsignedInteger('flags')->default(0);
                $table->primary(['team_id', 'staff_id']);
            });
            $schema->create('ost_staff_dept_access', function (Blueprint $table): void {
                $table->unsignedInteger('staff_id');
                $table->unsignedInteger('dept_id');
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('flags')->default(1);
                $table->primary(['staff_id', 'dept_id']);
            });
            $schema->create('ost_ticket', function (Blueprint $table): void {
                $table->unsignedInteger('ticket_id')->primary();
                $table->unsignedInteger('staff_id')->default(0);
            });
            $schema->create('ost_thread_entry', function (Blueprint $table): void {
                $table->unsignedInteger('id')->primary();
                $table->unsignedInteger('staff_id')->default(0);
                $table->string('poster')->nullable();
            });
        }
    }

    private function clearHelpdeskTables(): void
    {
        foreach (['ost_team_member', 'ost_staff_dept_access', 'ost_ticket', 'ost_thread_entry', 'ost_staff'] as $table) {
            DB::connection('helpdesk_test')->table($table)->delete();
        }
    }
}
