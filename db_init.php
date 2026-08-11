<?php
// Inisialisasi tabel yang diperlukan jika belum ada.
function initializeDatabaseTables($koneksi) {
    $queries = [
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