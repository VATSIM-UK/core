<?php

namespace App\Filament\Training\Pages\Mentoring;

use App\Models\Cts\Session;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

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

        return $table
            ->query(
                Session::query()
                    ->where('student_id', $user->id)
                    ->with(['mentor', 'student'])
            )
            ->columns([
                TextColumn::make('student.cid')
                    ->label('CID')
                    ->searchable(),

                TextColumn::make('student.account.name')
                    ->label('Student Name'),

                TextColumn::make('position')
                    ->label('Position'),

                TextColumn::make('taken_date')
                    ->label('Session date')
                    ->dateTime(),

                TextColumn::make('filed')
                    ->label('Report filed')
                    ->placeholder('Report not filed'),
            ])
            ->defaultSort('taken_date', 'desc');
    }
}
