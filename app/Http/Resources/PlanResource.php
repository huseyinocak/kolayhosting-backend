<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Kullanıcının admin olup olmadığını kontrol eden yardımcı değişken
        // Auth::check() yerine $request->user() kullanmak, middleware olmadan da token varsa kullanıcıyı yakalar.
        $user = $request->user(); // İstekten kimliği doğrulanmış kullanıcıyı al
        $isAdmin = $user && $user->role === UserRole::ADMIN;
        // Eğer kullanıcının rolü bir enum değilse ve string ise:
        // $isAdmin = $user && $user->role === 'admin';


        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug, // Adminler için sluga -admin ekle
            'price' => $this->price,
            'currency' => $this->currency,
            // Sadece adminlerin görmesi gereken alanlar
            'renewal_price' => $this->when($isAdmin, $this->renewal_price),
            'discount_percentage' => $this->when($isAdmin, $this->discount_percentage),
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
