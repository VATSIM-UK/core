<?php

namespace App\Filament\Training\Support;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;

trait TheoryExamViewTrait
{
    protected function buildQuestionPlaceholders($record): array
    {

        if (! $record) {
            return [];
        }

        $answers = $record->answers()->with('question')->get();

        return $answers->map(function ($answer, $index) use ($record) {
            $number = $index + 1;
            $question = $answer->question;
            $questionText = $question->question ?? 'Unknown question';

            $givenAnswer = $record->getOptionText($question, $answer->answer_given);
            $correctAnswer = $record->getOptionText($question, $question->answer ?? null);

            $isCorrect = $answer->answer_given == ($question->answer ?? null);

            return Fieldset::make("Question {$number}")
                ->schema([
                    TextEntry::make("question_{$number}_text")
                        ->label('Question')
                        ->getStateUsing($questionText),
                    TextEntry::make("question_{$number}_answer")
                        ->label('Member Answer')
                        ->getStateUsing($givenAnswer),
                    TextEntry::make("question_{$number}_correct")
                        ->label('Correct Answer')
                        ->getStateUsing($correctAnswer),
                    TextEntry::make("question_{$number}_status")
                        ->label('Status')
                        ->badge()
                        ->color($isCorrect ? 'success' : 'danger')
                        ->getStateUsing($isCorrect ? 'CORRECT' : 'INCORRECT'),
                ])
                ->columns(4);
        })->all();
    }

    protected function theoryExamInfoList(): array
    {
        return [
            Fieldset::make('Exam Information')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('cid')->label('CID')->getStateUsing(fn ($record) => $record->student_id),

                    TextEntry::make('Name')->label('Name')->getStateUsing(fn ($record) => $record->student?->account?->name ?? 'Unknown'),

                    TextEntry::make('Exam')->label('Exam')->getStateUsing(fn ($record) => $record->exam_label),

                    TextEntry::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                        'Passed' => 'success',
                        'Failed' => 'danger',
                    })->label('Result'),
                ]),

            Fieldset::make('Details')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('started')->label('Started')->getStateUsing(fn ($record) => Carbon::parse($record->started)->isoFormat('lll')),
                    TextEntry::make('submitted_time')->label('Submitted Time')->getStateUsing(fn ($record) => $record->submitted_time ? Carbon::parse($record->submitted_time)->isoFormat('lll') : 'N/A'), // Some exams will not be submitted if they run out of time etc
                    TextEntry::make('score')->label('Score')->getStateUsing(fn ($record) => "{$record->correct} / {$record->questions} (Passmark: {$record->passmark})"),
                    TextEntry::make('time_mins')->label('Time Limit')->getStateUsing(fn ($record) => "{$record->time_mins} Mins"),
                ]),

            Section::make('Questions')->collapsible()->collapsed()->columnSpanFull()->schema(fn ($record) => $this->buildQuestionPlaceholders($record)),
        ];
    }
}
