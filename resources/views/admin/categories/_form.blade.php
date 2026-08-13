@csrf
@if(isset($category)) @method('PUT') @endif

<div class="mb-3">
    <label class="form-label">Induk Kategori (opsional — kosongkan jika ini kategori utama)</label>
    <select name="parent_id" class="form-select">
        <option value="">— Kategori Utama —</option>
        @foreach($parents as $parent)
            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>{{ $parent->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Nama Kategori</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Slug (opsional, otomatis dari nama jika kosong)</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Icon (opsional, mis. class Bootstrap Icons)</label>
        <input type="text" name="icon" class="form-control" value="{{ old('icon', $category->icon ?? '') }}" placeholder="bi-cpu">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Urutan Tampil</label>
        <input type="number" name="order" class="form-control" value="{{ old('order', $category->order ?? 0) }}">
    </div>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
        @checked(old('is_active', $category->is_active ?? true))>
    <label class="form-check-label" for="is_active">Aktif (tampil di portal)</label>
</div>

<button class="btn btn-info text-white">Simpan</button>
<a href="{{ route('admin.categories.index') }}" class="btn btn-link">Batal</a>
