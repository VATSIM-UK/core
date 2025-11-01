<?php

namespace App\Services\Markdown;

use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class TableNodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Table) {
            throw new \InvalidArgumentException('Expected Table node');
        }

        $attrs = ['class' => 'markdown-table'];

        return new HtmlElement('table', $attrs, $childRenderer->renderNodes($node->children()));
    }
}
