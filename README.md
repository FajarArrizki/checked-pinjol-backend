# Checked Pinjol Backend

Initial backend scaffold untuk project `checked-pinjol-backend` dengan pendekatan native PHP, API-first, reusable, dan mudah di-maintain. Struktur ini disiapkan supaya nanti mudah dihubungkan ke frontend Vite + React memakai JavaScript atau TypeScript.

## Tujuan

- Menjadi fondasi backend API yang rapi sejak awal.
- Memisahkan core infrastructure dan domain modules.
- Menjaga kode reusable dan tidak cepat berantakan saat fitur bertambah.
- Menyiapkan arah untuk auth JWT dan MySQL/MariaDB tanpa memaksa implementasi penuh di fase init.

## Tech Stack

- PHP 8.1+
- Composer untuk autoload
- PDO untuk database access
- MySQL/MariaDB sebagai default database
- Frontend target: Vite + React

## Struktur Folder

```text
checked-pinjol-backend/
|- bootstrap/
|- config/
|- database/
|  |- migrations/
|  |- schema/
|  `- seeders/
|- docs/
|  `- plans/
|- public/
|- routes/
|- src/
|  |- Bootstrap/
|  |- Core/
|  |  |- Config/
|  |  |- Database/
|  |  |- Http/
|  |  |- Middleware/
|  |  `- Routing/
|  |- Modules/
|  |  |- Auth/
|  |  `- Health/
|  `- Support/
|- storage/
|  |- cache/
|  `- logs/
`- tests/
```

## Penjelasan Arsitektur

### `public/`
Entry point server. Semua request masuk lewat `public/index.php`.

### `src/Core/`
Berisi komponen reusable umum:
- `Application`: siklus request ke response
- `Container`: dependency resolution sederhana
- `Routing`: pendaftaran dan dispatch route
- `Http`: request dan response object
- `Config`: pembacaan konfigurasi
- `Database`: factory dan manager koneksi PDO
- `Middleware`: middleware umum seperti CORS

Semua yang bersifat generic sebaiknya masuk sini, bukan ke module bisnis.

### `src/Modules/`
Berisi fitur/domain aplikasi.

Contoh awal:
- `Health`: endpoint untuk health check
- `Auth`: placeholder route untuk register, login, dan `me`

Kalau nanti ada fitur baru, tambahkan module baru seperti:
- `Users`
- `Pinjol`
- `Reports`
- `Documents`

### `config/`
Konfigurasi dipisah per concern:
- `app.php`
- `database.php`
- `auth.php`
- `cors.php`

Semua nilai yang bisa berubah antar environment harus diambil dari `.env`.

### `database/`
- `schema/` untuk SQL awal
- `migrations/` untuk perubahan schema bertahap
- `seeders/` untuk data awal atau data testing

### `storage/`
Untuk file runtime seperti log dan cache.

## Alur Request

1. Request masuk ke `public/index.php`
2. App dibuat lewat `ApplicationFactory`
3. Env dan config dimuat
4. Route dari `routes/api.php` didaftarkan
5. Router mencocokkan method + path
6. Controller module dieksekusi
7. Response JSON dikembalikan
8. CORS header ditambahkan sebelum response dikirim

## Endpoint Awal

### Health Check

`GET /api/health`

Dipakai untuk memastikan backend hidup.

### Swagger / OpenAPI Docs

- `GET /swagger`
- `GET /swagger/openapi.json`

Dokumentasi API disajikan lewat Swagger UI, dengan definisi OpenAPI disimpan terpisah di folder `docs/openapi`.

## Setup Lokal

### 1. Copy env

```bash
cp .env.example .env
```

Kalau di Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 2. Install Composer dependency

```bash
composer install
```

### 3. Siapkan database

Buat database MySQL/MariaDB sesuai isi `.env`, atau jalankan SQL di `database/schema/init.sql`.

### 4. Jalankan server lokal

```bash
composer serve
```

Atau langsung:

```bash
php -S localhost:8000 -t public router.php
```

Catatan: gunakan `router.php` saat memakai PHP built-in server supaya semua endpoint API dan Swagger seperti `/swagger/openapi.json` tetap diarahkan ke front controller.

### 5. Cek health endpoint

```bash
curl http://localhost:8000/api/health
```

### 6. Buka dokumentasi API

```bash
http://localhost:8000/swagger
```

## Workflow Pengembangan

### Tambah endpoint baru

1. Tambahkan route di `routes/api.php`
2. Buat controller di module terkait
3. Buat service untuk business logic
4. Kalau butuh DB, buat repository atau query layer di module terkait
5. Simpan komponen reusable di `src/Core` kalau memang generic

### Aturan maintainability

- Jangan campur query database langsung di controller
- Jangan simpan business logic besar di route file
- Jangan hardcode config, secret, host, port, atau credential
- Pisahkan kode generic dan kode domain
- Tambahkan module baru per fitur, bukan menumpuk semua di satu tempat

### Kapan masuk ke `Core`

Masuk ke `src/Core` jika:
- dipakai lintas fitur
- tidak spesifik ke satu domain
- berupa fondasi app seperti router, response, db, config, middleware

### Kapan masuk ke `Modules`

Masuk ke `src/Modules` jika:
- logic terkait satu fitur/domain
- hanya relevan untuk auth, users, pinjol, atau domain lain tertentu

## Rekomendasi Struktur Modul Selanjutnya

Saat mulai implementasi nyata, setiap module bisa dikembangkan seperti ini:

```text
src/Modules/Users/
|- Controllers/
|- Services/
|- Repositories/
|- DTOs/
`- Validators/
```

Tidak perlu semua folder dibuat dari awal. Tambahkan saat memang dibutuhkan.

## Catatan Frontend Integration

Karena backend ini API-first:
- frontend Vite + React cukup memanggil endpoint lewat HTTP
- CORS sudah disiapkan lewat `config/cors.php`
- origin default diarahkan ke `http://localhost:5173`

## Testing Awal

Untuk sekarang ada smoke check sederhana:

```bash
composer test
```

Ini baru memverifikasi file penting scaffold sudah ada. Nanti bisa ditambah test request, service, dan database seiring implementasi fitur.

## Next Step yang Masuk Akal

1. Tambahkan migrasi tabel `users`
2. Implementasi register dan login JWT
3. Tambahkan validation layer
4. Tambahkan base repository atau query abstraction bila benar-benar dibutuhkan
5. Tambahkan error handling yang lebih konsisten untuk production
6. Tambahkan test endpoint dan auth flow

## Prinsip Utama Project

- Mulai kecil, tapi rapi
- Reusable tanpa over-engineering
- Pisahkan foundation dan business logic
- Mudah dibaca developer lain
- Mudah dikembangkan ke API production yang lebih besar
