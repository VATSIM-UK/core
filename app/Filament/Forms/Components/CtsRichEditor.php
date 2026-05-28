<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\RichEditor\Actions\CtsLinkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Support\Icons\Heroicon;

class CtsRichEditor extends RichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->json()
            ->textColors(static::ctsTextColors())
            ->customTextColors(false)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link', 'textColor'],
                ['h2', 'h3'],
                ['alignStart', 'alignCenter', 'alignEnd'],
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                ['table'],
                ['undo', 'redo'],
            ])
            ->floatingToolbars(fn () => [
                ...$this->getDefaultFloatingToolbars(),
                'link' => ['link', 'unlink'],
            ])
            ->tools([
                RichEditorTool::make('link')
                    ->label(__('filament-forms::components.rich_editor.tools.link'))
                    ->action(arguments: <<<'JS'
                        {
                            url: $getEditor().getAttributes('link')?.href,
                            shouldOpenInNewTab: $getEditor().getAttributes('link')?.target === '_blank',
                            text: $getEditor().state.doc.textBetween(
                                $getEditor().state.selection.from,
                                $getEditor().state.selection.to,
                                ' '
                            ),
                        }
                        JS)
                    ->icon(Heroicon::Link)
                    ->iconAlias('forms:components.rich-editor.toolbar.link')
                    ->extraAttributes([
                        'x-mousetrap.global.mod-k' => '$el.click()',
                    ]),
                RichEditorTool::make('unlink')
                    ->label('Remove link')
                    ->jsHandler('$getEditor()?.chain().focus().unsetLink().run()')
                    ->activeKey('link')
                    ->disabledWhenNotActive()
                    ->icon('heroicon-o-link-slash'),
            ]);
    }

    /**
     * @return array<string, TextColor>
     */
    public static function ctsTextColors(): array
    {
        return [
            'improvement' => TextColor::make('Improvement', '#16a34a', '#4ade80'),
            'regression' => TextColor::make('Regression', '#dc2626', '#f87171'),
            'homework' => TextColor::make('Homework', '#7c3aed', '#a78bfa'),
        ];
    }

    /**
     * @return array<\Filament\Actions\Action>
     */
    public function getDefaultActions(): array
    {
        $actions = parent::getDefaultActions();
        $replaced = false;

        // Replacing old link action with new custom link action
        foreach ($actions as $index => $action) {
            if ($action->getName() !== 'link') {
                continue;
            }

            $actions[$index] = CtsLinkAction::make();
            $replaced = true;
        }

        if (! $replaced) {
            $actions[] = CtsLinkAction::make();
        }

        return $actions;
    }
}
