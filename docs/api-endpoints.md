Paste yo

# API Uç Noktaları

Bu doküman, KolayHosting API'sinin tüm uç noktalarını, HTTP metodlarını, gerekli parametreleri ve yetkilendirme gereksinimlerini detaylandırır.

**Temel URL:** `http://localhost:8000/api/v1` (Kurulumunuza göre değişebilir)

## 1\. Kimlik Doğrulama (Authentication)

Kimlik doğrulama rotaları, kullanıcıların sisteme kayıt olmasını, giriş yapmasını ve oturumlarını yönetmesini sağlar. Bu rotalar, `RouteServiceProvider.php` içinde tanımlanan `auth` rate limiter'ı ile korunmaktadır (varsayılan: dakikada 10 istek).

### 1.1. Kullanıcı Kaydı

* *   **URL:** `/register`
*     
* *   **Metod:** `POST`
*     
* *   **Açıklama:** Yeni bir kullanıcı hesabı oluşturur.
*     
* *   **Yetkilendirme:** Yok (Public)
*     
* *   **İstek Gövdesi (JSON):**
*     
*     ```
*     {
*         "name": "Kullanıcı Adı",
*         "email": "kullanici@example.com",
*         "password": "sifre",
*         "password_confirmation": "sifre"
*     }
*     ```
*     
* *   **Başarılı Yanıt (201 Created):**
*     
*     ```
*     {
*         "message": "Kayıt başarılı!",
*         "user": {
*             "id": 1,
*             "name": "Kullanıcı Adı",
*             "email": "kullanici@example.com",
*             "role": "user",
*             "created_at": "...",
*             "updated_at": "..."
*         },
*         "access_token": "YOUR_ACCESS_TOKEN",
*         "token_type": "Bearer"
*     }
*     ```
*     

### 1.2. Kullanıcı Girişi

* *   **URL:** `/login`
*     
* *   **Metod:** `POST`
*     
* *   **Açıklama:** Kullanıcının kimlik bilgilerini doğrulayarak bir API token'ı döndürür.
*     
* *   **Yetkilendirme:** Yok (Public)
*     
* *   **İstek Gövdesi (JSON):**
*     
*     ```
*     {
*         "email": "kullanici@example.com",
*         "password": "sifre"
*     }
*     ```
*     
* *   **Başarılı Yanıt (200 OK):**
*     
*     ```
*     {
*         "message": "Giriş başarılı!",
*         "user": {
*             "id": 1,
*             "name": "Kullanıcı Adı",
*             "email": "kullanici@example.com",
*             "role": "user",
*             "created_at": "...",
*             "updated_at": "..."
*         },
*         "access_token": "YOUR_NEW_ACCESS_TOKEN",
*         "token_type": "Bearer"
*     }
*     ```
*     

### 1.3. Kullanıcı Çıkışı

* *   **URL:** `/logout`
*     
* *   **Metod:** `POST`
*     
* *   **Açıklama:** Kimliği doğrulanmış kullanıcının mevcut API token'ını iptal eder.
*     
* *   **Yetkilendirme:** `Bearer Token` (Giriş yapılmış olmalı)
*     
* *   **Başarılı Yanıt (200 OK):**
*     
*     ```
*     {
*         "message": "Çıkış başarılı."
*     }
*     ```
*     

### 1.4. Kimliği Doğrulanmış Kullanıcı Bilgileri

* *   **URL:** `/user`
*     
* *   **Metod:** `GET`
*     
* *   **Açıklama:** Kimliği doğrulanmış kullanıcının bilgilerini döndürür.
*     
* *   **Yetkilendirme:** `Bearer Token` (Giriş yapılmış olmalı)
*     
* *   **Başarılı Yanıt (200 OK):**
*     
*     ```
*     {
*         "id": 1,
*         "name": "Kullanıcı Adı",
*         "email": "kullanici@example.com",
*         "email_verified_at": null,
*         "role": "user",
*         "created_at": "...",
*         "updated_at": "..."
*     }
*     ```
*     

## 2\. Kategoriler (Categories)

Kategoriler, hosting türlerini (Web Hosting, VPS Hosting vb.) yönetmek için kullanılır. CRUD işlemleri için `admin` rolü gereklidir. Okuma işlemleri tüm kimliği doğrulanmış kullanıcılar için açıktır.

* *   **Temel URL:** `/categories`
*     

**Metod**

**URL**

**Açıklama**

**Yetkilendirme**

`GET`

`/categories`

Tüm kategorileri listele.

`Bearer Token`

`GET`

`/categories/{id}`

Belirli bir kategoriyi göster.

`Bearer Token`

`POST`

`/categories`

Yeni kategori oluştur.

`Bearer Token` (`admin` rolü)

`PUT`

`/categories/{id}`

Belirli bir kategoriyi güncelle.

`Bearer Token` (`admin` rolü)

`DELETE`

`/categories/{id}`

Belirli bir kategoriyi sil.

`Bearer Token` (`admin` rolü)

`GET`

`/categories/{id}/plans`

Kategoriye ait planları listele.

`Bearer Token`

**Örnek `POST /categories` İstek Gövdesi (JSON):**

```
{
    "name": "Yeni Kategori Adı",
    "description": "Bu yeni bir hosting kategorisidir."
}
```

## 3\. Sağlayıcılar (Providers)

Sağlayıcılar, hosting hizmeti sunan şirketleri (Hostinger, Bluehost vb.) yönetmek için kullanılır. CRUD işlemleri için `admin` rolü gereklidir. Okuma işlemleri tüm kimliği doğrulanmış kullanıcılar için açıktır.

* *   **Temel URL:** `/providers`
*     

**Metod**

**URL**

**Açıklama**

**Yetkilendirme**

`GET`

`/providers`

