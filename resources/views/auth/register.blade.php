@extends('layouts.app')

@section('title', 'Daftar — Smarts.id')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-4 text-center">Buat Akun Baru</h4>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-info text-white w-100">Daftar</button>
                </form>

                <p class="text-center mt-3 mb-0 small">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
