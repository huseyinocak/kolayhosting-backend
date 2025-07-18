<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProviderPolicy
{
    /**
     * Tüm yetkilendirme kontrolleri öncesinde çalışır.
     * Admin rolündeki kullanıcıların her şeye erişimi olmasını sağlar.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|void
     */
    public function before(User $user, string $ability)
    {
        if ($user->role === UserRole::ADMIN) {
            return true; // Admin her şeye erişebilir
        }
    }

    /**
     * Kullanıcının herhangi bir sağlayıcıyı görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar sağlayıcıları listeleyebilir.
        return true;
    }

    /**
     * Kullanıcının belirli bir sağlayıcıyı görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, Provider $provider): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar belirli bir sağlayıcıyı görüntüleyebilir.
        return true;
    }

    /**
     * Kullanıcının bir sağlayıcı oluşturup oluşturamayacağını belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Sadece adminler sağlayıcı oluşturabilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının bir sağlayıcıyı güncelleyip güncelleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Provider $provider): bool
    {
        // Sadece adminler sağlayıcıları güncelleyebilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının bir sağlayıcıyı silip silemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Provider $provider): bool
    {
        // Sadece adminler sağlayıcıları silebilir.
        return $user->role === UserRole::ADMIN;
    }
}
