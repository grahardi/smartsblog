@extends('layouts.app')

@section('title', 'Smarts.id — Portal Kecerdasan, Pendidikan & Pengetahuan')

@section('content')

    {{-- Slider tema: kecerdasan, matematika, fisika, kimia & biologi --}}
    <div id="themeSlider" class="carousel slide hero-slider mb-4 rounded overflow-hidden shadow-sm" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/hero/slide-bahaya-teknologi.jpg') }}" class="d-block w-100 hero-img" alt="Bahaya Teknologi Lanjutan">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/hero/slide-kimia-biologi.jpg') }}" class="d-block w-100 hero-img" alt="Kimia dan Biologi">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/hero/slide-matematika-fisika.jpg') }}" class="d-block w-100 hero-img" alt="Matematika, Rumus dan Inovasi">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#themeSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#themeSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#themeSlider" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#themeSlider" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#themeSlider" data-bs-slide-to="2"></button>
        </div>
    </div>

    @if($featured->count())
        <div id="heroCarousel" class="carousel slide mb-5 rounded overflow-hidden shadow-sm" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($featured as $i => $article)
                    <div class="carousel-item @if($i === 0) active @endif">
                        <a href="{{ route('article.show', $article->slug) }}" class="text-decoration-none">
                            <img src="{{ $article->featured_image ?? 'https://placehold.co/1200x420?text='.urlencode($article->title) }}"
                                 class="d-block w-100" style="height:420px;object-fit:cover;" alt="{{ $article->title }}">
                            <div class="carousel-caption bg-dark bg-opacity-50 rounded p-3">
                                <span class="badge bg-info mb-2">{{ $article->categories->first()?->name }}</span>
                                <h3>{{ $article->title }}</h3>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            @if($featured->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <h4 class="mb-3">Artikel Terbaru</h4>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                @forelse($latest as $article)
                    <div class="col">
                        <div class="card h-100 shadow-sm article-card">
                            <img src="{{ $article->featured_image ?? 'https://placehold.co/400x220?text='.urlencode($article->title) }}" class="card-img-top" alt="{{ $article->title }}">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2">{{ $article->categories->first()?->name }}</span>
                                <h5 class="card-title">
                                    <a href="{{ route('article.show', $article->slug) }}" class="text-decoration-none text-dark">{{ $article->title }}</a>
                                </h5>
                                <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($article->excerpt ?? strip_tags($article->content), 100) }}</p>
                            </div>
                            <div class="card-footer bg-white small text-muted">
                                {{ $article->author->name }} &middot; {{ $article->published_at?->translatedFormat('d M Y') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Belum ada artikel dipublikasikan.</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $latest->links() }}</div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3">Jelajahi Kategori</h6>
                    <div class="d-flex flex-column gap-1">
                        @foreach($categories as $cat)
                            <a href="{{ route('category.show', $cat->slug) }}" class="category-tile">
                                <div class="icon-wrap">
                                    <i class="bi {{ $cat->icon ?? 'bi-folder2' }}"></i>
                                </div>
                                <div>
                                    <div class="tile-name">{{ $cat->name }}</div>
                                    @if($cat->activeChildren->count())
                                        <div class="tile-count">{{ $cat->activeChildren->pluck('name')->implode(', ') }}</div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
