<?php

namespace App\Services\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\Table\Table;
use Spatie\LaravelMarkdown\MarkdownRenderer;

class CustomMarkdownRenderer extends MarkdownRenderer
{
    protected function configureCommonMarkEnvironment(EnvironmentBuilderInterface $environment): void
    {
        parent::configureCommonMarkEnvironment($environment);

        $environment->addRenderer(Table::class, new TableNodeRenderer);
    }
}
