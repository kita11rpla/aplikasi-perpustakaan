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

$alert_msg = "";
$alert_type = "";

// 1. PROSES INPUT PEMINJAMAN BUKU BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam_buku'])) {
    $isbn          = mysqli_real_escape_string($koneksi, trim($_POST['isbn']));
    $nama_peminjam = mysqli_real_escape_string($koneksi, trim($_POST['nama_peminjam']));
    $tanggal_pinjam= mysqli_real_escape_string($koneksi, trim($_POST['tanggal_pinjam']));

    // A. Cek apakah ISBN terdaftar di database buku
    $cek_buku = mysqli_query($koneksi, "SELECT * FROM penambahanbuku WHERE ISBN = '$isbn'");
    if (mysqli_num_rows($cek_buku) == 0) {
        $alert_msg = "Gagal! Buku dengan ISBN tersebut tidak ditemukan di database.";
        $alert_type = "danger";
    } else {
        // B. Cek apakah buku sedang dipinjam orang lain
        $cek_status = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE ISBN = '$isbn' AND status_transaksi = 'dipinjam'");
        if (mysqli_num_rows($cek_status) > 0) {
            $alert_msg = "Gagal! Buku tersebut saat ini sedang dipinjam dan belum dikembalikan.";
            $alert_type = "warning";
        } else {
            // C. Simpan data peminjaman
            $insert = mysqli_query($koneksi, "INSERT INTO transaksi (ISBN, nama_peminjam, tanggal_pinjam, status_transaksi) 
                                              VALUES ('$isbn', '$nama_peminjam', '$tanggal_pinjam', 'dipinjam')");
            if ($insert) {
                $alert_msg = "Peminjaman berhasil dicatat! Status buku sekarang 'Tidak Tersedia'.";
                $alert_type = "success";
            } else {
                $alert_msg = "Error DB: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        }
    }
}

// 2. PROSES PENGEMBALIAN BUKU
if (isset($_GET['kembalikan_id'])) {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_GET['kembalikan_id']);
    $tgl_sekarang = date('Y-m-d');

    $update = mysqli_query($koneksi, "UPDATE transaksi 
                                      SET status_transaksi = 'dikembalikan', tanggal_kembali = '$tgl_sekarang' 
                                      WHERE id_transaksi = '$id_transaksi'");
    if ($update) {
        $alert_msg = "Buku berhasil dikembalikan! Status buku sekarang 'Tersedia'.";
        $alert_type = "success";
    } else {
        $alert_msg = "Gagal mengembalikan buku: " . mysqli_error($koneksi);
        $alert_type = "danger";
    }
}

// 3. AMBIL DATA DAFTAR PEMINJAMAN DARI DATABASE
$query_transaksi = "SELECT t.*, p.judul_buku 
                    FROM transaksi t 
                    LEFT JOIN penambahanbuku p ON t.ISBN = p.ISBN 
                    ORDER BY t.id_transaksi DESC";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Peminjaman - Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Perpustakaan RPL</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="pencarian.php">Pencarian Buku</a>
                    <a class="nav-link active" href="transaksi.php">Transaksi & Barcode</a>
                    <a class="nav-link" href="laporan.php">Laporan</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Transaksi Peminjaman & Pengembalian</h2>
            <a href="pencarian.php" class="btn btn-secondary fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Ke Pencarian Buku
            </a>
        </div>

        <!-- Alert Notifikasi -->
        <?php if(!empty($alert_msg)): ?>
            <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $alert_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Input Peminjaman Baru -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white font-weight-bold">
                        <i class="bi bi-journal-plus me-1"></i> Form Pinjam Buku
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">ISBN Buku</label>
                                <input type="text" name="isbn" class="form-control" placeholder="Ketik atau Scan ISBN..." required autofocus>
                                <small class="text-muted">Dapat di-scan menggunakan Barcode Scanner.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Nama Peminjam</label>
                                <input type="text" name="nama_peminjam" class="form-control" placeholder="Nama Siswa / Guru..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" name="pinjam_buku" class="btn btn-primary w-100 fw-bold">
                                Simpan Peminjaman
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat & Proses Pengembalian -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Daftar Peminjaman Aktif & Riwayat</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul Buku / ISBN</th>
                                        <th>Peminjam</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if(mysqli_num_rows($result_transaksi) > 0) {
                                        while($row = mysqli_fetch_assoc($result_transaksi)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($row['judul_buku'] ?? 'Judul Tidak Ditemukan'); ?></span>
                                            <small class="text-muted">ISBN: <?php echo htmlspecialchars($row['ISBN']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['nama_peminjam']); ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($row['tanggal_pinjam'])); ?></td>
                                        <td>
                                            <?php if ($row['status_transaksi'] === 'dipinjam'): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Dipinjam</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i> Dikembalikan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['status_transaksi'] === 'dipinjam'): ?>
                                                <a href="transaksi.php?kembalikan_id=<?php echo $row['id_transaksi']; ?>" 
                                                   class="btn btn-sm btn-outline-success"
                                                   onclick="return confirm('Proses pengembalian buku ini?')">
                                                    Kembalikan
                                                </a>
                                            <?php else: ?>
                                                <small class="text-muted"><?php echo date("d/m/Y", strtotime($row['tanggal_kembali'])); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada transaksi peminjaman.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>