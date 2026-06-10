# Dokumentasi Arsitektur dan Fitur Kurotsuki

Dokumen ini menjelaskan inti program Kurotsuki secara ringkas tetapi tetap teknis: bagaimana arsitekturnya disusun, bagaimana pola MVC dan Repository Pattern dipakai, fitur apa saja yang tersedia, dan apa yang membuat website ini menonjol.

Catatan:
- Nomor baris di bawah mengikuti snapshot codebase saat dokumen ini dibuat.
- Jika file berubah di masa depan, nomor baris bisa ikut bergeser.

## 1. Gambaran Singkat Program

Kurotsuki adalah website katalog komik/manhwa dengan fokus pada:
- katalog yang cepat discan lewat cover,
- detail komik yang informatif,
- reader chapter yang nyaman di desktop dan mobile,
- AI smart search dengan natural language,
- integrasi yang tetap kompatibel dengan hasil scraping eksternal,
- panel admin untuk mengelola komik dan chapter tanpa mengubah jalur data utama.

Secara struktur, proyek ini memakai pendekatan PHP sederhana namun rapi:
- `public/index.php` sebagai entry point,
- `routes/web.php` sebagai router sekaligus controller layer,
- `app/repositories.php` sebagai pusat query dan logika akses data,
- folder `views/` sebagai presentation layer.

## 2. MVC yang Dipakai di Proyek Ini

Di proyek ini, MVC tidak dibungkus framework besar seperti Laravel, tetapi diterapkan secara manual dan cukup jelas.

### 2.1 Controller

Controller utama berada di dua tempat berikut:

| Bagian | File | Baris | Fungsi |
| --- | --- | --- | --- |
| Entry point | `public/index.php` | `1-9` | Memulai aplikasi, memanggil bootstrap, membaca route aktif, lalu meneruskan ke router utama. |
| Router + controller actions | `routes/web.php` | `123-506` | Menentukan aksi untuk setiap route seperti home, detail komik, reader, login, bookmark, vote, dan admin CRUD. |

Controller pada `routes/web.php` menangani:
- validasi request,
- guard akses seperti `require_login()` dan `require_admin()`,
- pemanggilan repository,
- pengiriman data ke view lewat `render(...)`,
- redirect dan flash message.

Contoh route penting:
- AI search endpoint: `routes/web.php:188-246`
- Home katalog: `routes/web.php:248-267`
- Detail komik: `routes/web.php:269-277`
- Reader chapter: `routes/web.php:279-288`
- Bookmark, like, vote: `routes/web.php:350-385`
- Admin manga/chapter CRUD: `routes/web.php:387-500`

### 2.2 Model

Lapisan model di proyek ini tidak dibuat sebagai class model per entitas, tetapi dibagi menjadi:
- koneksi database,
- query helper,
- repository functions,
- auth helper yang tetap berhubungan dengan data user.

| Bagian | File | Baris | Fungsi |
| --- | --- | --- | --- |
| Bootstrap modul | `app/bootstrap.php` | `8-36` | Memuat `.env`, helper, database, auth, repository, dan session. |
| Koneksi DB | `app/database.php` | `5-40` | Menyediakan `db()` dan `db_query()` sebagai dasar seluruh query. |
| Auth state | `app/auth.php` | `5-71` | Menyediakan current user, login state, role admin, dan proteksi route. |
| Business data layer | `app/repositories.php` | `196-1154` | Pusat query, agregasi, pencarian, detail, interaksi user, dan CRUD admin. |

Jadi, lapisan model di proyek ini lebih mirip kombinasi:
- `database access layer`,
- `auth service`,
- `repository layer`.

### 2.3 View

Semua tampilan utama berada di folder `views/`.

| Bagian | File | Baris | Fungsi |
| --- | --- | --- | --- |
| Render helper | `app/helpers.php` | `194-200` | Menyuntikkan data ke file view lalu membungkusnya dengan layout utama. |
| Layout global | `views/layout.php` | `1-136` | Kerangka HTML global, head, favicon, brand, navbar, topbar AI search, flash message, dan script global. |
| Home | `views/home.php` | `14-187` | Hero, featured comic, mobile AI search, grid katalog, dan pagination. |
| Detail komik | `views/comic-show.php` | `1-135` | Cover, metadata, genre, tombol like/bookmark, vote, dan daftar chapter. |
| Reader | `views/reader.php` | `1-9` | Wrapper reader yang merakit area gambar, topbar mobile, controls, dan chapter picker. |
| Card komik reusable | `views/partials/comic-card.php` | `1-24` | Template card komik model “buku” untuk katalog dan bookmark. |

