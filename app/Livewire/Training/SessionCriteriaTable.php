<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\Cts\ProgSheetField;
use App\Models\Cts\Session;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class SessionCriteriaTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $sessionId;

    public function table(Table $table): Table
    {
        $session = Session::query()
            ->with(['reportSheets.field.category'])
            ->findOrFail($this->sessionId);

        $scores = [];
        $notes = [];
        foreach ($session->reportSheets as $sheet) {
            $scores[$sheet->field_id] = $sheet->field_score;
            $notes[$sheet->field_id] = $sheet->notes;
        }

        $fields = ProgSheetField::query()
            ->whereIn('field_id', array_keys($scores))
            ->with('category')
            ->get();

        return $table
            ->records(fn () => $fields)
            ->columns([
                TextColumn::make('field')
                    ->label('Criteria')
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),

                TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->state(fn (ProgSheetField $record) => $scores[$record->field_id] ?? '-'),
            ])
            ->defaultGroup(
                Group::make('category.catName')
                    ->label('Category')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->emptyStateHeading('No Criteria From Previous Sessions')
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.training.session-criteria-table');
    }
}
