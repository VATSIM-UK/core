<?php

namespace App\Console\Commands\Roster;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;
use App\Models\Roster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateRosterGanderControllers extends Command
{
    protected $signature = 'roster:gander';

    protected $description = 'Update the ATC roster based on those within the Gander roster.';

    public function handle()
    {
        $gander = Http::get('https://ganderoceanic.ca/api/roster')
            ->collect()
            ->where('active', true)
            ->pluck('cid');

        DB::transaction(function () use ($gander) {
            Roster::upsert(
                $gander->map(fn ($value) => ['account_id' => $value])->toArray(),
                ['account_id']
            );

            Endorsement::upsert(
                $gander->map(fn ($value) => [
                    'account_id' => $value,
                    'endorsable_id' => PositionGroup::where('name', 'Shanwick Oceanic (EGGX)')->firstOrFail()->id,
                    'endorsable_type' => PositionGroup::class,
                ])->toArray(),
                ['account_id', 'endorsable_id', 'endorsable_type']
            );
        });
    }
}
