<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug'); // name ve provider_id kombinasyonu ile benzersiz olacak
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('renewal_price', 8, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable(); // %99.99'a kadar
            $table->text('features_summary')->nullable();
            $table->string('link')->nullable();
            $table->string('status')->default('pending'); // App\Enums\PlanStatus'a göre
            $table->string('affiliate_url')->nullable();
            $table->timestamps();

            // name ve provider_id kombinasyonunun benzersizliğini sağlar
            $table->unique(['name', 'provider_id']);
            // slug ve provider_id kombinasyonunun benzersizliğini sağlar
            $table->unique(['slug', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
