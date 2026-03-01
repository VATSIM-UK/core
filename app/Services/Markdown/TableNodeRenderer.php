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
        $tableNode = $this->asTableNode($node);

        return new HtmlElement('table', ['class' => 'markdown-table'], $childRenderer->renderNodes($tableNode->children()));
    }

    private function asTableNode(Node $node): Table
    {
        if (! $node instanceof Table) {
            throw new \InvalidArgumentException('Expected Table node');
        }

        return $node;
    }
}
