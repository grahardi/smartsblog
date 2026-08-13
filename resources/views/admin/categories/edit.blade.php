@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('content')
    <h3 class="mb-4">Edit Kategori</h3>
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="card shadow-sm p-4" style="max-width:700px;">
        @include('admin.categories._form')
    </form>
@endsection