View dibagi lagi menjadi partial agar tidak menumpuk dalam satu file besar, contohnya:
- `views/layouts/reader-mobile-header.php:10-37`
- `views/layouts/reader-mobile-controls.php:9-50`
- `views/layouts/reader-chapter-picker.php:1-44`

## 3. Repository Pattern yang Dipakai

### 3.1 Penjelasan Singkat

Repository Pattern dipakai untuk memisahkan logika akses data dari controller.

Artinya:
- controller di `routes/web.php` tidak menulis query SQL langsung,
- controller cukup memanggil fungsi seperti `paginate_mangas(...)`, `find_manga_by_slug(...)`, `find_chapter(...)`, `toggle_bookmark(...)`, `create_manga(...)`, dan seterusnya,
- detail query, join, agregasi, ranking, dan transformasi data disimpan di `app/repositories.php`.

Ini penting karena membuat:
- kode lebih rapi,
- controller lebih fokus pada flow request,
- query lebih mudah dirawat,
- fitur baru lebih mudah ditambah tanpa mengacaukan route logic.

### 3.2 Letak Repository Pattern di Codebase

Repository utama berada di:
- `app/repositories.php:196-1154`

Beberapa bagian repository yang paling penting:

| Repository Function | File | Baris | Fungsi |
| --- | --- | --- | --- |
| `build_ai_search_intent()` | `app/repositories.php` | `196-290` | Mengubah query bebas menjadi intent pencarian lokal/AI. |
| `score_manga_against_intent()` | `app/repositories.php` | `292-351` | Memberi skor relevansi hasil komik terhadap intent pencarian. |
| `paginate_mangas()` | `app/repositories.php` | `613-689` | Mengambil daftar komik dengan search, sorting, ranking, dan pagination. |
| `find_manga_by_slug()` | `app/repositories.php` | `691-739` | Mengambil detail komik lengkap beserta chapter dan statistik. |
| `find_chapter()` | `app/repositories.php` | `741-801` | Mengambil data chapter, gambar, prev/next chapter, dan daftar chapter. |
| `user_bookmarks()` | `app/repositories.php` | `803-818` | Mengambil daftar bookmark milik user. |
| `admin_dashboard_summary()` | `app/repositories.php` | `820-828` | Agregasi metrik admin dashboard. |
| `toggle_like()` | `app/repositories.php` | `877-896` | Menambah/menghapus like komik. |
| `toggle_bookmark()` | `app/repositories.php` | `898-917` | Menambah/menghapus bookmark komik. |
| `save_vote()` | `app/repositories.php` | `919-933` | Menyimpan vote lanjut/stop dengan upsert. |
| `all_mangas_for_admin()` | `app/repositories.php` | `935-949` | Mengambil seluruh komik untuk tabel admin. |
| `find_manga_by_id()` | `app/repositories.php` | `951-966` | Mengambil satu komik untuk form edit admin. |
| `create_manga()` | `app/repositories.php` | `968-1006` | Menyimpan komik baru. |
| `update_manga()` | `app/repositories.php` | `1008-1030` | Memperbarui metadata komik. |
| `delete_manga()` | `app/repositories.php` | `1032-1035` | Menghapus komik. |
| `create_chapter()` | `app/repositories.php` | `1049-1084` | Menyimpan chapter baru dan sinkron count. |
| `update_chapter()` | `app/repositories.php` | `1086-1109` | Memperbarui chapter dan gambar. |
| `replace_chapter_images()` | `app/repositories.php` | `1111-1127` | Mengganti daftar gambar chapter secara penuh. |
| `find_chapter_by_id()` | `app/repositories.php` | `1129-1145` | Mengambil chapter untuk form edit admin. |
| `delete_chapter()` | `app/repositories.php` | `1147-1154` | Menghapus chapter dan sinkron jumlah chapter manga. |

### 3.3 Kenapa Repository Pattern di proyek ini penting

Keuntungan paling terasa di proyek ini:
- route tetap singkat walau fitur banyak,
- query kompleks seperti AI search ranking tetap berada di satu tempat,
- data detail komik dan data reader tidak tercecer ke banyak file,
- admin CRUD dan user interaction tetap konsisten memakai pola akses data yang sama.

