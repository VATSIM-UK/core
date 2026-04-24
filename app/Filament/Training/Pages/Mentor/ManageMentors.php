<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ManageMentors extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.training.pages.manage-mentors';

    protected static ?int $navigationSort = 45;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Manage Mentors';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.mentors.view.atc') || auth()->user()->can('training.mentors.view.pilot');
    }

    public function mount(): void
    {
        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->firstVisibleCategory() ?? '';
        }
    }

    protected function getHeaderActions(): array
    {
        $allCategories = collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories());

        return [
            ActionGroup::make(
                $allCategories
                    ->filter(fn (string $cat) => $this->canViewCategory($cat))
                    ->map(fn (string $cat) => Action::make('cat_'.str($cat)->slug('_'))
                        ->label($this->formatCategoryLabel($cat))
                        ->url(static::getUrl(['category' => $cat]))
                        ->icon($this->category === $cat ? 'heroicon-m-check' : null)
                    )
                    ->all())
                ->label("Training Group: {$this->formatCategoryLabel($this->category)}")
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        $canManage = $this->canManageCategory($this->category);

        return $table
            ->query($this->mentorsQuery($this->category))
            ->columns([
                TextColumn::make('id')->label('CID')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('mentoring_permissions')
                    ->label(fn () => $this->formatCategoryLabel($this->category).' Positions')
                    ->state(fn (Account $record) => $this->resolvePermissionsArray($record, $this->category))
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(3)
                    ->expandableLimitedList(),
            ])
            ->headerActions([
                Action::make('addMentor')
                    ->label('Add Mentor')
                    ->icon('heroicon-o-plus')
                    ->modalHeading(fn () => 'Add Mentor to '.$this->formatCategoryLabel($this->category))
                    ->modalSubmitActionLabel('Add Mentor')
                    ->visible(fn () => $canManage && ! empty($this->category))
                    ->form([
                        Select::make('account_id')
                            ->label('Member')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return TrainingMemberAccountSearch::searchAccountsForSelect($search, 50);
                            })
                            ->getOptionLabelUsing(fn ($v) => Account::find($v)?->name.' ('.$v.')')
                            ->required(),
                        CheckboxList::make('position_ids')
                            ->label(fn () => $this->formatCategoryLabel($this->category).' Mentoring Permissions')
                            ->options(fn () => $this->positionOptions($this->category))
                            ->bulkToggleable()
                            ->columns(2)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $account = Account::findOrFail($data['account_id']);
                        $positions = TrainingPosition::findMany($data['position_ids']);

                        app(MentorPermissionService::class)->assignToPositions(
                            $account,
                            $positions,
                            auth()->user(),
                            $this->category
                        );

                        Notification::make()->title('Mentor added')->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('managePositions')
                    ->label('Manage Permissions')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn () => $canManage)
                    ->modalSubmitActionLabel('Update Permissions')
                    ->form(fn (Account $record) => [
                        CheckboxList::make('position_ids')
                            ->label(fn () => $this->formatCategoryLabel($this->category).' Mentoring Permissions')
                            ->options(fn () => $this->positionOptions($this->category))
                            ->bulkToggleable()
                            ->columns(2)
                            ->required()
                            ->default($this->currentPositionIds($record, $this->category)),
                    ])
                    ->action(function (array $data, Account $record): void {
                        app(MentorPermissionService::class)->syncPositionsInCategory(
                            $record,
                            $this->category,
                            collect($data['position_ids'] ?? []),
                            auth()->user(),
                        );

                        Notification::make()->title('Permissions updated')->success()->send();
                    }),

                Action::make('removeAll')
                    ->label('Remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->modalHeading(fn () => 'Remove Mentor from '.$this->formatCategoryLabel($this->category))
                    ->modalSubheading('This will revoke all mentoring permissions for this member within this specific training group.')
                    ->modalButton('Remove Mentor')
                    ->modalIcon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn () => $canManage)
                    ->action(function (Account $record): void {
                        app(MentorPermissionService::class)->revokeFromCategory(
                            $record,
                            $this->category
                        );

                        Notification::make()
                            ->title('Mentor Access Revoked')
                            ->body("All permissions for {$record->name} in {$this->formatCategoryLabel($this->category)} have been removed.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private function getCategoryType(string $category): string
    {
        return MentorPermissionService::categoryType($category);
    }

    private function canViewCategory(string $category): bool
    {
        return auth()->user()->can('training.mentors.view.'.$this->getCategoryType($category));
    }

    private function canManageCategory(string $category): bool
    {
        return auth()->user()->can('training.mentors.manage.'.$this->getCategoryType($category));
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories())
            ->first(fn (string $cat) => $this->canViewCategory($cat));
    }

    private function mentorsQuery(string $category): Builder
    {
        return Account::query()
            ->with([
                'mentorTrainingPositions.trainingPosition.position',
            ])
            ->whereHas('mentorTrainingPositions', fn (Builder $q) => $q
                ->whereHas('trainingPosition', fn (Builder $q2) => $q2->where('category', $category))
            );
    }

    private function positionOptions(string $category): array
    {
        return TrainingPosition::where('category', $category)->get()
            ->mapWithKeys(fn (TrainingPosition $p) => [
                $p->id => $p->name ?? $p->position?->callsign ?? collect($p->cts_positions)->first() ?? "Position {$p->id}",
            ])->toArray();
    }

    private function currentPositionIds(Account $account, string $category): array
    {
        return $account->mentorTrainingPositions()
            ->whereHas('trainingPosition', fn ($q) => $q->where('category', $category))
            ->pluck('training_position_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    private function resolvePermissionsArray(Account $record, string $category): array
    {
        return $record->mentorTrainingPositions
            ->filter(fn ($mtp) => $mtp->trainingPosition?->category === $category)
            ->map(fn ($mtp) => $mtp->trainingPosition?->position?->callsign)
            ->filter()
            ->unique()
            ->toArray();
    }

    private function formatCategoryLabel(?string $category): string
    {
        if (! $category) {
            return '';
        }

        return str($category)
            ->replace('Obs', 'OBS')
            ->replace('Air', 'AIR')
            ->replace('Apc', 'APC')
            ->replace('Gmc', 'GMC')
            ->toString();
    }
}
