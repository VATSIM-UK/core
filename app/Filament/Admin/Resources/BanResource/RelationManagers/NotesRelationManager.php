<?php

namespace App\Filament\Admin\Resources\BanResource\RelationManagers;

use App\Filament\Admin\Resources\AccountResource\RelationManagers\NotesRelationManager as AccountNotesRelationManager;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotesRelationManager extends AccountNotesRelationManager
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->required()
                    ->minLength(5),
            ]);
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->using(function (array $data, self $livewire) {
                        return static::createNote($data, $livewire, Type::isShortCode('discipline')->first());
                    }),
            ]);
    }

    protected static function getSubject($livewire): Account
    {
        return $livewire->ownerRecord->account;
    }

    protected static function getAttachment($livewire): ?Model
    {
        return $livewire->ownerRecord;
    }
}
