<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
