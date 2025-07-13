<?php

namespace App\Models;

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
        'is_approved',
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
