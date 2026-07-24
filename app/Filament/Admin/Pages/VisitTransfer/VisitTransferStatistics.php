<?php

namespace App\Filament\Admin\Pages\VisitTransfer;

use App\Filament\Admin\Helpers\Pages\BasePage;
use App\Models\VisitTransfer\Application;
use App\Services\Admin\VisitTransferStatsExport;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitTransferStatistics extends BasePage implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Visiting / Transferring';

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Visiting & Transferring Overview';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.visit-transfer.visit-transfer-statistics';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'visit-transfer/overview';
    }

    protected function getDescription(): ?string
    {
        $year = $this->year ?: now()->year;
        $type = $this->type;
        $quarter = $this->quarter;

        return "Showing statistics for {$year} - ".($type ? ($type === Application::TYPE_TRANSFER ? 'Transfer' : 'Visit') : 'All Types').($quarter ? " - Quarter {$quarter}" : ' - All Quarters');
    }

    public int $year = 0;

    public ?int $type = null;

    public int $quarter = 0;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->type = null;
        $this->quarter = 0;
    }

    public function filtersAction(): Action
    {
        return Action::make('filters')
            ->label('Filters')
            ->icon('heroicon-o-funnel')
            ->modalHeading('Filter Statistics')
            ->form([
                Select::make('year')
                    ->label('Year')
                    ->options(
                        collect(range(now()->year, 2016))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                    )
                    ->selectablePlaceholder(false),

                Select::make('quarter')
                    ->label('Quarter')
                    ->options([
                        0 => 'All Quarters',
                        1 => 'Q1 (Jan–Mar)',
                        2 => 'Q2 (Apr–Jun)',
                        3 => 'Q3 (Jul–Sep)',
                        4 => 'Q4 (Oct–Dec)',
                    ])
                    ->selectablePlaceholder(false)
                    ->default(0),

                Select::make('type')
                    ->label('Application Type')
                    ->options([
                        Application::TYPE_TRANSFER => 'Transfer',
                        Application::TYPE_VISIT => 'Visit',
                    ])
                    ->placeholder('All Types'),
            ])
            ->fillForm([
                'year' => $this->year,
                'type' => $this->type,
                'quarter' => $this->quarter,
            ])
            ->action(function (array $data): void {
                $this->year = (int) $data['year'];
                $this->type = isset($data['type'])
                    ? (int) $data['type']
                    : null;
                $this->quarter = (int) ($data['quarter'] ?? 0);
            });
    }

    protected function getQuarterRange(int $year, int $quarter): array
    {
        if ($quarter === 0) {
            return [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        return [
            Carbon::create($year, $startMonth, 1)->startOfDay(),
            Carbon::create($year, $endMonth, Carbon::create($year, $endMonth, 1)->daysInMonth)->endOfDay(),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            $this->exportAction(),
            $this->filtersAction(),
        ];
    }

    public function exportAction(): Action
    {
        return Action::make('export')
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (): BinaryFileResponse {
                [$start, $end] = $this->getQuarterRange($this->year, $this->quarter);

                $path = VisitTransferStatsExport::build($this->type, $start, $end, $this->year, $this->quarter);

                return response()->download($path, 'vt-statistics-'.$this->year.($this->quarter ? "-Q{$this->quarter}" : '').'.xlsx')
                    ->deleteFileAfterSend(true);
            });
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('vt.access');
    }
}
