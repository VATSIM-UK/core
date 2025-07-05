<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryResultResource\Pages;
use App\Models\Cts\TheoryResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TheoryResultResource extends Resource
{
    protected static ?string $model = TheoryResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Mentoring';

    // Permissions

    public static function canViewAny(): bool
    {
        return auth()->user()->can('theory-exams.access');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('theory-exams.results.view.*')) {
            return $query;
        }

        $allowedCategories = collect(['atc', 'pilot'])
            ->filter(fn ($category) => auth()->user()->can("theory-exams.results.view.$category"));

        if ($allowedCategories->isEmpty()) {
            return $query->whereRaw('1=0');
        }

        return $query->where(function ($q) use ($allowedCategories) {
            foreach ($allowedCategories as $category) {
                match ($category) {
                    'atc' => $q->orWhere(function ($sub) {
                        $sub->where('exam', 'like', 'S%')
                            ->orWhere('exam', 'like', 'C%');
                    }),
                    'pilot' => $q->orWhere('exam', 'like', 'P%')
                };
            }
        });
    }
    // Permissions End

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('member_information')
                    ->label('Member Information')
                    ->schema([
                        Forms\Components\Placeholder::make('student_id')
                            ->label('CID')
                            ->content(fn ($record) => $record->student_id),

                        Forms\Components\Placeholder::make('name')
                            ->label('Name')
                            ->content(fn ($record) => $record->name),
                    ]),

                Forms\Components\Fieldset::make('exam_information')
                    ->label('Exam Information')
                    ->schema([
                        Forms\Components\Placeholder::make('exam')
                            ->label('Exam')
                            ->content(fn ($record) => $record->exam),

                        Forms\Components\Placeholder::make('correct_questions')
                            ->label('Correct / Total Questions')
                            ->content(fn ($record) => "{$record->correct} out of {$record->questions}"),

                        Forms\Components\Placeholder::make('time_mins')
                            ->label('Time (mins)')
                            ->content(fn ($record) => $record->time_mins),

                        Forms\Components\Placeholder::make('passmark')
                            ->label('Passmark')
                            ->content(fn ($record) => $record->passmark),

                        Forms\Components\Placeholder::make('started')
                            ->label('Started')
                            ->content(fn ($record) => $record->started),

                        Forms\Components\Placeholder::make('expires')
                            ->label('Expires')
                            ->content(fn ($record) => $record->expires),

                        Forms\Components\Placeholder::make('submitted_time')
                            ->label('Submitted')
                            ->content(fn ($record) => $record->submitted_time),

                        Forms\Components\Placeholder::make('pass_status')
                            ->label('Result')
                            ->content(fn ($record) => $record->correct >= $record->passmark ? 'Passed' : 'Failed'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam')->label('Exam')->searchable(),
                TextColumn::make('student_id')->label('CID')->searchable(),
                TextColumn::make('name')->label('Name'), // ->searchable(),
                TextColumn::make('pass_status')
                    ->label('Result')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->correct >= $record->passmark ? 'Passed' : 'Failed')
                    ->color(fn ($record) => match ($record->correct >= $record->passmark) {
                        true => 'success',
                        false => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('exam')
                    ->label('Exam')
                    ->options(fn () => self::$model::query()->distinct()->pluck('exam', 'exam')->toArray()),

                SelectFilter::make('pass_status')
                    ->label('Result')
                    ->options([
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                    ])
                    ->query(function ($query, $data) {
                        return match ($data['value'] ?? null) {
                            'passed' => $query->theoryPassed(),
                            'failed' => $query->theoryFailed(),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheoryResults::route('/'),
            'create' => Pages\CreateTheoryResult::route('/create'),
            'edit' => Pages\EditTheoryResult::route('/{record}/edit'),
        ];
    }
}
