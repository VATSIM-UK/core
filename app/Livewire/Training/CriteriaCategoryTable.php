<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\Session;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class CriteriaCategoryTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $currentSessionId;

    public array $sessionIds = [];

    public string $categoryName;

    public function table(Table $table): Table
    {
        $sessions = Session::query()
            ->with(['reportSheets.field.category'])
            ->whereIn('id', $this->sessionIds)
            ->orderBy('taken_date')
            ->get();

        $scores = [];
        foreach ($sessions as $session) {
            foreach ($session->reportSheets as $sheet) {
                if ($sheet->field?->category?->catName === $this->categoryName) {
                    $scores[$sheet->field_id][$session->id] = $sheet->field_score;
                }
            }
        }

        $fields = ProgSheetField::query()
            ->whereHas('category', fn ($query) => $query->where('catName', $this->categoryName))
            ->whereIn('field_id', array_keys($scores))
            ->get();

        $columns = [
            TextColumn::make('field')
                ->label('Criteria')
                ->weight(FontWeight::SemiBold)
                ->grow(),
        ];

        foreach ($sessions as $session) {
            $isCurrentSession = $session->id === $this->currentSessionId;

            $sessionId = $session->id;

            $columns[] = TextColumn::make("score_{$sessionId}")
                ->label(Carbon::parse($session->taken_date)->format('d/m/Y'))
                ->badge()
                ->url($isCurrentSession ? null : ViewMentoringReport::getUrl(['sessionId' => $sessionId]))
                ->state(fn (ProgSheetField $record) => $scores[$record->field_id][$sessionId] ?? null)
                ->extraHeaderAttributes($isCurrentSession ? ['class' => 'text-gray-400 dark:text-gray-500'] : [])
                ->default('-');
        }

        return $table
            ->records(fn () => $fields)
            ->columns($columns)
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.training.criteria-category-table');
    }
}
