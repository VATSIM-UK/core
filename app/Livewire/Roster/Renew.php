<?php

namespace App\Livewire\Roster;

use App\Libraries\UKCP;
use App\Models\Roster;
use App\Services\Networkdata\AtcNetworkdataService;
use Carbon\Carbon;
use Livewire\Component;

class Renew extends Component
{
    public int $page;

    public function mount()
    {
        $this->page = 1;

        $userOnRoster = Roster::where('account_id', auth()->user()->id)->exists();

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
        $this->page++;
    }

    public function render(UKCP $ukcp)
    {
        $lastLogon = AtcNetworkdataService::getLatestNetworkdataForAccount(auth()->user())?->disconnected_at;
        $canReactivate = $lastLogon && Carbon::now()->diffInMonths($lastLogon) <= 18;

        return view('livewire.roster.renew', [
            'notifications' => $ukcp->getNotifications(),
            'canReactivate' => $canReactivate,
            'lastLogon' => $lastLogon?->diffForHumans(),
            'page' => $this->page,
        ]);
    }

    public function reactivate()
    {
        Roster::create([
            'account_id' => auth()->user()->id,
        ]);

        session()->flash('success', '✅ You have been reactivated on the roster! Welcome back!');

        return redirect()->route('site.roster.index');
    }
}
