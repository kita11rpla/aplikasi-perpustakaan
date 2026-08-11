<?php
/**
 * api/validate_barcode.php
 *
 * Menerima kode barcode hasil scan (POST, field: barcode, jenis).
 * `jenis` = 'pinjam' atau 'beli' -> menentukan validasi mana yang dipakai.
 *
 * Mengembalikan JSON:
 * {
 *   "valid": true|false,
 *   "message": "...",
 *   "buku": { ...detail buku... } | null
 * }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$barcode = trim((string)($_POST['barcode'] ?? ''));
$jenis   = trim((string)($_POST['jenis'] ?? 'pinjam')); // 'pinjam' | 'beli'

// Validasi input dasar
if ($barcode === '') {
    echo json_encode(['valid' => false, 'message' => 'Barcode kosong / gagal terbaca.', 'buku' => null]);
    exit;
}

if (!in_array($jenis, ['pinjam', 'beli'], true)) {
    echo json_encode(['valid' => false, 'message' => 'Jenis transaksi tidak dikenal.', 'buku' => null]);
    exit;
}

// Barcode scanner kadang mengirim karakter kontrol / whitespace ekstra di akhir
$barcode = preg_replace('/[^A-Za-z0-9\-]/', '', $barcode);

try {
    $pdo = getDB();

    $stmt = $pdo->prepare('SELECT * FROM buku WHERE barcode = :barcode LIMIT 1');
    $stmt->execute(['barcode' => $barcode]);
    $buku = $stmt->fetch();

    if (!$buku) {
        echo json_encode([
            'valid'   => false,
            'message' => "Buku dengan barcode \"$barcode\" tidak ditemukan di database.",
            'buku'    => null,
        ]);
        exit;
    }

    if ($buku['status'] !== 'aktif') {
        echo json_encode([
            'valid'   => false,
            'message' => 'Buku ini berstatus nonaktif dan tidak bisa ditransaksikan.',
            'buku'    => $buku,
        ]);
        exit;
    }

    if ($jenis === 'pinjam') {
        if ((int)$buku['stok_pinjam'] <= 0) {
            echo json_encode([
                'valid'   => false,
                'message' => 'Stok pinjam habis. Semua eksemplar sedang dipinjam.',
                'buku'    => $buku,
            ]);
            exit;
        }

        echo json_encode([
            'valid'   => true,
            'message' => 'Buku valid dan tersedia untuk dipinjam.',
            'buku'    => $buku,
        ]);
        exit;
    }

    // jenis === 'beli'
    if ($buku['harga'] === null) {
        echo json_encode([
            'valid'   => false,
            'message' => 'Buku ini tidak dijual (tidak ada harga terdaftar).',
            'buku'    => $buku,
        ]);
        exit;
    }

    if ((int)$buku['stok_jual'] <= 0) {
        echo json_encode([
            'valid'   => false,
            'message' => 'Stok jual habis.',
            'buku'    => $buku,
        ]);
        exit;
    }

    echo json_encode([
        'valid'   => true,
        'message' => 'Buku valid dan tersedia untuk dibeli.',
        'buku'    => $buku,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Kesalahan server database.', 'buku' => null]);
    // log the real error server-side instead of exposing it to the client
    error_log('validate_barcode.php DB error: ' . $e->getMessage());
}
