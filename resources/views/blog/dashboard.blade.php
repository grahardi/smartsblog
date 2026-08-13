@extends('layouts.app')

@section('title', 'Dashboard Blog Saya')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">{{ $blog->name }}</h2>
            <a href="{{ route('blog.show', $blog->slug) }}" class="small text-muted" target="_blank">
                smarts.id/blog/{{ $blog->slug }} <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
        <div>
            <a href="{{ route('blog.profile.edit') }}" class="btn btn-outline-secondary">Edit Profil Blog</a>
            <a href="{{ route('blog.posts.create') }}" class="btn btn-info text-white">+ Tulis Postingan</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Total Postingan</div>
                <div class="fs-3 fw-bold">{{ $blog->posts()->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Dipublikasikan</div>
                <div class="fs-3 fw-bold">{{ $blog->publishedPosts()->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Total Views</div>
                <div class="fs-3 fw-bold">{{ $blog->posts()->sum('views') }}</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between">
            <span>Postingan Terbaru</span>
            <a href="{{ route('blog.posts.index') }}" class="small">Lihat semua &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Judul</th><th>Status</th><th>Views</th><th>Tanggal</th><th></th></tr></thead>
                <tbody>
                @forelse($blog->posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>
                            <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'pending_review' ? 'warning' : 'secondary') }}">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td>{{ $post->views }}</td>
                        <td>{{ $post->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('blog.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada postingan. Mulai menulis!</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
