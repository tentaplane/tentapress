<?php

declare(strict_types=1);

namespace TentaPress\PageEditor\Render;

use Illuminate\Support\Str;

final class PageDocumentRenderer
{
    public function render(array $document): string
    {
        return $this->renderNode($document);
    }

    private function renderNode(array $node): string
    {
        $type = (string) ($node['type'] ?? '');

        return match ($type) {
            'page' => $this->renderChildren($node),
            'paragraph' => '<p>'.$this->renderChildren($node).'</p>',
            'heading' => $this->renderHeading($node),
            'blockquote' => '<blockquote>'.$this->renderChildren($node).'</blockquote>',
            'text' => $this->renderText($node),
            default => '',
        };
    }

    private function renderChildren(array $node): string
    {
        $children = $node['content'] ?? [];
        if (! is_array($children)) {
            return '';
        }

        $html = '';
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            $html .= $this->renderNode($child);
        }

        return $html;
    }

    private function renderHeading(array $node): string
    {
        $level = (int) (($node['attrs']['level'] ?? 2));
        if ($level < 1 || $level > 3) {
            $level = 2;
        }

        return '<h'.$level.'>'.$this->renderChildren($node).'</h'.$level.'>';
    }

    private function renderText(array $node): string
    {
        $text = e((string) ($node['text'] ?? ''));
        $marks = $node['marks'] ?? [];

        if (! is_array($marks)) {
            return $text;
        }

        foreach ($marks as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $text = $this->applyMark($mark, $text);
        }

        return $text;
    }

    private function applyMark(array $mark, string $html): string
    {
        $type = (string) ($mark['type'] ?? '');

        return match ($type) {
            'bold' => '<strong>'.$html.'</strong>',
            'italic' => '<em>'.$html.'</em>',
            'link' => $this->renderLink($mark, $html),
            default => $html,
        };
    }

    private function renderLink(array $mark, string $html): string
    {
        $href = (string) (($mark['attrs']['href'] ?? '') ?: '');
        $href = trim($href);

        if ($href === '' || $this->isUnsafeUrl($href)) {
            return $html;
        }

        $target = (string) (($mark['attrs']['target'] ?? '') ?: '');
        $target = $target === '_blank' ? '_blank' : '';
        $rel = $target === '_blank' ? ' rel="noopener noreferrer"' : '';
        $targetAttr = $target !== '' ? ' target="'.$target.'"' : '';

        return '<a href="'.e($href).'"'.$targetAttr.$rel.'>'.$html.'</a>';
    }

    private function isUnsafeUrl(string $url): bool
    {
        $lower = Str::lower($url);

        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
            return true;
        }

        return false;
    }
}
