<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_category', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_id', 'category_id']);
        });

        // pindahkan data category_id lama ke pivot supaya tidak hilang
        if (Schema::hasColumn('articles', 'category_id')) {
            DB::table('articles')->whereNotNull('category_id')->orderBy('id')
                ->chunk(200, function ($articles) {
                    $rows = $articles->map(fn ($a) => [
                        'article_id' => $a->id,
                        'category_id' => $a->category_id,
                    ])->all();
                    if ($rows) {
                        DB::table('article_category')->insertOrIgnore($rows);
                    }
                });

            Schema::table('articles', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('article_category')->orderBy('article_id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('articles')->where('id', $row->article_id)
                    ->whereNull('category_id')
                    ->update(['category_id' => $row->category_id]);
            }
        });

        Schema::dropIfExists('article_category');
    }
};
