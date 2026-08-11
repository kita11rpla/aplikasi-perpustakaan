<?php
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $db = 'dbbuku';

    $koneksi = mysqli_connect($host, $user, $password, $db);
    mysqli_set_charset($koneksi, 'utf8mb4');

    if (mysqli_connect_errno()) {
        die('Koneksi database gagal: ' . mysqli_connect_error());
    }

    if (file_exists(__DIR__ . '/db_init.php')) {
        require_once __DIR__ . '/db_init.php';
        initializeDatabaseTables($koneksi);
    }
?>