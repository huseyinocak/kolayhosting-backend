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
        Schema::create('categories', function (Blueprint $table) {
             $table->id(); // Primary Key, Auto-increment
            $table->string('name')->unique(); // Kategorinin adı, benzersiz olmalı
            $table->string('slug')->unique(); // SEO dostu URL için benzersiz slug
            $table->text('description')->nullable(); // Kategori hakkında kısa açıklama
            $table->timestamps(); // created_at ve updated_at sütunları
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
