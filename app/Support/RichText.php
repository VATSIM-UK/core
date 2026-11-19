<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

final class RichText
{
    /**
     * Sanitise rich-text HTML (e.g. CTS notes) so it is safe to store and render unescaped.
     *
     * Uses the sanitiser Filament configures for its rich editor output (exposed as
     * `Str::sanitizeHtml()`): formatting, lists and tables are kept, along with the
     * attributes the editor emits for colours and alignment, while scripts, event
     * handlers and `javascript:`/`data:` URLs are removed.
     */
    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return Str::sanitizeHtml($html);
    }
}
