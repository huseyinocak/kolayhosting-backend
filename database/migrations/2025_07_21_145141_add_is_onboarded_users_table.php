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
        Schema::table('users', function (Blueprint $table) {
            // Yeni 'status' sütununu ekleyin
            // Varsayılan olarak 'pending' ve null olamaz
            $table->boolean('is_onboarded')->default(false)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Users', function (Blueprint $table) {
            // 'status' sütununu kaldırın
            $table->dropColumn('is_onboarded ');
        });
    }
};
