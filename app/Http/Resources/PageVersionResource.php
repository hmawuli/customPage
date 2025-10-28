<?php

namespace App\Http\Resources;
use App\Models\Page;
use App\Models\Client;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'version_number' => $this->version_number,
            'formatted_version' => $this->getFormattedVersionNumber(),
            'title' => $this->title,
            'content' => $this->content,
            'images' => $this->images,
            'change_summary' => $this->change_summary,
            'changes' => $this->changes,

            // Relationships
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'page' => new PageResource($this->whenLoaded('page')),
            'client' => new ClientResource($this->whenLoaded('client')),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
