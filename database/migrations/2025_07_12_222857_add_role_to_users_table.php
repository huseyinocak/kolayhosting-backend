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
            // 'role' sütununu ekle. Varsayılan olarak 'user' rolünü ata.
            // Bu sütun 'admin', 'user' gibi değerler alabilir.
            // admin:           Her şeyi yönetir
            // manager:         Site'nin çoğu yönünü yönetir
            // editor:          İçerik planlama ve yönetimi yapar
            // author:          Önemli içerikler yazar
            // contributors:    Sınırlı haklara sahip yazarlar
            // moderator:       Kullanıcı içeriğini denetler
            // member:          Özel kullanıcı erişimi
            // subscriber:      Abone olan kullanıcı
            // user:            Ortalama kullanıcı
            $table->enum('role', ['admin', 'manager', 'editor', 'author', 'contributors', 'moderator', 'member', 'subscriber', 'user'])->default('user')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'role' sütununu geri alırken sil.
            $table->dropColumn('role');
        });
    }
};
