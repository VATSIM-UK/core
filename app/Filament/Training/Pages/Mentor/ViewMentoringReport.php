<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Models\Cts\Session;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ViewMentoringReport extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.training.pages.view-mentoring-report';

    protected static ?string $slug = 'mentoring/report/{sessionId}';

    public int $sessionId;

    public Session $session;

    public function mount(): void
    {
        $this->session = Session::with([
            'student',
            'mentor',
            'reportSheets.field.category',
            'reportSheets.progSheet',
        ])->findOrFail($this->sessionId);

        $user = auth()->user();

        // Students may always view their own session report
        if ($this->session->student?->cid === $user->id) {
            return;
        }

        // Mentors may always view reports for sessions they conducted
        if ($this->session->mentor?->cid === $user->id) {
            return;
        }

        // TODO: add position specific permission logic
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->record($this->session)->components([
            Section::make('')->columnSpanFull()->schema([

                Grid::make(1)->schema([
                    Section::make('Student')->schema([
                        TextEntry::make('student.account.name')->label('Name'),
                        TextEntry::make('student.account.id')->label('CID')->copyable(),
                    ])->columns(2),

                    Section::make('Mentor')->schema([
                        TextEntry::make('mentor.account.name')->label('Name'),
                        TextEntry::make('mentor.account.id')->label('CID')->copyable(),
                    ])->columns(2),
                ])->columnSpan(1),

                Section::make('Session')->schema([
                    TextEntry::make('position')->label('Position'),
                    TextEntry::make('taken_date')->label('Date')->date(),
                    TextEntry::make('taken_from')->label('From'),
                    TextEntry::make('taken_to')->label('To'),
                ])->columns(2)->columnSpan(1)->extraAttributes(['class' => 'h-full']),

            ])->columns(2)->extraAttributes(['class' => 'items-stretch']),
        ]);
    }

    public function reportInfolist(Schema $schema): Schema
    {
        $groupedSheets = $this->session->reportSheets->groupBy(fn ($sheet) => $sheet->field->category->catName);

        $categorySections = [];

        foreach ($groupedSheets as $categoryName => $sheets) {

            $sheetRows = [];
            foreach ($sheets as $index => $sheet) {
                $uniqueKey = $sheet->field_id ?? $index;

                $sheetRows[] = Grid::make(12)
                    ->schema([
                        TextEntry::make("field_name_{$uniqueKey}")
                            ->state($sheet->field->field)
                            ->hiddenLabel()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpan(10),

                        TextEntry::make("field_score_{$uniqueKey}")
                            ->hiddenLabel()
                            ->state($sheet->field_score)
                            ->badge()
                            ->columnSpan(2),

                        TextEntry::make("field_notes_{$uniqueKey}")
                            ->label('Notes')
                            ->state($sheet->notes)
                            ->hiddenLabel()
                            ->html()
                            ->extraAttributes(['style' => 'word-break:break-word'])
                            ->columnSpan(12)
                            ->hidden(blank($sheet->notes)),
                    ])
                    ->extraAttributes(['class' => 'border-b border-gray-200 dark:border-white/10 pb-4 mb-4']);
            }

            $categorySections[] = Section::make($categoryName)
                ->schema($sheetRows)
                ->collapsible();
        }

        return $schema->record($this->session)->components([
            Section::make('Progress Report')
                ->columnSpanFull()
                ->schema(count($categorySections) > 0 ? $categorySections : [TextEntry::make('empty')->label('')->state('No report data found for this session.')]),
        ]);
    }
}
