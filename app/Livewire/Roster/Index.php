<?php

namespace App\Livewire\Roster;

use App\Models\Roster;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.roster.index', [
            'roster' => Roster::where('account_id', auth()->user()->id)->exists(),
        ]);
    }
}
