<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Base64 Image Processor
 * 
 * Mengekstrak gambar base64 dari HTML content dan menyimpannya sebagai file.
 * Mengganti data URL dengan URL file yang proper untuk optimasi performa.
 */
class Base64ImageProcessor
{
    /**
     * Direktori penyimpanan untuk gambar yang diekstrak
     */
    protected string $storageDir = 'uploads/editor';

    /**
     * Disk storage yang digunakan
     */
    protected string $disk = 'public';

    /**
     * Maksimum ukuran file yang diizinkan (5MB)
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * MIME types yang diizinkan
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Process HTML content, extract base64 images and save as files
     */
    public function process(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        // Pattern untuk mencocokkan base64 image dalam tag img
        $pattern = '/<img[^>]+src=["\']data:image\/([a-zA-Z]+);base64,([^"\']+)["\'][^>]*>/i';

        return preg_replace_callback($pattern, function ($matches) {
            return $this->processMatch($matches);
        }, $html);
    }

    /**
     * Process single regex match
     */
    protected function processMatch(array $matches): string
    {
        $fullTag = $matches[0];
        $imageType = strtolower($matches[1]);
        $base64Data = $matches[2];

        // Validasi tipe gambar
        $mimeType = 'image/' . ($imageType === 'jpg' ? 'jpeg' : $imageType);
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            // Hapus gambar yang tidak diizinkan
            return '';
        }

        // Decode base64
        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            return $fullTag; // Gagal decode, kembalikan tag asli
        }

        // Validasi ukuran file
        if (strlen($imageData) > $this->maxFileSize) {
            // Gambar terlalu besar, skip
            return $fullTag;
        }

        // Generate nama file unik
        $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
        $filename = Str::uuid() . '.' . $extension;
        $path = $this->storageDir . '/' . date('Y/m') . '/' . $filename;

        // Simpan ke storage
        try {
            Storage::disk($this->disk)->put($path, $imageData);
        } catch (\Exception $e) {
            // Gagal menyimpan, kembalikan tag asli
            \Log::error('Failed to save base64 image: ' . $e->getMessage());
            return $fullTag;
        }

        // Generate URL baru
        // Untuk public disk, gunakan asset() helper
        $newUrl = asset('storage/' . $path);

        // Ganti src dalam tag img dengan URL baru
        // Pertahankan atribut lain seperti style, class, alt, dll
        $newTag = preg_replace(
            '/src=["\']data:image\/[^"\']+["\']/i',
            'src="' . $newUrl . '"',
            $fullTag
        );

        return $newTag;
    }

    /**
     * Hitung estimasi ukuran base64 dalam HTML
     */
    public static function estimateBase64Size(string $html): int
    {
        $pattern = '/data:image\/[a-zA-Z]+;base64,([^"\']+)/i';
        $totalSize = 0;

        preg_match_all($pattern, $html, $matches);

        foreach ($matches[1] as $base64Data) {
            // Base64 encoding increases size by ~33%
            $totalSize += (int) (strlen($base64Data) * 0.75);
        }

        return $totalSize;
    }

    /**
     * Cek apakah HTML mengandung base64 images
     */
    public static function hasBase64Images(string $html): bool
    {
        return (bool) preg_match('/src=["\']data:image\//i', $html);
    }
}
