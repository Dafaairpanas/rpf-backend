<?php

namespace App\Helpers;

class HtmlSanitizer
{
    /**
     * Sanitize HTML content untuk mencegah XSS attacks
     * Mempertahankan tag HTML yang aman untuk CKEditor
     */
    public static function sanitize(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // Daftar tag yang diizinkan (sesuai dengan CKEditor standard)
        $allowedTags = [
            'p',
            'br',
            'strong',
            'em',
            'u',
            's',
            'sub',
            'sup',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'ul',
            'ol',
            'li',
            'a',
            'img',
            'figure',
            'figcaption',
            'table',
            'thead',
            'tbody',
            'tr',
            'th',
            'td',
            'blockquote',
            'pre',
            'code',
            'span',
            'div',
            'hr',
        ];

        // Daftar atribut yang diizinkan per tag
        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'style', 'class'],
            'figure' => ['class', 'style'],
            'figcaption' => ['class', 'style'],
            'table' => ['border', 'cellpadding', 'cellspacing', 'width'],
            'td' => ['colspan', 'rowspan', 'width', 'height'],
            'th' => ['colspan', 'rowspan', 'width', 'height'],
            'span' => ['style', 'class'],
            'div' => ['style', 'class'],
            'p' => ['style', 'class'],
        ];

        // Konfigurasi strip_tags dengan allowed tags
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        $cleaned = strip_tags($html, $allowedTagsString);

        // Bersihkan atribut berbahaya menggunakan regex
        $cleaned = self::sanitizeAttributes($cleaned, $allowedAttributes);

        return $cleaned;
    }

    /**
     * Sanitize attributes untuk mencegah XSS via attributes
     * Menggunakan DOMDocument untuk parsing yang akurat
     */
    private static function sanitizeAttributes(string $html, array $allowedAttributes): string
    {
        // Gunakan DOMDocument untuk parsing HTML
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Suppress errors karena HTML mungkin tidak sempurna
        libxml_use_internal_errors(true);

        // Wrap dalam div untuk handle fragment HTML
        $wrapped = '<div>' . $html . '</div>';
        $dom->loadHTML('<?xml encoding="UTF-8">' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        // Daftar CSS properties yang diizinkan
        $allowedCssProperties = [
            'width',
            'height',
            'max-width',
            'max-height',
            'min-width',
            'min-height',
            'margin',
            'margin-left',
            'margin-right',
            'margin-top',
            'margin-bottom',
            'padding',
            'padding-left',
            'padding-right',
            'padding-top',
            'padding-bottom',
            'text-align',
            'display',
            'float',
            'clear',
            'aspect-ratio',
            'object-fit',
            'object-position',
            'border',
            'border-radius',
            'border-width',
            'border-style',
            'border-color',
            'background-color',
            'color',
            'font-size',
            'font-weight',
            'font-style',
        ];

        // Proses semua elemen
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*');

        foreach ($elements as $element) {
            if (!$element instanceof \DOMElement)
                continue;

            $tagName = strtolower($element->tagName);
            $allowedAttrs = $allowedAttributes[$tagName] ?? [];

            // Kumpulkan atribut yang perlu dihapus
            $attrsToRemove = [];
            foreach ($element->attributes as $attr) {
                $attrName = strtolower($attr->name);

                // Selalu hapus event handlers
                if (strpos($attrName, 'on') === 0) {
                    $attrsToRemove[] = $attr->name;
                    continue;
                }

                // Jika atribut tidak dalam whitelist, hapus
                if (!in_array($attrName, $allowedAttrs)) {
                    $attrsToRemove[] = $attr->name;
                    continue;
                }

                // Untuk atribut style, filter CSS properties
                if ($attrName === 'style') {
                    $sanitizedStyle = self::sanitizeStyle($attr->value, $allowedCssProperties);
                    if (empty($sanitizedStyle)) {
                        $attrsToRemove[] = $attr->name;
                    } else {
                        $element->setAttribute('style', $sanitizedStyle);
                    }
                }

                // Untuk href, cek javascript: dan data: protocol
                if ($attrName === 'href') {
                    $value = strtolower(trim($attr->value));
                    if (strpos($value, 'javascript:') === 0 || strpos($value, 'data:') === 0) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
            }

            // Hapus atribut yang tidak diizinkan
            foreach ($attrsToRemove as $attrName) {
                $element->removeAttribute($attrName);
            }
        }

        // Ambil konten dalam wrapper div
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Sanitize CSS style string
     */
    private static function sanitizeStyle(string $style, array $allowedProperties): string
    {
        $sanitized = [];

        // Parse style string
        $declarations = explode(';', $style);
        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if (empty($declaration))
                continue;

            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2)
                continue;

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            // Cek apakah property diizinkan
            if (in_array($property, $allowedProperties)) {
                // Pastikan value tidak mengandung javascript atau expression
                $valueLower = strtolower($value);
                if (
                    strpos($valueLower, 'javascript') !== false ||
                    strpos($valueLower, 'expression') !== false ||
                    strpos($valueLower, 'url(') !== false
                ) {
                    continue;
                }
                $sanitized[] = $property . ':' . $value;
            }
        }

        return implode('; ', $sanitized);
    }
}
