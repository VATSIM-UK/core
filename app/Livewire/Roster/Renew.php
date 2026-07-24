<?php

namespace App\Livewire\Roster;

use App\Libraries\UKCP;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Services\Networkdata\AtcNetworkdataService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Renew extends Component
{
    public int $page;

    public Collection $notifications;

    public function mount(UKCP $ukcp)
    {
        $this->page = 1;

        $userOnRoster = Roster::where('account_id', auth()->user()->id)->exists();

        $this->notifications = collect($ukcp->getUnreadNotificationsForUser(auth()->user()))->keyBy('id');

        if ($userOnRoster) {
            session()->flash('error', 'You are already on the roster!');

            return redirect()->route('site.roster.index');
        }

        if (! auth()->user()->hasState('DIVISION') || ! auth()->user()->has_controller_rating) {
            return redirect()->route('site.roster.index');
        }
    }

    public function nextPage()
    {
        if (! $this->canReactivate()) {
            abort(403);
        }

        $this->page++;
    }

    private function canReactivate(): bool
    {
        $account = auth()->user();
        $lastLogon = AtcNetworkdataService::getLatestNetworkdataForAccount($account)?->disconnected_at;

        // Check if the account has had a connection in the last 18 months
        if (! $lastLogon || $lastLogon->diffInMonths(Carbon::now()) > 18) {
            return false;
        }

        if (RosterHistory::where('account_id', $account->id)->exists()) {
            return $this->hasMetHoursInLastTwoQuarters();
        }

        return true;
    }

    private function hasMetHoursInLastTwoQuarters(): bool
    {
        // The account must have met the minimum controlling hours in one of the last 2 consectutive quarters
        $now = Carbon::now();
        $currentQuarterStart = $now->copy()->startOfQuarter();

        $q1End = $currentQuarterStart->copy()->subDay()->endOfDay();
        $q1Start = $currentQuarterStart->copy()->subMonths(3);

        $q2End = $q1Start->copy()->subDay()->endOfDay();
        $q2Start = $q1Start->copy()->subMonths(3);

        $q1Hours = $this->getQuarterlyHours($q1Start, $q1End);
        $q2Hours = $this->getQuarterlyHours($q2Start, $q2End);

        return $q1Hours >= 3 || $q2Hours >= 3;
    }

    private function getQuarterlyHours(Carbon $start, Carbon $end): float
    {
        return Atc::where('account_id', auth()->user()->id)
            ->isUK()
            ->whereBetween('disconnected_at', [$start, $end])
            ->sum('minutes_online') / 60;
    }

    public function render(UKCP $ukcp)
    {
        $account = auth()->user();
        $canReactivate = $this->canReactivate();
        $lastLogon = $canReactivate ? AtcNetworkdataService::getLatestNetworkdataForAccount($account)->disconnected_at : null;
        $lastTwoQuartersFailed = ! $this->hasMetHoursInLastTwoQuarters();

        return view('livewire.roster.renew', [
            'notifications' => $this->notifications,
            'canReactivate' => $canReactivate,
            'lastLogon' => $lastLogon?->diffForHumans(),
            'page' => $this->page,
            'lastTwoQuartersFailed' => $lastTwoQuartersFailed,
        ]);
    }

    public function reactivate()
    {
        if (! $this->canReactivate()) {
            abort(403);
        }

        Roster::create([
            'account_id' => auth()->user()->id,
        ]);

        session()->flash('success', '✅ You have been reactivated on the roster! Welcome back!');

        return redirect()->route('site.roster.index');
    }

    public function markNotificationRead(UKCP $ukcp, int $notificationId, int $arrayIndex)
    {
        $result = $ukcp->markNotificationReadForUser(auth()->user(), $notificationId);

        if ($result) {
            $this->notifications->forget($notificationId);
        }
    }

    #[Computed]
    public function reactivateButtonDisabled()
    {
        return $this->notifications->isNotEmpty();
    }
}
