@extends('layouts.admin')

@section('title', 'Edit Artikel')

@section('content')
    <h3 class="mb-4">Edit Artikel</h3>
    <form action="{{ route('admin.articles.update', $article) }}" method="POST" class="card shadow-sm p-4">
        @include('admin.articles._form')
    </form>
@endsection
