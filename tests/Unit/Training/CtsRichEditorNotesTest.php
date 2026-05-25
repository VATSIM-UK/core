<?php

declare(strict_types=1);

namespace Tests\Unit\Training;

use App\Filament\Training\Concerns\InteractsWithCtsRichEditorNotes;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CtsRichEditorNotesTest extends TestCase
{
    use InteractsWithCtsRichEditorNotes;

    #[Test]
    public function it_preserves_allowed_rich_text_formatting_when_saving(): void
    {
        $html = '<p><strong>Bold</strong> and <em>italic</em> with <u>underline</u></p>';

        $saved = $this->ctsRichContentNotesForCts($html);

        $this->assertStringContainsString('<strong>Bold</strong>', $saved);
        $this->assertStringContainsString('<em>italic</em>', $saved);
        $this->assertStringContainsString('<u>underline</u>', $saved);
    }

    #[Test]
    public function it_preserves_paragraphs_and_line_breaks_when_saving(): void
    {
        $html = '<p>First line</p><p>Second line</p><p>Third with<br>break</p>';

        $saved = $this->ctsRichContentNotesForCts($html);

        $this->assertStringContainsString('<p>First line</p>', $saved);
        $this->assertStringContainsString('<p>Second line</p>', $saved);
        $this->assertStringContainsString('<br>', $saved);
    }

    #[Test]
    public function it_normalizes_empty_paragraphs_when_saving(): void
    {
        $html = '<p>Before</p><p></p><p>After</p>';

        $saved = $this->ctsRichContentNotesForCts($html);

        $this->assertStringContainsString('<p><br></p>', $saved);
    }

    #[Test]
    public function it_strips_disallowed_tags_and_attributes_when_saving(): void
    {
        $html = '<p onclick="alert(1)">Hello<script>alert(1)</script> <a href="https://evil.test">link</a></p>';

        $saved = $this->ctsRichContentNotesForCts($html);

        $this->assertSame('<p>Hello link</p>', $saved);
    }

    #[Test]
    public function it_hydrates_plain_text_notes_as_paragraph_html(): void
    {
        $this->assertSame(
            '<p>Line one</p><p><br></p><p>Line three</p>',
            $this->ctsRichEditorHtmlForHydration("Line one\n\nLine three"),
        );
    }

    #[Test]
    public function it_renders_plain_text_whitespace_for_display(): void
    {
        $display = $this->ctsPlainNotesForHtmlDisplay("Line one\n\nLine  with  spaces");

        $this->assertStringContainsString('white-space:pre-wrap', $display);
        $this->assertStringContainsString("Line one\n\nLine  with  spaces", $display);
    }

    #[Test]
    public function it_renders_sanitized_html_notes_for_display(): void
    {
        $html = '<p><strong>Legacy</strong></p><p></p><p>content</p>';

        $display = $this->ctsPlainNotesForHtmlDisplay($html);

        $this->assertStringContainsString('<strong>Legacy</strong>', $display);
        $this->assertStringContainsString('<p><br></p>', $display);
        $this->assertStringContainsString('white-space:pre-wrap', $display);
    }
}
