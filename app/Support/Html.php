<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/** Small allowlist sanitizer for admin rich-text (product descriptions). */
class Html
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'a', 'h2', 'h3', 'blockquote'];

    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $html = strip_tags($html, self::TAGS);

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();

        foreach ((new DOMXPath($doc))->query('//*') as $el) {
            /** @var DOMElement $el */
            foreach (iterator_to_array($el->attributes) as $attr) {
                $keep = $el->tagName === 'a' && $attr->name === 'href' && preg_match('~^(https?://|mailto:|tel:|/)~i', trim($attr->value));
                if (! $keep) {
                    $el->removeAttribute($attr->name);
                }
            }
            if ($el->tagName === 'a') {
                $el->setAttribute('rel', 'nofollow noopener');
                $el->setAttribute('target', '_blank');
            }
        }

        $root = $doc->getElementsByTagName('div')->item(0);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }
}