## 4. Alur Kerja Program Secara Ringkas

Secara sederhana, alurnya seperti ini:

1. Request masuk ke `public/index.php:5-9`.
2. `app/bootstrap.php:8-36` memuat env, helper, DB, auth, repository, dan session.
3. `routes/web.php:123-506` memilih route aktif.
4. Route memanggil function repository di `app/repositories.php`.
5. Data dilempar ke view melalui `app/helpers.php:194-200`.
6. `views/layout.php:1-136` membungkus page content dengan layout global.

## 5. Fitur-Fitur Utama Website

### 5.1 Home katalog dengan featured comic dan pagination

Implementasi utama:
- controller: `routes/web.php:248-267`
- view: `views/home.php:14-187`

Yang dikerjakan:
- menampilkan hero katalog,
- featured comic,
- cover stack visual,
- grid komik reusable,
- pagination,
- limit tampil yang adaptif untuk desktop/mobile lewat `app/helpers.php:173-191`.

### 5.2 AI Smart Search dengan natural language

Implementasi utama:
- endpoint: `routes/web.php:188-246`
- intent + ranking: `app/repositories.php:196-351`
- layout dropdown search: `views/layout.php:43-90`
- mobile AI search: `views/home.php:94-143`
- frontend interaksi: `public/assets/js/search-ai.js:55-264`

Fitur ini memungkinkan user mencari dengan kalimat bebas seperti:
- romance kerajaan cewek kuat
- manhwa lucu yang ringan
- cerita sedih dan seru

### 5.3 Detail komik lengkap

Implementasi utama:
- route: `routes/web.php:269-277`
- repository detail: `app/repositories.php:691-739`
- view detail: `views/comic-show.php:1-135`

Yang ditampilkan:
- cover komik,
- status,
- jumlah chapter,
- author/artist,
- deskripsi,
- genre,
- source base,
- statistik like/bookmark/vote,
- daftar chapter.

### 5.4 Reader chapter yang fokus ke pengalaman baca

Implementasi utama:
- route: `routes/web.php:279-288`
- repository reader: `app/repositories.php:741-801`
- wrapper view: `views/reader.php:1-9`
- mobile topbar: `views/layouts/reader-mobile-header.php:10-37`
- mobile controls: `views/layouts/reader-mobile-controls.php:9-50`
- chapter picker: `views/layouts/reader-chapter-picker.php:1-44`
- interaksi mobile: `public/assets/js/reader-mobile.js:1-126`

Fitur reader:
- gambar chapter berurutan,
- prev/next chapter,
- topbar dan control mobile hide/show saat area baca disentuh,
- chapter picker ala app,
- urutan daftar chapter tersimpan di browser user.

### 5.5 Like, bookmark, dan vote kelanjutan komik

Implementasi utama:
- route like: `routes/web.php:355-362`
- route bookmark: `routes/web.php:364-371`
- route vote: `routes/web.php:373-385`
- repository like: `app/repositories.php:877-896`
- repository bookmark: `app/repositories.php:898-917`
- repository vote: `app/repositories.php:919-933`
- UI detail komik: `views/comic-show.php:31-98`

Ini membuat website tidak hanya jadi katalog pasif, tapi punya interaksi komunitas ringan.

### 5.6 Bookmark page

Implementasi utama:
- route: `routes/web.php:350-353`
- repository: `app/repositories.php:803-818`
- view: `views/bookmarks.php:1-19`

User bisa menyimpan komik dan membukanya lagi dari halaman bookmark pribadi.

### 5.7 Panel admin untuk manga dan chapter

Implementasi utama:
- dashboard: `routes/web.php:387-393`, `views/admin-dashboard.php:1-54`
- form manga: `routes/web.php:395-442`, `views/admin-manga-form.php:17-78`
- form chapter: `routes/web.php:444-500`, `views/admin-chapter-form.php:10-40`
- CRUD data: `app/repositories.php:951-1154`

Admin bisa:
- tambah/edit/hapus komik,
- tambah/edit/hapus chapter,
- mengelola metadata yang tetap kompatibel dengan scraper.

### 5.8 Mobile bottom navigation

Implementasi utama:
- layout mobile nav: `public/layouts/mobile-navbar.php:1-55`
- include global: `views/layout.php:130-134`

Ini membuat pengalaman mobile terasa lebih mirip app daripada website biasa.

