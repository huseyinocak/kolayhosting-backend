<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    /**
     * POST /api/v1/admin/import/plans
     * Body: { records: [ { name, provider_id?, provider_name?, category_id?, category_name?, price?, currency?, renewal_price?, discount_percentage?, features_summary?, link?, affiliate_url?, status?, features?: (ids or names)[] } ] }
     */
    public function importPlans(Request $request)
    {
        // Hem {records: [...]} hem de düz dizi gövdesini destekle
        $raw = $request->all();
        $records = $raw['records'] ?? $raw;

        // 1) Top-level doğrulama (DB şemanızla hizalı kurallar)
        $validator = Validator::make(['records' => $records], [
            'records' => ['required', 'array', 'min:1'],

            'records.*.name' => ['required', 'string', 'max:255'],
            'records.*.provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'records.*.provider_name' => ['nullable', 'string', 'max:255'],
            'records.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'records.*.category_name' => ['nullable', 'string', 'max:255'],

            'records.*.price' => ['nullable', 'numeric', 'min:0'],
            'records.*.currency' => ['nullable', 'string', 'in:TRY,USD,EUR', 'max:3'],
            'records.*.renewal_price' => ['nullable', 'numeric', 'min:0'],
            'records.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'records.*.features_summary' => ['nullable', 'string'],

            // DB kolonları varchar(255) — 255 ile sınırla
            'records.*.link' => ['nullable', 'url', 'max:255'],
            'records.*.affiliate_url' => ['nullable', 'url', 'max:255'],

            // PlanStatus enum’unuz: active|inactive|pending
            'records.*.status' => ['nullable', 'in:active,inactive,pending'],

            // Özellikler: id/value veya sadece id veya string isim desteklenir
            'records.*.features' => ['nullable', 'array'],
            'records.*.features.*' => ['nullable'],
        ], [
            // İsteğe bağlı: daha okunaklı hata mesajları
            '*.link.url' => 'Geçerli bir bağlantı girin.',
        ]);

        $data = $validator->validate();
        $records = $data['records'];

        $results = [];

        foreach ($records as $idx => $rec) {
            try {
                DB::beginTransaction();

                // 2) Provider / Category’yi isimlerden çöz (id yoksa)
                if (empty($rec['provider_id']) && !empty($rec['provider_name'])) {
                    $provider = Provider::firstOrCreate(
                        ['name' => $rec['provider_name']],
                        ['slug' => Str::slug($rec['provider_name'])]
                    );
                    $rec['provider_id'] = $provider->id;
                }

                if (empty($rec['category_id']) && !empty($rec['category_name'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $rec['category_name']],
                        ['slug' => Str::slug($rec['category_name'])]
                    );
                    $rec['category_id'] = $category->id;
                }

                // DB’de NOT NULL olan foreign key’ler boş bırakılamaz
                if (empty($rec['provider_id']) || empty($rec['category_id'])) {
                    throw ValidationException::withMessages([
                        'provider_id' => ['provider_id zorunludur'],
                        'category_id' => ['category_id zorunludur'],
                    ]);
                }

                // 3) Linkleri DB sınırına uydur (utm/gclid temizle + 255’e kısalt)
                foreach (['link', 'affiliate_url'] as $urlKey) {
                    if (!empty($rec[$urlKey])) {
                        $clean = (string) Str::of($rec[$urlKey])->before('?');
                        if (strlen($clean) > 255) {
                            $clean = substr($clean, 0, 255);
                        }
                        $rec[$urlKey] = $clean;
                    }
                }

                // 4) Varsayılanlar / normalize
                $payload = [
                    'provider_id' => (int) $rec['provider_id'],
                    'category_id' => (int) $rec['category_id'],
                    'name' => $rec['name'],
                    'slug' => Str::slug($rec['slug'] ?? $rec['name']),
                    'price' => isset($rec['price']) ? (float) $rec['price'] : 0.0,
                    'currency' => strtoupper($rec['currency'] ?? 'TRY'),
                    'renewal_price' => isset($rec['renewal_price']) ? (float) $rec['renewal_price'] : null,
                    'discount_percentage' => isset($rec['discount_percentage']) ? (float) $rec['discount_percentage'] : null,
                    'features_summary' => $rec['features_summary'] ?? null,
                    'link' => $rec['link'] ?? null,
                    'affiliate_url' => $rec['affiliate_url'] ?? null,
                    'status' => $rec['status'] ?? 'active',
                ];

                // 5) Upsert: (name, provider_id) eşsiz — tekrar import idempotent olsun
                $plan = Plan::updateOrCreate(
                    ['name' => $payload['name'], 'provider_id' => $payload['provider_id']],
                    Arr::except($payload, ['name', 'provider_id'])
                );

                // 6) Özellik senkronizasyonu (id/value | id | string name)
                if (!empty($rec['features']) && is_array($rec['features'])) {
                    $sync = [];
                    foreach ($rec['features'] as $f) {
                        $fid = null;
                        $value = '';

                        if (is_array($f) && isset($f['id'])) {
                            $fid = (int) $f['id'];
                            $value = array_key_exists('value', $f)
                                ? (is_bool($f['value']) ? ($f['value'] ? '1' : '0') : (string) $f['value'])
                                : '';
                        } elseif (is_numeric($f)) {
                            $fid = (int) $f;
                            $value = '';
                        } elseif (is_string($f) && strlen($f)) {
                            $feature = Feature::firstOrCreate(
                                ['name' => $f],
                                ['type' => 'text'] // yeni oluşturulursa text tip
                            );
                            $fid = $feature->id;
                            $value = '';
                        }

                        if ($fid) {
                            $sync[$fid] = ['value' => $value];
                        }
                    }

                    if (!empty($sync)) {
                        // Tüm özellik setini gelenle hizala (idempotent)
                        $plan->features()->sync($sync);
                    }
                }

                DB::commit();
                $results[] = ['index' => $idx, 'status' => 'created', 'id' => $plan->id];
            } catch (ValidationException $e) {
                DB::rollBack();
                $results[] = [
                    'index' => $idx,
                    'status' => 'failed',
                    'errors' => $e->errors(),
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $results[] = [
                    'index' => $idx,
                    'status' => 'failed',
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        // En az bir kayıt düştüyse 422, yoksa 201 dön
        $hasFail = collect($results)->contains(fn($r) => $r['status'] === 'failed');
        return response()->json(['results' => $results], $hasFail ? 422 : 201);
    }

    /**
     * POST /api/v1/admin/import/features
     * Body: { records: [ { name, type?, unit? } ] }
     */
    public function importFeatures(Request $request)
    {
        $data = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.name' => 'required|string|max:255',
            'records.*.type' => 'nullable|string|max:50',
            'records.*.unit' => 'nullable|string|max:50',
        ]);

        $created = 0;
        $updated = 0;
        DB::beginTransaction();
        try {
            foreach ($data['records'] as $rec) {
                $payload = Arr::only($rec, ['name', 'type', 'unit']);
                $feature = Feature::updateOrCreate(['name' => $rec['name']], $payload);
                if ($feature->wasRecentlyCreated)
                    $created++;
                else
                    $updated++;
            }
            DB::commit();
            return response()->json(['ok' => true, 'created' => $created, 'updated' => $updated]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import features failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
        }
    }
}
