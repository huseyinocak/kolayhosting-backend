# Kimlik Doğrulama (Authentication)

KolayHosting API'si, kullanıcı kimlik doğrulaması için [Laravel Sanctum](https://laravel.com/docs/sanctum "null") kullanır. Bu, API token'ları aracılığıyla güvenli ve durumsuz (stateless) bir kimlik doğrulama mekanizması sağlar.

## Nasıl Çalışır?

1. 1.  **Kayıt veya Giriş:** Kullanıcılar `/register` veya `/login` uç noktalarına istek göndererek bir hesap oluşturur veya mevcut hesaplarıyla giriş yaparlar.
1.     
1. 2.  **Token Alma:** Başarılı bir giriş veya kayıt işleminden sonra API, kullanıcıya özel bir `access_token` (erişim token'ı) döndürür.
1.     
1. 3.  **Korumalı Rotalara Erişim:** Kullanıcı, bu `access_token`'ı sonraki tüm korumalı API isteklerinde `Authorization` başlığında `Bearer Token` olarak göndermelidir.
1.     
1.     **Örnek `Authorization` Başlığı:** `Authorization: Bearer YOUR_ACCESS_TOKEN_BURAYA_GELECEK`
1.     

## Kimlik Doğrulama Akışı

### 1\. Kayıt Olma

Yeni bir kullanıcı hesabı oluşturmak için `/api/v1/register` uç noktasına `POST` isteği gönderilir.

* *   **URL:** `http://localhost:8000/api/v1/register`
*     
* *   **Metod:** `POST`
*     
* *   **Gerekli Alanlar:** `name`, `email`, `password`, `password_confirmation`
*     
* *   **Örnek İstek (JSON):**
*     
*     ```
*     {
*         "name": "Yeni Kullanıcı",
*         "email": "yeni@example.com",
*         "password": "cokgizlisifre",
*         "password_confirmation": "cokgizlisifre"
*     }
*     ```
*     
* *   **Başarılı Yanıt:** `201 Created` durum kodu ve kullanıcı bilgileri ile birlikte `access_token`.
*     

### 2\. Giriş Yapma

Mevcut bir kullanıcı hesabı ile giriş yapmak için `/api/v1/login` uç noktasına `POST` isteği gönderilir.

* *   **URL:** `http://localhost:8000/api/v1/login`
*     
* *   **Metod:** `POST`
*     
* *   **Gerekli Alanlar:** `email`, `password`
*     
* *   **Örnek İstek (JSON):**
*     
*     ```
*     {
*         "email": "kullanici@example.com",
*         "password": "sifre"
*     }
*     ```
*     
* *   **Başarılı Yanıt:** `200 OK` durum kodu ve kullanıcı bilgileri ile birlikte `access_token`. Bu token, kullanıcının önceki tüm token'larını geçersiz kılar.
*     

### 3\. Token Kullanımı (Korumalı Rotalar)

Giriş yaptıktan sonra elde ettiğiniz `access_token`'ı, korumalı rotalara yapacağınız her istekte `Authorization` HTTP başlığı içinde `Bearer` şemasıyla birlikte göndermelisiniz.

**Örnek:** `/api/v1/user` uç noktasına kimliği doğrulanmış kullanıcı bilgilerini almak için `GET` isteği.

* *   **URL:** `http://localhost:8000/api/v1/user`
*     
* *   **Metod:** `GET`
*     
* *   **Başlık:** `Authorization: Bearer YOUR_ACCESS_TOKEN_BURAYA_GELECEK`
*     
* *   **Başarılı Yanıt:** `200 OK` durum kodu ve kimliği doğrulanmış kullanıcının bilgileri.
*     

### 4\. Çıkış Yapma

Kullanıcının oturumunu sonlandırmak ve mevcut `access_token`'ını iptal etmek için `/api/v1/logout` uç noktasına `POST` isteği gönderilir.

* *   **URL:** `http://localhost:8000/api/v1/logout`
*     
* *   **Metod:** `POST`
*     
* *   **Yetkilendirme:** `Bearer Token` (Giriş yapılmış olmalı)
*     
* *   **Başarılı Yanıt:** `200 OK` durum kodu ve "Çıkış başarılı." mesajı.
*     

## İstek Sınırlama (Rate Limiting)

Kimlik doğrulama rotaları (`/register`, `/login`), deneme yanılma (brute-force) saldırılarını önlemek amacıyla istek sınırlamasına tabidir. Varsayılan olarak, her IP adresi için dakikada 10 istek ile sınırlıdır. Diğer tüm korumalı API rotaları ise genel bir sınırlayıcıya sahiptir (varsayılan: dakikada 60 istek). Bu limitler `app/Providers/RouteServiceProvider.php` dosyasında yapılandırılmıştır.
