<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);


        $this->call([
            CategorySeeder::class,
            ProviderSeeder::class,
            FeatureSeeder::class,
            PlanSeeder::class,
            PlanFeatureSeeder::class,
            ReviewSeeder::class,
            // Uncomment the following line if you have a UserSeeder
            // UserSeeder::class,
            // Uncomment the following line if you have a ReviewSeeder
            // Add other seeders here as needed
        ]);


    }
}
