<?php

namespace App\Filament\Admin\Pages\ATCTraining;

use App\Filament\Admin\Helpers\Pages\BasePage;
use App\Filament\Admin\Pages\ATCTraining\Widgets\EndorsementWidget;
use App\Filament\Admin\Pages\ATCTraining\Widgets\RosterWidget;
use App\Services\Admin\ATCTrainingStats;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;

class GenerateATCTrainingQuarterlyStats extends BasePage implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'ATC Training';

    protected string $view = 'filament.pages.atc-training.generate-atc-quarterly-stats';

    protected static ?string $navigationLabel = 'Quarterly Stats';

    protected static ?string $title = 'ATC Quarterly Stats';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'generate-atc-quarterly-stats';
    }

    public ?string $quarter = null;

    public ?string $year = null;

    public $statistics = null;

    private $quarterMappings = ['01-01' => 'Q1', '04-01' => 'Q2', '07-01' => 'Q3', '10-01' => 'Q4'];

    public function mount(): void
    {
        parent::mount();
        $this->form->fill();
    }

    public function getHeaderWidgets(): array
    {
        return [
            EndorsementWidget::make(['position' => 'Heathrow']),
            EndorsementWidget::make(['position' => 'Military']),
            RosterWidget::class,
        ];
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

    public function submit(): void
    {
        $this->validate();

        $startDate = Carbon::parse($this->year.'-'.$this->quarter);
        $endDate = $startDate->copy()->addMonths(3);

        $this->statistics = collect([
            'Completed Mentoring Sessions' => ATCTrainingStats::completedMentoringSessions($startDate, $endDate),
            'Exam Passes' => ATCTrainingStats::examPasses($startDate, $endDate),
            'Issued Position Group Endorsements' => ATCTrainingStats::issuedPositionGroupEndorsements($startDate, $endDate),
            'Roster' => [
                ['name' => 'Roster Update', 'value' => ATCTrainingStats::rosterUpdateLink($startDate, $endDate)],
            ],
        ]);
    }

    public static function canAccess(): bool
    {

        return auth()->user()->can('atc.stats');
    }
}
