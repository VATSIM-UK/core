<?php

use League\CommonMark\Extension\Table\TableExtension;

return [
    'extensions' => [
        TableExtension::class,
    ],
    'allow_unsafe_links' => false,
    'commonmark' => [
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisks' => true,
        'use_underscores' => true,
    ],
    'html_input' => 'strip',
    'max_nesting_level' => 100,
    'renderer_class' => \App\Services\Markdown\CustomMarkdownRenderer::class,
];
