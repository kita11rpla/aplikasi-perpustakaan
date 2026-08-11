# Ringkasan Project Aplikasi Perpustakaan

## Gambaran Umum
Project ini adalah aplikasi perpustakaan berbasis PHP dan MySQL untuk XAMPP. Folder `cadangan/` tidak dibaca saat ringkasan ini dibuat.

## Koneksi Database
File utama koneksi: `Koneksi.php`
- Host: `localhost`
- User: `root`
- Password: `` (kosong)
- Database: `dbbuku`

File `.env` di root berisi:
- `NAMA_DB=dbbuku`
- `PASS_DB=` (kosong)

## Flow Login / Register / Dashboard Siswa
- `login-siswa.php`
  - Menghubungkan ke `Koneksi.php`
  - Memeriksa tabel `siswa` berdasarkan `nisn` dan `password`
  - Menyimpan session `$_SESSION['siswa']` saat login berhasil
  - Mengarahkan pengguna ke `dashboard-siswa.php`

- `register.php`
  - Menghubungkan ke `Koneksi.php`
  - Menyimpan data baru ke tabel `siswa`
  - Memeriksa apakah `nisn` sudah terdaftar sebelum menyimpan

- `dashboard-siswa.php`
  - Melindungi halaman dengan session `$_SESSION['siswa']`
  - Mengambil daftar buku dari tabel `buku`
  - Menampilkan nama siswa yang login

- `logout-siswa.php`
  - File baru yang dibuat untuk menghancurkan session dan kembali ke `login-siswa.php`

## Database `dbbuku.sql`
File `dbbuku.sql` sekarang berisi:
- Perintah `CREATE DATABASE IF NOT EXISTS dbbuku` dan `USE dbbuku`
- Tabel `penambahanbuku`
- Tabel `transaksi`
- Tabel `users`
- Tabel `siswa` (ditambahkan)
- Tabel `buku` (ditambahkan)

## Akun Pengujian
Akun siswa testing yang sudah disiapkan dalam SQL:
- NISN: `20240001`, Password: `test123`
- NISN: `20240002`, Password: `password123`

## Catatan Tambahan
- `dashboard-siswa.php` sekarang menggunakan session dan me-redirect jika belum login.
- `login-siswa.php` tidak lagi mengirim form langsung ke `dashboard-siswa.php` melalui `formaction`; alur login sekarang terjadi di server.
- `register.php` mengarahkan pengguna ke `login-siswa.php` setelah pendaftaran berhasil.

## Hal yang Perlu Dicek Saat Deploy
- Pastikan MySQL di XAMPP berjalan.
- Import `dbbuku.sql` ke MySQL.
- Pastikan file `Koneksi.php` dapat mengakses database tanpa password.
