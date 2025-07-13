<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
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
        $isSelf = $user && $user->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Rol bilgisini sadece adminler veya kendi kullanıcısı görebilir
            'role' => $this->when($isAdmin || $isSelf, $this->role),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
