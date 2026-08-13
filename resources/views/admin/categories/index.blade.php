@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Kategori & Subkategori</h3>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-info text-white">+ Kategori Baru</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Nama</th><th>Slug</th><th>Aktif</th><th>Urutan</th><th></th></tr></thead>
                <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>
                            <strong>{{ $cat->name }}</strong>
                            @if($cat->children->count())
                                <div class="ms-3 mt-1">
                                    @foreach($cat->children as $sub)
                                        <div class="small text-muted">— {{ $sub->name }}
                                            <a href="{{ route('admin.categories.edit', $sub) }}" class="ms-1">edit</a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>{!! $cat->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
                        <td>{{ $cat->order }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
