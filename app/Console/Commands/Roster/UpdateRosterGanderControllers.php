<?php

namespace App\Console\Commands\Roster;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
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
        $ganderValidatedAccountIds = Http::get(config('services.gander-oceanic.api.base').'/roster')
            ->collect()
            ->where('active', true)
            ->pluck('cid');

        DB::transaction(function () use ($ganderValidatedAccountIds) {
            /**
             * Ensure each account on the Gander roster exists in the database
             * in the event they do not, create them with default values.
             * These accounts have not signed into our system before
             * and because we don't have their name, we'll use 'Unknown'
             * When they sign in, we will receive their name and update it
             * from VATSIM Connect.
             **/
            $ganderValidatedAccountIds->each(function ($accountCid) {
                Account::firstOrCreate(['id' => $accountCid], [
                    'name_first' => 'Unknown',
                    'name_last' => 'Unknown',
                ]);
            });

            Roster::upsert(
                $ganderValidatedAccountIds->map(fn ($value) => ['account_id' => $value])->toArray(),
                ['account_id']
            );

            $positionGroup = PositionGroup::where('name', 'Shanwick Oceanic (EGGX)')->firstOrFail();

            $ganderValidatedAccountIds->reject(function ($value) use ($positionGroup) {
                return Endorsement::where([
                    'account_id' => $value,
                    'endorsable_id' => $positionGroup->id,
                    'endorsable_type' => PositionGroup::class,
                ])->exists();
            })->each(function ($value) use ($positionGroup) {
                Endorsement::create([
                    'account_id' => $value,
                    'endorsable_id' => $positionGroup->id,
                    'endorsable_type' => PositionGroup::class,
                ]);
            });
        });
    }
}
