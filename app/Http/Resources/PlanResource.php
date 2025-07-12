<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'currency' => $this->currency,
            'renewal_price' => $this->renewal_price,
            'discount_percentage' => $this->discount_percentage,
            'features_summary' => $this->features_summary,
            'link' => $this->link,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // İlişkili modelleri dahil et
            'category' => new CategoryResource($this->whenLoaded('category')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            // Plan özellikleri pivot tablosu ile birlikte
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            // İlişkili incelemeler
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
