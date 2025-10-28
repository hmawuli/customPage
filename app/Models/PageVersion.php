<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PageVersion extends Model
{

        use HasFactory,HasApiTokens, SoftDeletes;

    protected $table = "page_versions";

protected $fillable = [
        'page_id',
        'client_id',
        'version_number',
        'title',
        'content',
        'images',
        'theme_id',
        'change_summary',
        'changes',
    ];

    protected $casts = [
        'content' => 'array',
        'images' => 'array',
        'changes' => 'array',
    ];

    // Relationships
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    // Scopes
    public function scopeForPage($query, $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    // Helper Methods
    public function restore()
    {
        $this->page->update([
            'title' => $this->title,
            'content' => $this->content,
            'images' => $this->images,
            'theme_id' => $this->theme_id,
        ]);
    }

    public function getFormattedVersionNumber(): string
    {
        return 'v' . $this->version_number;
    }
}
