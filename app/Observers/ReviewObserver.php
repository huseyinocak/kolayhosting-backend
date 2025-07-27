<?php

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     * Bir inceleme oluşturulduğunda çalışır.
     * @param  \App\Models\Review  $review
     * @return void
     */
    public function created(Review $review)
    {
        // Eğer inceleme bir sağlayıcıya aitse ve onaylandıysa, sağlayıcının ortalama derecelendirmesini güncelle
        if ($review->provider && $review->status === ReviewStatus::APPROVED) {
            $review->provider->updateAverageRating();
        }
        // Eğer inceleme bir plana aitse ve onaylandıysa, planın ortalama derecelendirmesini güncelle
        if ($review->plan && $review->status === ReviewStatus::APPROVED) {
            $review->plan->updateAverageRating();
        }
    }

    /**
     * Handle the Review "updated" event.
     * Bir inceleme güncellendiğinde çalışır.
     */
    public function updated(Review $review)
    {
        // Eğer incelemenin durumu veya derecelendirmesi değiştiyse, ilgili sağlayıcı ve planın ortalama derecelendirmesini güncelle
        if ($review->isDirty('status') || $review->isDirty('rating')) {
            if ($review->provider) {
                $review->provider->updateAverageRating();
            }
            if ($review->plan) {
                $review->plan->updateAverageRating();
            }
        }
    }

    /**
     * Handle the Review "deleted" event.
     * Bir inceleme silindiğinde çalışır.
     */
    public function deleted(Review $review)
    {
        // Eğer inceleme bir sağlayıcıya aitse, sağlayıcının ortalama derecelendirmesini güncelle
        if ($review->provider) {
            $review->provider->updateAverageRating();
        }
        // Eğer inceleme bir plana aitse, planın ortalama derecelendirmesini güncelle
        if ($review->plan) {
            $review->plan->updateAverageRating();
        }
    }

    /**
     * Handle the Review "restored" event.
     * Bir inceleme geri yüklendiğinde çalışır.
     */
    public function restored(Review $review)
    {
        // Eğer inceleme bir sağlayıcıya aitse ve onaylandıysa, sağlayıcının ortalama derecelendirmesini güncelle
        if ($review->provider) {
            $review->provider->updateAverageRating();
        }
        // Eğer inceleme bir plana aitse ve onaylandıysa, planın ortalama derecelendirmesini güncelle
        if ($review->plan && $review->status === ReviewStatus::APPROVED) {
            $review->plan->updateAverageRating();
        }
    }
    /**
     * Handle the Review "forceDeleted" event.
     * Bir inceleme kalıcı olarak silindiğinde çalışır.
     */
    public function forceDeleted(Review $review)
    {
        // Eğer inceleme bir sağlayıcıya aitse, sağlayıcının ortalama derecelendirmesini güncelle
        if ($review->provider) {
            $review->provider->updateAverageRating();
        }
        // Eğer inceleme bir plana aitse, planın ortalama derecelendirmesini güncelle
        if ($review->plan) {
            $review->plan->updateAverageRating();
        }
    }
}
