<?php

namespace App\Http\Resources;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'content' => $this->content,
            'images' => $this->images,
            'status' => $this->status,
            'view_count' => $this->view_count,
            'version' => $this->version,
            'published_at' => $this->published_at?->toDateTimeString(),
            'public_url' => $this->getPublicUrl(),
            'is_published' => $this->isPublished(),

            // Relationships
            'client' => new ClientResource($this->whenLoaded('client')),
            'theme' => new ThemeResource($this->whenLoaded('theme')),

            // Analytics (only for detail view)
            'analytics' => $this->when(
                $request->routeIs('page.show'),
                [
                    'total_views' => $this->pageViews()->count(),
                    'unique_visitors' => $this->pageViews()->uniqueVisitors()->count(),
                    'bounce_rate' => PageView::getBounceRate($this->id),
                    'avg_time_on_page' => PageView::getAverageTimeOnPage($this->id),
                ]
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
