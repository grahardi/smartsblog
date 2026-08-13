<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserApprovalController extends Controller
{
    // daftar user yang mengajukan jadi blogger dan menunggu approval
    public function index(): View
    {
        $pendingUsers = User::where('blogger_status', 'pending')
            ->orderBy('blogger_requested_at')
            ->paginate(20);

        return view('admin.approvals.index', compact('pendingUsers'));
    }

    public function approve(User $user): RedirectResponse
    {
        if ($user->blogger_status !== 'pending') {
            return back()->withErrors(['user' => 'Pengajuan ini sudah diproses sebelumnya.']);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'role' => 'author',
                'blogger_status' => 'approved',
                'blogger_approved_at' => now(),
                'blogger_approved_by' => Auth::id(),
            ]);

            // otomatis buatkan blog kosong untuk user begitu disetujui
            if (! $user->blog) {
                Blog::create([
                    'user_id' => $user->id,
                    'name' => $user->name."'s Blog",
                    'description' => null,
                    'status' => 'active',
                ]);
            }
        });

        // TODO: kirim notifikasi email ke user bahwa pengajuan disetujui

        return back()->with('success', "Pengajuan blogger dari {$user->name} disetujui.");
    }

    public function reject(User $user): RedirectResponse
    {
        if ($user->blogger_status !== 'pending') {
            return back()->withErrors(['user' => 'Pengajuan ini sudah diproses sebelumnya.']);
        }

        $user->update([
            'blogger_status' => 'rejected',
        ]);

        // TODO: kirim notifikasi email ke user bahwa pengajuan ditolak

        return back()->with('success', "Pengajuan blogger dari {$user->name} ditolak.");
    }
}
