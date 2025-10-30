<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Theme extends Model
{
use HasFactory,HasApiTokens,SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'background_color',
        'additional_colors',
        'preview_image',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'additional_colors' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($theme) {
            if (empty($theme->slug)) {
                $theme->slug = Str::slug($theme->name);
            }
        });
    }

    // Relationships
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    // Helper Methods
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function decrementUsage()
    {
        $this->decrement('usage_count');
    }

    public function getColorPalette(): array
    {
        return [
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
            'accent' => $this->accent_color,
            'text' => $this->text_color,
            'background' => $this->background_color,
        ];
    }

    public function getCssVariables(): string
    {
        return "
            --primary-color: {$this->primary_color};
            --secondary-color: {$this->secondary_color};
            --accent-color: {$this->accent_color};
            --text-color: {$this->text_color};
            --background-color: {$this->background_color};
        ";
    }
}
