<?php
include 'Koneksi.php';

// Ambil semua data dari penambahanbuku
$q_buku = mysqli_query($koneksi, "SELECT ISBN, stok FROM penambahanbuku");

$counter = 1;

while ($buku = mysqli_fetch_assoc($q_buku)) {
    $isbn = mysqli_real_escape_string($koneksi, $buku['ISBN']);
    $stok = (int)$buku['stok'];

    // Cek berapa eksemplar yang sudah dibuat untuk ISBN ini
    $q_cek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM eksemplar_buku WHERE ISBN = '$isbn'");
    $d_cek = mysqli_fetch_assoc($q_cek);
    $sudah_ada = (int)$d_cek['total'];

    // Hitung berapa kekurangannya
    $kekurangan = $stok - $sudah_ada;

    for ($i = 0; $i < $kekurangan; $i++) {
        // Format kode buku unik (contoh: BK-001, BK-002)
        $kode_buku = "BK-" . str_pad($counter, 3, "0", STR_PAD_LEFT);
        
        // Pastikan kode_buku tidak terduplikasi di database
        while (mysqli_num_rows(mysqli_query($koneksi, "SELECT id_eksemplar FROM eksemplar_buku WHERE kode_buku = '$kode_buku'")) > 0) {
            $counter++;
            $kode_buku = "BK-" . str_pad($counter, 3, "0", STR_PAD_LEFT);
        }

        mysqli_query($koneksi, "INSERT INTO eksemplar_buku (ISBN, kode_buku, status) VALUES ('$isbn', '$kode_buku', 'Tersedia')");
        $counter++;
    }
}

echo "<h3>Berhasil men-generate kode eksemplar untuk semua stok buku!</h3>";
echo "<a href='pencarian.php'>Kembali ke Pencarian</a>";
?>