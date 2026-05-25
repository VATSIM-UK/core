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
     *
     * Plain-text notes from CTS are converted into paragraph HTML for the editor.
     */
    protected function ctsRichEditorHtmlForHydration(mixed $html): mixed
    {
        if ($html === null || $html === '' || (is_string($html) && trim($html) === '')) {
            return '<p></p>';
        }

        if (! is_string($html) || $this->ctsNotesContainHtmlMarkup($html)) {
            return $html;
        }

        $lines = preg_split("/\r\n|\n|\r/", $html);

        if ($lines === false || $lines === []) {
            return '<p></p>';
        }

        $paragraphs = array_map(
            fn (string $line): string => $line === ''
                ? '<p><br></p>'
                : '<p>'.$this->ctsEscapePlainTextLineForEditor($line).'</p>',
            $lines,
        );

        return implode('', $paragraphs);
    }

    /**
     * Persist RichEditor HTML for CTS, keeping basic formatting (bold, italic, lists, etc.).
     *
     * Returns null when the content is empty.
     */
    protected function ctsRichContentNotesForCts(mixed $html): ?string
    {
        if (! is_string($html) || trim($html) === '') {
            return null;
        }

        if (! $this->ctsNotesContainHtmlMarkup($html)) {
            return trim($html) !== '' ? $html : null;
        }

        $sanitized = $this->ctsSanitizeNotesHtml($html);

        if (trim(strip_tags($sanitized)) === '') {
            return null;
        }

        return $this->ctsNormalizeEmptyParagraphs($sanitized);
    }

    /**
     * Render CTS notes for Filament TextEntry::html().
     */
    protected function ctsPlainNotesForHtmlDisplay(mixed $notes): ?string
    {
        if (! is_string($notes) || trim($notes) === '') {
            return null;
        }

        $content = $this->ctsNotesContainHtmlMarkup($notes)
            ? $this->ctsNormalizeEmptyParagraphs($this->ctsSanitizeNotesHtml($notes))
            : e($notes);

        return '<div class="cts-notes-content" style="white-space:pre-wrap;word-break:break-word">'.$content.'</div>';
    }

    protected function ctsAllowedNotesHtmlTags(): string
    {
        return '<p><br><strong><b><em><i><u><s><ul><ol><li>';
    }

    protected function ctsSanitizeNotesHtml(string $html): string
    {
        $withoutScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $withoutEventHandlers = preg_replace('/\s+on\w+\s*=\s*("|\').*?\1/i', '', $withoutScripts) ?? $withoutScripts;

        return strip_tags($withoutEventHandlers, $this->ctsAllowedNotesHtmlTags());
    }

    protected function ctsNormalizeEmptyParagraphs(string $html): string
    {
        $normalised = preg_replace('/<p>\s*<\/p>/i', '<p><br></p>', $html) ?? $html;

        return preg_replace('/<p><br\s*\/?>\s*<\/p>/i', '<p><br></p>', $normalised) ?? $normalised;
    }

    protected function ctsEscapePlainTextLineForEditor(string $line): string
    {
        $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        while (str_contains($escaped, '  ')) {
            $escaped = str_replace('  ', ' &nbsp;', $escaped);
        }

        return $escaped;
    }

    protected function ctsNotesContainHtmlMarkup(string $value): bool
    {
        return (bool) preg_match('/<[a-z][^>]*>/i', $value);
    }
}
