<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
                'is_onboarded' => false, // Yeni kayıt olan kullanıcı varsayılan olarak onboarding yapılmamış
            ]);

            // Kullanıcıya bir API token'ı oluştur
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Kayıt başarılı!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Sağlanan kimlik bilgileri yanlış.'],
                ]);
            }

            // Mevcut tüm token'ları sil ve yeni bir token oluştur
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Giriş başarılı!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Giriş hatası.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        // Kullanıcıyı döndürürken is_onboarded sütununu açıkça seçiyoruz.
        // Normalde tüm sütunlar döner, ancak bu şekilde daha güvenli olur.
        $user = $request->user()->only(['id', 'name', 'email', 'role', 'created_at', 'updated_at', 'is_onboarded','is_premium']);

        return response()->json(['data' => $user]); // Frontend'deki getUser API'si data.data beklediği için 'data' anahtarı eklendi.
    }

    public function updateIsOnboarded(Request $request)
    {
        $user = $request->user(); // Kimliği doğrulanmış kullanıcıyı al

        $request->validate([
            'is_onboarded' => 'boolean',
            // ... diğer profil alanları için doğrulama
        ]);

        if ($request->has('is_onboarded')) {
            $user->is_onboarded = $request->input('is_onboarded');
        }
        // ... diğer profil alanlarını güncelleme

        $user->save();

        return response()->json(['message' => 'IsOnboarded.', 'user' => $user], 200);
    }
}
