<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Page;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientOwnsPage
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pageId = $request->route('page') ?? $request->route('id');

        if ($pageId) {
            $page = Page::find($pageId);

            if ($page && !$request->user()->isAdmin()) {
                if ($page->client_id !== $request->user()->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to access this page'
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
