<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Exam;

use App\Models\Mship\Account;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ManageExaminers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.training.pages.manage-examiners';

    protected static ?string $navigationGroup = 'Exams';

    protected static ?string $navigationLabel = 'Manage Examiners';

    protected static ?string $title = 'Manage Examiners';

    /** @var 'obs'|'twr'|'app'|'ctr' */
    #[Url]
    public string $role = 'obs';

    /**
     * Map URL segment to Spatie role name (must match RolesAndPermissionsSeeder).
     *
     * @var array<string, string>
     */
    private const EXAMINER_ROLE_MAP = [
        'obs' => 'ATC Examiner (OBS)',
        'twr' => 'ATC Examiner (TWR)',
        'app' => 'ATC Examiner (APP)',
        'ctr' => 'ATC Examiner (CTR)',
    ];

    /**
     * @var list<string>
     */
    private const EXAMINER_ROLE_KEYS = ['obs', 'twr', 'app', 'ctr'];

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.examiners.view.atc');
    }

    public function mount(): void
    {
        if (! array_key_exists($this->role, self::EXAMINER_ROLE_MAP)) {
            $this->role = 'obs';
        }
    }

    protected function getHeaderActions(): array
    {
        return collect(self::EXAMINER_ROLE_KEYS)
            ->map(function (string $key): Action {
                return Action::make($key)
                    ->label(strtoupper($key))
                    ->url(static::getUrl(['role' => $key]))
                    ->color($this->role === $key ? 'primary' : 'gray');
            })
            ->all();
    }

    public function updatedRole(): void
    {
        if (! array_key_exists($this->role, self::EXAMINER_ROLE_MAP)) {
            $this->role = 'obs';
        }

        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $roleName = self::EXAMINER_ROLE_MAP[$this->role] ?? self::EXAMINER_ROLE_MAP['obs'];

        return $table
            ->query($this->examinersQuery($roleName))
            ->columns([
                TextColumn::make('id')->label('CID')->searchable(),
                TextColumn::make('name')->searchable(),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    private function examinersQuery(string $roleName): Builder
    {
        return Account::query()->role($roleName);
    }
}
