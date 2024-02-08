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

    public function search()
    {
        try {
            $account = Account::findOrFail($this->searchTerm);

            $this->redirect(route('site.roster.show', ['account' => $account]), navigate: true);
        } catch (ModelNotFoundException $e) {
            $this->searchTerm = null;

            Notification::make()
                ->title('No account found with that CID.')
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.roster.search');
    }
}
