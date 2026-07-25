<?php

namespace App\Filament\Training\Pages\StudentOverview;

use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListStudentOverviews extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Student Overviews';

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?int $navigationSort = 15;

    protected ?string $heading = 'Student Overviews';

    protected string $view = 'filament.training.pages.list-student-overviews';

    protected static ?string $slug = '/mentoring/student-overview';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->can('viewAny', Session::class);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $allowedCategories = $user?->getAvailableMentoringCategories() ?? [];

        $categoryGroup = Group::make('trainingPosition.category')
            ->label('Category')
            ->titlePrefixedWithLabel(false)
            ->collapsible()
            ->getTitleFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->trainingPosition?->category)
                    ? $record->trainingPosition->category
                    : 'Uncategorised'
            )
            ->getKeyFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->trainingPosition?->category)
                    ? $record->trainingPosition->category
                    : '__uncategorised__'
            )
            ->scopeQueryByKeyUsing(function (Builder $query, string $key): Builder {
                if ($key === '__uncategorised__') {
                    return $query->whereHas('trainingPosition', fn (Builder $query) => $query->whereNull('category')->orWhere('category', ''));
                }

                return $query->whereHas('trainingPosition', fn (Builder $query) => $query->where('category', $key));
            });

        return $table
            ->queryStringIdentifier('students')
            ->groups([$categoryGroup])
            ->defaultGroup($categoryGroup)
            ->query(
                TrainingPlace::query()
                    ->with([
                        'account',
                        'trainingPosition.position',
                        'waitingListAccount',
                    ])
                    ->whereHas('trainingPosition', function (Builder $query) use ($allowedCategories, $user) {
                        if ($user?->can('viewAll', Session::class)) {
                            return;
                        }

                        if (! empty($allowedCategories)) {
                            $query->whereIn('category', $allowedCategories);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    })
            )
            ->columns([
                TextColumn::make('account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last'])
                    ->url(fn (TrainingPlace $record) => ViewStudentOverview::getUrl(['trainingPlaceId' => $record->id]))
                    ->description(fn (TrainingPlace $record): string => (string) $record->account_id),
                TextColumn::make('trainingPosition.position.callsign')
                    ->label('Primary Position')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Training Start')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn (TrainingPlace $record): string => $record->created_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(fn () => $this->getCategoryOptions($allowedCategories))
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->whereHas('trainingPosition', fn (Builder $q) => $q->where('category', $data['value'])) : $query),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                ViewAction::make()
                    ->label('View Student Overview')
                    ->url(fn (TrainingPlace $record) => ViewStudentOverview::getUrl(['trainingPlaceId' => $record->id])),
            ])
            ->emptyStateHeading('No students found');
    }

    private function getCategoryOptions(array $allowedCategories): array
    {
        $categories = TrainingPosition::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $options = [];
        foreach ($categories as $category) {
            if (empty($allowedCategories) || in_array($category, $allowedCategories)) {
                $options[$category] = $category;
            }
        }

        return $options;
    }
}
