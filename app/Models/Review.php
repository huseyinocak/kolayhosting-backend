<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Review Modeli
 *
 * Bu model, kullanıcıların veya uzmanların sağlayıcılar veya planlar hakkındaki
 * incelemelerini ve derecelendirmelerini temsil eder.
 */
class Review extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'reviews';

    /**
     * Toplu atama yapılabilen sütunlar.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider_id',
        'plan_id',
        'user_id',
        'user_name',
        'rating',
        'title',
        'content',
        'published_at',
        'status',
    ];

    /**
     * Modelin "boot" metodu.
     * Yeni bir inceleme oluşturulurken varsayılan 'status' değerini ayarlar.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            // Eğer status alanı ayarlanmamışsa, varsayılan olarak 'pending' yap
            if (is_null($review->status)) {
                $review->status = ReviewStatus::PENDING;
            }
        });
    }

    /**
     * Tür dönüşümü yapılması gereken sütunlar.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime', // Bu satırı ekleyin
        'status' => ReviewStatus::class, // 'is_approved' yerine 'status' ve ReviewStatus enum'ına cast edildi
    ];

    /**
     * İncelemenin ait olduğu sağlayıcıyı tanımlar.
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * İncelemenin ait olduğu planı tanımlar.
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * İncelemeyi yapan kullanıcıyı tanımlar.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
