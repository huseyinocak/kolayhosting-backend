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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade'); // Plan yabancı anahtarı
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade'); // Özellik yabancı anahtarı
            $table->string('value'); // Özelliğin değeri (örn. "Sınırsız", "100 GB", "Evet")
            $table->timestamps(); // created_at ve updated_at sütunları

            // $table->primary(['plan_id', 'feature_id']); // Bileşik Primary Key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
