<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
          $table->id(); // Primary Key, Auto-increment
            $table->string('name')->unique(); // Sağlayıcının adı, benzersiz olmalı
            $table->string('slug')->unique(); // SEO dostu URL için benzersiz slug
            $table->string('logo_url')->nullable(); // Sağlayıcı logosunun URL'si
            $table->string('website_url'); // Sağlayıcının web sitesi URL'si
            $table->text('description')->nullable(); // Sağlayıcı hakkında genel açıklama
            $table->decimal('average_rating', 3, 2)->nullable(); // Ortalama derecelendirme (örn. 4.50)
            $table->timestamps(); // created_at ve updated_at sütunları
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
