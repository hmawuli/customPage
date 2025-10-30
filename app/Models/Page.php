<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = "pages";

    protected $fillable = [
        'client_id',
        'title',
        'slug',
        'meta_description',
        'meta_keywords',
        'content',
        'images',
        'theme_id',
        'og_image',
        'status',
        'published_at',
        'view_count',
        'version',
    ];

    protected $casts = [
        'content' => 'array',
        'images' => 'array',
        'meta_keywords' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);

                // Ensure uniqueness
                $count = static::where('slug', 'like', $page->slug . '%')->count();
                if ($count > 0) {
                    $page->slug = $page->slug . '-' . ($count + 1);
                }
            }
        });

        // Create version history on update
        static::updated(function ($page) {
            if ($page->isDirty(['content', 'images', 'title', 'theme_id'])) {
                $page->createVersion();
            }
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    public function versions()
    {
        return $this->hasMany(PageVersion::class)->orderBy('version_number', 'desc');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Helper Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
        ]);
    }

    public function archive()
    {
        $this->update([
            'status' => 'archived',
        ]);
    }

    public function incrementViews()
    {
        $this->increment('view_count');
    }

    public function createVersion()
    {
        PageVersion::create([
            'page_id' => $this->id,
            'client_id' => $this->client_id,
            'version_number' => $this->version + 1,
            'title' => $this->title,
            'content' => $this->content,
            'images' => $this->images,
            'theme_id' => $this->theme_id,
        ]);

        $this->increment('version');
    }

    public function getActiveTheme()
    {
        return $this->theme ?? $this->client->theme;
    }

    public function getPublicUrl(): string
    {
        return route('page.show', ['clientSlug' => $this->client->slug, 'pageSlug' => $this->slug]);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            $this->published_at &&
            $this->published_at->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
}
