<?php

namespace App\Livewire\Roster;

use App\Models\Mship\Account;
use App\Models\Roster;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class Search extends Component
{
    public ?string $searchTerm;

    public ?Account $account;

    public ?Roster $roster;

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

    // home member - RATING + other stuff
    // visiting/transferring - other stuff

    public function clear()
    {
        $this->account = null;
    }

    public function render()
    {
        return view('livewire.roster.search');
    }
}
