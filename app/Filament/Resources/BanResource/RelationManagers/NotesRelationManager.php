<?php

namespace App\Filament\Resources\BanResource\RelationManagers;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Note;
use App\Models\Mship\Note\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->minLength(5),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.name'),
                Tables\Columns\TextColumn::make('content')->wrap(),
                Tables\Columns\TextColumn::make('writer.name')->label('From'),
                Tables\Columns\TextColumn::make('created_at')->label('Made')->since()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disableCreateAnother()
                    ->using(function (array $data, self $livewire) {
                        return static::createNote($data, $livewire, Type::isShortCode('discipline')->first());
                    }),
            ])->defaultSort('created_at');
    }

    protected static function createNote(array $data, self $livewire, Type $type): Note
    {
        return static::getSubject($livewire)->addNote(
            $type,
            $data['content'],
            auth()->user(),
            $livewire->ownerRecord
        );
    }

    protected static function getSubject($livewire): Account
    {
        return $livewire->ownerRecord->account;
    }
}
