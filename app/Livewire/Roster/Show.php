<?php

namespace App\Livewire\Roster;

use App\Models\Mship\Account;
use App\Models\Roster;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class Show extends Component
{
    public Account $account;

    public ?Roster $roster;

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->roster = Roster::where('account_id', $this->account->id)->first();
    }

    public function render()
    {
        return view('livewire.roster.show');
    }
}
