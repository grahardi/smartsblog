@extends('layouts.app')

@section('title', $blog->name.' — Blog Smarts.id')
@section('meta_description', $blog->description)

@section('content')
    <div class="p-4 mb-4 bg-white rounded shadow-sm d-flex align-items-center gap-3">
        <img src="{{ $blog->logo ?? 'https://placehold.co/80x80?text='.substr($blog->name,0,1) }}"
             class="rounded-circle" width="80" height="80" style="object-fit:cover;" alt="{{ $blog->name }}">
        <div>
            <h2 class="mb-1">{{ $blog->name }}</h2>
            <p class="text-muted mb-1">oleh {{ $blog->owner->name }}</p>
            @if($blog->description)
                <p class="mb-0">{{ $blog->description }}</p>
            @endif
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        @forelse($posts as $post)
            <div class="col">
                <div class="card h-100 shadow-sm post-card">
                    <img src="{{ $post->featured_image ?? 'https://placehold.co/400x220?text='.urlencode($post->title) }}" class="card-img-top" alt="{{ $post->title }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $post->title }}</h5>
                        <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($post->excerpt ?? strip_tags($post->content), 100) }}</p>
                    </div>
                    <div class="card-footer bg-white small text-muted">
                        {{ $post->published_at?->translatedFormat('d M Y') }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Blog ini belum memiliki postingan.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
@endsection
