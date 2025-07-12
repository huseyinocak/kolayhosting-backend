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
            $table->id(); // Primary Key, Auto-increment
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade'); // Sağlayıcıya ait yabancı anahtar
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Kategoriye ait yabancı anahtar
            $table->string('name'); // Planın adı
            $table->string('slug')->unique(); // SEO dostu URL için benzersiz slug
            $table->decimal('price', 8, 2); // Planın fiyatı (örn. 999999.99)
            $table->string('currency', 3)->default('USD'); // Fiyatın para birimi (örn. USD, TRY)
            $table->decimal('renewal_price', 8, 2)->nullable(); // Yenileme fiyatı (varsa)
            $table->decimal('discount_percentage', 5, 2)->nullable(); // İndirim yüzdesi (örn. 25.50)
            $table->text('features_summary')->nullable(); // Planın ana özelliklerinin özeti
            $table->string('link'); // Planın doğrudan satın alma veya detay sayfası linki
            $table->enum('status', ['active', 'inactive', 'deprecated'])->default('active'); // Planın durumu
            $table->timestamps(); // created_at ve updated_at sütunları

            // provider_id ve category_id kombinasyonunun benzersizliğini sağlamak için
            $table->unique(['provider_id', 'category_id', 'name']);
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
