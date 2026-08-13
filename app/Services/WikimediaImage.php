<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Mengambil gambar dari Wikimedia lalu MENGUNDUHNYA ke storage lokal
 * (storage/app/public/articles), bukan sekadar hotlink. Ini legal karena
 * kita hanya mengambil gambar (yang berlisensi terbuka di Wikimedia),
 * bukan menyalin teks artikelnya.
 */
class WikimediaImage
{
    public static function fetchThumbnail(string $title, int $width = 800): ?string
    {
        foreach (['id', 'en'] as $lang) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders(['User-Agent' => 'SmartsID/1.0 (contact: admin@smarts.id)'])
                    ->get("https://{$lang}.wikipedia.org/api/rest_v1/page/summary/".rawurlencode($title));

                if ($response->ok()) {
                    $thumbnail = $response->json('thumbnail.source')
                        ?? $response->json('originalimage.source');

                    if ($thumbnail) {
                        return preg_replace('/\/\d+px-/', "/{$width}px-", $thumbnail);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("WikimediaImage: gagal ambil gambar untuk \"{$title}\" ({$lang}): ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Cari gambar di Wikimedia untuk judul topik tertentu, unduh, simpan
     * ke storage lokal (disk "public", folder articles/), lalu kembalikan
     * path relatif untuk disimpan di kolom featured_image (misal
     * "storage/articles/hipotesis-riemann.jpg").
     */
    public static function fetchAndStoreLocal(string $title, string $slugHint, int $width = 800): ?string
    {
        $remoteUrl = self::fetchThumbnail($title, $width);
        if (! $remoteUrl) {
            return null;
        }

        return self::downloadToLocal($remoteUrl, $slugHint);
    }

    public static function downloadToLocal(string $remoteUrl, string $slugHint): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'SmartsID/1.0 (contact: admin@smarts.id)'])
                ->get($remoteUrl);

            if (! $response->ok()) {
                return null;
            }

            $extension = self::guessExtension($remoteUrl, $response->header('Content-Type'));
            $filename = Str::slug($slugHint).'-'.Str::random(6).'.'.$extension;

            Storage::disk('public')->put("articles/{$filename}", $response->body());

            return "storage/articles/{$filename}";
        } catch (\Throwable $e) {
            Log::warning("WikimediaImage: gagal unduh gambar dari {$remoteUrl}: ".$e->getMessage());

            return null;
        }
    }

    public static function guessExtension(string $url, ?string $contentType): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        return match (true) {
            str_contains((string) $contentType, 'png') => 'png',
            str_contains((string) $contentType, 'webp') => 'webp',
            str_contains((string) $contentType, 'gif') => 'gif',
            str_contains((string) $contentType, 'svg') => 'svg',
            default => 'jpg',
        };
    }
}
