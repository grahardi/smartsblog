@extends('layouts.admin')

@section('title', 'Approval Blogger')

@section('content')
    <h3 class="mb-4">Pengajuan Blogger Menunggu Approval</h3>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Nama</th><th>Email</th><th>Diajukan</th><th></th></tr></thead>
                <tbody>
                @forelse($pendingUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->blogger_requested_at?->diffForHumans() }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.approvals.approve', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">Setujui</button>
                            </form>
                            <form action="{{ route('admin.approvals.reject', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak pengajuan ini?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Tolak</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada pengajuan menunggu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $pendingUsers->links() }}</div>
@endsection
