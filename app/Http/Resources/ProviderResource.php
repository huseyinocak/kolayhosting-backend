<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'logo_url' => $this->logo_url,
            'website_url' => $this->website_url,
            'description' => $this->description,
            'average_rating' => $this->average_rating,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // İlişkili planları ve incelemeleri dahil etmek isterseniz (isteğe bağlı, performans düşünülmeli)
            'plans' => PlanResource::collection($this->whenLoaded('plans')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
