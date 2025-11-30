<?php

namespace App\Filament\Training\Pages\TrainingPlace;

use App\Filament\Training\Pages\TrainingPlace\Widgets\TrainingPlaceStatsWidget;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewTrainingPlace extends Page implements HasInfolists, HasTable
{
    use InteractsWithInfolists;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.view-training-place';

    protected static ?string $slug = 'training-places/{trainingPlaceId}';

    public TrainingPlace $trainingPlace;

    public string $trainingPlaceId;

    public function mount(): void
    {
        $this->trainingPlace = TrainingPlace::where('id', $this->trainingPlaceId)->with('waitingListAccount', 'trainingPosition')->firstOrFail();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingPlaceStatsWidget::make([
                'trainingPlace' => $this->trainingPlace,
            ]),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->record($this->trainingPlace)->schema([
            Section::make('Training Place Details')->schema([
                TextEntry::make('waitingListAccount.account.name')->label('Name'),
                TextEntry::make('waitingListAccount.account.id')->label('CID'),
                TextEntry::make('trainingPosition.position.name')->label('Position'),
                TextEntry::make('created_at')->label('Training Start')->dateTime('d/m/Y H:i:s'),
                TextEntry::make('waitingListAccount.created_at')->label('Waiting List Join Date')->dateTime('d/m/Y H:i:s'),
            ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Mentoring session history')
            ->queryStringIdentifier('mentoring')
            ->query(Session::query()->where('position', $this->trainingPlace->trainingPosition->position->callsign)->where('taken_date', '>=', now()->subDays(180)))
            ->defaultSort('date_1', 'desc')
            ->paginated([10])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('position')->label('Position'),
                TextColumn::make('date_1')->label('Date')->date('d/m/Y'),
                TextColumn::make('mentor.cid')->label('Mentor CID'),
                TextColumn::make('mentor.name')->label('Mentor'),
                TextColumn::make('status')->label('Status')->badge()->getStateUsing(
                    function ($record) {
                        if ($record->noShow) {
                            return 'No Show';
                        }
                        if ($record->cancelled_datetime) {
                            return 'Cancelled';
                        }
                        if ($record->session_done) {
                            return 'Completed';
                        }

                        return 'Pending';
                    })
                    ->color(fn ($state) => match ($state) {
                        'Pending' => 'primary',
                        'No Show' => 'danger',
                        'Cancelled' => 'warning',
                        'Completed' => 'success',
                    }),
            ])
            ->actions([
                ViewAction::make()->url(fn ($record) => "https://cts.vatsim.uk/mentors/report.php?id={$record->id}&view=report"),
            ])
            ->emptyStateHeading('No mentoring sessions found');
    }
}
