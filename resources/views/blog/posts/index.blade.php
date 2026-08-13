@extends('layouts.app')

@section('title', 'Postingan Saya')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Postingan Saya</h2>
        <a href="{{ route('blog.posts.create') }}" class="btn btn-info text-white">+ Tulis Postingan</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Views</th><th>Tanggal</th><th></th></tr></thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->category?->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'pending_review' ? 'warning' : 'secondary') }}">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td>{{ $post->views }}</td>
                        <td>{{ $post->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('blog.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('blog.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus postingan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada postingan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
@endsection
