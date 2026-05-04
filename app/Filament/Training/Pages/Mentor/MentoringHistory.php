<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Cts\Member;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class MentoringHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected string $view = 'filament.training.pages.mentoring-history';

    protected static ?int $navigationSort = 30;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Mentoring History';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        // If a user has any mentoring permissions they are allowed to view this page
        return auth()->user()->mentorTrainingPositions()->exists();
    }

    public function mount(): void
    {
        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->firstVisibleCategory() ?? '';
        }
    }

    protected function getHeaderActions(): array
    {
        $allCategories = collect(MentorPermissionService::atcCategories())->merge(MentorPermissionService::pilotCategories());

        return [
            ActionGroup::make(
                $allCategories
                    ->filter(fn (string $cat) => $this->canViewCategory($cat))
                    ->map(fn (string $cat) => Action::make('cat_'.str($cat)->slug('_'))
                        ->label($cat)
                        ->url(static::getUrl(['category' => $cat]))
                        ->icon($this->category === $cat ? 'heroicon-m-check' : null)
                    )
                    ->all()
            )
                ->label("Training Group: {$this->category}")
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading("{$this->category} Session History")
            ->queryStringIdentifier('mentoring_history')
            ->query((new SessionRepository)->getAllAcceptedSessionsForPositionsQuery($this->getVisibleCtsPositions())->where('taken_date', '<', now()))
            ->defaultSort('taken_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->getStateUsing(fn ($record) => $record->student->name)
                    ->description(fn ($record) => $record->student->cid),

                TextColumn::make('mentor_name')
                    ->label('Mentor')
                    ->getStateUsing(fn ($record) => $record->mentor->name)
                    ->description(fn ($record) => $record->mentor->cid),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('taken_date')
                    ->label('Date & Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->noShow == 1 => 'No Show',
                        $record->cancelled_datetime != null => 'Cancelled',
                        $record->filed != null => 'Completed',
                        default => 'Pending',
                    })
                    ->color(fn ($state) => match ($state) {
                        'Pending' => 'primary',
                        'No Show' => 'danger',
                        'Cancelled' => 'warning',
                        'Completed' => 'success',
                    })
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('Position')
                    ->options(function () {
                        $positions = $this->getVisibleCtsPositions();

                        return array_combine($positions, $positions);
                    })
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('student')
                    ->label('Student')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => TrainingMemberAccountSearch::searchAccountsForSelect($search))
                    ->getOptionLabelUsing(fn ($value) => Member::where('cid', $value)->first()?->name." ({$value})")
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value) => $query->whereHas('student', fn ($q) => $q->where('cid', $value))
                        );
                    }),

                SelectFilter::make('mentor')
                    ->label('Mentor')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => TrainingMemberAccountSearch::searchAccountsForSelect($search))
                    ->getOptionLabelUsing(fn ($value) => Member::where('cid', $value)->first()?->name." ({$value})")
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value) => $query->whereHas('mentor', fn ($q) => $q->where('cid', $value))
                        );
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                        'pending' => 'Pending',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'completed' => $query->whereNotNull('filed'),
                            'cancelled' => $query->whereNotNull('cancelled_datetime'),
                            'no_show' => $query->where('noShow', 1),
                            'pending' => $query->whereNull('filed')
                                ->whereNull('cancelled_datetime')
                                ->where('noShow', 0),
                            default => $query,
                        };
                    }),

                Filter::make('taken_date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('taken_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('taken_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => "https://cts.vatsim.uk/mentors/report.php?id={$record->id}&view=report")
                    ->visible(fn ($record) => $record->filed != null)
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No mentoring sessions found in this group');
    }

    private function getVisibleCtsPositions(): array
    {
        return app(MentorPermissionService::class)->getAssignedCtsCallsigns(auth()->user(), $this->category);
    }

    private function canViewCategory(string $category): bool
    {
        $assignedCallsigns = app(MentorPermissionService::class)->getAssignedCtsCallsigns(auth()->user(), $category);

        return count($assignedCallsigns) > 0;
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(MentorPermissionService::atcCategories())->merge(MentorPermissionService::pilotCategories())->first(fn (string $cat) => $this->canViewCategory($cat));
    }
}
