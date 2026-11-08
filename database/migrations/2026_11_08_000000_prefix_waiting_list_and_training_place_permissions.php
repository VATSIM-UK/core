<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PREFIXES = [
        'waiting-lists',
        'training-places',
    ];

    private const NEW_PREFIX = 'training.';

    public function up(): void
    {
        DB::transaction(function () {
            $permissions = DB::table('mship_permission')
                ->where(fn ($query) => $this->whereLegacyName($query))
                ->orderBy('id')
                ->get();

            foreach ($permissions as $permission) {
                $this->renameOrMerge($permission, self::NEW_PREFIX.$permission->name);
            }
        });

        $this->forgetPermissionCache();
    }

    public function down(): void
    {
        DB::transaction(function () {
            $permissions = DB::table('mship_permission')
                ->where(function ($query) {
                    foreach (self::PREFIXES as $prefix) {
                        $query->orWhere('name', 'like', self::NEW_PREFIX.$prefix.'%');
                    }
                })
                ->orderBy('id')
                ->get();

            foreach ($permissions as $permission) {
                $this->renameOrMerge($permission, substr($permission->name, strlen(self::NEW_PREFIX)));
            }
        });

        $this->forgetPermissionCache();
    }

    private function whereLegacyName(Builder $query): void
    {
        foreach (self::PREFIXES as $prefix) {
            $query->orWhere('name', $prefix)
                ->orWhere('name', 'like', $prefix.'.%');
        }
    }

    private function renameOrMerge(object $permission, string $newName): void
    {
        $existing = DB::table('mship_permission')
            ->where('name', $newName)
            ->where('guard_name', $permission->guard_name)
            ->where('id', '!=', $permission->id)
            ->first();

        if ($existing === null) {
            DB::table('mship_permission')
                ->where('id', $permission->id)
                ->update([
                    'name' => $newName,
                    'updated_at' => now(),
                ]);

            return;
        }

        $this->repointPivots('mship_role_permission', $permission->id, $existing->id, ['role_id']);
        $this->repointPivots('mship_account_permission', $permission->id, $existing->id, ['model_id', 'model_type']);

        DB::table('mship_permission')->where('id', $permission->id)->delete();
    }

    /**
     * @param  list<string>  $columns
     */
    private function repointPivots(string $table, int $fromId, int $toId, array $columns): void
    {
        $duplicates = DB::table($table.' as old')
            ->join($table.' as existing', function ($join) use ($columns) {
                foreach ($columns as $column) {
                    $join->on('existing.'.$column, '=', 'old.'.$column);
                }
            })
            ->where('existing.permission_id', $toId)
            ->where('old.permission_id', $fromId)
            ->get(array_map(fn (string $column) => 'old.'.$column.' as '.$column, $columns));

        foreach ($duplicates as $duplicate) {
            $delete = DB::table($table)->where('permission_id', $fromId);

            foreach ($columns as $column) {
                $delete->where($column, $duplicate->{$column});
            }

            $delete->delete();
        }

        DB::table($table)
            ->where('permission_id', $fromId)
            ->update(['permission_id' => $toId]);
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
