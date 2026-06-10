# Panduan Setup XAMPP KomikHub

Panduan ini dibuat supaya setup project `Komik_Website` di XAMPP lebih gampang diikuti.

## 1. Pindahkan Project ke `htdocs`

Pastikan folder project ada di:

```text
C:\xampp\htdocs\Komik_Website
```

Cek file penting ini harus ada:

```text
C:\xampp\htdocs\Komik_Website\public\index.php
```

## 2. Jalankan XAMPP

Buka **XAMPP Control Panel**, lalu nyalakan:

- `Apache`
- `MySQL`

Kalau dua service ini sudah hijau, lanjut ke langkah berikutnya.

## 3. Buat Database

Buka phpMyAdmin:

[http://localhost/phpmyadmin](http://localhost/phpmyadmin)

Lalu:

1. Klik `New`
2. Isi nama database: `komik_web`
3. Klik `Create`

## 4. Import Schema Database

Masuk ke database `komik_web`, lalu:

1. Klik tab `Import`
2. Klik `Choose File`
3. Pilih file:

```text
C:\xampp\htdocs\Komik_Website\database\schema.sql
```

4. Klik `Go`

Kalau berhasil, tabel-tabel website akan otomatis dibuat.

## 5. Buat File `.env`

Di folder project:

```text
C:\xampp\htdocs\Komik_Website
```

copy file:

```text
.env.example
```

menjadi:

```text
.env
```

Isi file `.env` seperti ini:

```env
APP_NAME=KomikHub
APP_URL=http://localhost/Komik_Website/public
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=komik_web
DB_USER=root
DB_PASS=
SESSION_NAME=komikhub_session
```

## 6. Buat Password Admin

Buka **CMD** atau **PowerShell** di folder:

```text
C:\xampp\htdocs\Komik_Website
```

Lalu jalankan:

```powershell
C:\xampp\php\php.exe tools\hash-password.php admin123456
```

Nanti akan muncul hasil hash password.

Contohnya bentuknya seperti ini:

```text
$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Copy hasil hash itu.

## 7. Buat User Admin di Database

Buka lagi phpMyAdmin:

[http://localhost/phpmyadmin](http://localhost/phpmyadmin)

Masuk ke database `komik_web`, lalu:

1. Klik tab `SQL`
2. Jalankan query ini:

```sql
INSERT INTO users (name, email, password, role)
VALUES ('Administrator', 'admin@local.test', 'PASTE_HASH_DI_SINI', 'admin');
```

Ganti:

```text
PASTE_HASH_DI_SINI
```

dengan hash hasil dari langkah 6.

## 8. Buka Website

Buka URL ini di browser:

[http://localhost/Komik_Website/public](http://localhost/Komik_Website/public)

## 9. Login Admin

Gunakan akun:

- Email: `admin@local.test`
- Password: `admin123456`

## 10. Jika Ingin Isi Data Komik

Ada 2 cara:

### Cara A. Input manual dari admin panel

Setelah login admin:

- masuk ke menu `Admin`
- tambah komik
- tambah chapter
- isi URL gambar CDN satu per baris

### Cara B. Sync dari project scraping

Kalau mau pakai project scraping di:

```text
D:\Program\Project\ComicsScraping
```

Pastikan database MySQL scraper diarahkan ke database yang sama, yaitu:

```env
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=
MYSQL_DATABASE=komik_web
MYSQL_CHARSET=utf8mb4
```

Lalu jalankan dari folder scraper:

```powershell
python main.py -f urls.txt -o ./ --chapters smart --mysql-init --mysql-sync
```

## Ringkasan Cepat

Urutan singkatnya:

1. Pindah project ke `C:\xampp\htdocs\Komik_Website`
2. Nyalakan `Apache` dan `MySQL`
3. Buat database `komik_web`
4. Import `database/schema.sql`
5. Buat file `.env`
6. Generate hash admin
7. Insert admin ke tabel `users`
8. Buka `http://localhost/Komik_Website/public`

## Kalau Ada Error

Hal yang paling sering jadi masalah:

- `Apache` atau `MySQL` belum nyala
- file `.env` belum dibuat
- database `komik_web` belum ada
- `schema.sql` belum di-import
- password admin belum dimasukkan ke tabel `users`
- URL yang dibuka salah, harus ke `/public`

## File Terkait

- [README.md](D:/Kuliah/Komik_Website/README.md)
- [schema.sql](D:/Kuliah/Komik_Website/database/schema.sql)
- [hash-password.php](D:/Kuliah/Komik_Website/tools/hash-password.php)
