<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Helpers\Pages\LogRelationAccess;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Note;
use App\Models\Mship\Note\Type;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotesRelationManager extends RelationManager
{
    use LogRelationAccess;

    protected function getLogActionName(): string
    {
        return 'ViewNotes';
    }

    protected static string $relationship = 'notes';

    protected static ?string $recordTitleAttribute = 'id';

    /**
     * The listeners for the relation manager.
     * Allows actions against a resource to trigger a refresh of the relation manager.
     *
     * Current usages:
     * - Roster restrictions refreshing the notes relation manager when a roster restriction is added or removed.
     */
    protected $listeners = ['refreshNotes' => '$refresh'];

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        $usableTypes = Type::usable()->orderBy('name')->pluck('name', 'id');

        return $schema
            ->components([
                Textarea::make('content')
                    ->required()
                    ->minLength(5),
                Select::make('type')
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
