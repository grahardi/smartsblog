@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
    <h3 class="mb-4">Tambah Kategori</h3>
    <form action="{{ route('admin.categories.store') }}" method="POST" class="card shadow-sm p-4" style="max-width:700px;">
        @include('admin.categories._form')
    </form>
@endsection
