<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal publik (smarts.id)
|--------------------------------------------------------------------------
*/
Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/kategori/{slug}', [PortalController::class, 'category'])->name('category.show');
Route::get('/artikel/{slug}', [PortalController::class, 'article'])->name('article.show');

// halaman publik blog pengguna: smarts.id/blog/{slug}
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

/*
|--------------------------------------------------------------------------
| Area user login: pengajuan blogger & kelola blog sendiri
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/blogger/request', [BlogController::class, 'requestAccess'])->name('blogger.request');

    Route::middleware('blogger.approved')->prefix('dashboard/blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [BlogController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profil', [BlogController::class, 'updateProfile'])->name('profile.update');

        Route::resource('posts', BlogPostController::class)->except(['show']);
    });
});

/*
|--------------------------------------------------------------------------
| Area admin: kelola kategori, artikel resmi, approval blogger
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('articles', AdminArticleController::class)->except(['show']);
});

// approval hanya boleh oleh admin (bukan editor)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/approvals', [UserApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{user}/approve', [UserApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{user}/reject', [UserApprovalController::class, 'reject'])->name('approvals.reject');
});

/*
|--------------------------------------------------------------------------
| Auth minimal (login, register, logout) — tanpa dependency Breeze
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
