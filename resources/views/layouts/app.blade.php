<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smarts.id — Portal Artikel & Blog')</title>
    <meta name="description" content="@yield('meta_description', 'Portal artikel kecerdasan, pendidikan, dan pengetahuan.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f7f8fa; }
        .navbar-brand { font-weight:700; letter-spacing:-.5px; }
        .article-card img, .post-card img { height:180px; object-fit:cover; }
        footer { background:#1c1f26; color:#adb5bd; }
        footer a { color:#e9ecef; text-decoration:none; }

        /* --- Hero image slider --- */
        .hero-slider .hero-img { width:100%; height:auto; max-height:480px; object-fit:cover; }
        @media (max-width:768px) {
            .hero-slider .hero-img { max-height:220px; object-fit:cover; object-position:left; }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-mortarboard-fill text-info fs-4"></i>
            Smarts<span class="text-info">.id</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @foreach(\App\Models\Category::with('activeChildren')->parents()->active()->orderBy('order')->get() as $cat)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('category.show', $cat->slug) }}" data-bs-toggle="dropdown">
                            {{ $cat->name }}
                        </a>
                        @if($cat->activeChildren->count())
                            <ul class="dropdown-menu">
                                @foreach($cat->activeChildren as $sub)
                                    <li><a class="dropdown-item" href="{{ route('category.show', $sub->slug) }}">{{ $sub->name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
            <ul class="navbar-nav">
                @auth
                    @if(auth()->user()->isApprovedBlogger())
                        <li class="nav-item"><a class="nav-link" href="{{ route('blog.dashboard') }}"><i class="bi bi-journal-text"></i> Dashboard Blog</a></li>
                    @elseif(auth()->user()->blogger_status === 'none')
                        <li class="nav-item">
                            <form action="{{ route('blogger.request') }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-info mt-1">Jadi Blogger</button>
                            </form>
                        </li>
                    @elseif(auth()->user()->blogger_status === 'pending')
                        <li class="nav-item"><span class="nav-link text-warning">Menunggu approval</span></li>
                    @endif
                    @if(auth()->user()->isEditor())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.articles.index') }}"><i class="bi bi-speedometer2"></i> Admin</a></li>
                    @endif
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-light ms-2">Keluar</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="btn btn-sm btn-outline-light me-2" href="{{ route('login') }}">Masuk</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-info" href="{{ route('register') }}">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="py-4 mt-5">
    <div class="container text-center small">
        &copy; {{ date('Y') }} Smarts.id — Portal Kecerdasan, Pendidikan & Pengetahuan.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
