<?php

namespace App\Support\Motivations\Templates;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist sanitiser for the `rich_text` block body and for admin edits to
 * draft letter snapshots. The builder sends a small HTML subset; everything
 * else is dropped. The result is still raw HTML — placeholder substitution
 * runs after this, with escaped values, so injected answers can't close
 * tags.
 */
class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'small',
        'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4',
        'blockquote', 'hr',
        'a', 'span',
    ];

    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title'],
        'span' => ['class', 'style'],
        'p' => ['class', 'style'],
        'h1' => ['style'],
        'h2' => ['style'],
        'h3' => ['style'],
        'h4' => ['style'],
        'li' => ['style'],
    ];

    private const ALLOWED_STYLES = [
        'text-align',
        'color',
        'font-weight',
        'font-style',
        'text-decoration',
    ];

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $wrapped = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>';
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {
            return '';
        }

        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        // Snapshot the children; we may remove some while iterating.
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Replace disallowed element with its contents (unwrap).
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                self::scrubAttributes($child, $tag);
                self::walk($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private static function scrubAttributes(DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];
        $toRemove = [];
        foreach ($el->attributes as $attr) {
            if (! in_array(strtolower($attr->nodeName), $allowed, true)) {
                $toRemove[] = $attr->nodeName;
            }
        }
        foreach ($toRemove as $name) {
            $el->removeAttribute($name);
        }

        if ($tag === 'a' && $el->hasAttribute('href')) {
            $href = trim($el->getAttribute('href'));
            // Allow http/https/mailto/tel and bare anchors (`#…`).
            if (! preg_match('~^(https?:|mailto:|tel:|\#)~i', $href)) {
                $el->removeAttribute('href');
            } else {
                $el->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($el->hasAttribute('style')) {
            $el->setAttribute('style', self::filterStyle($el->getAttribute('style')));
            if ($el->getAttribute('style') === '') {
                $el->removeAttribute('style');
            }
        }
    }

    private static function filterStyle(string $style): string
    {
        $out = [];
        foreach (explode(';', $style) as $decl) {
            if (! str_contains($decl, ':')) {
                continue;
            }
            [$prop, $value] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);
            if (! in_array($prop, self::ALLOWED_STYLES, true)) {
                continue;
            }
            // No urls / expressions in style values.
            if (preg_match('/url\s*\(|expression\s*\(|javascript:/i', $value)) {
                continue;
            }
            $out[] = $prop.': '.$value;
        }

        return implode('; ', $out);
    }
}
