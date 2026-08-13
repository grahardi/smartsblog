<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pakai raw SQL (bukan Schema::change) supaya tidak butuh package doctrine/dbal
        DB::statement('ALTER TABLE articles MODIFY featured_image TEXT NULL');

        if (Schema::hasTable('blog_posts')) {
            DB::statement('ALTER TABLE blog_posts MODIFY featured_image TEXT NULL');
        }
        if (Schema::hasColumn('categories', 'cover_image')) {
            DB::statement('ALTER TABLE categories MODIFY cover_image TEXT NULL');
        }
        if (Schema::hasTable('blogs')) {
            DB::statement('ALTER TABLE blogs MODIFY logo TEXT NULL');
            DB::statement('ALTER TABLE blogs MODIFY cover_image TEXT NULL');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE articles MODIFY featured_image VARCHAR(255) NULL');

        if (Schema::hasTable('blog_posts')) {
            DB::statement('ALTER TABLE blog_posts MODIFY featured_image VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('categories', 'cover_image')) {
            DB::statement('ALTER TABLE categories MODIFY cover_image VARCHAR(255) NULL');
        }
        if (Schema::hasTable('blogs')) {
            DB::statement('ALTER TABLE blogs MODIFY logo VARCHAR(255) NULL');
            DB::statement('ALTER TABLE blogs MODIFY cover_image VARCHAR(255) NULL');
        }
    }
};
