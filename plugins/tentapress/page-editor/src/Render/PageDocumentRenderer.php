<?php

declare(strict_types=1);

namespace TentaPress\PageEditor\Render;

use Illuminate\Support\Str;

final class PageDocumentRenderer
{
    public function render(array $document): string
    {
        if (isset($document['blocks']) && is_array($document['blocks'])) {
            return $this->renderBlocks($document['blocks']);
        }

        if (($document['type'] ?? null) === 'page') {
            return $this->renderLegacyNode($document);
        }

        return '';
    }

    /**
     * @param  array<int,mixed>  $blocks
     */
    private function renderBlocks(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $html .= match ($type) {
                'paragraph' => $this->wrap('p', $this->renderInline((string) ($data['text'] ?? ''))),
                'header' => $this->renderHeader($data),
                'list' => $this->renderList($data),
                'quote' => $this->renderQuote($data),
                'image' => $this->renderImage($data),
                'embed' => $this->renderEmbed($data),
                'checklist' => $this->renderChecklist($data),
                'code' => $this->renderCode($data),
                'callout' => $this->renderCallout($data),
                'delimiter' => '<hr>',
                default => '',
            };
        }

        return $html;
    }

    private function renderHeader(array $data): string
    {
        $level = (int) ($data['level'] ?? 2);
        if ($level < 1 || $level > 3) {
            $level = 2;
        }

        return $this->wrap('h'.$level, $this->renderInline((string) ($data['text'] ?? '')));
    }

    private function renderList(array $data): string
    {
        $style = (string) ($data['style'] ?? 'unordered');
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $tag = $style === 'ordered' ? 'ol' : 'ul';

        $html = '';
        foreach ($items as $item) {
            $html .= $this->renderListItem($item);
        }

        return $this->wrap($tag, $html);
    }

    /**
     * @param  mixed  $item
     */
    private function renderListItem(mixed $item): string
    {
        if (is_string($item)) {
            return '<li>'.$this->renderInline($item).'</li>';
        }

        if (! is_array($item)) {
            return '';
        }

        $content = $this->renderInline((string) ($item['content'] ?? ''));
        $children = is_array($item['items'] ?? null) ? $item['items'] : [];

        $nested = '';
        if ($children !== []) {
            $nestedItems = '';
            foreach ($children as $child) {
                $nestedItems .= $this->renderListItem($child);
            }
            if ($nestedItems !== '') {
                $nested = '<ul>'.$nestedItems.'</ul>';
            }
        }

        return '<li>'.$content.$nested.'</li>';
    }

    private function renderQuote(array $data): string
    {
        $text = $this->renderInline((string) ($data['text'] ?? ''));
        $caption = trim((string) ($data['caption'] ?? ''));

        if ($caption !== '') {
            $text .= '<cite>'.$this->renderInline($caption).'</cite>';
        }

        return $this->wrap('blockquote', $text);
    }

    private function renderImage(array $data): string
    {
        $reference = [
            'media_id' => $data['media_id'] ?? null,
            'url' => $data['url'] ?? null,
            'alt' => $data['alt'] ?? null,
        ];

        $resolved = $this->resolveImageReference($reference, 'large');
        if ($resolved === null) {
            return '';
        }

        $caption = trim((string) ($data['caption'] ?? ''));
        $attributes = [
            'src' => e($resolved['src']),
            'alt' => e($resolved['alt']),
            'loading' => 'lazy',
            'decoding' => 'async',
        ];

        if (is_string($resolved['srcset']) && $resolved['srcset'] !== '') {
            $attributes['srcset'] = e($resolved['srcset']);
        }

        if (is_string($resolved['sizes']) && $resolved['sizes'] !== '') {
            $attributes['sizes'] = e($resolved['sizes']);
        }

        if (is_int($resolved['width']) && $resolved['width'] > 0) {
            $attributes['width'] = (string) $resolved['width'];
        }

        if (is_int($resolved['height']) && $resolved['height'] > 0) {
            $attributes['height'] = (string) $resolved['height'];
        }

        $img = '<img';
        foreach ($attributes as $key => $value) {
            $img .= ' '.$key.'="'.$value.'"';
        }
        $img .= ' />';
        if ($caption === '') {
            return $this->wrap('figure', $img);
        }

        $figcaption = '<figcaption>'.$this->renderInline($caption).'</figcaption>';

        return $this->wrap('figure', $img.$figcaption);
    }

    private function renderEmbed(array $data): string
    {
        $service = trim((string) ($data['service'] ?? ''));
        $embed = trim((string) ($data['embed'] ?? ''));
        if ($embed === '') {
            return '';
        }

        if (! in_array($service, ['youtube', 'vimeo'], true)) {
            return '';
        }

        if ($this->isUnsafeUrl($embed) || ! $this->isAllowedEmbedUrl($service, $embed)) {
            return '';
        }

        $iframe = '<iframe src="'.e($embed).'" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" style="position:absolute;inset:0;width:100%;height:100%;border:0;"></iframe>';
        $wrapper = '<div class="tp-embed" style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;background:#0f172a;">'.$iframe.'</div>';

        $caption = trim((string) ($data['caption'] ?? ''));
        if ($caption !== '') {
            $wrapper .= '<div class="tp-embed-caption">'.$this->renderInline($caption).'</div>';
        }

        return '<figure>'.$wrapper.'</figure>';
    }

    private function isAllowedEmbedUrl(string $service, string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if ($service === 'youtube') {
            $allowedHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'];
            if (! in_array($host, $allowedHosts, true)) {
                return false;
            }

            return $host === 'youtu.be' || str_contains($path, '/embed/');
        }

        if ($service === 'vimeo') {
            $allowedHosts = ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
            if (! in_array($host, $allowedHosts, true)) {
                return false;
            }

            return $host !== 'player.vimeo.com' || str_contains($path, '/video/');
        }

        return false;
    }

    private function renderChecklist(array $data): string
    {
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        if ($items === []) {
            return '';
        }

        $html = '';
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $checked = (bool) ($item['checked'] ?? false);
            $html .= '<li class="tp-checklist-item"><input type="checkbox" '.($checked ? 'checked ' : '').'disabled /> <span>'.$this->renderInline($text).'</span></li>';
        }

        if ($html === '') {
            return '';
        }

        return '<ul class="tp-checklist">'.$html.'</ul>';
    }

    private function renderCode(array $data): string
    {
        $code = (string) ($data['code'] ?? '');
        if (trim($code) === '') {
            return '';
        }

        $language = trim((string) ($data['language'] ?? ''));
        $label = $language !== '' ? '<div class="tp-code-language">'.e($language).'</div>' : '';

        return '<pre class="tp-code-block">'.$label.'<code>'.e($code).'</code></pre>';
    }

    private function renderCallout(array $data): string
    {
        $text = trim((string) ($data['text'] ?? ''));
        if ($text === '') {
            return '';
        }
        $type = trim((string) ($data['type'] ?? 'info'));
        if (! in_array($type, ['info', 'warning', 'success'], true)) {
            $type = 'info';
        }

        return '<div class="tp-callout tp-callout-'.$type.'">'.$this->renderInline($text).'</div>';
    }

    private function wrap(string $tag, string $content): string
    {
        if ($content === '') {
            return '';
        }

        return '<'.$tag.'>'.$content.'</'.$tag.'>';
    }

    private function renderInline(string $text): string
    {
        $allowed = '<b><strong><i><em><u><s><code><a>';
        $clean = strip_tags($text, $allowed);

        $clean = preg_replace_callback(
            '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i',
            function (array $matches): string {
                $href = trim($matches[1] ?? '');
                if ($href === '' || $this->isUnsafeUrl($href)) {
                    return '<a>';
                }

                return '<a href="'.e($href).'" rel="noopener noreferrer">';
            },
            $clean
        ) ?? $clean;

        return $clean;
    }

    private function isUnsafeUrl(string $url): bool
    {
        $lower = Str::lower($url);

        return str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:');
    }

    /**
     * @param  array{media_id:mixed,url:mixed,alt:mixed}  $reference
     * @return array{
     *   id:int|null,
     *   src:string,
     *   alt:string,
     *   srcset:string|null,
     *   sizes:string|null,
     *   width:int|null,
     *   height:int|null
     * }|null
     */
    private function resolveImageReference(array $reference, string $variant): ?array
    {
        if (app()->bound('tp.media.reference_resolver')) {
            $resolver = app('tp.media.reference_resolver');
            if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
                /** @var array{id:int|null,src:string,alt:string,srcset:string|null,sizes:string|null,width:int|null,height:int|null}|null $resolved */
                $resolved = $resolver->resolveImage($reference, ['variant' => $variant]);
                if ($resolved !== null && ! $this->isUnsafeUrl($resolved['src'])) {
                    return $resolved;
                }
            }
        }

        $url = is_string($reference['url']) ? trim($reference['url']) : '';
        if ($url === '' || $this->isUnsafeUrl($url)) {
            return null;
        }

        $alt = is_string($reference['alt']) ? trim($reference['alt']) : '';

        return [
            'id' => null,
            'src' => $url,
            'alt' => $alt,
            'srcset' => null,
            'sizes' => null,
            'width' => null,
            'height' => null,
        ];
    }

    private function renderLegacyNode(array $node): string
    {
        $type = (string) ($node['type'] ?? '');

        return match ($type) {
            'page' => $this->renderLegacyChildren($node),
            'paragraph' => '<p>'.$this->renderLegacyChildren($node).'</p>',
            'heading' => $this->renderLegacyHeading($node),
            'blockquote' => '<blockquote>'.$this->renderLegacyChildren($node).'</blockquote>',
            'text' => e((string) ($node['text'] ?? '')),
            default => '',
        };
    }

    private function renderLegacyChildren(array $node): string
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

            $html .= $this->renderLegacyNode($child);
        }

        return $html;
    }

    private function renderLegacyHeading(array $node): string
    {
        $level = (int) (($node['attrs']['level'] ?? 2));
        if ($level < 1 || $level > 3) {
            $level = 2;
        }

        return '<h'.$level.'>'.$this->renderLegacyChildren($node).'</h'.$level.'>';
    }
}
