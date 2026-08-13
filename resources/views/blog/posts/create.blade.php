@extends('layouts.app')

@section('title', 'Tulis Postingan Baru')

@section('content')
    <h2 class="mb-4">Tulis Postingan Baru</h2>
    <form action="{{ route('blog.posts.store') }}" method="POST" class="card shadow-sm p-4">
        @include('blog.posts._form')
    </form>
@endsection
