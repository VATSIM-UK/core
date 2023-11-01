<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\BanResource\RelationManagers\NotesRelationManager as BansNotesRelationManager;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;

class NotesRelationManager extends BansNotesRelationManager
{
    // protected static string $relationship = 'notes';

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
        return parent::table($table)
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->using(function (array $data, self $livewire) {
                        return static::createNote($data, $livewire, Type::find($data['type']));
                    }),
            ]);
    }

    protected static function getSubject($livewire): Account
    {
        return $livewire->ownerRecord;
    }
}
