<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\RichEditor\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class CtsLinkAction
{
    public static function make(): Action
    {
        return Action::make('link')
            ->label(__('filament-forms::components.rich_editor.actions.link.label'))
            ->modalHeading('Insert link')
            ->modalWidth(Width::Large)
            ->fillForm(fn (array $arguments): array => [
                'text' => $arguments['text'] ?? null,
                'url' => $arguments['url'] ?? null,
                'shouldOpenInNewTab' => $arguments['shouldOpenInNewTab'] ?? false,
            ])
            ->schema([
                TextInput::make('text')
                    ->label('Link text')
                    ->placeholder('Add link text'),
                TextInput::make('url')
                    ->label(__('filament-forms::components.rich_editor.actions.link.modal.form.url.label'))
                    ->inputMode('url'),
                Checkbox::make('shouldOpenInNewTab')
                    ->label(__('filament-forms::components.rich_editor.actions.link.modal.form.should_open_in_new_tab.label')),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $isSingleCharacterSelection = ($arguments['editorSelection']['head'] ?? null) === ($arguments['editorSelection']['anchor'] ?? null);
                $url = trim((string) ($data['url'] ?? ''));
                $text = trim((string) ($data['text'] ?? ''));

                if ($url === '') {
                    $component->runCommands(
                        [
                            ...($isSingleCharacterSelection ? [EditorCommand::make(
                                'extendMarkRange',
                                arguments: ['link'],
                            )] : []),
                            EditorCommand::make('unsetLink'),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );

                    return;
                }

                if ($text !== '') {
                    $component->runCommands(
                        [
                            EditorCommand::make(
                                'insertContent',
                                arguments: [[
                                    'type' => 'text',
                                    'text' => $text,
                                    'marks' => [[
                                        'type' => 'link',
                                        'attrs' => [
                                            'href' => $url,
                                            'target' => ($data['shouldOpenInNewTab'] ?? false) ? '_blank' : null,
                                        ],
                                    ]],
                                ]],
                            ),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );

                    return;
                }

                $component->runCommands(
                    [
                        ...($isSingleCharacterSelection ? [EditorCommand::make(
                            'extendMarkRange',
                            arguments: ['link'],
                        )] : []),
                        EditorCommand::make(
                            'setLink',
                            arguments: [[
                                'href' => $url,
                                'target' => ($data['shouldOpenInNewTab'] ?? false) ? '_blank' : null,
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
