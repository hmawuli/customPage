<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display a listing of pages
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Page::with(['client', 'theme'])
            ->when(!$request->user()->isAdmin(), function ($q) use ($request) {
                // Clients can only see their own pages
                $q->where('client_id', $request->user()->id);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            })
            ->latest();

        $pages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PageResource::collection($pages),
            'meta' => [
                'total' => $pages->total(),
                'per_page' => $pages->perPage(),
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
            ]
        ], 200);
    }

    /**
     * Store a newly created page
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'content' => 'required|array',
            'images' => 'nullable|array',
            'theme_id' => 'nullable|exists:themes,id',
            'status' => 'sometimes|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $page = Page::create([
                'client_id' => $request->user()->id,
                'title' => $request->title,
                'slug' => $request->slug,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'content' => $request->content,
                'images' => $request->images,
                'theme_id' => $request->theme_id,
                'status' => $request->status ?? 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Page created successfully',
                'data' => new PageResource($page->load(['client', 'theme']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Page creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified page
     */
    public function show(Request $request, $id)
    {
        $page = Page::with(['client', 'theme', 'versions'])->findOrFail($id);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PageResource($page)
        ], 200);
    }

    /**
     * Update the specified page
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:pages,slug,' . $id,
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'content' => 'sometimes|array',
            'images' => 'nullable|array',
            'theme_id' => 'nullable|exists:themes,id',
            'status' => 'sometimes|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $page->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully',
                'data' => new PageResource($page->load(['client', 'theme']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Page update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified page
     */
    public function destroy(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Delete associated images
        if ($page->images) {
            foreach ($page->images as $image) {
                if (isset($image['path'])) {
                    Storage::disk('public')->delete($image['path']);
                }
            }
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully'
        ], 200);
    }

    /**
     * Publish a page
     */
    public function publish(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $page->publish();

        return response()->json([
            'success' => true,
            'message' => 'Page published successfully',
            'data' => new PageResource($page)
        ], 200);
    }

    /**
     * Unpublish a page
     */
    public function unpublish(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        // Check authorization
        if (!$request->user()->isAdmin() && $page->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $page->unpublish();

        return response()->json([
            'success' => true,
            'message' => 'Page unpublished successfully',
            'data' => new PageResource($page)
        ], 200);
    }

    /**
     * Upload image for page
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'sometimes|string|in:background,logo,content',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');
            $type = $request->input('type', 'content');

            $path = $image->store("pages/{$request->user()->id}/{$type}", 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'type' => $type,
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if (Storage::disk('public')->exists($request->path)) {
                Storage::disk('public')->delete($request->path);

                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
