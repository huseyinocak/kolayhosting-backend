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
        Schema::table('reviews', function (Blueprint $table) {
            // user_id sütununu ekle ve users tablosuna foreign key olarak bağla.
            // Nullable yapıyoruz çünkü mevcut seed verilerinde user_id olmayabilir.
            // Eğer her incelemenin bir kullanıcıya ait olması zorunluysa nullable'ı kaldırın.
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
