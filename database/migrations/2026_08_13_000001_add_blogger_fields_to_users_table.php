<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role dasar sistem: admin bisa kelola semua, editor bisa kelola artikel resmi,
            // author adalah user biasa yang sudah disetujui punya blog, user adalah default
            $table->enum('role', ['admin', 'editor', 'author', 'user'])
                ->default('user')
                ->after('email');

            // status pengajuan menjadi blogger/author
            $table->enum('blogger_status', ['none', 'pending', 'approved', 'rejected'])
                ->default('none')
                ->after('role');

            $table->text('bio')->nullable()->after('blogger_status');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('slug')->nullable()->unique()->after('avatar');
            $table->timestamp('blogger_requested_at')->nullable()->after('slug');
            $table->timestamp('blogger_approved_at')->nullable()->after('blogger_requested_at');
            $table->foreignId('blogger_approved_by')->nullable()
                ->after('blogger_approved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blogger_approved_by');
            $table->dropColumn([
                'role', 'blogger_status', 'bio', 'avatar', 'slug',
                'blogger_requested_at', 'blogger_approved_at',
            ]);
        });
    }
};
