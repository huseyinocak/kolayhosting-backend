<?php

namespace Database\Seeders;

use App\Enums\FeatureType;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = Plan::all();
        $features = Feature::all();

        if ($plans->isEmpty() || $features->isEmpty()) {
            $this->command->info('Plan veya Feature bulunamadı. Lütfen önce PlanSeeder ve FeatureSeeder\'ı çalıştırın.');
            return;
        }

        foreach ($plans as $plan) {
            // Her plana rastgele 3 ila 7 özellik ata
            $planFeatures = $features->random(rand(3, 7));

            foreach ($planFeatures as $feature) {
                $value = null;
                switch ($feature->type) {
                    case FeatureType::NUMERIC:
                        $value = fake()->numberBetween(1, 100) . ' ' . $feature->unit;
                        break;
                    case FeatureType::BOOLEAN:
                        $value = fake()->boolean() ? 'Evet' : 'Hayır';
                        break;
                    case FeatureType::TEXT:
                        $value = fake()->randomElement(['cPanel', 'Plesk', 'DirectAdmin', 'Özel Panel', '7/24 Canlı Destek', 'Telefon Desteği']);
                        break;
                    default:
                        $value = fake()->word();
                        break;
                }

                PlanFeature::firstOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }
}
