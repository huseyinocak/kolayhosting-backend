<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    use HasFactory;

    /**
     * Modelin ilişkili olduğu tablo adı.
     *
     * @var string
     */
    protected $table = 'plan_features';

    /**
     * Toplu atama yapılabilen sütunlar.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'feature_id',
        'value',
    ];

    /**
     * PlanFeature modelinde primary key'in olmaması durumunda.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Primary key sütunlarını tanımlar.
     *
     * @var array<string>
     */
    protected $primaryKey = ['plan_id', 'feature_id'];
}
