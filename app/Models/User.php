<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'blogger_status',
        'bio', 'avatar', 'slug',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blogger_requested_at' => 'datetime',
            'blogger_approved_at' => 'datetime',
        ];
    }

    // --- relasi ---

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function blog(): HasOne
    {
        return $this->hasOne(Blog::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blogger_approved_by');
    }

    // --- helper role ---

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    // --- helper status blogger ---

    public function hasPendingBloggerRequest(): bool
    {
        return $this->blogger_status === 'pending';
    }

    public function isApprovedBlogger(): bool
    {
        return $this->blogger_status === 'approved';
    }

    public function requestBloggerAccess(): void
    {
        $this->update([
            'blogger_status' => 'pending',
            'blogger_requested_at' => now(),
        ]);
    }
}
