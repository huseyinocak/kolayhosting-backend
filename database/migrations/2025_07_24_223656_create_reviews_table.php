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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('providers')->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Kullanıcı silinirse user_id null olsun
            $table->string('user_name')->nullable(); // Misafir yorumları için
            $table->integer('rating')->unsigned(); // 1-5 arası derecelendirme
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at')->nullable(); // İnceleme onaylandığında dolacak
            $table->string('status')->default('pending'); // App\Enums\ReviewStatus'a göre
            $table->timestamps();

            // // Derecelendirme için kısıt (1-5 arası)
            // $table->check('rating >= 1 AND rating <= 5', 'rating_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
