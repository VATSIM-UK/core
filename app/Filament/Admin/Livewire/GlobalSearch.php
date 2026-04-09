<?php

namespace App\Filament\Admin\Livewire;

use Filament\Facades\Filament;
use Filament\Livewire\GlobalSearch as BaseGlobalSearch;

class GlobalSearch extends BaseGlobalSearch
{
    public function updatedSearch(): void
    {
        if (Filament::getCurrentPanel()?->getId() !== 'app') {
            return;
        }

        if (blank(trim($this->search))) {
            return;
        }

        $results = $this->getResults();

        if ($results === null) {
            return;
        }

        $allResults = collect($results->getCategories())
            ->flatten();

        if ($allResults->count() === 1) {
            $this->redirect($allResults->first()->url);
        }
    }
}
