<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()
                ->constrained()->nullOnDelete(); // pakai taksonomi kategori yang sama

            $table->string('title');
            $table->string('slug');
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            // pending_review dipakai kalau Anda ingin moderasi tiap post,
            // kalau tidak, cukup draft/published langsung setelah blog disetujui
            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])
                ->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['blog_id', 'slug']);
            $table->index(['status', 'published_at']);
            $table->fullText(['title', 'excerpt', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
