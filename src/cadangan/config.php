<?php
/**
 * config.php
 * Koneksi database pakai PDO. Sesuaikan kredensial di bawah.
 */

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'perpustakaan_sekolah';
const DB_USER = 'root';
const DB_PASS = '';

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // pakai prepared statement asli -> aman dari SQL injection
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
