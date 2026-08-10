<?php
/**
 * api/process_transaksi.php
 *
 * Dipanggil SETELAH validate_barcode.php mengonfirmasi buku valid, dan
 * petugas menekan tombol "Proses" di scan.php.
 *
 * POST fields: barcode, jenis ('pinjam'|'beli'), nis_nip, petugas
 *
 * PENTING: validasi di sini diulang ulang lagi dari server (bukan cuma
 * percaya hasil validate_barcode.php tadi), supaya tidak ada celah race
 * condition antara scan pertama dan klik "Proses".
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$barcode = preg_replace('/[^A-Za-z0-9\-]/', '', trim((string)($_POST['barcode'] ?? '')));
$jenis   = trim((string)($_POST['jenis'] ?? ''));
$nisNip  = trim((string)($_POST['nis_nip'] ?? ''));
$petugas = trim((string)($_POST['petugas'] ?? 'sistem'));

if ($barcode === '' || !in_array($jenis, ['pinjam', 'beli'], true) || $nisNip === '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    // Kunci baris buku (FOR UPDATE) supaya dua scan bersamaan tidak
    // sama-sama lolos saat stok tinggal 1.
    $stmt = $pdo->prepare('SELECT * FROM buku WHERE barcode = :barcode LIMIT 1 FOR UPDATE');
    $stmt->execute(['barcode' => $barcode]);
    $buku = $stmt->fetch();

    if (!$buku || $buku['status'] !== 'aktif') {
        throw new RuntimeException('Buku tidak ditemukan atau nonaktif.');
    }

    $stmt = $pdo->prepare('SELECT * FROM anggota WHERE nis_nip = :nis LIMIT 1 FOR UPDATE');
    $stmt->execute(['nis' => $nisNip]);
    $anggota = $stmt->fetch();

    if (!$anggota || $anggota['status'] !== 'aktif') {
        throw new RuntimeException('Anggota tidak ditemukan atau nonaktif.');
    }

    if ($jenis === 'pinjam') {
        if ((int)$buku['stok_pinjam'] <= 0) {
            throw new RuntimeException('Stok pinjam habis.');
        }

        // Cek batas maksimal pinjam per anggota
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS jumlah FROM transaksi
             WHERE anggota_id = :aid AND jenis = 'pinjam' AND status = 'berjalan'"
        );
        $stmt->execute(['aid' => $anggota['id']]);
        $jumlahPinjamAktif = (int)$stmt->fetch()['jumlah'];

        if ($jumlahPinjamAktif >= (int)$anggota['max_pinjam']) {
            throw new RuntimeException(
                "Anggota sudah mencapai batas maksimal pinjam ({$anggota['max_pinjam']} buku)."
            );
        }

        $pdo->prepare('UPDATE buku SET stok_pinjam = stok_pinjam - 1 WHERE id = :id')
            ->execute(['id' => $buku['id']]);

        $jatuhTempo = (new DateTime('+7 days'))->format('Y-m-d'); // masa pinjam 7 hari

        $pdo->prepare(
            "INSERT INTO transaksi (buku_id, anggota_id, jenis, jatuh_tempo, status, petugas)
             VALUES (:buku_id, :anggota_id, 'pinjam', :jatuh_tempo, 'berjalan', :petugas)"
        )->execute([
            'buku_id'     => $buku['id'],
            'anggota_id'  => $anggota['id'],
            'jatuh_tempo' => $jatuhTempo,
            'petugas'     => $petugas,
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "Peminjaman berhasil. Wajib dikembalikan sebelum $jatuhTempo.",
        ]);
        exit;
    }

    // jenis === 'beli'
    if ($buku['harga'] === null || (int)$buku['stok_jual'] <= 0) {
        throw new RuntimeException('Buku tidak tersedia untuk dibeli.');
    }

    $pdo->prepare('UPDATE buku SET stok_jual = stok_jual - 1 WHERE id = :id')
        ->execute(['id' => $buku['id']]);

    $pdo->prepare(
        "INSERT INTO transaksi (buku_id, anggota_id, jenis, harga_saat_transaksi, status, petugas)
         VALUES (:buku_id, :anggota_id, 'beli', :harga, 'selesai', :petugas)"
    )->execute([
        'buku_id'    => $buku['id'],
        'anggota_id' => $anggota['id'],
        'harga'      => $buku['harga'],
        'petugas'    => $petugas,
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pembelian berhasil dicatat. Total: Rp ' . number_format((float)$buku['harga'], 0, ',', '.'),
    ]);
} catch (RuntimeException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    error_log('process_transaksi.php DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Kesalahan server database.']);
}
