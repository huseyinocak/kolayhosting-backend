<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
use App\Policies\CategoryPolicy;
use App\Policies\FeaturePolicy;
use App\Policies\PlanPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Uygulama için model-policy eşlemeleri.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Provider::class => ProviderPolicy::class,
        Plan::class => PlanPolicy::class,
        Feature::class => FeaturePolicy::class,
        Review::class => ReviewPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
