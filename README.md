# PHP Auth API (php-auth-api)

Sebuah REST API yang aman dan ringan untuk manajemen user dan otentikasi (Register, Login), dibangun hanya dengan PHP (tanpa framework). Proyek ini menggunakan standar modern termasuk JWT untuk otentikasi, PSR-4 autoloading dengan Composer, dan password hashing BCRYPT.

## Fitur Utama

* **API Otentikasi Lengkap:** Endpoint untuk Registrasi, Login, dan Validasi (Protected Route).
* **Keamanan Modern:** Menggunakan **BCRYPT** (`password_hash` dan `password_verify`) untuk keamanan password.
* **Otentikasi Berbasis Token:** Menggunakan **JSON Web Tokens (JWT)** (`firebase/php-jwt`) untuk mengelola sesi API.
* **Kode Bersih & Modern:** Ditulis menggunakan PHP 8+ dengan pendekatan OOP (Class-based Models & Config).
* **PSR-4 Autoloading:** Menggunakan **Composer** untuk autoloading class (PSR-4) dan manajemen dependensi.
* **CORS Ready:** Siap menangani request Preflight `OPTIONS` dan header CORS.
* **Server Config:** Termasuk konfigurasi `.htaccess` untuk meng-handle header `Authorization` di Apache.

## Tech Stack

* **Backend:** PHP 8+ (Vanilla)
* **Database:** MySQL / MariaDB (Menggunakan ekstensi PDO)
* **Package Manager:** Composer
* **Libraries:** `firebase/php-jwt`
* **Lingkungan:** Laragon (Apache)

---

## Instalasi & Setup

1.  **Clone Repository:**
    ```bash
    git clone [https://github.com/muhtegaralfikri/php-auth-api.git]
    cd php-auth-api
    ```

2.  **Setup Database:**
    * Buat database baru di MySQL (misal: `php_auth_api`).
    * Impor skema tabel. Jalankan SQL query berikut untuk membuat tabel `users`:
    ```sql
    CREATE TABLE `users` (
      `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `email` VARCHAR(255) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `users_email_unique` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```

3.  **Install Dependensi:**
    * Pastikan Composer terinstal. Jalankan:
    ```bash
    composer install
    ```

4.  **Konfigurasi Environment:**
    * Buka file `/config/core.php`.
    * Ubah nilai `$secret_key` menjadi string acak milikmu sendiri yang sangat rahasia.
    * Sesuaikan `$issuer` dan `$audience` jika perlu.

5.  **Setup Server (Laragon):**
    * Project ini membutuhkan Apache dan file `.htaccess` yang disertakan untuk menangani header otentikasi.
    * Cara termudah adalah menggunakan Laragon dan menempatkan folder ini di `www/`, yang akan otomatis membuat VHost (misal: `http://php-auth-api.test`).

---

## Dokumentasi Endpoint API

Semua request dan respons menggunakan format `JSON`.

### 1. Registrasi User
Membuat user baru.

* **Method:** `POST`
* **Endpoint:** `/api/auth/register.php`
* **Body (raw/json):**
    ```json
    {
        "email": "user@example.com",
        "password": "passwordMinimal8Karakter"
    }
    ```
* **Respons Sukses (201 Created):**
    ```json
    {
        "message": "Registrasi berhasil."
    }
    ```

### 2. Login User
Mengotentikasi user dan mendapatkan JWT.

* **Method:** `POST`
* **Endpoint:** `/api/auth/login.php`
* **Body (raw/json):**
    ```json
    {
        "email": "user@example.com",
        "password": "passwordMinimal8Karakter"
    }
    ```
* **Respons Sukses (200 OK):**
    ```json
    {
        "message": "Login berhasil.",
        "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3Mi...",
        "expiresIn": 1757857995
    }
    ```
* **Respons Gagal (401 Unauthorized):**
    ```json
    {
        "message": "Login gagal. Email atau password salah."
    }
    ```

### 3. Get Profil User (Protected Endpoint)
Mengambil data profil user yang sedang login. Membutuhkan otentikasi Bearer Token.

* **Method:** `GET`
* **Endpoint:** `/api/users/profile.php`
* **Headers:**
    * `Authorization`: `Bearer <token_jwt_dari_login>`
* **Respons Sukses (200 OK):**
    ```json
    {
        "message": "Akses diizinkan.",
        "data": {
            "id": 1,
            "email": "user@example.com"
        }
    }
    ```
* **Respons Gagal (401 Unauthorized):**
    ```json
    {
        "message": "Akses ditolak. Token tidak disediakan."
    }
    ```