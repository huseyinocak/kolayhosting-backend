<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    /**
     * Tüm kullanıcıları listele.
     * Sadece adminler erişebilir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $query = User::query();

        // Arama (isteğe bağlı)
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sıralama (isteğe bağlı)
        if ($request->has('sort_by') && $request->input('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc'); // Varsayılan olarak en yenileri başta
        }

        // Sayfalama
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Yeni bir kullanıcı oluştur.
     * Sadece adminler erişebilir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role' => ['sometimes', 'required', 'string', Rule::in(['user', 'admin'])], // Rol kısıtlaması
                'avatar_url' => 'nullable|url',
                'is_onboarded' => 'boolean',
                'is_premium' => 'boolean',
            ]);

            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            return response()->json(['message' => 'Kullanıcı başarıyla oluşturuldu.', 'user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (Exception $e) {
            Log::error('Kullanıcı oluşturulurken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Kullanıcı oluşturulurken bir hata oluştu.', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Belirli bir kullanıcıyı göster.
     * Sadece adminler erişebilir.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Belirli bir kullanıcıyı güncelle.
     * Sadece adminler erişebilir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id), // Kendi e-postasını göz ardı et
                ],
                'password' => 'sometimes|nullable|string|min(6)|confirmed', // 'confirmed' kuralı password_confirmation'ı bekler
                'role' => ['sometimes', 'required', 'string', Rule::in(['user', 'admin'])], // Rol kısıtlaması
                'avatar_url' => 'sometimes|nullable|url', // Avatar URL'si eklendi
                'is_onboarded' => 'sometimes|boolean',
                'is_premium' => 'sometimes|boolean',
            ]);

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

            return response()->json(['message' => 'Kullanıcı başarıyla güncellendi.', 'user' => $user]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (Exception $e) {
            Log::error('Kullanıcı güncellenirken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Kullanıcı güncellenirken bir hata oluştu.', 'error' => $e->getMessage()], 500);
        }


    }

    /**
     * Belirli bir kullanıcıyı sil.
     * Sadece adminler erişebilir.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {

        // Kendi kendini silmeyi engelle (opsiyonel ama önerilir)
        if (Auth::id() === $user->id) { // auth()->user()->id yerine Auth::id() kullanıldı
            return response()->json(['message' => 'Kendi hesabınızı silemezsiniz.'], 403);
        }
        try {
            $user->delete();

            return response()->json(['message' => 'Kullanıcı başarıyla silindi.']);
        } catch (Exception $e) {
            Log::error('Kullanıcı silinirken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Kullanıcı silinirken bir hata oluştu.', 'error' => $e->getMessage()], 500);
        }

    }

    public function getStats(): JsonResponse
    {
        $totalUsers = User::count();
        $totalProviders = Provider::count();
        $totalPlans = Plan::count();
        $pendingReviews = Review::where('status', 'pending')->count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_providers' => $totalProviders,
            'total_plans' => $totalPlans,
            'pending_reviews' => $pendingReviews,
        ]);
    }
}
