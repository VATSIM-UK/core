<?php

namespace App\Filament\Pages\PilotTraining;

use App\Filament\Helpers\Pages\BasePage;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;

class Reporting extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Reporting';

    protected static string $view = 'filament.pages.pilot-training.reporting';

    protected static ?string $navigationLabel = 'Pilot Training Reporting';

    protected ?string $heading = 'Pilot Training Reporting';

    public ?int $quarter = null;

    public ?int $year = null;

    private $quarterMappings = ['01-01' => 'Q1', '04-01' => 'Q2', '07-01' => 'Q3', '10-01' => 'Q4'];

    public function mount(): void
    {
        parent::mount();
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        $yearOptions = range(now()->year, 2016, -1);

        return [
            Grid::make()->schema([
                Select::make('quarter')
                    ->required()
                    ->inOptions()
                    ->options($this->quarterMappings),
                Select::make('year')
                    ->required()
                    ->inOptions()
                    ->options(collect($yearOptions)->mapWithKeys(fn ($year) => [$year => $year])),
            ]),
        ];
    }
}
