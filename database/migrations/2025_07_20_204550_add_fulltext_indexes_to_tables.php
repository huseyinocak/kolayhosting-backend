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
        // Plans tablosuna FULLTEXT indeks ekle
        Schema::table('plans', function (Blueprint $table) {
            // 'name' ve 'features_summary' sütunlarının varlığını kontrol et
            if (Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'features_summary')) {
                // Eğer zaten bir fulltext indeksi varsa, önce onu kaldırın (opsiyonel, hata verirse)
                // $table->dropIndex(['name', 'features_summary']);
                $table->fullText(['name', 'features_summary']);
            }
        });

        // Providers tablosuna FULLTEXT indeks ekle
        Schema::table('providers', function (Blueprint $table) {
            // 'name' ve 'description' sütunlarının varlığını kontrol et
            if (Schema::hasColumn('providers', 'name') && Schema::hasColumn('providers', 'description')) {
                // $table->dropIndex(['name', 'description']);
                $table->fullText(['name', 'description']);
            }
        });

        // Categories tablosuna FULLTEXT indeks ekle
        Schema::table('categories', function (Blueprint $table) {
            // 'name' ve 'description' sütunlarının varlığını kontrol et
            if (Schema::hasColumn('categories', 'name') && Schema::hasColumn('categories', 'description')) {
                // $table->dropIndex(['name', 'description']);
                $table->fullText(['name', 'description']);
            }
        });

        // Features tablosuna FULLTEXT indeks ekle
        Schema::table('features', function (Blueprint $table) {
            // 'name' sütununun varlığını kontrol et
            if (Schema::hasColumn('features', 'name')) {
                // $table->dropIndex(['name']);
                $table->fullText('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'features_summary')) {
                $table->dropIndex(['name', 'features_summary']);
            }
        });

        Schema::table('providers', function (Blueprint $table) {
            if (Schema::hasColumn('providers', 'name') && Schema::hasColumn('providers', 'description')) {
                $table->dropIndex(['name', 'description']);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'name') && Schema::hasColumn('categories', 'description')) {
                $table->dropIndex(['name', 'description']);
            }
        });

        Schema::table('features', function (Blueprint $table) {
            if (Schema::hasColumn('features', 'name')) {
                $table->dropIndex(['name']);
            }
        });
    }
};
