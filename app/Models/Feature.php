<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Feature Modeli
 *
 * Bu model, hosting planlarının sunduğu özellikleri temsil eder.
 */
class Feature extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'features';

    /**
     * Toplu atama yapılabilen sütunlar.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'unit',
        'type',
    ];

    /**
     * Özelliğin ait olduğu planları tanımlar (çoktan çoğa ilişki).
     *
     * @return BelongsToMany
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot('value') // Pivot tablosundaki 'value' sütununu dahil et
            ->withTimestamps(); // Pivot tablosundaki created_at ve updated_at sütunlarını kullan
    }
}
