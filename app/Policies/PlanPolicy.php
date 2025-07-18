<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlanPolicy
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
     * Kullanıcının herhangi bir planı görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar planları listeleyebilir.
        return true;
    }

    /**
     * Kullanıcının belirli bir planı görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, Plan $plan): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar belirli bir planı görüntüleyebilir.
        return true;
    }
    /**
     * Kullanıcının bir plan oluşturup oluşturamayacağını belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Sadece adminler plan oluşturabilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının bir planı güncelleyip güncelleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Plan $plan): bool
    {
        // Sadece adminler planları güncelleyebilir.
        return $user->role === UserRole::ADMIN;
    }
    /**
     * Kullanıcının bir planı silip silemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Plan $plan): bool
    {
        // Sadece adminler planları silebilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plan $plan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plan $plan): bool
    {
        return false;
    }
}
