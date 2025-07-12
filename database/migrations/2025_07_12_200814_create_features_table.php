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
        Schema::create('features', function (Blueprint $table) {
            $table->id(); // Primary Key, Auto-increment
            $table->string('name')->unique(); // Özelliğin adı, benzersiz olmalı
            $table->string('unit')->nullable(); // Özelliğin birimi (örn. GB, Adet)
            $table->enum('type', ['boolean', 'numeric', 'text'])->default('text'); // Özellik tipi
            $table->timestamps(); // created_at ve updated_at sütunları
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
