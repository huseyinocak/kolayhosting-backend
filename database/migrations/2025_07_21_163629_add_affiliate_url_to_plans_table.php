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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('affiliate_url')->nullable()->after('status'); // veya uygun bir yer
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->string('affiliate_url')->nullable()->after('average_rating'); // veya uygun bir yer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('affiliate_url');
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('affiliate_url');
        });
    }
};
