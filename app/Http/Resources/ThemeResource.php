<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'colors' => [
                'primary' => $this->primary_color,
                'secondary' => $this->secondary_color,
                'accent' => $this->accent_color,
                'text' => $this->text_color,
                'background' => $this->background_color,
                'additional' => $this->additional_colors,
            ],
            'color_palette' => $this->getColorPalette(),
            'css_variables' => $this->getCssVariables(),
            'preview_image' => $this->preview_image ? asset('storage/' . $this->preview_image) : null,
            'is_active' => $this->is_active,
            'usage_count' => $this->usage_count,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

}
