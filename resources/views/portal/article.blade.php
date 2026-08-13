@extends('layouts.app')

@section('title', $article->meta_title ?? $article->title)
@section('meta_description', $article->meta_description ?? $article->excerpt)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($article->title, 40) }}</li>
                </ol>
            </nav>

            @if($article->categories->count())
                <div class="mb-2">
                    @foreach($article->categories as $cat)
                        <a href="{{ route('category.show', $cat->slug) }}" class="badge bg-secondary text-decoration-none me-1">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            <h1 class="mb-2">{{ $article->title }}</h1>
            <p class="text-muted">
                Oleh {{ $article->author->name }} &middot; {{ $article->published_at?->translatedFormat('d M Y') }}
                &middot; {{ $article->views }} views
            </p>

            @if($article->featured_image)
                <img src="{{ $article->featured_image }}" class="img-fluid rounded mb-4" alt="{{ $article->title }}">
            @endif

            <div class="article-content fs-5" style="line-height:1.8;">
                {!! $article->content !!}
            </div>

            @if($article->tags->count())
                <div class="mt-4">
                    @foreach($article->tags as $tag)
                        <span class="badge bg-light text-dark border me-1">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            @if($related->count())
                <hr class="my-4">
                <h5>Artikel Terkait</h5>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    @foreach($related as $r)
                        <div class="col">
                            <a href="{{ route('article.show', $r->slug) }}" class="text-decoration-none text-dark">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $r->title }}</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Tentang Penulis</h6>
                    <p class="mb-0 fw-semibold">{{ $article->author->name }}</p>
                    @if($article->author->bio)
                        <p class="text-muted small">{{ $article->author->bio }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
