<?php

namespace App\Models;

use App\Enums\PlanStatus;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Plan Modeli
 *
 * Bu model, her sağlayıcının sunduğu belirli hosting planlarını temsil eder.
 */
class Plan extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'plans';

    /**
     * Toplu atama yapılabilen sütunlar.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'slug',
        'price',
        'currency',
        'renewal_price',
        'discount_percentage',
        'features_summary',
        'link',
        'status',
        'affiliate_url',
    ];


    protected $casts = [
        'status' => PlanStatus::class, // PlanStatus enum olarak tanımlanacak
    ];

    /**
     * Modelin "boot" metodu.
     * Model olaylarını dinlemek için kullanılır.
     */
    protected static function boot()
    {
        parent::boot();

        // Yeni bir plan oluşturulmadan önce slug'ı otomatik olarak oluştur
        static::creating(function ($plan) {
            $plan->slug = Str::slug($plan->name . '-' . $plan->provider_id);
        });

        // Bir plan güncellenmeden önce isim veya sağlayıcı değiştiyse slug'ı güncelle
        static::updating(function ($plan) {
            if ($plan->isDirty('name') || $plan->isDirty('provider_id')) {
                $plan->slug = Str::slug($plan->name . '-' . $plan->provider_id);
            }
        });
    }

    /**
     * Planın ait olduğu sağlayıcıyı tanımlar.
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Planın ait olduğu kategoriyi tanımlar.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Plana ait özellikleri tanımlar (çoktan çoğa ilişki).
     *
     * @return BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('value') // Pivot tablosundaki 'value' sütununu dahil et
            ->withTimestamps(); // Pivot tablosundaki created_at ve updated_at sütunlarını kullan
    }

    /**
     * Plana ait incelemeleri tanımlar.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Planın ortalama derecelendirmesini döndürür.
     *
     * @return float|null
     */
    public function getAverageRatingAttribute(): ?float
    {
        // Yalnızca onaylanmış incelemelerin ortalamasını al
        return $this->reviews()->where('status', ReviewStatus::APPROVED)->avg('rating');
    }
}
