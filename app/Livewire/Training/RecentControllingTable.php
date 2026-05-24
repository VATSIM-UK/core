<?php

namespace App\Livewire\Training;

use App\Models\Cts\Session as CtsSession;
use App\Models\NetworkData\Atc;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\CarbonInterval;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class RecentControllingTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    private ?Collection $completedMentoringSessions = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Controlling during training')
            ->queryStringIdentifier('recent-controlling')
            ->query(
                Atc::query()->where('account_id', $this->trainingPlace->account_id)
                    ->where('connected_at', '>=', $this->trainingPlace->created_at)
                    ->isUk()
            )
            ->defaultSort('connected_at', 'desc')
            ->columns([
                TextColumn::make('connected_at')->label('Date')->date('d/m/Y H:i:s'),
                TextColumn::make('callsign')
                    ->label('Callsign')
                    ->icon(function ($record) {
                        if ($record->hasOverlappingCompletedMentoringSession($this->getCompletedMentoringSessions())) {
                            return 'heroicon-o-academic-cap';
                        }

                        return null;
                    })
                    ->iconColor('success')
                    ->tooltip(function ($record) {
                        if ($record->hasOverlappingCompletedMentoringSession($this->getCompletedMentoringSessions())) {
                            return 'This session was part of a mentoring session';
                        }

                        return null;
                    }),
                TextColumn::make('duration')->label('Duration')->getStateUsing(function ($record) {
                    $minutes = $record->minutes_online ?? 0;
                    $interval = CarbonInterval::minutes($minutes)->cascade();

                    $parts = [];
                    if ($interval->hours > 0) {
                        $parts[] = "{$interval->hours} hours";
                    }
                    if ($interval->minutes > 0) {
                        $parts[] = "{$interval->minutes} minutes";
                    }

                    return $parts ? implode(' ', $parts) : '0 minutes';
                }),
            ])
            ->emptyStateHeading('No records found');
    }

    public function render()
    {
        return view('livewire.training.recent-controlling-table');
    }

    private function getCompletedMentoringSessions(): Collection
    {
        if ($this->completedMentoringSessions === null) {
            $member = $this->trainingPlace->account->member;

            if (! $member) {
                $this->completedMentoringSessions = collect();
            } else {
                $this->completedMentoringSessions = CtsSession::where('student_id', $member->id)
                    ->where('session_done', 1)
                    ->where('noShow', 0)
                    ->whereNull('cancelled_datetime')
                    ->whereNotNull('taken_date')
                    ->whereNotNull('taken_from')
                    ->whereNotNull('taken_to')
                    ->get();
            }
        }

        return $this->completedMentoringSessions;
    }
}
