<?php

namespace App\Http\Resources;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
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
        // Kimliği doğrulanmış kullanıcıyı alın
        $user = Auth::user();

        // Kullanıcının admin olup olmadığını kontrol edin
        $isAdmin = $user && $user->role === UserRole::ADMIN;
        // İncelemenin sahibi olup olmadığını kontrol edin
        $isOwner = $user && $user->id === $this->user_id;

        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'plan_id' => $this->plan_id,
            'user_name' => $this->user_name,
            'rating' => $this->rating,
            'title' => $this->title,
            // İçeriği gösterme koşulu:
            // 1. Eğer kullanıcı admin ise (her zaman göster) VEYA
            // 2. İnceleme onaylanmışsa VEYA
            // 3. Kullanıcı bu incelemenin sahibi ise
            // 'content' => $this->when($isAdmin || $isOwner || $this->status === ReviewStatus::APPROVED, $this->content),
            'content' => $this->content,
            'published_at' => $this->published_at ? $this->published_at->toDateTimeString() : null,
            // 'status' alanını her zaman döndür, ancak adminler için tam değeri göster
            // Diğer kullanıcılar için sadece onaylı/reddedilmiş/beklemede bilgisini döndür
            'status' => $this->when(
                $isAdmin || $isOwner, // Adminler ve sahipleri tam durumu görebilir
                $this->status->value, // Enum değerini string olarak döndür
                $this->status->value // Diğerleri için de enum değerini döndür (frontend'in ihtiyacı bu)
            ),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // İlişkili modelleri dahil et
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            // Kullanıcı bilgisini her zaman döndür, UserResource kendi içindeki yetkilendirmeyi yapsın.
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
