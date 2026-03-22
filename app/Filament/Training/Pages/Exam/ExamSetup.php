<?php

namespace App\Filament\Training\Pages\Exam;

use App\Enums\PilotExamType;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Repositories\Cts\ExamResultRepository;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\ExamForwardingService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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

    public ?array $dataPilot = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->formOBS->fill();
        $this->formPilot->fill();
    }

    protected function getForms(): array
    {
        return [
            'formOBS',
            'form',
            'formPilot',
        ];
    }

    public function setupExam()
    {
        $validated = $this->validate([
            'data.position' => 'required',
            'data.student' => 'required',
        ]);

        $trainingPosition = TrainingPosition::where('position_id', $validated['data']['position'])->firstOrFail();
        $ctsMember = Member::where('id', $validated['data']['student'])->first();

        $service = new ExamForwardingService;
        $service->forwardForExam($ctsMember, $trainingPosition, Auth::user()->id);
        $service->notifySuccess($trainingPosition->exam_callsign ?? $trainingPosition->position->callsign);

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

        $trainingPosition = TrainingPosition::whereJsonContains('cts_positions', $position->callsign)->firstOrFail();
        $ctsMember = Member::where('id', $this->dataOBS['student_obs'])->first();

        $service = new ExamForwardingService;
        $service->forwardForObsExam($ctsMember, $trainingPosition);
        $service->notifySuccess($trainingPosition->exam_callsign ?? $trainingPosition->position->callsign);

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

    public function setupExamPilot()
    {
        $validated = $this->validate([
            'dataPilot.exam_type' => 'required',
            'dataPilot.student_pilot' => 'required',
        ]);

        $ctsMember = Member::where('id', $validated['dataPilot']['student_pilot'])->first();

        $service = new ExamForwardingService;
        $service->forwardForPilotExam($ctsMember, $validated['dataPilot']['exam_type'], Auth::user()->id);
        $service->notifySuccess($validated['dataPilot']['exam_type']);

        return redirect()->route('filament.training.pages.exam-setup');
    }

    public function formPilot(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Exam Setup - Pilot')
                    ->schema([
                        Select::make('exam_type')
                            ->label('Exam')
                            ->options(collect(PilotExamType::cases())
                                ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
                                ->toArray()
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('student_pilot', null)),

                        Select::make('student_pilot')
                            ->label('Student')
                            ->getSearchResultsUsing(function (string $search, Get $get): array {
                                $examType = $get('exam_type');
                                if (! $examType) {
                                    return [];
                                }

                                $prerequisiteRating = PilotExamType::from($examType)->prerequisiteRating();

                                $eligibleCids = Account::whereHas('qualifications', fn ($q) => $q
                                    ->where('type', 'pilot')
                                    ->where('code', $prerequisiteRating)
                                )->pluck('id');

                                return Member::whereIn('cid', $eligibleCids)
                                    ->where(fn ($query) => $query
                                        ->where('name', 'LIKE', "%{$search}%")
                                        ->orWhere('cid', 'LIKE', "%{$search}%")
                                    )
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn ($member) => [$member->id => "{$member->name} ({$member->cid})"])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Member::find($value)?->name)
                            ->searchable()
                            ->placeholder('Select an exam type first')
                            ->disabled(fn (Get $get): bool => ! $get('exam_type'))
                            ->required()
                            ->live(),
                    ]),
            ])
            ->statePath('dataPilot');
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
