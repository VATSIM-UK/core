<?php

namespace App\Filament\Training\Pages;

use App\Enums\EmailType;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected string $view = 'filament.training.pages.email-settings';

    protected static ?string $navigationLabel = 'Email Settings';

    protected static ?string $title = 'Training Email Settings';

    protected static ?string $slug = 'email-settings';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $account = auth()->user();
        $settings = $account->emailSettings()->pluck('enabled', 'email_type')->toArray();

        $formData = [];
        foreach (EmailType::cases() as $type) {
            $formData[$type->value] = $settings[$type->value] ?? true;
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema($this->buildFormSchema())
            ->statePath('data');
    }

    protected function buildFormSchema(): array
    {
        $sections = [];

        foreach (EmailType::categories() as $category) {
            $types = EmailType::forCategory($category);
            $fields = [];

            foreach ($types as $type) {
                $fields[] = Checkbox::make($type->value)
                    ->label($type->label())
                    ->helperText($type->description())
                    ->default(true);
            }

            $sections[] = Section::make($category)
                ->description("Manage your {$category} email notifications")
                ->schema([
                    Grid::make(1)
                        ->schema($fields),
                ])
                ->collapsible();
        }

        return $sections;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $account = auth()->user();

        $account->setEmailPreferences($data);

        Notification::make()
            ->title('Email settings saved')
            ->success()
            ->send();
    }
}