### 5.9 Image proxy + cache

Implementasi utama:
- `routes/web.php:123-186`

Fungsi ini cukup penting karena:
- cover dan image dari sumber luar diproxy lewat server sendiri,
- ada cache file lokal,
- website lebih stabil saat menampilkan gambar dari CDN luar,
- lebih aman daripada menembak hotlink mentah di semua tempat.

## 6. Keunikan Website Kurotsuki

Berikut bagian yang paling membedakan website ini dari katalog komik biasa.

### 6.1 AI search tidak sekadar form biasa

Website ini tidak berhenti di search keyword standar. Query user dipecah menjadi intent, mood, genre, tag, dan status lalu dipakai untuk ranking hasil.

Bagian penting:
- `app/repositories.php:196-351`
- `routes/web.php:188-246`
- `public/assets/js/search-ai.js:55-264`

### 6.2 Reader mobile terasa seperti aplikasi

Reader bukan sekadar daftar gambar. Ada:
- floating mobile header,
- icon-only bottom controls,
- hide/show chrome saat area gambar disentuh,
- chapter picker ala bottom sheet,
- urutan chapter yang diingat browser.

Bagian penting:
- `views/reader.php:1-9`
- `views/layouts/reader-mobile-header.php:10-37`
- `views/layouts/reader-mobile-controls.php:9-50`
- `views/layouts/reader-chapter-picker.php:1-44`
- `public/assets/js/reader-mobile.js:1-126`

### 6.3 Tetap kompatibel dengan alur scraping eksternal

Website ini disusun agar bisa menerima data dari scraper tanpa admin harus input ulang semuanya. Field penting seperti `source_base`, `external_manga_id`, `description`, dan `genres_json` sudah disiapkan.

Bagian penting:
- payload normalization: `routes/web.php:58-75`
- admin form manga: `views/admin-manga-form.php:17-78`
- create/update manga: `app/repositories.php:968-1030`
- detail yang menampilkan source dan genre: `views/comic-show.php:49-67`

### 6.4 Card katalog sudah dipikirkan sebagai reusable component

Alih-alih markup card diulang-ulang, proyek ini punya partial card reusable.

Bagian penting:
- `views/partials/comic-card.php:1-24`
- dipakai di `views/home.php:153-158`
- dipakai juga di `views/bookmarks.php:7-12`

Ini bagus untuk maintainability karena perubahan tampilan card cukup dilakukan di satu tempat.

### 6.5 Adaptif desktop dan mobile, bukan sekadar responsive biasa

Contohnya:
- jumlah item per page dibedakan antara desktop dan mobile,
- mobile search dipisahkan dari desktop topbar search,
- mobile navbar punya layout sendiri,
- mobile reader punya workflow sendiri.

Bagian penting:
- `app/helpers.php:173-191`
- `views/home.php:94-143`
- `public/layouts/mobile-navbar.php:1-55`
- `public/assets/js/reader-mobile.js:1-126`

## 7. Bagian Paling Penting untuk Dipahami Dulu

Kalau ada developer baru yang mau cepat paham proyek ini, urutan belajarnya paling enak begini:

1. `public/index.php:1-9`
   Ini pintu masuk aplikasi.

2. `routes/web.php:123-506`
   Ini pusat alur request dan route.

3. `app/repositories.php:613-1154`
   Ini pusat data utama untuk katalog, detail, reader, interaksi, dan admin CRUD.

4. `views/layout.php:1-136`
   Ini layout global yang dipakai semua halaman.

5. `views/home.php:14-187`, `views/comic-show.php:1-135`, `views/reader.php:1-9`
   Ini tiga halaman inti user journey.

6. `public/assets/js/search-ai.js:55-264` dan `public/assets/js/reader-mobile.js:1-126`
   Ini dua interaksi frontend paling khas di proyek ini.

## 8. Kesimpulan

Secara arsitektur, Kurotsuki sudah punya fondasi yang kuat walaupun tanpa framework besar:
- entry point jelas,
- controller terpusat,
- repository layer tegas,
- view dipisah dengan cukup baik,
- interaksi mobile dan AI search menjadi nilai tambah yang menonjol.

Kalau diringkas dalam satu kalimat:

> Kurotsuki adalah website katalog komik yang menggabungkan struktur PHP manual yang rapi, repository pattern yang jelas, AI search yang berguna, dan reader mobile yang terasa seperti aplikasi.
