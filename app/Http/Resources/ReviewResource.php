<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role === 'admin';
        $isOwner = $user && $user->id === $this->user_id;

        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'plan_id' => $this->plan_id,
            'user_name' => $this->user_name,
            'rating' => $this->rating,
            'title' => $this->title,
            // İçeriği sadece admin veya incelemenin sahibi onaylanmamışsa görebilir.
            // Eğer onaylanmışsa herkes görebilir.
            'content' => $this->when($this->is_approved || $isAdmin || $isOwner, $this->content),
            'published_at' => $this->published_at ? $this->published_at->toDateTimeString() : null,
            // is_approved alanını sadece adminler görebilir
            'is_approved' => $this->when($isAdmin, (bool) $this->is_approved),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // İlişkili modelleri dahil et
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            // Kullanıcı bilgisini sadece adminler veya incelemenin sahibi görebilir
            'user' => new UserResource($this->whenLoaded('user') && ($isAdmin || $isOwner), $this->user),
        ];
    }
}
