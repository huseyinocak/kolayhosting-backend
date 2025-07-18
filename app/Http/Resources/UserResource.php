<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
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

        // Eğer resource null ise (örneğin user_id null olduğunda), boş bir dizi döndür.
        // Bu, null resource üzerinde özelliklere erişmeye çalışmaktan kaynaklanan 500 hatasını engeller.
        if (is_null($this->resource)) {
            return [];
        }

        // Yetkilendirme mantığı ReviewResource'a taşındığı için bu bloklar kaldırılabilir
        // $user = Auth::user();
        // $isAdmin = $user && $user->role === 'admin';
        // $isSelf = $user && $user->id === $this->id;



        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Rol bilgisini sadece adminler veya kendi kullanıcısı görebilir
            // Bu kısım UserResource'un doğrudan çağrıldığı yerlerde Auth kontrolü gerektirebilir.
            // Ancak ReviewResource içinden çağrıldığında, yetkilendirme ReviewResource'da yapıldığı için
            // burada koşulsuz olarak 'role' dönebilir veya tamamen kaldırılabilir.
            // Şimdilik, mevcut mantığı koruyarak bırakıyorum, ancak daha temiz bir tasarım için düşünülebilir.
            'role' => $this->when(Auth::check() && (Auth::user()->role === UserRole::ADMIN || Auth::user()->id === $this->id), $this->role),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
