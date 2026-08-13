<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'status', 'published_at', 'is_featured',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title).'-'.Str::random(6);
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    // helper: kategori utama untuk breadcrumb/badge tunggal (kategori pertama yang tersimpan)
    public function primaryCategory(): ?Category
    {
        return $this->relationLoaded('categories')
            ? $this->categories->first()
            : $this->categories()->first();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * URL gambar yang selalu absolut, aman dipakai langsung di <img src>.
     * - null tetap null (biar view yang menentukan placeholder)
     * - URL eksternal (http/https) dikembalikan apa adanya
     * - path lokal (mis. "storage/articles/xxx.jpg") dibungkus asset()
     *   supaya jadi absolut, bukan relatif terhadap URL halaman saat ini.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->featured_image)) {
            return null;
        }

        if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
            return $this->featured_image;
        }

        return asset(ltrim($this->featured_image, '/'));
    }
}
