<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'plan_id' => $this->plan_id,
            'user_name' => $this->user_name,
            'rating' => $this->rating,
            'title' => $this->title,
            'content' => $this->content,
            'published_at' => $this->published_at ? $this->published_at->toDateTimeString() : null,
            'is_approved' => (bool) $this->is_approved,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // İlişkili modelleri dahil et
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
