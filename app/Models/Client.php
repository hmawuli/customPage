<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
        'company_name',
        'phone',
        'theme_id',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Automatically generate slug on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->slug)) {
                $client->slug = Str::slug($client->name);

                // Ensure uniqueness
                $count = static::where('slug', 'like', $client->slug . '%')->count();
                if ($count > 0) {
                    $client->slug = $client->slug . '-' . ($count + 1);
                }
            }
        });
    }

    // Relationships
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    public function pageVersions()
    {
        return $this->hasMany(PageVersion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function getTotalPageViews(): int
    {
        return $this->pageViews()->count();
    }

    public function getPublishedPagesCount(): int
    {
        return $this->pages()->published()->count();
    }
}
