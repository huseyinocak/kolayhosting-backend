<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL; // URL Facade'ı eklendi
use Illuminate\Support\Facades\Config; // Config Facade'ı eklend


class AuthController extends Controller
{
    /**
     * Yeni bir kullanıcı kaydı yapar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            // Kullanıcıya bir API token'ı oluştur (isteğe bağlı, doğrulama sonrasında yapılabilir)
            // $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Kayıt başarılı! E-posta doğrulama bağlantısı gönderildi.',
                'user' => $user,
                // 'access_token' => $token, // Eğer kayıt sonrası hemen token vermek isterseniz
                'token_type' => 'Bearer',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Kayıt sırasında bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Kayıt sırasında bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kullanıcının giriş yapmasını sağlar ve bir token döndürür.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Kullanıcıyı e-posta ile bul
            $user = User::where('email', $request->email)->first();
            // Kullanıcı yoksa veya şifre yanlışsa
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Geçersiz kimlik bilgileri.'], 401);
            }

            // Eğer kullanıcı e-postasını doğrulamadıysa ve doğrulaması gerekiyorsa
            if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Lütfen e-posta adresinizi doğrulayın.'], 403);
            }
            // Eski token'ları sil ve yeni token oluştur
            $user->tokens()->delete(); // Mevcut tüm token'ları sil
            $token = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh-token'])->plainTextToken;
            return response()->json([
                'message' => 'Giriş başarılı.',
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
            ]);


        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Giriş hatası.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Giriş sırasında bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kullanıcının çıkış yapmasını sağlar ve token'ını iptal eder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Mevcut token'ı sil
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Çıkış başarılı.']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Çıkış sırasında bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kimliği doğrulanmış kullanıcı bilgilerini döndürür.
     * Bu rota 'auth:sanctum' middleware'i ile korunacaktır.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Şifre sıfırlama bağlantısı gönder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => trans($status)]);
        }

        return response()->json(['message' => trans($status)], 500);
    }

    /**
     * Şifreyi sıfırla.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->setRememberToken(Str::random(60)); // Str sınıfını dahil etmeniz gerekebilir
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => trans($status)]);
        }

        return response()->json(['message' => trans($status)], 500);
    }

    /**
     * E-posta doğrulama bağlantısını yeniden gönder.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationLink(Request $request): JsonResponse
    {
        // Kullanıcının kimliği doğrulanmış ve e-postasını doğrulamamış olması gerekir
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-posta adresiniz zaten doğrulanmış.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Doğrulama bağlantısı e-posta adresinize yeniden gönderildi.']);
    }

    /**
     * E-posta adresini doğrula.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request, $id, $hash): RedirectResponse
    {
        Log::info('E-posta doğrulama isteği alındı.', ['user_id_from_url' => $id, 'hash_from_url' => $hash]);
        $user = User::find($id);
        // Kullanıcı yoksa veya hash geçerli değilse
        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Log::warning('Geçersiz e-posta doğrulama linki veya kullanıcı bulunamadı.', ['user_id' => $id, 'hash' => $hash]);
            // Frontend'e hata mesajı ile yönlendirme veya hata yanıtı
            $frontendRedirectUrl = config('app.frontend_url') . '/login?error=invalid_verification_link';
            return redirect()->away($frontendRedirectUrl);
        }
        Log::info('E-posta doğrulama: Kullanıcı bulundu.', ['user_id' => $user->id, 'user_email' => $user->email]);
        if ($user->hasVerifiedEmail()) {
            Log::info('E-posta zaten doğrulanmış.', ['user_id' => $user->id]);
            // Kullanıcıya token verip frontend'e yönlendir
            $token = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh-token'])->plainTextToken;

            // Frontend'e yönlendirme URL'si (başarılı durum)
            $frontendRedirectUrl = config('app.frontend_url') . '/login-success?access_token=' . $token . '&refresh_token=' . $refreshToken;
            return redirect()->away($frontendRedirectUrl);
        }

        // E-postayı doğrula
        try {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                Log::info('E-posta doğrulama: E-posta başarıyla doğrulandı ve Verified olayı tetiklendi.', ['user_id' => $user->id]);

                // Kullanıcıya token verip frontend'e yönlendir
                $token = $user->createToken('auth_token')->plainTextToken;
                $refreshToken = $user->createToken('refresh_token', ['refresh-token'])->plainTextToken;

                $frontendRedirectUrl = config('app.frontend_url') . '/login-success?access_token=' . $token . '&refresh_token=' . $refreshToken;
                return redirect()->away($frontendRedirectUrl);
            } else {
                Log::error('E-posta doğrulama: markEmailAsVerified() false döndürdü.', ['user_id' => $user->id]);
                $frontendRedirectUrl = config('app.frontend_url') . '/login?error=verification_failed';
                return redirect()->away($frontendRedirectUrl);
            }
        } catch (Exception $e) {
            Log::error('E-posta doğrulama sırasında beklenmeyen bir hata oluştu: ' . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            $frontendRedirectUrl = config('app.frontend_url') . '/login?error=verification_failed';
            return redirect()->away($frontendRedirectUrl);
        }
    }
}
