<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageViewResource;
use App\Models\Page;
use App\Models\PageView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get analytics overview for authenticated client
     */
    public function overview(Request $request)
    {
        $clientId = $request->user()->id;
        $period = $request->input('period', 30); // days

        $stats = [
            'total_pages' => Page::where('client_id', $clientId)->count(),
            'published_pages' => Page::where('client_id', $clientId)->published()->count(),
            'total_views' => PageView::where('client_id', $clientId)->count(),
            'unique_visitors' => PageView::where('client_id', $clientId)
                ->uniqueVisitors()
                ->count(),
            'views_today' => PageView::where('client_id', $clientId)
                ->today()
                ->count(),
            'views_this_week' => PageView::where('client_id', $clientId)
                ->thisWeek()
                ->count(),
            'views_this_month' => PageView::where('client_id', $clientId)
                ->thisMonth()
                ->count(),
            'avg_time_on_page' => round(
                PageView::where('client_id', $clientId)
                    ->where('time_on_page', '>', 0)
                    ->avg('time_on_page') ?? 0,
                2
            ),
            'bounce_rate' => $this->calculateBounceRate($clientId),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * Get detailed analytics for a specific page
     */
    public function pageAnalytics(Request $request, $pageId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $period = $request->input('period', 30);
        $startDate = Carbon::now()->subDays($period);

        $analytics = [
            'page_info' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'published_at' => $page->published_at?->toDateTimeString(),
            ],
            'total_views' => PageView::forPage($pageId)->count(),
            'unique_visitors' => PageView::forPage($pageId)
                ->uniqueVisitors()
                ->count(),
            'views_by_period' => [
                'today' => PageView::forPage($pageId)->today()->count(),
                'this_week' => PageView::forPage($pageId)->thisWeek()->count(),
                'this_month' => PageView::forPage($pageId)->thisMonth()->count(),
            ],
            'bounce_rate' => PageView::getBounceRate($pageId),
            'avg_time_on_page' => round(PageView::getAverageTimeOnPage($pageId) ?? 0, 2),
            'views_trend' => $this->getViewsTrend($pageId, $period),
            'top_referrers' => $this->getTopReferrers($pageId, 10),
            'device_breakdown' => $this->getDeviceBreakdown($pageId),
            'browser_breakdown' => $this->getBrowserBreakdown($pageId),
            'geographic_data' => $this->getGeographicData($pageId),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ], 200);
    }

    /**
     * Get views trend over time
     */
    public function viewsTrend(Request $request)
    {
        $clientId = $request->user()->id;
        $period = $request->input('period', 30);
        $pageId = $request->input('page_id');

        $query = PageView::where('client_id', $clientId)
            ->where('viewed_at', '>=', Carbon::now()->subDays($period))
            ->when($pageId, function ($q) use ($pageId) {
                $q->where('page_id', $pageId);
            });

        $trend = $query->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trend
        ], 200);
    }

    /**
     * Get top performing pages
     */
    public function topPages(Request $request)
    {
        $clientId = $request->user()->id;
        $limit = $request->input('limit', 10);
        $period = $request->input('period', 30);

        $topPages = Page::where('client_id', $clientId)
            ->withCount(['pageViews' => function ($query) use ($period) {
                $query->where('viewed_at', '>=', Carbon::now()->subDays($period));
            }])
            ->orderBy('page_views_count', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'views' => $page->page_views_count,
                    'url' => $page->getPublicUrl(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topPages
        ], 200);
    }

    /**
     * Get visitor analytics
     */
    public function visitorAnalytics(Request $request)
    {
        $clientId = $request->user()->id;
        $period = $request->input('period', 30);

        $analytics = [
            'total_visitors' => PageView::where('client_id', $clientId)
                ->lastDays($period)
                ->count(),
            'unique_visitors' => PageView::where('client_id', $clientId)
                ->lastDays($period)
                ->uniqueVisitors()
                ->count(),
            'returning_visitors' => PageView::where('client_id', $clientId)
                ->lastDays($period)
                ->where('is_unique_visitor', false)
                ->count(),
            'devices' => $this->getDeviceBreakdownForClient($clientId, $period),
            'browsers' => $this->getBrowserBreakdownForClient($clientId, $period),
            'countries' => $this->getCountryBreakdownForClient($clientId, $period),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ], 200);
    }

    /**
     * Export analytics data as CSV
     */
    public function export(Request $request)
    {
        $clientId = $request->user()->id;
        $period = $request->input('period', 30);
        $pageId = $request->input('page_id');

        $query = PageView::where('client_id', $clientId)
            ->with('page')
            ->where('viewed_at', '>=', Carbon::now()->subDays($period))
            ->when($pageId, function ($q) use ($pageId) {
                $q->where('page_id', $pageId);
            })
            ->orderBy('viewed_at', 'desc');

        $views = $query->get();

        $filename = 'analytics_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($views) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Time',
                'Page Title',
                'Page URL',
                'Visitor IP',
                'Device Type',
                'Browser',
                'Platform',
                'Country',
                'City',
                'Referrer',
                'Time on Page (seconds)',
                'Is Unique',
                'Is Bounce'
            ]);

            // Add data rows
            foreach ($views as $view) {
                fputcsv($file, [
                    $view->viewed_at->format('Y-m-d'),
                    $view->viewed_at->format('H:i:s'),
                    $view->page->title ?? 'N/A',
                    $view->page->getPublicUrl() ?? 'N/A',
                    $view->visitor_ip,
                    $view->device_type ?? 'Unknown',
                    $view->browser ?? 'Unknown',
                    $view->platform ?? 'Unknown',
                    $view->country ?? 'Unknown',
                    $view->city ?? 'Unknown',
                    $view->referrer ?? 'Direct',
                    $view->time_on_page,
                    $view->is_unique_visitor ? 'Yes' : 'No',
                    $view->is_bounce ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get real-time analytics
     */
    public function realtime(Request $request)
    {
        $clientId = $request->user()->id;
        $minutes = $request->input('minutes', 30);

        $realtimeData = [
            'active_visitors' => PageView::where('client_id', $clientId)
                ->where('viewed_at', '>=', Carbon::now()->subMinutes($minutes))
                ->distinct('session_id')
                ->count(),
            'recent_views' => PageView::where('client_id', $clientId)
                ->where('viewed_at', '>=', Carbon::now()->subMinutes($minutes))
                ->count(),
            'active_pages' => PageView::where('client_id', $clientId)
                ->where('viewed_at', '>=', Carbon::now()->subMinutes($minutes))
                ->with('page')
                ->get()
                ->groupBy('page_id')
                ->map(function ($views, $pageId) {
                    return [
                        'page' => $views->first()->page->title,
                        'active_visitors' => $views->unique('session_id')->count(),
                        'views' => $views->count(),
                    ];
                })
                ->values(),
            'recent_activity' => PageView::where('client_id', $clientId)
                ->where('viewed_at', '>=', Carbon::now()->subMinutes($minutes))
                ->with('page')
                ->latest('viewed_at')
                ->take(20)
                ->get()
                ->map(function ($view) {
                    return [
                        'page' => $view->page->title,
                        'time' => $view->viewed_at->diffForHumans(),
                        'device' => $view->device_type,
                        'location' => $view->city && $view->country
                            ? "{$view->city}, {$view->country}"
                            : ($view->country ?? 'Unknown'),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $realtimeData
        ], 200);
    }

    // ========== HELPER METHODS ==========

    private function calculateBounceRate($clientId)
    {
        $total = PageView::where('client_id', $clientId)->count();
        $bounces = PageView::where('client_id', $clientId)->bounces()->count();

        return $total > 0 ? round(($bounces / $total) * 100, 2) : 0;
    }

    private function getViewsTrend($pageId, $days)
    {
        return PageView::where('page_id', $pageId)
            ->where('viewed_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopReferrers($pageId, $limit)
    {
        return PageView::where('page_id', $pageId)
            ->whereNotNull('referrer_domain')
            ->select('referrer_domain', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer_domain')
            ->orderBy('count', 'desc')
            ->take($limit)
            ->get();
    }

    private function getDeviceBreakdown($pageId)
    {
        return PageView::where('page_id', $pageId)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->device_type ?? 'Unknown' => $item->count];
            });
    }

    private function getBrowserBreakdown($pageId)
    {
        return PageView::where('page_id', $pageId)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->browser ?? 'Unknown' => $item->count];
            });
    }

    private function getGeographicData($pageId)
    {
        return PageView::where('page_id', $pageId)
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->country => $item->count];
            });
    }

    private function getDeviceBreakdownForClient($clientId, $days)
    {
        return PageView::where('client_id', $clientId)
            ->lastDays($days)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->device_type ?? 'Unknown' => $item->count];
            });
    }

    private function getBrowserBreakdownForClient($clientId, $days)
    {
        return PageView::where('client_id', $clientId)
            ->lastDays($days)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->browser ?? 'Unknown' => $item->count];
            });
    }

    private function getCountryBreakdownForClient($clientId, $days)
    {
        return PageView::where('client_id', $clientId)
            ->lastDays($days)
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->country => $item->count];
            });
    }
}
