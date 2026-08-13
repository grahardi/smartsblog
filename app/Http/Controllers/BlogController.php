<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BlogController extends Controller
{
    // user mengajukan diri untuk jadi blogger
    public function requestAccess(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->blogger_status === 'approved') {
            return redirect()->route('blog.dashboard')->with('info', 'Anda sudah menjadi blogger.');
        }

        if ($user->blogger_status === 'pending') {
            return back()->with('info', 'Pengajuan Anda sedang menunggu review admin.');
        }

        $user->requestBloggerAccess();

        return back()->with('success', 'Pengajuan blogger berhasil dikirim, tunggu approval admin.');
    }

    // dashboard blog milik user yang sudah disetujui
    public function dashboard(): View
    {
        $blog = Auth::user()->blog()->with(['posts' => fn ($q) => $q->latest()->limit(10)])->firstOrFail();

        return view('blog.dashboard', compact('blog'));
    }

    public function editProfile(): View
    {
        $blog = Auth::user()->blog()->firstOrFail();

        return view('blog.edit-profile', compact('blog'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $blog = Auth::user()->blog()->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:255'],
        ]);

        $blog->update($data);

        return back()->with('success', 'Profil blog berhasil diperbarui.');
    }

    // halaman publik sebuah blog
    public function show(string $slug): View
    {
        $blog = Blog::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $posts = $blog->publishedPosts()->latest('published_at')->paginate(10);

        return view('blog.show', compact('blog', 'posts'));
    }
}