Tüm sağlayıcıları listele.

`Bearer Token`

`GET`

`/providers/{id}`

Belirli bir sağlayıcıyı göster.

`Bearer Token`

`POST`

`/providers`

Yeni sağlayıcı oluştur.

`Bearer Token` (`admin` rolü)

`PUT`

`/providers/{id}`

Belirli bir sağlayıcıyı güncelle.

`Bearer Token` (`admin` rolü)

`DELETE`

`/providers/{id}`

Belirli bir sağlayıcıyı sil.

`Bearer Token` (`admin` rolü)

`GET`

`/providers/{id}/plans`

Sağlayıcıya ait planları listele.

`Bearer Token`

`GET`

`/providers/{id}/reviews`

Sağlayıcıya ait incelemeleri listele.

`Bearer Token`

**Örnek `POST /providers` İstek Gövdesi (JSON):**

```
{
    "name": "Yeni Sağlayıcı",
    "logo_url": "https://example.com/logo.png",
    "website_url": "https://www.yenisaglayici.com",
    "description": "Bu harika bir hosting sağlayıcısıdır.",
    "average_rating": 4.2
}
```

## 4\. Planlar (Plans)

Planlar, her sağlayıcının sunduğu belirli hosting paketlerini yönetmek için kullanılır. CRUD işlemleri için `admin` rolü gereklidir. Okuma işlemleri tüm kimliği doğrulanmış kullanıcılar için açıktır.

* *   **Temel URL:** `/plans`
*     

**Metod**

**URL**

**Açıklama**

**Yetkilendirme**

`GET`

`/plans`

Tüm planları listele.

`Bearer Token`

`GET`

`/plans/{id}`

Belirli bir planı göster.

`Bearer Token`

`POST`

`/plans`

Yeni plan oluştur.

`Bearer Token` (`admin` rolü)

`PUT`

`/plans/{id}`

Belirli bir planı güncelle.

`Bearer Token` (`admin` rolü)

`DELETE`

`/plans/{id}`

Belirli bir planı sil.

`Bearer Token` (`admin` rolü)

`GET`

`/plans/{id}/features`

Plana ait özellikleri listele.

`Bearer Token`

`GET`

`/plans/{id}/reviews`

Plana ait incelemeleri listele.

`Bearer Token`

**Örnek `POST /plans` İstek Gövdesi (JSON):**

```
{
    "provider_id": 1,
    "category_id": 1,
    "name": "Örnek Hosting Planı",
    "price": 5.99,
    "currency": "USD",
    "renewal_price": 10.99,
    "discount_percentage": 45.5,
    "features_summary": "100 GB SSD, Ücretsiz SSL, Sınırsız Bant Genişliği",
    "link": "https://example.com/plan-link",
    "status": "active"
}
```

## 5\. Özellikler (Features)

Özellikler, hosting planlarının sunduğu teknik özellikleri (Disk Alanı, RAM vb.) yönetmek için kullanılır. CRUD işlemleri için `admin` rolü gereklidir. Okuma işlemleri tüm kimliği doğrulanmış kullanıcılar için açıktır.

* *   **Temel URL:** `/features`
*     

**Metod**

**URL**

**Açıklama**

**Yetkilendirme**

`GET`

`/features`

Tüm özellikleri listele.

`Bearer Token`

`GET`

`/features/{id}`

Belirli bir özelliği göster.

`Bearer Token`

`POST`

`/features`

Yeni özellik oluştur.

`Bearer Token` (`admin` rolü)

`PUT`

`/features/{id}`

Belirli bir özelliği güncelle.

`Bearer Token` (`admin` rolü)

`DELETE`

`/features/{id}`

Belirli bir özelliği sil.

`Bearer Token` (`admin` rolü)

**Örnek `POST /features` İstek Gövdesi (JSON):**

```
{
    "name": "CPU Çekirdeği",
    "unit": "Adet",
    "type": "numeric"
}
```

## 6\. İncelemeler (Reviews)

İncelemeler, kullanıcıların sağlayıcılar veya planlar hakkında yorum ve derecelendirme yapmasını sağlar. `create` işlemi tüm kimliği doğrulanmış kullanıcılar için açıktır. `update` ve `delete` işlemleri için kullanıcı kendi incelemesini yönetebilir, `admin` rolündeki kullanıcılar ise tüm incelemeleri yönetebilir. Okuma işlemleri tüm kimliği doğrulanmış kullanıcılar için açıktır.

* *   **Temel URL:** `/reviews`
*     

**Metod**

**URL**

**Açıklama**

**Yetkilendirme**

`GET`

`/reviews`

Tüm incelemeleri listele.

`Bearer Token`

`GET`

`/reviews/{id}`

Belirli bir incelemeyi göster.

`Bearer Token`

`POST`

`/reviews`

Yeni inceleme oluştur.

`Bearer Token`

`PUT`

`/reviews/{id}`

Belirli bir incelemeyi güncelle.

`Bearer Token` (Kendi incelemesi veya `admin` rolü)

`DELETE`

`/reviews/{id}`

Belirli bir incelemeyi sil.

`Bearer Token` (Kendi incelemesi veya `admin` rolü)

**Örnek `POST /reviews` İstek Gövdesi (JSON):**

```
{
    "provider_id": 1,
    "plan_id": null,
    "user_name": "Deneme Kullanıcı",
    "rating": 5,
    "title": "Harika Hizmet!",
    "content": "Bu hosting sağlayıcısından çok memnun kaldım, destekleri çok iyi.",
    "is_approved": false
}
```

**Not:** `provider_id` veya `plan_id`'den en az biri zorunludur. `user_id` otomatik olarak giriş yapan kullanıcıdan alınır.

ur rich text content here. You can paste directly from Word or other rich text sources.
