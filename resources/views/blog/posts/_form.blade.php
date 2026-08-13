@csrf
@if(isset($post)) @method('PUT') @endif

<div class="mb-3">
    <label class="form-label">Judul</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $post->title ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="category_id" class="form-select">
        <option value="">— Tanpa kategori —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $post->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @foreach($cat->children as $sub)
                <option value="{{ $sub->id }}" @selected(old('category_id', $post->category_id ?? '') == $sub->id)>— {{ $sub->name }}</option>
            @endforeach
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Ringkasan (excerpt)</label>
    <textarea name="excerpt" class="form-control" rows="2" maxlength="500">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Konten</label>
    <textarea name="content" class="form-control" rows="12" required>{{ old('content', $post->content ?? '') }}</textarea>
    <div class="form-text">Mendukung HTML dasar. Bisa dihubungkan ke rich text editor (TinyMCE/Quill) nanti.</div>
</div>

<div class="mb-3">
    <label class="form-label">URL Gambar Unggulan</label>
    <input type="text" name="featured_image" class="form-control" value="{{ old('featured_image', $post->featured_image ?? '') }}" placeholder="https://...">
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>Draft</option>
        <option value="pending_review" @selected(old('status', $post->status ?? '') === 'pending_review')>Kirim untuk direview</option>
        <option value="published" @selected(old('status', $post->status ?? '') === 'published')>Publikasikan</option>
    </select>
</div>

<button class="btn btn-info text-white">Simpan</button>
<a href="{{ route('blog.posts.index') }}" class="btn btn-link">Batal</a>
