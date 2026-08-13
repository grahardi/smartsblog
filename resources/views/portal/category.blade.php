@extends('layouts.app')

@section('title', $category->name.' — Smarts.id')
@section('meta_description', $category->meta_description ?? $category->description)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            @if($category->parent)
                <li class="breadcrumb-item"><a href="{{ route('category.show', $category->parent->slug) }}">{{ $category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>

    <h2 class="mb-1">{{ $category->name }}</h2>
    @if($category->description)
        <p class="text-muted">{{ $category->description }}</p>
    @endif

    @if($category->activeChildren->count())
        <div class="mb-4">
            @foreach($category->activeChildren as $sub)
                <a href="{{ route('category.show', $sub->slug) }}" class="btn btn-sm btn-outline-secondary me-2 mb-2">{{ $sub->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="row row-cols-1 row-cols-md-3 g-4">
        @forelse($articles as $article)
            <div class="col">
                <div class="card h-100 shadow-sm article-card">
                    <img src="{{ $article->featured_image ?? 'https://placehold.co/400x220?text='.urlencode($article->title) }}" class="card-img-top" alt="{{ $article->title }}">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('article.show', $article->slug) }}" class="text-decoration-none text-dark">{{ $article->title }}</a>
                        </h5>
                        <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($article->excerpt ?? strip_tags($article->content), 90) }}</p>
                    </div>
                    <div class="card-footer bg-white small text-muted">
                        {{ $article->author->name }} &middot; {{ $article->published_at?->translatedFormat('d M Y') }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada artikel di kategori ini.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $articles->links() }}</div>
@endsection
