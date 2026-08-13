<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\WikimediaImage;
use Illuminate\Console\Command;

class LocalizeArticleImages extends Command
{
    protected $signature = 'articles:localize-images';

    protected $description = 'Unduh semua featured_image artikel yang masih berupa URL eksternal (mis. Wikimedia) ke storage lokal, lalu update record.';

    public function handle(): int
    {
        $articles = Article::whereNotNull('featured_image')
            ->where('featured_image', 'like', 'http%')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Tidak ada artikel dengan gambar eksternal. Semua sudah lokal.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$articles->count()} artikel dengan gambar eksternal. Mengunduh...");
        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($articles as $article) {
            $localPath = WikimediaImage::downloadToLocal($article->featured_image, $article->title);

            if ($localPath) {
                $article->update(['featured_image' => $localPath]);
                $success++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. Berhasil: {$success}, gagal: {$failed}.");

        if ($failed > 0) {
            $this->warn('Artikel yang gagal diunduh gambarnya tetap memakai URL lama (tidak hilang), coba jalankan ulang command ini nanti.');
        }

        return self::SUCCESS;
    }
}
