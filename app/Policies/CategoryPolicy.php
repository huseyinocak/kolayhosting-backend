<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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
     * Kullanıcının herhangi bir kategoriyi görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar kategorileri listeleyebilir.
        return true;
    }

    /**
     * Kullanıcının belirli bir kategoriyi görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Category $category): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar belirli bir kategoriyi görüntüleyebilir.
        return true;
    }

    /**
     * Kullanıcının bir kategori oluşturup oluşturamayacağını belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Sadece adminler kategori oluşturabilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının bir kategoriyi güncelleyip güncelleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Category $category): bool
    {
        // Sadece adminler kategorileri güncelleyebilir.
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının bir kategoriyi silip silemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Category $category): bool
    {
        // Sadece adminler kategorileri silebilir.
        return $user->role === UserRole::ADMIN;
    }
}
