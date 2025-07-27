<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Provider Modeli
 *
 * Bu model, hosting sağlayıcılarını temsil eder.
 */
class Provider extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'providers';

    /**
     * Toplu atama yapılabilen sütunlar.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'website_url',
        'description',
        'average_rating',
        'affiliate_url',
        'logo_url',
    ];

    /**
     * Modelin "boot" metodu.
     * Model olaylarını dinlemek için kullanılır.
     */
    protected static function boot()
    {
        parent::boot();

        // Yeni bir sağlayıcı oluşturulmadan önce slug'ı otomatik olarak oluştur
        static::creating(function ($provider) {
            $provider->slug = Str::slug($provider->name);
        });

        // Bir sağlayıcı güncellenmeden önce isim değiştiyse slug'ı güncelle
        static::updating(function ($provider) {
            if ($provider->isDirty('name')) {
                $provider->slug = Str::slug($provider->name);
            }
        });
    }

    /**
     * Benzersiz bir slug oluşturur.
     *
     * @param string $name
     * @return string
     */
    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        // Aynı slug'a sahip başka bir sağlayıcı olup olmadığını kontrol et
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Sağlayıcıya ait planları tanımlar.
     *
     * @return HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Sağlayıcıya ait incelemeleri tanımlar.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
