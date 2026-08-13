# smarts.id — CMS Portal Artikel + Multi-Blog

Struktur ini untuk ditambahkan ke project Laravel 13 (PHP 8.3, MySQL) yang sudah Anda buat dengan
`composer create-project laravel/laravel smarts-id`.

## Cara pasang

1. Salin folder `database/migrations`, `app/Models`, `app/Http/Controllers`,
   `app/Http/Middleware`, dan isi `routes/web.php` ke project Laravel Anda
   (timpa `app/Models/User.php` dan `routes/web.php` bawaan Laravel).
2. Tambahkan alias middleware sesuai `bootstrap-app-snippet.php` ke `bootstrap/app.php`.
3. Jalankan:
   ```
   php artisan migrate
   ```
4. Buat user admin pertama secara manual (tinker atau seeder):
   ```php
   php artisan tinker
   User::create([
       'name' => 'Admin Smarts',
       'email' => 'admin@smarts.id',
       'password' => bcrypt('password'),
       'role' => 'admin',
   ]);
   ```

## Alur fitur

**Kategori & Subkategori**
- Satu tabel `categories` dengan `parent_id` self-referencing.
- `parent_id` null = kategori utama (mis. "Kecerdasan", "Pendidikan", "Pengetahuan").
- `parent_id` terisi = subkategori (mis. "Kecerdasan Buatan" di bawah "Kecerdasan").
- Bisa diperluas jadi lebih dari 2 level kalau perlu, karena relasinya rekursif.

**Artikel resmi (`articles`)**
- Ditulis oleh admin/editor, terhubung ke satu kategori, mendukung status
  draft/published/scheduled/archived, gambar unggulan, tag, dan full-text search.

**Multi-blog pengguna**
- User biasa (`role = user`) mengajukan diri lewat `POST /blogger/request` →
  `blogger_status` jadi `pending`.
- Admin melihat daftar pengajuan di `/admin/approvals`, approve/reject.
- Saat di-approve: `role` user berubah jadi `author`, dan sistem otomatis
  membuatkan satu record `blogs` (satu user = satu blog, sesuai kebutuhan
  "multiblog pengguna" — banyak blog dari banyak pengguna berbeda).
- Setelah disetujui, user bisa kelola profil blog dan CRUD `blog_posts` di
  `/dashboard/blog/posts`, dengan status draft/pending_review/published.
- Setiap blog tampil publik di `smarts.id/blog/{slug}`.

## Yang belum termasuk (siap saya buatkan lanjutannya kalau perlu)
- Blade views (index/create/edit untuk admin & blog dashboard) — saat ini
  hanya struktur backend (migration, model, controller, route).
- Form Request classes terpisah (validasi saat ini inline di controller).
- Notifikasi email saat approval/reject (sudah ada `// TODO` di controller).
- Upload gambar (kolom `featured_image`/`logo` masih string path, belum
  terhubung ke `Storage::putFile`).
- Sistem komentar pada artikel/post.
