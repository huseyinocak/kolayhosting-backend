# KolayHosting API: Hosting Karşılaştırma Rehberin!

KolayHosting, farklı hosting sağlayıcılarını ve planlarını karşılaştırmana yarayan süper kullanışlı bir API. Sana en uygun hosting'i bulman için tasarlandı!

## İçindekiler

-   [Bu Proje Ne Ayak?](https://www.google.com/search?q=%23bu-proje-ne-ayak "null")

-   [Neler Yapabiliyor?](https://www.google.com/search?q=%23neler-yapabiliyor "null")

-   [Hangi Teknolojilerle Yapıldı?](https://www.google.com/search?q=%23hangi-teknolojilerle-yap%C4%B1ld%C4%B1 "null")

-   [Nasıl Kurulur?](https://www.google.com/search?q=%23nas%C4%B1l-kurulur "null")

-   [API Uç Noktaları (Yani Nereye İstek Atacağız?)](https://www.google.com/search?q=%23api-u%C3%A7-noktalar%C4%B1-yani-nereye-istek-ataca%C4%9F%C4%B1z "null")

-   [Yetkilendirme (Kim Kimdir?)](https://www.google.com/search?q=%23yetkilendirme-kim-kimdir "null")

-   [Postman Koleksiyonu (Hazır İstekler!)](https://www.google.com/search?q=%23postman-koleksiyonu-haz%C4%B1r-istekler "null")

-   [Katkıda Bulunmak İster Misin?](https://www.google.com/search?q=%23katk%C4%B1da-bulunmak-ister-misin "null")

-   [Lisans (Resmi Kısım)](https://www.google.com/search?q=%23lisans-resmi-k%C4%B1s%C4%B1m "null")

## Bu Proje Ne Ayak?

KolayHosting, hosting planları ve sağlayıcıları için bir veri tabanı ve API sunar. Frontend uygulamalarına veri sağlayarak, hosting arayanlara yardımcı olmayı amaçlarız!

## Neler Yapabiliyor?

-   **Kategorileri Yönet:** Hosting kategorilerini (Web, VPS vb.) CRUD işlemleriyle yönet.

-   **Sağlayıcıları Yönet:** Hosting sağlayıcılarını (Hostinger, Bluehost vb.) CRUD işlemleriyle yönet.

-   **Planları Yönet:** Sağlayıcıların planlarını (fiyat, özellik vb.) CRUD işlemleriyle yönet.

-   **Özellikleri Yönet:** Planlara ait özellikleri (Disk Alanı, SSL vb.) CRUD işlemleriyle tanımla.

-   **İncelemeleri Yönet:** Kullanıcı yorumlarını ve puanlarını yönet.

-   **Veri Filtreleme/Sıralama:** Verileri kolayca filtrele ve sırala.

-   **Rol Tabanlı Yetkilendirme:** Admin ve normal kullanıcı rolleriyle erişimi kontrol et.

-   **API Güvenliği:** Laravel Sanctum ile API token güvenliği.

-   **Standart Yanıtlar:** Tüm API yanıtları tutarlı bir formatta.

## Hangi Teknolojilerle Yapıldı?

-   **Backend:** Laravel 12

-   **Dil:** PHP

-   **Veritabanı:** MySQL

-   **Giriş-Çıkış:** Laravel Sanctum

-   **CORS:** `fruitcake/laravel-cors`

-   **Yetkilendirme:** Laravel Policies

-   **İstek Doğrulama:** Laravel Form Requests

-   **API Yanıtları:** Laravel API Resources

## Nasıl Kurulur?

Projeyi çalıştırmak için:

1.  **Depoyu Klonla:**

    ```
    git clone https://github.com/huseyinocak/kolayhosting-backend.git
    cd kolayhosting

    ```

2.  **Gerekli Paketleri Yükle:**

    ```
    composer install

    ```

3.  **Ayarlar Dosyasını Hazırla:** `.env.example` dosyasını `.env` olarak kopyala ve veritabanı/uygulama ayarlarını düzenle.

    ```
    APP_URL=http://localhost:8000
    DB_DATABASE=kolayhosting_db
    DB_USERNAME=root
    DB_PASSWORD=
    SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1:8000

    ```

4.  **Uygulama Anahtarını Oluştur:**

    ```
    php artisan key:generate

    ```

5.  **Veritabanını Oluştur:**

    ```
    php artisan migrate

    ```

6.  **Örnek Verileri Doldur:**

    ```
    php artisan db:seed

    ```

    (Varsayılan kullanıcılar: `admin@example.com` ve `user@example.com`, şifreleri `password`)

7.  **Sunucuyu Başlat:**

    ```
    php artisan serve

    ```

    API'n artık `http://localhost:8000` adresinde aktif!

## API Uç Noktaları (Yani Nereye İstek Atacağız?)

Tüm API adresleri `/api/v1/` ile başlar. Korumalı rotalar için `Authorization: Bearer <TOKEN>` başlığını kullanmayı unutma!

-   **Giriş-Çıkış (Authentication):**

    -   `POST /api/v1/register`

    -   `POST /api/v1/login`

    -   `POST /api/v1/logout` (Korumalı)

    -   `GET` /api/v1/user (Korumalı)

-   **Kategoriler:** `GET /api/v1/categories`, `GET /api/v1/categories/{id}` (Admin için POST/PUT/DELETE)

-   **Sağlayıcılar:** `GET /api/v1/providers`, `GET /api/v1/providers/{id}` (Admin için POST/PUT/DELETE)

-   **Planlar:** `GET /api/v1/plans`, `GET /api/v1/plans/{id}` (Admin için POST/PUT/DELETE)

-   **Özellikler:** `GET /api/v1/features`, `GET /api/v1/features/{id}` (Admin için POST/PUT/DELETE)

-   **İncelemeler:** `GET /api/v1/reviews`, `GET /api/v1/reviews/{id}` (Kullanıcılar POST/PUT/DELETE kendi incelemelerini, Admin her şeyi)

-   **İlişkili Veriler:**

    -   `GET /api/v1/providers/{provider}/plans`

    -   `GET` /api/v1/categories/{category}/plans

    -   `GET /api/v1/plans/{plan}/features`

    -   `GET /api/v1/providers/{provider}/reviews`

    -   `GET /api/v1/plans/{plan}/reviews`

## Yetkilendirme (Kim Kimdir?)

-   **Admin Rolü:** Tüm CRUD işlemlerini yapabilir.

-   **Normal Kullanıcı Rolü:** Veri okuyabilir ve kendi incelemelerini yönetebilir.

## Postman Koleksiyonu (Hazır İstekler!)

API'yi denemek için Postman koleksiyonunu kullanabilirsin. Postman'e aktarmak için "Import" -> "Raw text" yolunu izle ve tam JSON içeriğini yapıştır. (Tam JSON içeriği için projenin ana deposuna bakabilirsin.)

## Katkıda Bulunmak İster Misin?

Projemize katkı sağlamak istersen, süper olur! Lütfen bir Pull Request atmadan önce mevcut kod yazım kurallarına uymaya çalış.

## Lisans (Resmi Kısım)

Bu proje MIT Lisansı altında. Daha fazla bilgi için `LICENSE` dosyasına bakabilirsin.

**KolayHosting Ekibi**
