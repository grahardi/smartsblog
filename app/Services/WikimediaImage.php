<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mengambil URL gambar thumbnail dari Wikipedia REST API (bukan teks artikel).
 * Ini legal untuk hotlink karena Wikimedia menyediakan endpoint publik resmi
 * untuk keperluan ini, dan gambar-gambar di Wikipedia umumnya berlisensi
 * terbuka (CC-BY-SA / public domain). Kita hanya mengambil gambar, bukan
 * menyalin teks artikelnya.
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
                        // minta ukuran lebih besar kalau URL-nya format thumb Wikimedia
                        return preg_replace('/\/\d+px-/', "/{$width}px-", $thumbnail);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("WikimediaImage: gagal ambil gambar untuk \"{$title}\" ({$lang}): ".$e->getMessage());
            }
        }

        return null;
    }
}
