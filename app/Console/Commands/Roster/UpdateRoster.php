<?php

namespace App\Console\Commands\Roster;

use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateRoster extends Command
{
    protected $signature = 'roster:update {fromDate} {toDate}';

    protected $description = 'Update the ATC roster based on ATC session data.';

    protected int $minimumHours = 3;

    protected Carbon $fromDate;

    protected Carbon $toDate;

    public function handle()
    {
        $this->fromDate = Carbon::parse($this->argument('fromDate'))->startOfDay();
        $this->toDate = Carbon::parse($this->argument('toDate'))->endOfDay();

        $eligible = Atc::with(['account.states'])
            ->select(['networkdata_atc.account_id'])
            ->whereBetween('disconnected_at', [$this->fromDate, $this->toDate])
            ->accountIsPartOfUk()
            ->positionIsWithinUk()
            ->groupBy('account_id')
            ->havingRaw("SUM(minutes_online) / 60 > {$this->minimumHours}")
            ->pluck('account_id');

        // On the roster, do not need to be on...
        Roster::withoutGlobalScopes()
            ->whereNotIn('account_id', $eligible)
            ->get()
            ->each
            ->remove();

        // Not on the roster, need to be on...
        Roster::upsert(
            $eligible->map(fn ($value) => ['account_id' => $value])->toArray(),
            ['account_id']
        );

        $this->comment('✅ Roster updated!');
    }
}
