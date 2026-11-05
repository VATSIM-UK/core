<?php

declare(strict_types=1);

namespace App\Services\Helpdesk;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HelpdeskStaffOptions
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $cache = [];

    /**
     * @return array<int, string>
     */
    public function departments(): array
    {
        return $this->options('ost_department', 'id', 'name');
    }

    /**
     * @return array<int, string>
     */
    public function teams(): array
    {
        return $this->options('ost_team', 'team_id', 'name');
    }

    /**
     * @return array<int, string>
     */
    public function roles(): array
    {
        return $this->options('ost_role', 'id', 'name');
    }

    /**
     * @return array<int, string>
     */
    private function options(string $table, string $key, string $label): array
    {
        if (array_key_exists($table, $this->cache)) {
            return $this->cache[$table];
        }

        $database = config('services.helpdesk.database');

        if (blank($database)) {
            return $this->cache[$table] = [];
        }

        try {
            return $this->cache[$table] = DB::connection(config('services.helpdesk.connection', 'mysql'))
                ->table("{$database}.{$table}")
                ->orderBy($label)
                ->pluck($label, $key)
                ->mapWithKeys(fn (string $value, int|string $id): array => [(int) $id => $value])
                ->all();
        } catch (Throwable $exception) {
            Log::debug('Unable to load helpdesk staff options', [
                'table' => $table,
                'exception' => $exception,
            ]);

            return $this->cache[$table] = [];
        }
    }
}
