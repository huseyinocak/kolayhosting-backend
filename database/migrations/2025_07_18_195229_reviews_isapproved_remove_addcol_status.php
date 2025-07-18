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
            // is_approved sütununu kaldırın
            $table->dropColumn('is_approved');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Yeni 'status' sütununu ekleyin
            // Varsayılan olarak 'pending' ve null olamaz
            $table->string('status')->default('pending')->after('published_at');
        });

        // Mevcut verileri güncellemek isterseniz (isteğe bağlı, dikkatli olun!)
        // Eğer is_approved sütununu kaldırmadan önce verileri dönüştürmek isterseniz:
        // Schema::table('reviews', function (Blueprint $table) {
        //     $table->string('status')->after('published_at')->nullable(); // Geçici olarak nullable yapın
        // });
        // DB::table('reviews')->where('is_approved', true)->update(['status' => 'approved']);
        // DB::table('reviews')->where('is_approved', false)->update(['status' => 'pending']); // Veya 'rejected'
        // Schema::table('reviews', function (Blueprint $table) {
        //     $table->dropColumn('is_approved');
        //     $table->string('status')->default('pending')->nullable(false)->change(); // Nullable'ı kaldırın
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // 'status' sütununu kaldırın
            $table->dropColumn('status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // 'is_approved' sütununu geri ekleyin
            $table->boolean('is_approved')->default(false)->after('published_at');
        });
    }
};
