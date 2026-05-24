<?php

declare(strict_types=1);

namespace App\Filament\Training\Concerns;

use Filament\Forms\Components\RichEditor;

trait InteractsWithCtsRichEditorNotes
{
    /**
     * Notes editor styled like filed mentoring reports (borderless, full width).
     */
    protected function mentoringReportNotesEditor(RichEditor $editor): RichEditor
    {
        return $editor
            ->hiddenLabel()
            ->disableToolbarButtons(['attachFiles', 'blockquote'])
            ->extraAttributes(['class' => 'mentoring-report-notes-editor'])
            ->extraFieldWrapperAttributes(['class' => 'mentoring-report-notes-field']);
    }

    /**
     * Filament v4 / Tiptap PHP parse HTML via DOMDocument::loadHTML; empty or whitespace-only strings
     * yield no <body> node, so DOMParser::getDocumentBody() returns null and crashes.
     */
    protected function ctsRichEditorHtmlForHydration(mixed $html): mixed
    {
        if ($html === null || $html === '' || (is_string($html) && trim($html) === '')) {
            return '<p></p>';
        }

        return $html;
    }

    /**
     * CTS stores mentoring/exam notes as plain text; Filament RichEditor state is HTML.
     *
     * Returns null when the content is empty.
     */
    protected function ctsRichContentNotesForCts(mixed $html): ?string
    {
        if (! is_string($html) || trim($html) === '') {
            return null;
        }

        $withNewlines = preg_replace('/<\/p>\s*<p>/i', "\n", $html);
        $text = html_entity_decode(strip_tags($withNewlines), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = trim($text);

        return $text !== '' ? $text : null;
    }
}
