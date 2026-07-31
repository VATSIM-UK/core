<?php

// app/Filament/Training/Pages/Endorsements/Tables/ResourceTable.php

namespace App\Filament\Training\Pages\Endorsements\Tables;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ResourceTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $resource;

    public function table(Table $table): Table
    {
        return ($this->resource)::table($table);
    }

    protected function getTableQuery(): ?Builder
    {
        return ($this->resource)::getEloquentQuery();
    }

    public function render()
    {
        return view('filament.training.pages.endorsements.tables.resource-table');
    }
}
