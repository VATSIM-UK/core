<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamBooking;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Exams extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.exams';

    public function table(Table $table): Table
    {
        $table->query(ExamBooking::query()->with('student', 'examiners'));

        $table->columns([
            TextColumn::make('student.cid')->label('CID'),
            TextColumn::make('student.name')->label('Name'),
            TextColumn::make('exam')->label('Level'),
            TextColumn::make('position_1')->label('Position'),
            TextColumn::make('start_date')->label('Date'),
        ]);

        $table->actions([
            Action::make('Conduct')
                ->url(fn (ExamBooking $exam): string => ConductExam::getUrl(['id' => $exam->id])),
        ]);

        return $table;
    }
}
