<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — Smarts.id')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f4f5f7; }
        .admin-sidebar { min-height:100vh; background:#1c1f26; }
        .admin-sidebar a { color:#adb5bd; }
        .admin-sidebar a.active, .admin-sidebar a:hover { color:#fff; background:#2b2f38; }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="admin-sidebar p-3" style="width:230px;">
        <a href="{{ route('home') }}" class="d-block text-white fw-bold mb-4 fs-5 text-decoration-none">Smarts<span class="text-info">.id</span> Admin</a>
        <nav class="nav flex-column gap-1">
            <a class="nav-link rounded {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}"><i class="bi bi-newspaper"></i> Artikel</a>
            <a class="nav-link rounded {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> Kategori</a>
            @if(auth()->user()->isAdmin())
                <a class="nav-link rounded {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}" href="{{ route('admin.approvals.index') }}"><i class="bi bi-person-check"></i> Approval Blogger</a>
            @endif
            <a class="nav-link rounded" href="{{ route('home') }}"><i class="bi bi-box-arrow-left"></i> Kembali ke Portal</a>
        </nav>
    </div>

    <div class="flex-fill p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
