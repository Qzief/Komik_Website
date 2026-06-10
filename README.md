# KomikHub

Website komik berbasis PHP native + MySQL dengan fitur:

- login dan register
- like komik
- bookmark komik
- voting apakah komik perlu dilanjutkan atau tidak
- panel admin untuk mengelola metadata komik dan chapter
- input chapter manual memakai URL CDN hasil scraping
- kompatibel dengan project scraper di `D:\Program\Project\ComicsScraping`

## Kompatibilitas Dengan Project Scraper

Project ini sengaja memakai tabel inti yang sama dengan scraper:

- `mangas`
- `chapters`
- `chapter_images`

Artinya ada 2 cara mengisi data:

1. Admin input komik dan chapter langsung dari panel website memakai URL CDN.
2. Project scraper sinkron langsung ke database yang sama memakai:

```bash
cd D:\Program\Project\ComicsScraping
python main.py -f urls.txt -o ./ --chapters smart --mysql-init --mysql-sync
```

Website ini hanya menambah tabel aplikasi:

- `users`
- `comic_likes`
- `comic_bookmarks`
- `comic_votes`

serta beberapa kolom metadata tambahan di `mangas`:

- `description`
- `author`
- `artist`
- `status`

Scraper tetap aman karena proses `INSERT ... ON DUPLICATE KEY UPDATE` di project scraping tidak bergantung pada kolom-kolom tambahan tersebut.

## Struktur Project

```text
public/          Front controller dan asset
app/             Bootstrap, auth, helper, repository
routes/          Routing sederhana
views/           Template halaman
database/        Schema MySQL
tools/           Utilitas kecil
```

## Setup

1. Salin file environment:

```bash
copy .env.example .env
```

2. Buat database MySQL, misalnya `komik_web`.

3. Import schema:

```bash
mysql -u root -p komik_web < database/schema.sql
```

4. Isi `.env`:

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

5. Buat password hash admin:

```bash
php tools/hash-password.php admin123456
```

6. Masukkan admin ke database:

```sql
INSERT INTO users (name, email, password, role)
VALUES ('Administrator', 'admin@local.test', 'HASIL_HASH_DARI_LANGKAH_5', 'admin');
```

7. Jalankan dengan salah satu cara:

- Taruh folder ini di `htdocs` XAMPP lalu buka `http://localhost/Komik_Website/public`
- Atau gunakan built-in server:

```bash
php -S localhost:8000 -t public
```

## Flow Admin

### Tambah komik manual

Admin bisa mengisi:

- `judul`
- `slug`
- `manga_url`
- `external_manga_id`
- `source_base`
- `thumbnail_url`
- metadata tambahan

Supaya sinkron dengan scraper, usahakan:

- `slug` sama dengan slug dari hasil scraping
- `source_base` sama dengan domain sumber, misalnya `https://05.ikiru.wtf`
- `external_manga_id` diisi jika ada ID manga dari source

### Tambah chapter manual dari CDN

Di form chapter, isi:

- nomor chapter
- label chapter
- URL halaman chapter
- daftar URL gambar CDN satu per baris

Semua URL akan otomatis disimpan ke tabel `chapter_images` sesuai urutan.

## Fitur User

- user bisa daftar dan login
- user bisa like komik
- user bisa bookmark komik
- user bisa vote `continue` atau `stop`
- halaman bookmark hanya menampilkan komik milik user yang login

## Catatan

- Tidak memakai framework agar ringan dan mudah dipindah.
- Routing dibuat lewat `public/index.php` dan query `route`.
- Jika Anda ingin, tahap berikutnya saya bisa lanjutkan dengan:
  - import otomatis dari folder JSON hasil scraping
  - dashboard statistik lebih lengkap
  - pencarian chapter
  - komentar user
  - REST API/AJAX
