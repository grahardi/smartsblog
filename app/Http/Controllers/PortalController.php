<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function home(): View
    {
        $featured = Article::with(['category', 'author'])
            ->published()->featured()->latest('published_at')->limit(5)->get();

        $latest = Article::with(['category', 'author'])
            ->published()->latest('published_at')->paginate(12);

        $categories = Category::with('activeChildren')->parents()->active()->orderBy('order')->get();

        return view('portal.home', compact('featured', 'latest', 'categories'));
    }

    // menampilkan artikel dalam satu kategori, termasuk artikel dari subkategorinya
    public function category(string $slug): View
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();

        $categoryIds = $category->isSubcategory()
            ? [$category->id]
            : $category->children()->pluck('id')->push($category->id);

        $articles = Article::with(['category', 'author'])
            ->published()
            ->whereIn('category_id', $categoryIds)
            ->latest('published_at')
            ->paginate(12);

        return view('portal.category', compact('category', 'articles'));
    }

    public function article(string $slug): View
    {
        $article = Article::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $article->incrementViews();

        $related = Article::published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('portal.article', compact('article', 'related'));
    }
}
