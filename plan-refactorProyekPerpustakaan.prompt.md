## Rencana Refactor Proyek Perpustakaan

### 1. Pembenahan Desain Login

Audit dan polish halaman:

- `admin/login-admin.php`
- `user/login-siswa.php`
- `user/register.php`

Perubahan yang perlu dilakukan:

- Membuat desain login lebih rapi dan konsisten.
- Memperjelas label, placeholder, tombol, dan pesan error.
- Membedakan fungsi tombol utama dan tombol sekunder.
- Menambahkan tampilan responsif untuk desktop dan mobile.
- Menentukan desain berdasarkan jenis login.

Keputusan awal yang direkomendasikan:

- Admin login menggunakan `email + password`.
- Siswa login menggunakan `NISN + password`.

Alasannya, sistem saat ini sudah menggunakan email untuk admin dan NISN sebagai identitas unik siswa.

Sebelum implementasi, cari dan sepakati beberapa referensi desain login.

### 2. Perbaikan URL, Path, Dan Koneksi Halaman

Setelah desain selesai, semua halaman harus diperiksa kembali agar tetap saling terhubung.

Yang perlu diaudit:

- Redirect `Location`.
- Link `href`.
- Form `action`.
- `require_once` dan `include`.
- Session admin dan siswa.
- Link login, dashboard, logout, transaksi, dan laporan.

URL utama yang digunakan:

- `/admin/login-admin.php`
- `/admin/dashboard.php`
- `/user/login-siswa.php`
- `/user/dashboard-siswa.php`

Alur yang harus diuji:

1. Login gagal.
2. Login berhasil.
3. Akses dashboard tanpa login.
4. Logout.
5. Admin tidak dapat masuk ke area siswa.
6. Siswa tidak dapat masuk ke area admin.

### 3. Perapian Database

File [dbbuku.sql](dbbuku.sql) saat ini masih mencampur:

- Data admin.
- Data siswa.
- Data buku.
- Data transaksi.
- Data denda.
- Tabel `buku` dan `penambahanbuku` yang memiliki fungsi mirip.
- Penamaan tabel dan kolom yang belum konsisten.

Masalah utama:

- Admin menggunakan tabel `users`.
- Siswa menggunakan tabel `siswa`.
- Belum ada struktur role yang jelas.
- Tabel buku masih memiliki data yang berulang.
- Relasi transaksi masih bergantung pada teks ISBN.
- Belum tersedia kolom genre, klasifikasi, dan cover.

Standar penamaan yang disarankan:

- Tabel menggunakan `snake_case`.
- Kolom menggunakan `snake_case`.
- Gunakan nama yang konsisten, misalnya:
  - `admin_users`
  - `student_users`
  - `books`
  - `book_copies`
  - `transactions`
  - `fines`

Istilah produk boleh menggunakan `user-admin` dan `user-siswa`, tetapi nama tabel sebaiknya tidak menggunakan tanda hubung karena kurang aman dan tidak umum dalam SQL.

### 4. Pemisahan Database

Ada dua pilihan:

#### Pilihan A: Satu database dengan dua file SQL

Contoh:

- `01-accounts.sql`
  - Admin.
  - Siswa.
  - Role.
  - Password.
- `02-library.sql`
  - Buku.
  - Eksemplar.
  - Genre.
  - Klasifikasi.
  - Transaksi.
  - Denda.

Pilihan ini direkomendasikan untuk tahap sekarang karena lebih sederhana dan memudahkan relasi antara user dan transaksi.

#### Pilihan B: Dua database MySQL terpisah

Contoh:

- Database akun.
- Database perpustakaan.

Konsekuensinya:

- Membutuhkan dua koneksi database.
- Backup harus dilakukan terpisah.
- Relasi user dan transaksi menjadi lebih kompleks.
- Harus mengatur sinkronisasi ID user.
- Query lintas database lebih sulit dirawat.

Karena itu, dua file SQL dalam satu database lebih disarankan daripada langsung membuat dua database fisik.

### 5. Migrasi Database

Sebelum mengganti nama tabel atau kolom:

1. Backup database lama.
2. Buat mapping nama lama ke nama baru.
3. Buat file migration.
4. Pindahkan data lama ke struktur baru.
5. Perbarui query PHP.
6. Uji semua fitur.
7. Siapkan rollback apabila migrasi gagal.

Perubahan database akan berdampak pada:

- [config/koneksi.php](config/koneksi.php)
- [admin/login-admin.php](admin/login-admin.php)
- [user/login-siswa.php](user/login-siswa.php)
- [admin/dashboard.php](admin/dashboard.php)
- [admin/pencarian.php](admin/pencarian.php)
- [admin/penambahan-buku.php](admin/penambahan-buku.php)
- [admin/transaksi.php](admin/transaksi.php)
- [admin/laporan.php](admin/laporan.php)

### 6. Fitur Katalog Berikutnya

Setelah schema database stabil, lanjutkan:

- Filter berdasarkan genre.
- Filter berdasarkan klasifikasi buku.
- Kolom pencarian yang lebih besar dan responsif.
- Tampilan cover buku.
- Fallback cover jika gambar belum tersedia.
- Validasi ukuran dan format file cover.

### Urutan Eksekusi Besok

1. Sepakati referensi desain login.
2. Tetapkan format login admin dan siswa.
3. Polish halaman login.
4. Audit seluruh URL dan redirect.
5. Backup database lama.
6. Audit dan rapikan schema.
7. Tentukan satu database atau dua database.
8. Buat migration SQL.
9. Perbarui query PHP.
10. Uji login, katalog, transaksi, dan laporan.

Rencana ini menjadi dasar pengerjaan dan refinement berikutnya. Implementasi tidak dimulai sebelum keputusan desain login, format autentikasi, dan target schema disepakati.
