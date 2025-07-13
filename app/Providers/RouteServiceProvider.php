<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Uygulamanızın "home" rotasının sabitleri.
     *
     * Genellikle kullanıcı kimlik doğrulamadan sonra nereye yönlendirilmeleri gerektiğini tanımlar.
     *
     * @var string
     */
    public const HOME = '/home';


    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // İstek sınırlama kurallarını yapılandır.
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Uygulama için istek sınırlama kurallarını yapılandır.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        // Genel API istek sınırlayıcı:
        // Her IP adresi için dakikada 60 istek.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Kimlik doğrulama (login/register) istek sınırlayıcı:
        // Her IP adresi için dakikada 10 istek.
        // Özellikle brute-force saldırılarını önlemek için kullanışlıdır.
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Diğer özel sınırlayıcılar buraya eklenebilir.
        // Örneğin, yorum gönderme veya belirli bir kaynağa yazma işlemleri için daha kısıtlayıcı limitler.
        // RateLimiter::for('reviews-post', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        // });
    }
}
