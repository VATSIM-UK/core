<?php

declare(strict_types=1);

namespace App\Filament\Training\Concerns;

use App\Filament\Forms\Components\TrainingRichEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

trait InteractsWithCtsRichEditorNotes
{
    /**
     * Notes editor styled like filed mentoring reports (borderless, full width).
     */
    protected function mentoringReportNotesEditor(TrainingRichEditor $editor): TrainingRichEditor
    {
        return $editor
            ->hiddenLabel()
            ->disableToolbarButtons(['attachFiles', 'blockquote'])
            ->extraAttributes(['class' => 'mentoring-report-notes-editor'])
            ->extraFieldWrapperAttributes(['class' => 'mentoring-report-notes-field']);
    }

    /**
     * Sync RichEditor state on blur only to avoid Livewire re-renders resetting TipTap cursor position mid-edit.
     *
     * @param  (callable(mixed): void)|null  $afterStateUpdated
     */
    protected function conductSessionRichEditor(RichEditor $editor, ?callable $afterStateUpdated = null): RichEditor
    {
        $editor = $editor
            ->live()
            ->extraFieldWrapperAttributes(['wire:ignore' => true]);

        if ($afterStateUpdated !== null) {
            return $editor->afterStateUpdated($afterStateUpdated);
        }

        return $editor->afterStateUpdated(fn () => $this->markDirty());
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
        if (is_array($html)) {
            $html = RichContentRenderer::make($html)
                ->textColors(TrainingRichEditor::ctsTextColors())
                ->toUnsafeHtml();
        }

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
        if (is_array($notes)) {
            $notes = RichContentRenderer::make($notes)
                ->textColors(TrainingRichEditor::ctsTextColors())
                ->toUnsafeHtml();
        }

        if (! is_string($notes) || trim($notes) === '') {
            return null;
        }

        $content = $this->ctsNotesContainHtmlMarkup($notes)
            ? $this->ctsStyleCodeBlocks($this->ctsNormalizeEmptyParagraphs($this->ctsSanitizeNotesHtml($notes)))
            : e($notes);

        return '<div class="cts-notes-content" style="white-space:pre-wrap;word-break:break-word">'.$content.'</div>';
    }

    /**
     * Bare <pre><code> from the editor carries no class or wrapper Filament's typography styles can key off, so inline the box styling directly onto
     * the tags
     */
    protected function ctsStyleCodeBlocks(string $html): string
    {
        $html = preg_replace_callback('/<pre(\s[^>]*)?>/i', function (array $m): string {
            if (isset($m[1]) && stripos($m[1], 'style=') !== false) {
                return $m[0];
            }

            $style = 'background:rgba(127,127,127,.12);border:1px solid rgba(127,127,127,.25);'
                .'border-radius:.375rem;padding:.75rem 1rem;overflow-x:auto;white-space:pre-wrap;'
                .'word-break:break-word;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;'
                .'font-size:.875em;line-height:1.5';

            return '<pre'.($m[1] ?? '').' style="'.$style.'">';
        }, $html) ?? $html;

        return preg_replace_callback('/<code(\s[^>]*)?>/i', function (array $m): string {
            if (isset($m[1]) && stripos($m[1], 'style=') !== false) {
                return $m[0];
            }

            return '<code'.($m[1] ?? '').' style="background:none;padding:0">';
        }, $html) ?? $html;
    }

    protected function ctsAllowedNotesHtmlTags(): string
    {
        return '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><ul><ol><li><a><span><pre><code>';
    }

    protected function ctsSanitizeNotesHtml(string $html): string
    {
        $withoutScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $withoutEventHandlers = preg_replace('/\s+on\w+\s*=\s*("|\').*?\1/i', '', $withoutScripts) ?? $withoutScripts;
        $sanitized = strip_tags($withoutEventHandlers, $this->ctsAllowedNotesHtmlTags());

        return $this->ctsStripUnsafeLinks($sanitized);
    }

    protected function ctsStripUnsafeLinks(string $html): string
    {
        return preg_replace('/\s+href\s*=\s*("|\')\s*(?:javascript|data|vbscript):.*?\1/i', '', $html) ?? $html;
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
