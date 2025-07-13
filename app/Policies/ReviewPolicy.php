<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
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
     * Kullanıcının herhangi bir incelemeyi görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar incelemeleri listeleyebilir.
        return true;
    }

    /**
     * Kullanıcının belirli bir incelemeyi görüntüleyip görüntüleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Review $review): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar belirli bir incelemeyi görüntüleyebilir.
        return true;
    }

    /**
     * Kullanıcının bir inceleme oluşturup oluşturamayacağını belirle.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Tüm kimliği doğrulanmış kullanıcılar inceleme oluşturabilir.
        return true;
    }

    /**
     * Kullanıcının bir incelemeyi güncelleyip güncelleyemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Review $review): bool
    {
        // Sadece adminler veya incelemeyi oluşturan kullanıcı güncelleyebilir.
        return $user->id === $review->user_id; // Review modelinde user_id sütunu olduğunu varsayıyoruz
    }

    /**
     * Kullanıcının bir incelemeyi silip silemeyeceğini belirle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Review $review): bool
    {
        // Sadece adminler veya incelemeyi oluşturan kullanıcı silebilir.
        return $user->id === $review->user_id; // Review modelinde user_id sütunu olduğunu varsayıyoruz
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }
}
