@extends('layouts.app')

@section('title', 'Edit Profil Blog')

@section('content')
    <h2 class="mb-4">Edit Profil Blog</h2>

    <form action="{{ route('blog.profile.update') }}" method="POST" class="card shadow-sm p-4" style="max-width:600px;">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nama Blog</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $blog->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $blog->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">URL Logo</label>
            <input type="text" name="logo" class="form-control" value="{{ old('logo', $blog->logo) }}" placeholder="https://...">
        </div>

        <div class="mb-3">
            <label class="form-label">URL Cover</label>
            <input type="text" name="cover_image" class="form-control" value="{{ old('cover_image', $blog->cover_image) }}" placeholder="https://...">
        </div>

        <button class="btn btn-info text-white">Simpan Perubahan</button>
    </form>
@endsection
