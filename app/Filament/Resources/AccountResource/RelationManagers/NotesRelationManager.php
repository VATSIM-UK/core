<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Note;
use App\Models\Mship\Note\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $recordTitleAttribute = 'id';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        $usableTypes = Type::usable()->pluck('name', 'id');

        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->minLength(5),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options($usableTypes)
                    ->default($usableTypes->keys()->first()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type.name'),
                TextColumn::make('content')->wrap(),
                TextColumn::make('writer.name')->label('From'),
                TextColumn::make('created_at')->label('Made')->since()->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data, self $livewire) {
                        return static::createNote($data, $livewire, Type::find($data['type']));
                    }),
            ])->defaultSort('created_at');
    }

    protected static function createNote(array $data, self $livewire, Type $type): Note
    {
        return static::getSubject($livewire)->addNote(
            $type,
            $data['content'],
            auth()->user(),
            static::getAttachment($livewire)
        );
    }

    protected static function getSubject($livewire): Account
    {
        return $livewire->ownerRecord;
    }

    protected static function getAttachment($livewire): ?Model
    {
        return null;
    }
}
