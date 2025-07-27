<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\FeaturePolicy;
use App\Policies\PlanPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
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
        // 'admin' yetkisini tanımla
        Gate::define('admin', function (User $user) {
            // Kullanıcının 'role' sütununun 'admin' olup olmadığını kontrol et
            return $user->isAdmin();
        });
    }
}
