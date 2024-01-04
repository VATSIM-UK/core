<?php

namespace App\Livewire;

use App\Models\Mship\Account;
use App\Models\Roster;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class RosterSearch extends Component
{
    public ?string $searchTerm;

    public ?Account $account;
    public ?Roster $roster;

    public function mount()
    {
            $this->account = Account::findOrFail(1169992);
            $this->roster = Roster::where('account_id', 1169992)->first();
            $this->searchTerm = null;
    }

    public function search()
    {
        try {
            $this->account = Account::findOrFail($this->searchTerm);
            $this->roster = Roster::where('account_id', $this->account->id)->first();
            $this->searchTerm = null;
        } catch (ModelNotFoundException $e) {
            $this->searchTerm = null;

            Notification::make()
                ->title('No account found with that CID.')
                ->send();
        }
    }

    // roster - CID, created, updated

    // restrictions - mship_account_restrictions - account_id, string, deleted_at

    // home member - RATING + other shit
    // visiting/transferring - other shit

    public function clear()
    {
        $this->account = null;
    }

    public function render()
    {
        return view('livewire.roster-search');
    }
}
