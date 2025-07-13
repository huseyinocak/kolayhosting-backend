# Laravel Modelleri ve Veritabanı Yapısı

KolayHosting API'sinin veritabanı yapısı, Laravel Eloquent ORM modelleri aracılığıyla temsil edilmektedir. Bu modeller, uygulamanın ana veri varlıklarını ve aralarındaki ilişkileri tanımlar.

## 1\. `User` Modeli

Sistemdeki kullanıcıları (admin, normal kullanıcı) temsil eder.

* *   **Tablo Adı:** `users`
*     
* *   **Temel Sütunlar:** `id`, `name`, `email`, `password`, `role` (`admin` veya `user`), `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `reviews()`: Bir kullanıcının yaptığı incelemeler (`HasMany` Review).
*     *     

## 2\. `Category` Modeli

Hosting kategorilerini (Web Hosting, VPS Hosting vb.) temsil eder.

* *   **Tablo Adı:** `categories`
*     
* *   **Temel Sütunlar:** `id`, `name` (benzersiz), `slug` (benzersiz, SEO dostu URL için), `description`, `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `plans()`: Bir kategoriye ait planlar (`HasMany` Plan).
*     *     

## 3\. `Provider` Modeli

Hosting hizmeti sunan sağlayıcıları (Hostinger, Bluehost vb.) temsil eder.

* *   **Tablo Adı:** `providers`
*     
* *   **Temel Sütunlar:** `id`, `name` (benzersiz), `slug` (benzersiz), `logo_url`, `website_url`, `description`, `average_rating` (ondalık), `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `plans()`: Bir sağlayıcıya ait planlar (`HasMany` Plan).
*     *     
*     * *   `reviews()`: Bir sağlayıcı hakkında yapılan incelemeler (`HasMany` Review).
*     *     

## 4\. `Plan` Modeli

Her sağlayıcının sunduğu belirli hosting planlarını temsil eder.

* *   **Tablo Adı:** `plans`
*     
* *   **Temel Sütunlar:** `id`, `provider_id` (Provider'a yabancı anahtar), `category_id` (Category'ye yabancı anahtar), `name`, `slug` (benzersiz), `price`, `currency`, `renewal_price` (isteğe bağlı), `discount_percentage` (isteğe bağlı), `features_summary`, `link`, `status` (`active`, `inactive`, `deprecated`), `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `provider()`: Planın ait olduğu sağlayıcı (`BelongsTo` Provider).
*     *     
*     * *   `category()`: Planın ait olduğu kategori (`BelongsTo` Category).
*     *     
*     * *   `features()`: Planın sahip olduğu özellikler (`BelongsToMany` Feature, `plan_features` pivot tablosu aracılığıyla).
*     *     
*     * *   `reviews()`: Plan hakkında yapılan incelemeler (`HasMany` Review).
*     *     

## 5\. `Feature` Modeli

Hosting planlarının sunduğu özellikleri (Disk Alanı, Bant Genişliği, Ücretsiz SSL vb.) temsil eder.

* *   **Tablo Adı:** `features`
*     
* *   **Temel Sütunlar:** `id`, `name` (benzersiz), `unit` (örn. "GB", "Adet"), `type` (`boolean`, `numeric`, `text`), `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `plans()`: Özelliğin ait olduğu planlar (`BelongsToMany` Plan, `plan_features` pivot tablosu aracılığıyla).
*     *     

## 6\. `PlanFeature` Modeli (Pivot Tablo)

`Plan` ve `Feature` modelleri arasındaki çoktan çoğa ilişkiyi yönetir ve bir planın belirli bir özelliği için değeri saklar. Laravel'de genellikle bu tür pivot tablolar için ayrı bir model oluşturulmaz, ancak gelişmiş işlemler için tanımlanabilir.

* *   **Tablo Adı:** `plan_features`
*     
* *   **Temel Sütunlar:** `plan_id` (Plan'a yabancı anahtar), `feature_id` (Feature'a yabancı anahtar), `value` (özelliğin değeri, örn. "Sınırsız", "100 GB", "Evet"), `created_at`, `updated_at`.
*     
* *   **İlişkiler:** Bu, `Plan` ve `Feature` modellerindeki `belongsToMany` ilişkileri tarafından yönetilir.
*     

## 7\. `Review` Modeli

Kullanıcıların veya uzmanların sağlayıcılar veya planlar hakkındaki incelemelerini ve derecelendirmelerini temsil eder.

* *   **Tablo Adı:** `reviews`
*     
* *   **Temel Sütunlar:** `id`, `provider_id` (Provider'a yabancı anahtar, null olabilir), `plan_id` (Plan'a yabancı anahtar, null olabilir), `user_id` (User'a yabancı anahtar, null olabilir), `user_name` (isteğe bağlı), `rating` (1-5 arası tam sayı), `title` (isteğe bağlı), `content`, `published_at` (isteğe bağlı), `is_approved` (boolean), `created_at`, `updated_at`.
*     
* *   **İlişkiler:**
*     
*     * *   `provider()`: İncelemenin ait olduğu sağlayıcı (`BelongsTo` Provider).
*     *     
*     * *   `plan()`: İncelemenin ait olduğu plan (`BelongsTo` Plan).
*     *     
*     * *   `user()`: İncelemeyi yapan kullanıcı (`BelongsTo` User).
*     *     

## 8\. `Article` Modeli (İsteğe Bağlı)

Eğer sitede blog yazıları, rehberler veya bilgilendirici makaleler olacaksa bu model eklenebilir.

* *   **Tablo Adı:** `articles`
*     
* *   **Temel Sütunlar:** `id`, `title`, `slug`, `content`, `author` (isteğe bağlı), `published_at`, `created_at`, `updated_at`.
*
