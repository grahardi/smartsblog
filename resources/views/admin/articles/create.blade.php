@extends('layouts.admin')

@section('title', 'Tambah Artikel')

@section('content')
    <h3 class="mb-4">Tambah Artikel</h3>
    <form action="{{ route('admin.articles.store') }}" method="POST" class="card shadow-sm p-4">
        @include('admin.articles._form')
    </form>
@endsection
