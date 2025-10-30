<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
class PageView extends Model
{
        use HasFactory,HasApiTokens, SoftDeletes;


    protected $table = "page_views";

    protected $fillable = [
        'page_id',
        'client_id',
        'visitor_ip',
        'session_id',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'referrer',
        'referrer_domain',
        'time_on_page',
        'is_unique_visitor',
        'is_bounce',
        'viewed_at',
    ];

    protected $casts = [
        'is_unique_visitor' => 'boolean',
        'is_bounce' => 'boolean',
        'viewed_at' => 'datetime',
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

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('viewed_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('viewed_at', Carbon::now()->month)
            ->whereYear('viewed_at', Carbon::now()->year);
    }

    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('viewed_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeUniqueVisitors($query)
    {
        return $query->where('is_unique_visitor', true);
    }

    public function scopeBounces($query)
    {
        return $query->where('is_bounce', true);
    }

    public function scopeForPage($query, $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Helper Methods
    public static function recordView(Page $page, array $data)
    {
        return static::create(array_merge([
            'page_id' => $page->id,
            'client_id' => $page->client_id,
            'viewed_at' => now(),
        ], $data));
    }

    public static function getViewsGroupedByDate($pageId, $days = 30)
    {
        return static::where('page_id', $pageId)
            ->where('viewed_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public static function getBounceRate($pageId)
    {
        $total = static::where('page_id', $pageId)->count();
        $bounces = static::where('page_id', $pageId)->bounces()->count();

        return $total > 0 ? round(($bounces / $total) * 100, 2) : 0;
    }

    public static function getAverageTimeOnPage($pageId)
    {
        return static::where('page_id', $pageId)
            ->where('time_on_page', '>', 0)
            ->avg('time_on_page');
    }
}
