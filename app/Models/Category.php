<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

}
