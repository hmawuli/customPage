<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageVersionResource;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Http\Request;

class PageVersionController extends Controller
{
    /**
     * Get all versions for a page
     */
    public function index(Request $request, $pageId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $versions = PageVersion::where('page_id', $pageId)
            ->with(['theme'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => PageVersionResource::collection($versions)
        ], 200);
    }

    /**
     * Get a specific version
     */
    public function show(Request $request, $pageId, $versionId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $version = PageVersion::where('page_id', $pageId)
            ->where('id', $versionId)
            ->with(['theme'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new PageVersionResource($version)
        ], 200);
    }

    /**
     * Restore a specific version
     */
    public function restore(Request $request, $pageId, $versionId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $version = PageVersion::where('page_id', $pageId)
            ->where('id', $versionId)
            ->firstOrFail();

        try {
            $version->restore();

            return response()->json([
                'success' => true,
                'message' => 'Version restored successfully',
                'data' => [
                    'version' => new PageVersionResource($version),
                    'page' => $page->fresh()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Version restore failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare two versions
     */
    public function compare(Request $request, $pageId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $version1Id = $request->input('version1');
        $version2Id = $request->input('version2');

        $version1 = PageVersion::where('page_id', $pageId)
            ->where('id', $version1Id)
            ->firstOrFail();

        $version2 = PageVersion::where('page_id', $pageId)
            ->where('id', $version2Id)
            ->firstOrFail();

        $comparison = [
            'version1' => new PageVersionResource($version1),
            'version2' => new PageVersionResource($version2),
            'differences' => $this->compareVersions($version1, $version2)
        ];

        return response()->json([
            'success' => true,
            'data' => $comparison
        ], 200);
    }

    /**
     * Delete a version
     */
    public function destroy(Request $request, $pageId, $versionId)
    {
        $page = Page::findOrFail($pageId);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $version = PageVersion::where('page_id', $pageId)
            ->where('id', $versionId)
            ->firstOrFail();

        // Don't allow deleting the latest version
        $latestVersion = PageVersion::where('page_id', $pageId)
            ->latest()
            ->first();

        if ($version->id === $latestVersion->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the latest version'
            ], 400);
        }

        $version->delete();

        return response()->json([
            'success' => true,
            'message' => 'Version deleted successfully'
        ], 200);
    }

    /**
     * Compare two versions and return differences
     */
    private function compareVersions($version1, $version2)
    {
        $differences = [];

        // Compare title
        if ($version1->title !== $version2->title) {
            $differences['title'] = [
                'old' => $version1->title,
                'new' => $version2->title,
            ];
        }

        // Compare content
        $contentDiff = $this->arrayDiff($version1->content, $version2->content);
        if (!empty($contentDiff)) {
            $differences['content'] = $contentDiff;
        }

        // Compare images
        $imagesDiff = $this->arrayDiff($version1->images ?? [], $version2->images ?? []);
        if (!empty($imagesDiff)) {
            $differences['images'] = $imagesDiff;
        }

        // Compare theme
        if ($version1->theme_id !== $version2->theme_id) {
            $differences['theme'] = [
                'old' => $version1->theme?->name ?? 'None',
                'new' => $version2->theme?->name ?? 'None',
            ];
        }

        return $differences;
    }

    /**
     * Calculate array differences
     */
    private function arrayDiff($array1, $array2)
    {
        $diff = [];

        foreach ($array1 as $key => $value) {
            if (!isset($array2[$key]) || $array2[$key] !== $value) {
                $diff[$key] = [
                    'old' => $value,
                    'new' => $array2[$key] ?? null,
                ];
            }
        }

        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key]) && !isset($diff[$key])) {
                $diff[$key] = [
                    'old' => null,
                    'new' => $value,
                ];
            }
        }

        return $diff;
    }
}
