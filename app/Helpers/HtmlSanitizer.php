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
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'table' => ['border', 'cellpadding', 'cellspacing', 'width'],
            'td' => ['colspan', 'rowspan', 'width', 'height'],
            'th' => ['colspan', 'rowspan', 'width', 'height'],
            'span' => ['style'],
            'div' => ['style'],
            'p' => ['style'],
        ];

        // Konfigurasi strip_tags dengan allowed tags
        $allowedTagsString = '<'.implode('><', $allowedTags).'>';
        $cleaned = strip_tags($html, $allowedTagsString);

        // Bersihkan atribut berbahaya menggunakan regex
        $cleaned = self::sanitizeAttributes($cleaned, $allowedAttributes);

        return $cleaned;
    }

    /**
     * Sanitize attributes untuk mencegah XSS via attributes
     */
    private static function sanitizeAttributes(string $html, array $allowedAttributes): string
    {
        // Remove event handlers (onclick, onerror, dll)
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove javascript: protocol
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);

        // Remove data: protocol kecuali untuk images
        $html = preg_replace('/href\s*=\s*["\']data:[^"\']*["\']/i', '', $html);

        return $html;
    }
}
