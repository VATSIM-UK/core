<?php

namespace App\Filament\Training\Pages;

use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup as ExamSetupModel;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Repositories\Cts\ExamResultRepository;
use App\Repositories\Cts\SessionRepository;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExamSetup extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.exam-setup';

    protected static ?string $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.exams.setup');
    }

    public ?array $data = [];

    public ?array $dataOBS = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->formOBS->fill();
    }

    protected function getForms(): array
    {
        return [
            'formOBS',
            'form',
        ];
    }

    public function setupExam()
    {
        $validated = $this->validate([
            'data.position' => 'required',
            'data.student' => 'required',
        ]);

        $positionId = $validated['data']['position'];
        $position = Position::find($positionId);

        $ctsMember = Member::where('cid', $validated['data']['student'])->first();

        $setup = ExamSetupModel::create([
            'rts_id' => $position->rts,
            'student_id' => $ctsMember->id,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => $position->examLevel,
            'setup_by' => Auth::user()->id,
            'setup_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'response' => 1,
            'dealt_by' => Auth::user()->id,
            'dealt_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $examBooking = ExamBooking::create([
            'rts_id' => $position->rts,
            'student_id' => $ctsMember->id,
            'student_rating' => $ctsMember->account->qualification_atc->vatsim,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => $position->examLevel,
        ]);

        $setup->update([
            'bookid' => $examBooking->id,
        ]);

        Notification::make()
            ->title('Exam Setup')
            ->success()
            ->body('Exam setup for '.$position->callsign.' has been created.')
            ->send();

        return redirect()->route('filament.training.pages.exam-setup');
    }

    public function setupExamOBS()
    {
        $validated = $this->validate([
            'dataOBS.position_obs' => 'required',
            'dataOBS.student_obs' => 'required',
        ]);

        $positionId = $validated['dataOBS']['position_obs'];
        $position = CtsPosition::find($positionId);

        $ctsMember = Member::where('cid', $this->dataOBS['student_obs'])->first();

        $setup = ExamSetupModel::create([
            'rts_id' => 14, // hard coded for OBS
            'student_id' => $ctsMember->id,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => 'OBS',
        ]);

        $examBooking = ExamBooking::create([
            'rts_id' => 14, // hard coded for OBS
            'student_id' => $ctsMember->id,
            'student_rating' => $ctsMember->account->qualification_atc->vatsim,
            'position_1' => $position->callsign,
            'position_2' => null,
            'exam' => 'OBS',
        ]);

        $setup->update([
            'bookid' => $examBooking->id,
        ]);

        Notification::make()
            ->title('Exam Setup')
            ->success()
            ->body('Exam setup for '.$position->callsign.' has been created.')
            ->send();

        return redirect()->route('filament.training.pages.exam-setup');
    }

    public function formOBS(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Exam Setup - OBS PT3')
                    ->schema([
                        Select::make('position_obs')
                            ->label('Position')
                            ->options(CtsPosition::where('callsign', 'LIKE', 'OBS_%_PT3')->orderBy('callsign')->pluck('callsign', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('student_obs', null);
                            }),

                        Select::make('student_obs')
                            ->label('Student')
                            ->options(function (Get $get): array {
                                // show recent students who have taken a session at the position
                                // PT2 i.e. OBS_XX_PT2. The position selected in the dropdown is
                                // going to be PT3. So we need to get the PT2 position.
                                // XX is the last two characters of the position callsign first part.

                                $positionId = $get('position_obs');
                                if ($positionId == null) {
                                    return [];
                                }

                                $position = CtsPosition::find($positionId);
                                $pt2Position = CtsPosition::where('callsign', 'LIKE', Str::replaceLast('PT3', 'PT2', $position->callsign))->first();

                                $recentPassedStudentIds = (new ExamResultRepository)
                                    ->getRecentPassedExamsOfType('OBS', daysConsideredRecent: 180)
                                    ->pluck('student_id');

                                $pendingStudentIds = (new ExamResultRepository)
                                    ->getPendingExamsOfType('OBS', daysConsideredRecent: 180)
                                    ->pluck('student_id');

                                return $this->generateStudentOptions(
                                    positionCallsign: $pt2Position->callsign,
                                    daysConsideredRecent: 180,
                                    recentPassedStudentIds: $recentPassedStudentIds,
                                    pendingStudentIds: $pendingStudentIds
                                )->toArray();

                            })
                            ->searchable()
                            ->placeholder('Select a position first')
                            ->disabled(fn (Get $get): bool => ! $get('position_obs'))
                            ->required()
                            ->live(),
                    ]),
            ])
            ->statePath('dataOBS');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Exam Setup - TWR to CTR')
                    ->schema([
                        Select::make('position')
                            ->options(Position::where('callsign', 'NOT LIKE', '%ATIS%')->orderBy('callsign')->pluck('callsign', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('student', null);
                            }),

                        Select::make('student')
                            ->options(function (Get $get): array {
                                $positionId = $get('position');
                                if (! $positionId) {
                                    return [];
                                }

                                $position = Position::find($positionId);
                                if (! $position) {
                                    return [];
                                }

                                $recentPassedStudentIds = (new ExamResultRepository)
                                    ->getRecentPassedExamsOfType($position->examLevel, daysConsideredRecent: 180)
                                    ->pluck('student_id');

                                $pendingStudentIds = (new ExamResultRepository)
                                    ->getPendingExamsOfType($position->examLevel, daysConsideredRecent: 180)
                                    ->pluck('student_id');

                                return $this->generateStudentOptions(positionCallsign: $position->callsign, daysConsideredRecent: 180, recentPassedStudentIds: $recentPassedStudentIds, pendingStudentIds: $pendingStudentIds)->toArray();
                            })
                            ->searchable()
                            ->placeholder('Select a position first')
                            ->disabled(fn (Get $get): bool => ! $get('position'))
                            ->required()
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function generateStudentOptions(string $positionCallsign, int $daysConsideredRecent, Collection $recentPassedStudentIds, ?Collection $pendingStudentIds = null): Collection
    {
        $recentCompletedSessions = (new SessionRepository)->getRecentCompletedSessionsForPosition($positionCallsign, daysConsideredRecent: $daysConsideredRecent);

        return $recentCompletedSessions->map(function ($session) {
            return [
                'cts_student_id' => $session->student_id,
                'name' => $session->student->name,
                'cid' => $session->student->cid,
            ];
        })->reject(function ($student) use ($recentPassedStudentIds, $pendingStudentIds) {
            return $recentPassedStudentIds->contains($student['cts_student_id']) ||
                   ($pendingStudentIds && $pendingStudentIds->contains($student['cts_student_id']));
        })->mapWithKeys(function ($student) {
            return [$student['cts_student_id'] => "{$student['name']} ({$student['cid']})"];
        });
    }
}
