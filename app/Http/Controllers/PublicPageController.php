<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Page;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class PublicPageController extends Controller
{
    /**
     * Display client's homepage
     */
    public function clientHome($clientSlug)
    {
        $client = Client::where('slug', $clientSlug)
            ->with('theme')
            ->firstOrFail();

        $homePage = Page::where('client_id', $client->id)
            ->where('slug', 'home')
            ->orWhere('slug', 'index')
            ->published()
            ->first();

        if (!$homePage) {
            $homePage = Page::where('client_id', $client->id)
                ->published()
                ->oldest()
                ->first();
        }

        if (!$homePage) {
            abort(404, 'No published pages found for this client');
        }

        // Track page view
        $this->trackPageView($homePage, request());

        return view('public.page', [
            'client' => $client,
            'page' => $homePage,
            'theme' => $homePage->getActiveTheme(),
        ]);
    }

    /**
     * Display a specific page
     */
    public function show($clientSlug, $pageSlug)
    {
        $client = Client::where('slug', $clientSlug)
            ->with('theme')
            ->firstOrFail();

        $page = Page::where('client_id', $client->id)
            ->where('slug', $pageSlug)
            ->published()
            ->with('theme')
            ->firstOrFail();

        // Track page view
        $this->trackPageView($page, request());

        return view('public.page', [
            'client' => $client,
            'page' => $page,
            'theme' => $page->getActiveTheme(),
        ]);
    }

    /**
     * Track page view for analytics
     */
    private function trackPageView(Page $page, Request $request)
    {
        try {
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            // Get or create session ID
            $sessionId = $request->session()->getId();

            // Check if this is a unique visitor
            $isUniqueVisitor = !PageView::where('page_id', $page->id)
                ->where('session_id', $sessionId)
                ->exists();

            // Detect device type
            $deviceType = 'desktop';
            if ($agent->isMobile()) {
                $deviceType = 'mobile';
            } elseif ($agent->isTablet()) {
                $deviceType = 'tablet';
            }

            // Get referrer information
            $referrer = $request->headers->get('referer');
            $referrerDomain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

            // Create page view record
            PageView::create([
                'page_id' => $page->id,
                'client_id' => $page->client_id,
                'visitor_ip' => $request->ip(),
                'session_id' => $sessionId,
                'user_agent' => $request->userAgent(),
                'device_type' => $deviceType,
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'referrer' => $referrer,
                'referrer_domain' => $referrerDomain,
                'is_unique_visitor' => $isUniqueVisitor,
                'viewed_at' => now(),
            ]);

            // Increment page view count
            $page->incrementViews();

        } catch (\Exception $e) {
            // Log error but don't break page display
            Log::error('Failed to track page view: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get page data (for AJAX requests)
     */
    public function getPageData($clientSlug, $pageSlug)
    {
        $client = Client::where('slug', $clientSlug)->firstOrFail();

        $page = Page::where('client_id', $client->id)
            ->where('slug', $pageSlug)
            ->published()
            ->with('theme')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'images' => $page->images,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                ],
                'theme' => [
                    'name' => $page->getActiveTheme()->name,
                    'colors' => $page->getActiveTheme()->getColorPalette(),
                    'css_variables' => $page->getActiveTheme()->getCssVariables(),
                ],
                'client' => [
                    'name' => $client->name,
                    'company_name' => $client->company_name,
                ]
            ]
        ]);
    }

    /**
     * Get all published pages for a client
     */
    public function clientPages($clientSlug)
    {
        $client = Client::where('slug', $clientSlug)->firstOrFail();

        $pages = Page::where('client_id', $client->id)
            ->published()
            ->select('id', 'title', 'slug', 'meta_description', 'published_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }
}
