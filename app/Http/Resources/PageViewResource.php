<?php

namespace App\Http\Resources;
use App\Models\Theme;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'visitor_info' => [
                'ip' => $this->visitor_ip,
                'device_type' => $this->device_type,
                'browser' => $this->browser,
                'platform' => $this->platform,
            ],
            'location' => [
                'country' => $this->country,
                'city' => $this->city,
            ],
            'referrer' => [
                'url' => $this->referrer,
                'domain' => $this->referrer_domain,
            ],
            'metrics' => [
                'time_on_page' => $this->time_on_page,
                'is_unique_visitor' => $this->is_unique_visitor,
                'is_bounce' => $this->is_bounce,
            ],
            'viewed_at' => $this->viewed_at?->toDateTimeString(),
            'page' => new PageResource($this->whenLoaded('page')),
        ];
    }
}
