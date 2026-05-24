<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\Cts\Session;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class PendingMentoringReportsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $category = '';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending Reports')
            ->description('Accepted sessions that have taken place but do not yet have a filed mentoring report.')
            ->query(
                (new SessionRepository)->getPendingReportSessionsForPositionsQuery(
                    $this->getVisibleCtsPositions()
                )
            )
            ->defaultSort('taken_date', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->getStateUsing(fn (Session $record) => $record->student->name)
                    ->description(fn (Session $record) => $record->student->cid),

                TextColumn::make('mentor_name')
                    ->label('Mentor')
                    ->getStateUsing(fn (Session $record) => $record->mentor->name)
                    ->description(fn (Session $record) => $record->mentor->cid),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('taken_date')
                    ->label('Date & Time')
                    ->getStateUsing(function (Session $record) {
                        $date = Carbon::parse($record->taken_date)->format('d/m/Y');
                        $time = Carbon::parse($record->taken_from)->format('H:i');

                        return trim("{$date} {$time}");
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderBy('taken_date', $direction)
                        ->orderBy('taken_from', $direction)
                    ),
            ])
            ->emptyStateHeading('No pending mentoring reports in this training group');
    }

    /**
     * @return array<int, string>
     */
    private function getVisibleCtsPositions(): array
    {
        $service = app(MentorPermissionService::class);

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            return $service->getAllCtsCallsignsForCategories($this->getVisibleCategories());
        }

        if (empty($this->category)) {
            return [];
        }

        return $service->getAllCtsCallsignsForCategory($this->category);
    }

    /**
     * @return array<int, string>
     */
    private function getVisibleCategories(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories())
            ->filter(function (string $category) use ($user) {
                if ($user->can('training.mentoring.view.*')) {
                    return true;
                }

                return $user->can('training.mentors.view.'.MentorPermissionService::categoryType($category));
            })
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.training.pending-mentoring-reports-table');
    }
}
