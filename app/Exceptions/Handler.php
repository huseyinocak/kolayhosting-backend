<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * Raporlanmaması gereken istisna türlerinin listesi.
     *
     * @var array<int, class-string>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Giriş doğrulaması için yeniden yönlendirilmemesi gereken URI'lerin listesi.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Uygulama için istisna işleme geri çağırmalarını kaydet.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Kimlik Doğrulama Hatası (401) için özel JSON yanıtı
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Kimlik doğrulama başarısız. Geçerli bir token sağlamanız gerekiyor.',
                    'status' => 401,
                ], 401);
            }
        });

        // Yetkilendirme Hatası (403) için özel JSON yanıtı
        // Hem AuthorizationException hem de AccessDeniedHttpException'ı yakalarız.
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                    'status' => 403,
                ], 403);
            }
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                    'status' => 403,
                ], 403);
            }
        });

        // Kaynak Bulunamadı Hatası (404) için özel JSON yanıtı
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'İstenen kaynak bulunamadı.',
                    'status' => 404,
                ], 404);
            }
        });

        // Metot İzin Verilmedi Hatası (405) için özel JSON yanıtı
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Bu rota için izin verilmeyen HTTP metodu.',
                    'status' => 405,
                ], 405);
            }
        });

        // Doğrulama Hatası (422) için özel JSON yanıtı
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Doğrulama hatası.',
                    'errors' => $e->errors(),
                    'status' => 422,
                ], 422);
            }
        });

        // Diğer tüm istisnalar için genel 500 Internal Server Error
        // renderable callback'ler tarafından işlenmeyen diğer tüm istisnalar için.
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                $message = config('app.debug') ? $e->getMessage() : 'Beklenmeyen bir hata oluştu.';

                $statusCode = 500;
                if ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                }

                return new JsonResponse([
                    'message' => $message . $e->getMessage(),
                    'status' => $statusCode,
                ], $statusCode);
            }
        });
    }

    /**
     * İstisnayı bir HTTP yanıtına dönüştür.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Tüm özel hata işleme mantığı register() metodundaki renderable callback'lerine taşındı.
        // Bu metod artık sadece Laravel'in varsayılan işleyicisini çağırır.
        return parent::render($request, $e);
    }
}
