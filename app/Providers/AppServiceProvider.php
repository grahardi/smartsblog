<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Proyek ini pakai Bootstrap 5 (bukan Tailwind), jadi paginasi
        // (yang dipanggil lewat {{ $items->links() }}) juga harus render
        // pakai markup Bootstrap. Tanpa ini, Laravel default memakai
        // markup Tailwind yang class-nya tidak dikenali di sini —
        // hasilnya arrow paginasi tampil polos/besar tanpa styling.
        Paginator::useBootstrapFive();
    }
}
