<?php

namespace App\Policies;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FeaturePolicy
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
        if ($user->role === 'admin') {
            return true; // Admin her şeye erişebilir
        }
    }

    /**
     * Kullanıcının herhangi bir özelliği görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar özellikleri listeleyebilir.
        return true;
    }

    /**
     * Kullanıcının belirli bir özelliği görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Feature $feature): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar belirli bir özelliği görüntüleyebilir.
        return true;
    }

    /**
     * Kullanıcının bir özellik oluşturup oluşturamayacağını belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Sadece adminler özellik oluşturabilir.
        return $user->role === 'admin';
    }

   /**
     * Kullanıcının bir özelliği güncelleyip güncelleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Feature $feature): bool
    {
        // Sadece adminler özellikleri güncelleyebilir.
        return $user->role === 'admin';
    }

    /**
     * Kullanıcının bir özelliği silip silemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Feature $feature): bool
    {
        // Sadece adminler özellikleri silebilir.
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Feature $feature): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Feature $feature): bool
    {
        return false;
    }
}
