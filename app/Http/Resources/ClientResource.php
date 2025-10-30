<?php

namespace App\Http\Resources;
use App\Models\PageView;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'slug' => $this->slug,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'pages_count' => $this->when($this->pages, $this->pages->count()),
            'total_views' => $this->when(
                $request->routeIs('client.show'),
                $this->getTotalPageViews()
            ),
            'published_pages_count' => $this->when(
                $request->routeIs('client.show'),
                $this->getPublishedPagesCount()
            ),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
