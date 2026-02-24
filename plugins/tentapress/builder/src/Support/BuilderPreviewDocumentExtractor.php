<?php

declare(strict_types=1);

namespace TentaPress\Builder\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Log;

final class BuilderPreviewDocumentExtractor
{
    /**
     * @return array{
     *     lang:string,
     *     body_class:string,
     *     styles:array<int,array{href:string,media:string}>,
     *     inline_styles:array<int,string>,
     *     body_html:string
     * }
     */
    public function extract(string $html): array
    {
        if (trim($html) === '') {
            Log::warning('builder.preview.extractor.empty_html');

            return $this->emptyDocument();
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $loaded = @$dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);

        if ($loaded !== true) {
            Log::warning('builder.preview.extractor.load_failed');

            return $this->emptyDocument();
        }

        $this->stripScripts($dom);
        $this->stripDangerousAttributes($dom);
        $xpath = new DOMXPath($dom);

        $lang = trim((string) $xpath->evaluate('string(/html/@lang)'));
        $lang = $lang !== '' ? $lang : 'en';

        $body = $dom->getElementsByTagName('body')->item(0);
        $bodyClass = $body instanceof DOMElement ? trim((string) $body->getAttribute('class')) : '';
        $bodyHtml = $body instanceof DOMElement ? $this->innerHtml($body) : '';

        $styles = [];
        /** @var DOMElement $link */
        foreach ($xpath->query('//head/link[@href]') ?? [] as $link) {
            $rel = strtolower(trim((string) $link->getAttribute('rel')));
            if (! str_contains(' '.$rel.' ', ' stylesheet ')) {
                continue;
            }

            $href = trim((string) $link->getAttribute('href'));
            if ($href === '' || ! $this->isSafeStylesheetHref($href)) {
                continue;
            }

            $styles[] = [
                'href' => $href,
                'media' => trim((string) $link->getAttribute('media')) ?: 'all',
            ];
        }

        $inlineStyles = [];
        /** @var DOMElement $style */
        foreach ($xpath->query('//head/style') ?? [] as $style) {
            $css = trim($style->textContent ?? '');
            if ($css !== '') {
                $inlineStyles[] = $css;
            }
        }

        return [
            'lang' => $lang,
            'body_class' => $bodyClass,
            'styles' => array_values($styles),
            'inline_styles' => array_values($inlineStyles),
            'body_html' => $bodyHtml,
        ];
    }

    private function stripScripts(DOMDocument $dom): void
    {
        while (true) {
            $script = $dom->getElementsByTagName('script')->item(0);
            if (! $script instanceof DOMNode || ! $script->parentNode instanceof DOMNode) {
                break;
            }

            $script->parentNode->removeChild($script);
        }
    }

    private function stripDangerousAttributes(DOMDocument $dom): void
    {
        $allNodes = $dom->getElementsByTagName('*');

        foreach ($allNodes as $node) {
            if (! $node instanceof DOMElement || ! $node->hasAttributes()) {
                continue;
            }

            $toRemove = [];
            foreach ($node->attributes as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim((string) $attribute->value);

                if (str_starts_with($name, 'on')) {
                    $toRemove[] = $attribute->name;
                    continue;
                }

                if (in_array($name, ['href', 'src', 'xlink:href', 'formaction'], true) && $this->isUnsafeUri($value)) {
                    $toRemove[] = $attribute->name;
                }
            }

            foreach ($toRemove as $name) {
                $node->removeAttribute($name);
            }
        }
    }

    private function innerHtml(DOMElement $element): string
    {
        $doc = $element->ownerDocument;
        if (! $doc instanceof DOMDocument) {
            return '';
        }

        $html = '';
        foreach ($element->childNodes as $node) {
            $fragment = $doc->saveHTML($node);
            if (is_string($fragment)) {
                $html .= $fragment;
            }
        }

        return $html;
    }

    private function isSafeStylesheetHref(string $href): bool
    {
        $normalized = strtolower(trim($href));

        return ! str_starts_with($normalized, 'javascript:') && ! str_starts_with($normalized, 'data:');
    }

    private function isUnsafeUri(string $uri): bool
    {
        $normalized = strtolower(trim($uri));

        return str_starts_with($normalized, 'javascript:') || str_starts_with($normalized, 'data:text/html');
    }

    /**
     * @return array{
     *     lang:string,
     *     body_class:string,
     *     styles:array<int,array{href:string,media:string}>,
     *     inline_styles:array<int,string>,
     *     body_html:string
     * }
     */
    private function emptyDocument(): array
    {
        return [
            'lang' => 'en',
            'body_class' => '',
            'styles' => [],
            'inline_styles' => [],
            'body_html' => '',
        ];
    }
}
