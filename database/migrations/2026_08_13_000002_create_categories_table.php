<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // parent_id null = kategori utama, parent_id terisi = subkategori
            $table->foreignId('parent_id')->nullable()
                ->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();      // nama icon (mis. class icon FontAwesome)
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);

            // meta SEO per kategori
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->timestamps();

            $table->index(['parent_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
