@csrf
@if(isset($article)) @method('PUT') @endif

<div class="mb-3">
    <label class="form-label">Judul</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $article->title ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="category_id" class="form-select" required>
        <option value="">— Pilih kategori —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $article->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @foreach($cat->children as $sub)
                <option value="{{ $sub->id }}" @selected(old('category_id', $article->category_id ?? '') == $sub->id)>— {{ $sub->name }}</option>
            @endforeach
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Ringkasan (excerpt)</label>
    <textarea name="excerpt" class="form-control" rows="2" maxlength="500">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Konten</label>
    <textarea name="content" class="form-control" rows="14" required>{{ old('content', $article->content ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">URL Gambar Unggulan</label>
    <input type="text" name="featured_image" class="form-control" value="{{ old('featured_image', $article->featured_image ?? '') }}" placeholder="https://...">
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['draft','published','scheduled','archived'] as $s)
                <option value="{{ $s }}" @selected(old('status', $article->status ?? 'draft') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3 form-check pt-4 mt-2">
        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured"
            @checked(old('is_featured', $article->is_featured ?? false))>
        <label class="form-check-label" for="is_featured">Tampilkan di carousel unggulan</label>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Meta Title (SEO)</label>
    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $article->meta_title ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Meta Description (SEO)</label>
    <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $article->meta_description ?? '') }}">
</div>

<button class="btn btn-info text-white">Simpan</button>
<a href="{{ route('admin.articles.index') }}" class="btn btn-link">Batal</a>
