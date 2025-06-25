<?php

namespace App\Livewire\Roster;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Roster;
use Filament\Notifications\Notification;
use Livewire\Component;

class Show extends Component
{
    public Account $account;

    public ?Roster $roster;

    public ?string $searchTerm = null;

    /**
     * @var Position[]|null
     */
    public ?array $positions = null;

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->roster = Roster::where('account_id', $this->account->id)->with('restrictionNote')->first();
    }

    public function search()
    {
        $this->positions = Position::where('callsign', 'LIKE', "%{$this->searchTerm}%")->take(10)->get()->all();

        if (! $this->positions) {
            $this->searchTerm = null;
            $this->positions = null;

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
