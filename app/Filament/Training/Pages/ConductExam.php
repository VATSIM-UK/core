<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamCriteria;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class ConductExam extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.conduct-exam';

    protected static ?string $slug = 'exams/conduct/{id?}';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save')->action(fn () => $this->save())
                ->label('Save')
                ->icon('heroicon-o-check'),
        ];
    }

    public function form(Form $form): Form
    {
        $criteria = ExamCriteria::byType('TWR')->get();

        $criteriaComponents = $criteria->map(
            function (ExamCriteria $criteria) {
                return Fieldset::make("form.{$criteria->id}")
                    ->label($criteria->criteria)
                    ->schema([
                        RichEditor::make("form.{$criteria->id}.comments")
                            ->label('Comments')
                            ->default('')
                            ->required()
                            ->columnSpan(9)
                            ->disableToolbarButtons(['attachFiles', 'blockquote']),
                        Select::make("form.{$criteria->id}.grade")
                            ->label('Grade')
                            ->options([
                                'P' => 'Fully Competent',
                                'M' => 'Mostly Competent',
                                'R' => 'Partially Competent',
                                'N' => 'Not Assessed',
                                'F' => 'Fail',
                            ])
                            ->default('N')
                            ->columnSpan(3)
                            ->required(),
                    ])->columns(12);
            }
        );

        return $form
            ->schema([
                ...$criteriaComponents,
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        dd($this->form->getState());
    }
}
