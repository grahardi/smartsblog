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
        .hero-slider .hero-img { width:100%; height:auto; max-height:360px; object-fit:cover; }
        @media (max-width:768px) {
            .hero-slider .hero-img { max-height:165px; object-fit:cover; object-position:left; }
        }

        /* --- Navbar --- */
        .navbar { backdrop-filter: blur(6px); background:rgba(28,31,38,.97) !important; border-bottom:1px solid rgba(255,255,255,.06); }
        .navbar-brand i { transition: transform .2s ease; }
        .navbar-brand:hover i { transform: rotate(-8deg) scale(1.05); }
        .navbar .nav-link { font-weight:500; padding:.55rem .9rem !important; border-radius:.5rem; transition: background .15s ease, color .15s ease; }
        .navbar .nav-link:hover { background:rgba(255,255,255,.08); color:#fff !important; }
        .navbar .nav-link.active-category { color:#0dcaf0 !important; background:rgba(13,202,240,.12); }
        .navbar .dropdown-menu {
            border:none; border-radius:.75rem; padding:.5rem; margin-top:.5rem;
            box-shadow:0 12px 28px rgba(0,0,0,.18); min-width:220px;
        }
        .navbar .dropdown-item { border-radius:.5rem; padding:.55rem .75rem; display:flex; align-items:center; gap:.6rem; }
        .navbar .dropdown-item:hover { background:#f1f3f5; }
        .navbar .dropdown-item i { color:#0dcaf0; font-size:1rem; width:1.1rem; text-align:center; }

        /* --- Chip / tombol kategori (dipakai di halaman kategori & sidebar) --- */
        .category-chip {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.55rem 1.1rem; border-radius:999px; font-weight:500; font-size:.92rem;
            background:#fff; border:1px solid #e9ecef; color:#343a40; text-decoration:none;
            transition: all .18s ease;
        }
        .category-chip i { color:#0dcaf0; font-size:1.05rem; }
        .category-chip:hover { border-color:#0dcaf0; background:#eafcff; color:#0b8fab; transform:translateY(-1px); box-shadow:0 4px 10px rgba(13,202,240,.15); }
        .category-chip.active { background:#0dcaf0; border-color:#0dcaf0; color:#fff; }
        .category-chip.active i { color:#fff; }

        .category-tile { display:flex; align-items:center; gap:.75rem; padding:.85rem 1rem; border-radius:.75rem;
            text-decoration:none; color:#212529; transition: background .15s ease, transform .15s ease; }
        .category-tile:hover { background:#f1f8fa; transform:translateX(2px); }
        .icon-wrap {
            width:42px; height:42px; border-radius:.6rem; display:flex; align-items:center; justify-content:center;
            background:linear-gradient(135deg,#0dcaf0,#4b1fb0); color:#fff; font-size:1.1rem; flex-shrink:0;
        }
        .category-tile .tile-name { font-weight:600; font-size:.95rem; }
        .category-tile .tile-count { font-size:.78rem; color:#868e96; }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top py-2">
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 {{ request()->is('kategori/'.$cat->slug) ? 'active-category' : '' }}"
                           href="{{ route('category.show', $cat->slug) }}" data-bs-toggle="dropdown">
                            @if($cat->icon)<i class="bi {{ $cat->icon }}"></i>@endif
                            {{ $cat->name }}
                        </a>
                        @if($cat->activeChildren->count())
                            <ul class="dropdown-menu">
                                @foreach($cat->activeChildren as $sub)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('category.show', $sub->slug) }}">
                                            @if($sub->icon)<i class="bi {{ $sub->icon }}"></i>@endif
                                            {{ $sub->name }}
                                        </a>
                                    </li>
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
                                <button class="btn btn-sm btn-outline-info mt-1 rounded-pill px-3">Jadi Blogger</button>
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
                            <button class="btn btn-sm btn-outline-light ms-2 rounded-pill px-3">Keluar</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="btn btn-sm btn-outline-light me-2 rounded-pill px-3" href="{{ route('login') }}">Masuk</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-info rounded-pill px-3" href="{{ route('register') }}">Daftar</a></li>
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
