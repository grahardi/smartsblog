<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(): View
    {
        $posts = Auth::user()->blog->posts()->latest()->paginate(15);

        return view('blog.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = Category::with('children')->parents()->active()->get();

        return view('blog.posts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $blog = Auth::user()->blog;

        abort_unless($blog && $blog->isActive(), 403, 'Blog Anda belum aktif.');

        $data = $this->validated($request);
        $data['blog_id'] = $blog->id;

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        if ($request->filled('tags')) {
            $post->tags()->sync($request->input('tags'));
        }

        return redirect()
            ->route('blog.posts.index')
            ->with('success', 'Postingan berhasil disimpan.');
    }

    public function edit(BlogPost $post): View
    {
        $this->authorizeOwnership($post);

        $categories = Category::with('children')->parents()->active()->get();

        return view('blog.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $this->authorizeOwnership($post);

        $data = $this->validated($request);

        if ($data['status'] === 'published' && empty($post->published_at)) {
            $data['published_at'] = now();
        }

        $post->update($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags', []));
        }

        return redirect()
            ->route('blog.posts.index')
            ->with('success', 'Postingan berhasil diperbarui.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->authorizeOwnership($post);

        $post->delete();

        return redirect()
            ->route('blog.posts.index')
            ->with('success', 'Postingan berhasil dihapus.');
    }

    // pastikan post yang diedit memang milik blog user yang login
    private function authorizeOwnership(BlogPost $post): void
    {
        abort_unless($post->blog_id === Auth::user()->blog?->id, 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,pending_review,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
