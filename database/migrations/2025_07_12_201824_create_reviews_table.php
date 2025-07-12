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
            $table->id(); // Primary Key, Auto-increment
            $table->foreignId('provider_id')->nullable()->constrained('providers')->onDelete('set null'); // Sağlayıcıya ait yabancı anahtar, null olabilir
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null'); // Plana ait yabancı anahtar, null olabilir
            $table->string('user_name')->nullable(); // İncelemeyi yapan kullanıcının adı
            $table->unsignedTinyInteger('rating'); // 1-5 arası derecelendirme (0-255 arası değerler için)
            $table->string('title')->nullable(); // İncelemenin başlığı
            $table->text('content'); // İncelemenin tam metni
            $table->timestamp('published_at')->nullable(); // İncelemenin yayınlanma tarihi
            $table->boolean('is_approved')->default(false); // İncelemenin yayınlanıp yayınlanmadığı
            $table->timestamps(); // created_at ve updated_at sütunları

            // Bir incelemenin ya sağlayıcıya ya da plana ait olmasını sağlamak için (veya her ikisine)
            // check kısıtlaması Laravel migration'larında doğrudan desteklenmez,
            // bu logic uygulama seviyesinde veya veritabanı trigger'ı ile yönetilebilir.
            // Örneğin: $table->check('provider_id IS NOT NULL OR plan_id IS NOT NULL');
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
