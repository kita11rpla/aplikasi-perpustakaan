-- ============================================================
-- Skema Database: Aplikasi Perpustakaan Sekolah
-- Fitur: Validasi barcode buku untuk Pinjam / Beli
-- ============================================================

CREATE DATABASE IF NOT EXISTS perpustakaan_sekolah;
USE perpustakaan_sekolah;

-- Tabel Buku
-- `barcode` menyimpan kode yang terbaca oleh scanner (biasanya ISBN-13/ISBN-10).
-- Jika buku belum punya barcode dari penerbit, generate kode internal sendiri
-- (lihat catatan di bagian bawah file ini).
CREATE TABLE IF NOT EXISTS buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) NOT NULL UNIQUE,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(150) DEFAULT NULL,
    penerbit VARCHAR(150) DEFAULT NULL,
    kategori VARCHAR(100) DEFAULT NULL,
    stok_pinjam INT NOT NULL DEFAULT 0,      -- eksemplar tersedia untuk dipinjam
    stok_jual INT NOT NULL DEFAULT 0,        -- eksemplar tersedia untuk dijual (mis. buku paket)
    harga DECIMAL(10,2) DEFAULT NULL,        -- harga jual, boleh NULL jika buku tidak dijual
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_barcode (barcode)
) ENGINE=InnoDB;

-- Tabel Anggota (siswa/guru peminjam)
CREATE TABLE IF NOT EXISTS anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis_nip VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    jenis ENUM('siswa','guru','staf') NOT NULL DEFAULT 'siswa',
    kelas VARCHAR(30) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    max_pinjam INT NOT NULL DEFAULT 2,       -- batas jumlah buku yang boleh dipinjam bersamaan
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Transaksi (mencatat baik peminjaman maupun pembelian)
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buku_id INT NOT NULL,
    anggota_id INT NOT NULL,
    jenis ENUM('pinjam','beli') NOT NULL,
    tanggal_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    jatuh_tempo DATE DEFAULT NULL,           -- hanya untuk jenis 'pinjam'
    tanggal_kembali DATETIME DEFAULT NULL,   -- diisi saat buku dikembalikan
    harga_saat_transaksi DECIMAL(10,2) DEFAULT NULL, -- hanya untuk jenis 'beli'
    status ENUM('berjalan','selesai','terlambat') NOT NULL DEFAULT 'berjalan',
    petugas VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (buku_id) REFERENCES buku(id),
    FOREIGN KEY (anggota_id) REFERENCES anggota(id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- CATATAN soal barcode:
-- - Jika buku fisik sudah punya barcode ISBN dari penerbit, cukup
--   input angka ISBN itu ke kolom `barcode` saat buku didaftarkan.
-- - Jika belum punya barcode (buku lama/paket internal), generate kode
--   sendiri saat pendataan, misalnya format: SEKOLAH-<ID>-<random>,
--   lalu cetak sebagai barcode Code128 untuk ditempel di buku
--   (bisa pakai library seperti picqer/php-barcode-generator).
-- ============================================================

-- Contoh data
INSERT INTO buku (barcode, judul, penulis, stok_pinjam, stok_jual, harga) VALUES
('9786020000001', 'Laskar Pelangi', 'Andrea Hirata', 3, 0, NULL),
('9786020000002', 'Matematika Kelas X', 'Tim MGMP', 5, 10, 45000.00);

INSERT INTO anggota (nis_nip, nama, jenis, kelas, max_pinjam) VALUES
('2024001', 'Diki Setiawan', 'siswa', 'X-A', 2);