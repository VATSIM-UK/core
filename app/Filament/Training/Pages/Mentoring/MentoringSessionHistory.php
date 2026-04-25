<?php

namespace App\Filament\Training\Pages\Mentoring;

use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MentoringSessionHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.training.pages.mentoring.mentoring-session-history';

    protected static ?int $navigationSort = 20;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $navigationLabel = 'Mentoring History';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.mentoring.access');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $allowedPositions = app(MentorPermissionService::class)->getAllowedCtsPositionCallsigns($user);

        return $table
            ->recordClasses(function (Session $record) {
                if ($record->noShow) {
                    return '!bg-red-300 dark:!bg-red-800';
                }

                if (filled($record->cancelled_datetime)) {
                    $sessionDatetime = Carbon::parse($record->taken_date.' '.$record->taken_from);
                    $cancelledAt = Carbon::parse($record->cancelled_datetime);
                    $diffInMinutes = $cancelledAt->diffInMinutes($sessionDatetime);

                    return match (true) {
                        $diffInMinutes >= 1440 => '!bg-gray-200 dark:!bg-gray-600', // 24+ hrs
                        $diffInMinutes > 300 => '!bg-yellow-100 dark:!bg-yellow-900', // >5h
                        $diffInMinutes > 60 => '!bg-orange-100 dark:!bg-orange-900', // >1h–5h
                        default => '!bg-red-200 dark:!bg-red-900', // <=1h
                    };
                }

                return null;
            })
            ->query(
                Session::query()
                    ->where(function (Builder $query) use ($user, $allowedPositions) {
                        $query->where('mentor_id', $user->id);

                        if (! empty($allowedPositions)) {
                            $query->orWhereIn('position', $allowedPositions);
                        }
                    })
                    ->with(['mentor', 'student'])
            )
            ->columns([
                TextColumn::make('taken_date')
                    ->label('Date')
                    ->date('D j M Y')
                    ->description(function (Session $record) {
                        if ($record->noShow) {
                            return 'No-Show';
                        }

                        if (filled($record->cancelled_datetime)) {
                            return 'Cancelled at '.$record->cancelled_datetime->format('D j M Y H:i');
                        }

                        return $record->taken_from.' - '.$record->taken_to;
                    })
                    ->sortable(),

                TextColumn::make('student.name')
                    ->label('Student')
                    ->description(fn (Session $record) => $record->student?->cid)
                    ->searchable(['student_id']),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('report_link')
                    ->label('Report')
                    ->state(fn (Session $record): bool => filled($record->filed))
                    ->formatStateUsing(fn ($state) => $state ? 'View Report' : 'No Report Filed')
                    ->color(fn ($state) => $state ? 'info' : 'danger')
                    ->url(fn (Session $record) => $record->filed ? "https://cts.vatsim.uk/mentors/report.php?id={$record->id}&view=report" : null) // Once viewMentoringSessionReport exists we can change this
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label('Student')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => TrainingMemberAccountSearch::searchAccountsForSelect($search))
                    ->getOptionLabelUsing(fn ($value): ?string => Account::find($value)?->name." ($value)"),

                SelectFilter::make('position')
                    ->options(fn () => Session::distinct()->pluck('position', 'position')->toArray())
                    ->searchable(),

                Filter::make('taken_date')
                    ->form([
                        DatePicker::make('from')->label('Session From'),
                        DatePicker::make('to')->label('Session To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('taken_date', '>=', $data['from']))
                            ->when($data['to'], fn ($q) => $q->whereDate('taken_date', '<=', $data['to']));
                    }),

                Filter::make('needs_report')
                    ->label('Missing Reports')
                    ->query(fn (Builder $query) => $query->whereNull('filed')),

                Filter::make('only_my_mentoring')
                    ->label('Only sessions I mentored')
                    ->query(fn (Builder $query) => $query->where('mentor_id', $user->id)),

            ])
            ->defaultSort('taken_date', 'desc')
            ->persistFiltersInSession()
            ->poll('60s');
    }
}
