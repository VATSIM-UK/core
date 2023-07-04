<?php

namespace App\Filament\Resources\BanResource\RelationManagers;

use App\Models\Mship\Account\Note;
use App\Models\Mship\Note\Type;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->minLength(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.name'),
                Tables\Columns\TextColumn::make('content')->wrap(),
                Tables\Columns\TextColumn::make('account.name')->label('From'),
                Tables\Columns\TextColumn::make('created_at')->label('Made')->since()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disableCreateAnother()
                    ->using(function (array $data, self $livewire) {
                        return static::createNote($data, $livewire);
                    }),
            ])->defaultSort('created_at');
    }

    private static function createNote(array $data, self $livewire): Note
    {
        return $livewire->ownerRecord->account->addNote(
            Type::isShortCode('discipline')->first(),
            $data['content'],
            auth()->user(),
            $livewire->ownerRecord
        );
    }
}
