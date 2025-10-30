<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    /**
     * Display a listing of themes
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $activeOnly = $request->input('active_only', true);

        $query = Theme::query()
            ->when($activeOnly, function ($q) {
                $q->active();
            })
            ->popular();

        if ($request->has('per_page') && $request->per_page === 'all') {
            $themes = $query->get();

            return response()->json([
                'success' => true,
                'data' => ThemeResource::collection($themes)
            ], 200);
        }

        $themes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ThemeResource::collection($themes),
            'meta' => [
                'total' => $themes->total(),
                'per_page' => $themes->perPage(),
                'current_page' => $themes->currentPage(),
                'last_page' => $themes->lastPage(),
            ]
        ], 200);
    }

    /**
     * Store a newly created theme (Admin only)
     */
    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:themes,name|max:255',
            'slug' => 'nullable|string|unique:themes,slug',
            'description' => 'nullable|string',
            'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'additional_colors' => 'nullable|array',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except('preview_image');

            // Handle preview image upload
            if ($request->hasFile('preview_image')) {
                $path = $request->file('preview_image')->store('themes', 'public');
                $data['preview_image'] = $path;
            }

            $theme = Theme::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Theme created successfully',
                'data' => new ThemeResource($theme)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Theme creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified theme
     */
    public function show($id)
    {
        $theme = Theme::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new ThemeResource($theme)
        ], 200);
    }

    /**
     * Update the specified theme (Admin only)
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $theme = Theme::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:themes,name,' . $id . '|max:255',
            'slug' => 'sometimes|string|unique:themes,slug,' . $id,
            'description' => 'nullable|string',
            'primary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'additional_colors' => 'nullable|array',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except('preview_image');

            // Handle preview image upload
            if ($request->hasFile('preview_image')) {
                // Delete old image
                if ($theme->preview_image) {
                    Storage::disk('public')->delete($theme->preview_image);
                }

                $path = $request->file('preview_image')->store('themes', 'public');
                $data['preview_image'] = $path;
            }

            $theme->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully',
                'data' => new ThemeResource($theme)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Theme update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified theme (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $theme = Theme::findOrFail($id);

        // Check if theme is in use
        if ($theme->clients()->count() > 0 || $theme->pages()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete theme that is currently in use'
            ], 400);
        }

        // Delete preview image
        if ($theme->preview_image) {
            Storage::disk('public')->delete($theme->preview_image);
        }

        $theme->delete();

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully'
        ], 200);
    }

    /**
     * Get popular themes
     */
    public function popular()
    {
        $themes = Theme::active()
            ->popular()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ThemeResource::collection($themes)
        ], 200);
    }

    /**
     * Preview theme on a page
     */
    public function preview(Request $request, $themeId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $theme = Theme::findOrFail($themeId);

        return response()->json([
            'success' => true,
            'data' => [
                'theme' => new ThemeResource($theme),
                'preview_html' => $this->generatePreviewHtml($theme, $request->content)
            ]
        ], 200);
    }

    /**
     * Generate preview HTML with theme applied
     */
    private function generatePreviewHtml($theme, $content)
    {
        $cssVariables = $theme->getCssVariables();

        return [
            'css' => $cssVariables,
            'content' => $content,
            'colors' => $theme->getColorPalette()
        ];
    }
}
