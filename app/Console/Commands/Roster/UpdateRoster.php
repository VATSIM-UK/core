<?php

namespace App\Console\Commands\Roster;

use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\RosterUpdate;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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

        $rosterUpdate = RosterUpdate::create([
            'period_start' => $this->fromDate,
            'period_end' => $this->toDate,
        ]);

        $meetHourRequirement = Atc::with(['account.states'])
            ->select(['networkdata_atc.account_id'])
            ->whereBetween('disconnected_at', [$this->fromDate, $this->toDate])
            ->accountIsPartOfUk()
            ->positionIsWithinUk()
            ->groupBy('account_id')
            ->havingRaw("SUM(minutes_online) / 60 > {$this->minimumHours}")
            ->pluck('account_id');

        // Automatically mark those on the Gander Oceanic roster as eligible
        $eligible = $meetHourRequirement->merge(
            $ganderControllers = Http::get(config('services.gander-oceanic.api.base').'/roster')
                ->collect()
                ->where('active', true)
                ->pluck('cid')
                ->flatten()
        )->unique();

        // On the roster, do not need to be on...
        $removeFromRoster = Roster::withoutGlobalScopes()
            ->whereNotIn('account_id', $eligible);

        $homeRemovals = $removeFromRoster->whereHas('account', function ($query) {
            $query->whereHas('states', function ($query) {
                $query
                    ->join('roster', 'mship_account_state.account_id', '=', 'roster.account_id')
                    ->whereIn('mship_state.code', ['DIVISION'])
                    ->orWhereColumn('roster.updated_at', '>', 'mship_account_state.start_at');
            });
        });

        $visitingAndTransferringRemovals = $removeFromRoster->whereHas('account', function ($query) {
            $query->whereHas('states', function ($query) {
                $query
                    ->join('roster', 'mship_account_state.account_id', '=', 'roster.account_id')
                    ->whereIn('mship_state.code', ['TRANSFERRING', 'VISITING'])
                    ->orWhereColumn('roster.updated_at', '>', 'mship_account_state.start_at');
            });
        });

        $removeFromRoster->get()
            ->each
            ->remove($rosterUpdate);

        // On an ATC waiting list, not on the roster, need to be removed...
        $removeFromWaitingList = WaitingListAccount::with('waitingList')
            ->whereIn('list_id', WaitingList::where('department', WaitingList::ATC_DEPARTMENT)->get('id'))
            ->whereNotIn('account_id', $eligible)
            ->get();
        $removeFromWaitingList
            ->each
            ->delete();

        // Not on the roster, need to be on...
        Roster::upsert(
            $eligible->map(fn ($value) => ['account_id' => $value])->toArray(),
            ['account_id']
        );

        $rosterUpdate->update([
            'data' => [
                'meetHourRequirement' => $meetHourRequirement->count(),
                'ganderControllers' => $ganderControllers->count(),
                'eligible' => $eligible->count(),
                'removeFromRoster' => $removeFromRoster->count(),
                'homeRemovals' => $homeRemovals->count(),
                'visitingAndTransferringRemovals' => $visitingAndTransferringRemovals->count(),
                'removeFromWaitingList' => $removeFromWaitingList->countBy('list_id'),
            ],
        ]);

        $this->comment('✅ Roster updated!');
    }
}
