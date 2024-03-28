<?php

namespace App\Livewire\Roster;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Roster;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class Show extends Component
{
    public Account $account;

    public ?Roster $roster;

    public ?string $searchTerm = null;

    public ?Position $position;

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->roster = Roster::where('account_id', $this->account->id)->first();
    }

    public function search()
    {
        try {
            $this->position = Position::where('callsign', 'LIKE', "%{$this->searchTerm}%")->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->searchTerm = null;
            $this->position = null;

            Notification::make()
                ->title('Position cannot be found.')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.roster.show');
    }
}
