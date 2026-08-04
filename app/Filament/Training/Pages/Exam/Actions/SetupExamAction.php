<?php

namespace App\Filament\Training\Pages\Exam\Actions;

use App\Services\Training\ExamSetupService;
use App\Services\Training\PendingExamExistsException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class SetupExamAction
{
    public static function make(): Action
    {
        return Action::make('setupExam')
            ->label('Setup Exam')
            ->icon('heroicon-o-plus-circle')
            ->modalHeading('Setup Exam')
            ->modalSubmitActionLabel('Forward for Exam')
            ->visible(fn (): bool => auth()->user()->can('training.exams.setup'))
            ->schema([
                Tabs::make('Setup Type')
                    ->tabs([
                        static::twrToCtrTab(),
                        static::obsTab(),
                        static::pilotTab(),
                    ]),
            ])
            ->action(function (array $data, Action $action): void {
                $service = app(ExamSetupService::class);

                try {
                    match (true) {
                        filled($data['twr_position'] ?? null) && filled($data['twr_student'] ?? null) => $service->setupTwrToCtr(
                            positionId: (int) $data['twr_position'],
                            studentId: (int) $data['twr_student'],
                            forwardedByUserId: Auth::id(),
                        ),
                        filled($data['obs_position'] ?? null) && filled($data['obs_student'] ?? null) => $service->setupObs(
                            positionId: (int) $data['obs_position'],
                            studentId: (int) $data['obs_student'],
                        ),
                        filled($data['pilot_exam_type'] ?? null) && filled($data['pilot_student'] ?? null) => $service->setupPilot(
                            examType: $data['pilot_exam_type'],
                            studentId: (int) $data['pilot_student'],
                            forwardedByUserId: Auth::id(),
                        ),
                        default => throw new PendingExamExistsException(
                            'Please complete one tab (position/exam and student) before submitting.'
                        ),
                    };
                } catch (PendingExamExistsException $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->danger()
                        ->send();

                    $action->halt();
                }
            });
    }

    protected static function twrToCtrTab(): Tab
    {
        return Tab::make('TWR to CTR')
            ->schema([
                Select::make('twr_position')
                    ->label('Position')
                    ->options(fn () => app(ExamSetupService::class)->twrToCtrPositionOptions())
                    ->required(fn (Get $get): bool => filled($get('twr_student')))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('twr_student', null)),

                Select::make('twr_student')
                    ->label('Student')
                    ->options(fn (Get $get): array => app(ExamSetupService::class)->twrToCtrStudentOptions(
                        $get('twr_position') ? (int) $get('twr_position') : null
                    ))
                    ->searchable()
                    ->placeholder('Select a position first')
                    ->disabled(fn (Get $get): bool => ! $get('twr_position'))
                    ->required(fn (Get $get): bool => filled($get('twr_position')))
                    ->live(),
            ]);
    }

    protected static function obsTab(): Tab
    {
        return Tab::make('OBS')
            ->schema([
                Select::make('obs_position')
                    ->label('Position')
                    ->options(fn () => app(ExamSetupService::class)->obsPositionOptions())
                    ->required(fn (Get $get): bool => filled($get('obs_student')))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('obs_student', null)),

                Select::make('obs_student')
                    ->label('Student')
                    ->options(fn (Get $get): array => app(ExamSetupService::class)->obsStudentOptions(
                        $get('obs_position') ? (int) $get('obs_position') : null
                    ))
                    ->searchable()
                    ->placeholder('Select a position first')
                    ->disabled(fn (Get $get): bool => ! $get('obs_position'))
                    ->required(fn (Get $get): bool => filled($get('obs_position')))
                    ->live(),
            ]);
    }

    protected static function pilotTab(): Tab
    {
        return Tab::make('Pilot')
            ->schema([
                Select::make('pilot_exam_type')
                    ->label('Exam')
                    ->options(fn () => app(ExamSetupService::class)->pilotExamTypeOptions())
                    ->required(fn (Get $get): bool => filled($get('pilot_student')))
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('pilot_student', null)),

                Select::make('pilot_student')
                    ->label('Student')
                    ->getSearchResultsUsing(fn (string $search, Get $get): array => app(ExamSetupService::class)
                        ->pilotStudentSearchResults($search, $get('pilot_exam_type'))
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => app(ExamSetupService::class)->pilotStudentLabel($value))
                    ->searchable()
                    ->placeholder('Select an exam type first')
                    ->disabled(fn (Get $get): bool => ! $get('pilot_exam_type'))
                    ->required(fn (Get $get): bool => filled($get('pilot_exam_type')))
                    ->live(),
            ]);
    }
}
