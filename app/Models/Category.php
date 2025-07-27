<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Category Modeli
 *
 * Bu model, karşılaştırılan ürün veya hizmet kategorilerini temsil eder.
 */
class Category extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'categories';

    /*
     * Modeling the categories table
     * This model represents the categories in the application.
     * It can be extended with relationships, scopes, and other methods as needed.
     */
    protected $fillable = ['name', 'slug', 'description'];
    /**
     * Kategoriye ait planları tanımlar.
     *
     * @return HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Yeni bir kategori oluşturulmadan önce slug'ı otomatik olarak oluşturma
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = STR::slug($category->name);
            }
        });

        // Kategori güncellenirken slug'ı otomatik olarak güncelleme
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

}
