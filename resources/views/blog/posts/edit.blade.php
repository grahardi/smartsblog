@extends('layouts.app')

@section('title', 'Edit Postingan')

@section('content')
    <h2 class="mb-4">Edit Postingan</h2>
    <form action="{{ route('blog.posts.update', $post) }}" method="POST" class="card shadow-sm p-4">
        @include('blog.posts._form')
    </form>
@endsection
