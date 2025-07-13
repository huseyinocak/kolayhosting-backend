\*\*

# Kurulum Rehberi

KolayHosting API'sini yerel geliştirme ortamınızda veya sunucunuzda kurmak için aşağıdaki adımları takip edin.

## Ön Gereksinimler

Kuruluma başlamadan önce sisteminizde aşağıdaki yazılımların yüklü olduğundan emin olun:

-   PHP: Sürüm 8.2 veya üzeri.
-   Composer: PHP bağımlılık yöneticisi.
-   MySQL: Veritabanı sunucusu (veya tercih ettiğiniz başka bir veritabanı).
-   Git: Versiyon kontrol sistemi.

## Adım Adım Kurulum

1.  **Depoyu Klonla:**
    Projenin kaynak kodunu GitHub'dan yerel makinenize klonlayın.

```
    git clone https://github.com/your-username/kolayhosting.git
     cd kolayhosting
```

Not: your-username ve kolayhosting kısmını kendi GitHub kullanıcı adınız ve depo adınızla değiştirin.

2.  **Composer Bağımlılıklarını Yükle:**  
    Projenin gerektirdiği tüm PHP kütüphanelerini Composer aracılığıyla yükleyin.  
     `   composer install`

3.  **Ortam Dosyasını Yapılandır (.env):**  
    Laravel, ortam değişkenlerini yönetmek için .env dosyasını kullanır. .env.example dosyasını kopyalayarak kendi .env dosyanızı oluşturun ve gerekli düzenlemeleri yapın.

```
 cp .env.example .env
    .env dosyasını açın ve aşağıdaki temel ayarları kendi ortamınıza göre düzenleyin:

     dotenv
     APP\_NAME="KolayHosting"
     APP\_ENV=local
     APP\_KEY= # Bu kısım boş kalabilir, bir sonraki adımda oluşturulacak
     APP\_DEBUG=true
     APP\_URL=http://localhost:8000 # Uygulamanızın çalışacağı URL

     DB\_CONNECTION=mysql
     DB\_HOST=127.0.0.1
     DB\_PORT=3306
     DB\_DATABASE=kolayhosting\_db # Kendi veritabanı adınızı girin
     DB\_USERNAME=root # Veritabanı kullanıcı adınızı girin
     DB\_PASSWORD= # Veritabanı şifrenizi girin

     # Laravel Sanctum için CORS ayarları
     # Frontend uygulamanızın adresini buraya ekleyin (örn. React uygulaması 3000 portunda çalışıyorsa)
     SANCTUM\_STATEFUL\_DOMAINS=localhost,localhost:3000,127.0.0.1:8000
     SANCTUM\_IMMUNE\_DOMAINS=
```

Not: DB_DATABASE, DB_USERNAME, DB_PASSWORD değerlerini kendi MySQL (veya kullandığınız veritabanı) ayarlarınıza göre doldurduğunuzdan emin olun.

4.  **Uygulama Anahtarını Oluştur: **
    Laravel uygulamanız için benzersiz bir uygulama anahtarı (encryption key) oluşturun.  
    `php artisan key:generate`
5.  **Veritabanı Migrasyonlarını Çalıştır: **
    Bu adım, veritabanı tablolarını oluşturur. Proje modellerine karşılık gelen tabloları oluşturmak için aşağıdaki komutu çalıştırın:

```
php artisan migrate
```

Bu komut, database/migrations klasöründeki tüm migration dosyalarını çalıştıracaktır. Buna add_role_to_users_table.php ve add_user_id_to_reviews_table.php gibi eklenen migration'lar da dahildir.

6.  **Örnek Verileri Doldur (Seed):**  
    Uygulamayı test etmek için örnek verileri veritabanına ekleyin. Bu, kategori, sağlayıcı, plan, özellik ve inceleme gibi temel verileri ve ayrıca varsayılan admin ve normal kullanıcı hesaplarını oluşturacaktır.

```
php artisan db:seed
```

Varsayılan Kullanıcılar (Şifreleri password):

-   Admin: admin@example.com
-   Normal Kullanıcı: user@example.com
-   Başka Bir Kullanıcı: another@example.com

7.  **Laravel Geliştirme Sunucusunu Başlat:**  
    API'nizi yerel olarak çalıştırmak için Laravel'in dahili geliştirme sunucusunu başlatın.

```
php artisan serve
```

API'niz artık genellikle http://localhost:8000 adresinde erişilebilir olacaktır. Tarayıcınızda bu adresi ziyaret ederek veya Postman gibi bir API istemcisiyle test ederek API'nizin çalışıp çalışmadığını kontrol edebilirsiniz.

Kurulum tamamlandı! Artık KolayHosting API'sini kullanmaya başlayabilirsiniz.
