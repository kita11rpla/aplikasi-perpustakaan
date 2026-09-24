<?php
// Inisialisasi tabel yang diperlukan jika belum ada.
function initializeDatabaseTables($koneksi) {
    $column_check = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penambahanbuku'
          AND COLUMN_NAME = 'stok'
    ");

    if ($column_check && (int)mysqli_fetch_assoc($column_check)['total'] === 0) {
        mysqli_query($koneksi, "ALTER TABLE penambahanbuku ADD COLUMN stok int(11) NOT NULL DEFAULT 1 AFTER ISBN");
    }

    $catalog_column_check = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'penambahanbuku'
          AND COLUMN_NAME = 'katalog'
    ");

    if ($catalog_column_check && (int)mysqli_fetch_assoc($catalog_column_check)['total'] === 0) {
        mysqli_query($koneksi, "ALTER TABLE penambahanbuku ADD COLUMN katalog varchar(100) DEFAULT NULL AFTER penulis");
    }

    $transaction_column_check = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'transaksi'
          AND COLUMN_NAME = 'kode_buku'
    ");

    if ($transaction_column_check && (int)mysqli_fetch_assoc($transaction_column_check)['total'] === 0) {
        mysqli_query($koneksi, "ALTER TABLE transaksi ADD COLUMN kode_buku varchar(30) DEFAULT NULL AFTER ISBN");
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS eksemplar_buku (
            id_eksemplar int(11) NOT NULL AUTO_INCREMENT,
            ISBN varchar(30) NOT NULL,
            kode_buku varchar(30) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'Tersedia',
            PRIMARY KEY (id_eksemplar),
            UNIQUE KEY uq_kode_buku (kode_buku)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "CREATE TABLE IF NOT EXISTS siswa (
            nisn varchar(20) NOT NULL,
            nama varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            PRIMARY KEY (nisn)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        "CREATE TABLE IF NOT EXISTS buku (
            id int(11) NOT NULL AUTO_INCREMENT,
            judul varchar(255) NOT NULL,
            pengarang varchar(255) NOT NULL,
            stok int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    ];

    foreach ($queries as $query) {
        if (!mysqli_query($koneksi, $query)) {
            die('Gagal membuat tabel: ' . mysqli_error($koneksi));
        }
    }

    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS cnt FROM siswa");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row['cnt'] == 0) {
            mysqli_query($koneksi, "INSERT INTO siswa (nisn, nama, password) VALUES
                ('20240001', 'Diki Setiawan', 'test123'),
                ('20240002', 'Siti Aminah', 'password123')");
        }
    }

    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS cnt FROM buku");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row['cnt'] == 0) {
            mysqli_query($koneksi, "INSERT INTO buku (judul, pengarang, stok) VALUES
                ('Pemrograman Web dengan PHP & MySQL', 'Budi Raharjo', 1),
                ('Belajar Dasar HTML & CSS', 'Ahmad Fauzi', 1),
                ('Algoritma & Struktur Data', 'Siti Aminah', 0)");
        }
    }
}
?>