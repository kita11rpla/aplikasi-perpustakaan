<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman: Wajib Login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    if (file_exists('../log-admin.php')) {
        header("Location: ../log-admin.php");
    } else {
        header("Location: log-admin.php");
    }
    exit();
}

include 'koneksi.php';

// Filter Pencarian & Tanggal di Laporan
$search     = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$tgl_mulai  = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$tgl_selesai= isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

// Query Mengambil Transaksi yang SUDAH DIKEMBALIKAN & LUNAS
$query = "SELECT t.*, p.judul_buku, p.penulis 
          FROM transaksi t 
          JOIN penambahanbuku p ON t.ISBN = p.ISBN 
          WHERE t.status_transaksi = 'dikembalikan'";

if ($search != '') {
    $query .= " AND (t.nama_peminjam LIKE '%$search%' OR p.judul_buku LIKE '%$search%' OR t.ISBN LIKE '%$search%')";
}

if ($tgl_mulai != '' && $tgl_selesai != '') {
    $query .= " AND (t.tanggal_kembali BETWEEN '$tgl_mulai' AND '$tgl_selesai')";
}

$query .= " ORDER BY t.tanggal_kembali DESC";
$result = mysqli_query($koneksi, $query);

// Ringkasan Statistik Laporan
$total_transaksi = 0;
$total_denda_terkumpul = 0;
$data_laporan = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Hitung Keterlambatan dan Denda (jika ada saat pengembalian)
        $tgl_tempo = new DateTime($row['tanggal_jatuh_tempo']);
        $tgl_kembali = new DateTime($row['tanggal_kembali']);
        
        $terlambat_hari = 0;
        $denda = 0;

        if ($tgl_kembali > $tgl_tempo) {
            $diff = $tgl_kembali->diff($tgl_tempo);
            $terlambat_hari = $diff->days;
            $denda = $terlambat_hari * 5000; // Denda Rp 5.000/hari
        }

        $row['terlambat_hari'] = $terlambat_hari;
        $row['denda_dibayar'] = $denda;

        $total_transaksi++;
        $total_denda_terkumpul += $denda;
        
        $data_laporan[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Selesai & Pelunasan - Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    /* CSS KHUSUS SAAT CETAK / SAVE TO PDF */
    @media print {
        /* 1. Atur orientasi kertas jadi Landscape (Mendatar) */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        /* 2. Sembunyikan elemen navigasi, filter, dan tombol */
        .no-print {
            display: none !important;
        }

        body {
            background-color: #fff !important;
            font-size: 11pt;
        }

        /* 3. Ratakan tabel agar pas 100% lebar kertas */
        .container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        /* 4. Kecilkan ukuran font & padding tabel agar tidak kepotong */
        .table {
            width: 100% !important;
            font-size: 9pt !important;
            border-collapse: collapse !important;
        }

        .table th, .table td {
            padding: 4px 6px !important;
            vertical-align: middle !important;
        }

        /* Hilangkan warna background badge saat cetak agar teks tetap jelas */
        .badge {
            border: 1px solid #999;
            color: #000 !important;
            background: none !important;
            font-size: 8pt !important;
        }
    }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Navigasi (Sembunyi Saat Cetak) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-book-half me-2"></i>Perpustakaan RPL
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                <a class="nav-link" href="pencarian.php"><i class="bi bi-search me-1"></i> Pencarian & Transaksi</a>
                <a class="nav-link active" href="laporan.php"><i class="bi bi-file-earmark-text me-1"></i> Laporan</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        
        <!-- Header & Navigasi -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-0">Laporan Pengembalian & Pelunasan</h2>
                <p class="text-muted mb-0">Data riwayat peminjaman buku yang telah selesai dikembalikan dan lunas.</p>
            </div>
            
            <div class="d-flex gap-2 no-print">
                <a href="pencarian.php" class="btn btn-danger fw-bold">
                    <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary fw-bold">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Cards Summary / Statistik (Sembunyi Saat Cetak) -->
        <div class="row g-3 mb-4 no-print">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Buku Dikembalikan</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_transaksi; ?> Transaksi</h3>
                        </div>
                        <i class="bi bi-journal-check fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Denda Terkumpul (Lunas)</h6>
                            <h3 class="fw-bold mb-0">Rp <?php echo number_format($total_denda_terkumpul, 0, ',', '.'); ?></h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Filter Laporan (Sembunyi Saat Cetak) -->
        <div class="card shadow-sm border-0 mb-4 no-print">
            <div class="card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama Siswa / Judul Buku..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tgl_mulai" class="form-control" value="<?php echo $tgl_mulai; ?>" title="Tanggal Pengembalian Mulai">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tgl_selesai" class="form-control" value="<?php echo $tgl_selesai; ?>" title="Tanggal Pengembalian Selesai">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filter</button>
                        <?php if($search != '' || $tgl_mulai != ''): ?>
                            <a href="laporan.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Laporan -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-check me-2"></i>Riwayat Transaksi Selesai</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Siswa</th>
                                <th>Judul Buku</th>
                                <th class="text-center">Tgl Pinjam</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th class="text-center">Tgl Dikembalikan</th>
                                <th class="text-center">Keterlambatan</th>
                                <th class="text-end">Denda Dibayar</th>
                                <th class="text-center">Status Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($data_laporan)) {
                                $no = 1;
                                foreach ($data_laporan as $row) {
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['nama_peminjam']); ?></td>
                                <td><?php echo htmlspecialchars($row['judul_buku']); ?></td>
                                <td class="text-center"><?php echo date("d/m/Y", strtotime($row['tanggal_pinjam'])); ?></td>
                                <td class="text-center"><?php echo date("d/m/Y", strtotime($row['tanggal_jatuh_tempo'])); ?></td>
                                <td class="text-center"><?php echo date("d/m/Y", strtotime($row['tanggal_kembali'])); ?></td>
                                
                                <!-- Keterlambatan -->
                                <td class="text-center">
                                    <?php if ($row['terlambat_hari'] > 0): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $row['terlambat_hari']; ?> Hari</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Tepat Waktu</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Denda -->
                                <td class="text-end fw-bold text-dark">
                                    Rp <?php echo number_format($row['denda_dibayar'], 0, ',', '.'); ?>
                                </td>

                                <!-- Status Denda -->
                                <td class="text-center">
                                    <span class="badge bg-success px-3 py-1"><i class="bi bi-check-circle me-1"></i> LUNAS</span>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center py-4 text-muted'>Belum ada riwayat transaksi pengembalian buku.</td></tr>";
                            }
                            ?>
                        </tbody>
                        <?php if (!empty($data_laporan)): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7" class="text-end">TOTAL KESELURUHAN DENDA LUNAS:</td>
                                <td class="text-end text-success">Rp <?php echo number_format($total_denda_terkumpul, 0, ',', '.'); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>