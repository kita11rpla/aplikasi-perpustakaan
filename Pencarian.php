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

include 'Koneksi.php';

$alert_msg = "";
$alert_type = "";

// 1. PROSES SIMPAN PEMINJAMAN BUKU BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pinjam'])) {
    $isbn                = mysqli_real_escape_string($koneksi, trim($_POST['isbn']));
    $nama_peminjam       = mysqli_real_escape_string($koneksi, trim($_POST['nama_peminjam']));
    $tanggal_pinjam      = mysqli_real_escape_string($koneksi, trim($_POST['tanggal_pinjam']));
    $tanggal_jatuh_tempo = mysqli_real_escape_string($koneksi, trim($_POST['tanggal_jatuh_tempo']));

    $tgl_pinjam_sec = strtotime($tanggal_pinjam);
    $tgl_tempo_sec  = strtotime($tanggal_jatuh_tempo);
    $selisih_hari   = ($tgl_tempo_sec - $tgl_pinjam_sec) / (60 * 60 * 24);

    if ($tgl_tempo_sec < $tgl_pinjam_sec) {
        $alert_msg = "Gagal! Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.";
        $alert_type = "danger";
    } elseif ($selisih_hari > 30) {
        $alert_msg = "Gagal! Durasi peminjaman maksimal adalah 1 bulan (30 hari).";
        $alert_type = "warning";
    } else {
        $cek_status = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE ISBN = '$isbn' AND status_transaksi = 'dipinjam'");
        if (mysqli_num_rows($cek_status) > 0) {
            $alert_msg = "Buku ini sedang dipinjam oleh siswa lain!";
            $alert_type = "warning";
        } else {
            $insert = mysqli_query($koneksi, "INSERT INTO transaksi (ISBN, nama_peminjam, tanggal_pinjam, tanggal_jatuh_tempo, status_transaksi, status_denda, denda_terutang) 
                                              VALUES ('$isbn', '$nama_peminjam', '$tanggal_pinjam', '$tanggal_jatuh_tempo', 'dipinjam', 'belum_lunas', 0)");
            if ($insert) {
                $alert_msg = "Peminjaman berhasil dicatat!";
                $alert_type = "success";
            } else {
                $alert_msg = "Gagal menyimpan: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        }
    }
}

// 2. PROSES PENGEMBALIAN BUKU (Hanya Mengembalikan Buku & Hitung Denda Terutang)
if (isset($_GET['action']) && $_GET['action'] == 'kembali' && isset($_GET['id_transaksi'])) {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_GET['id_transaksi']);
    $tgl_sekarang_str = date('Y-m-d');

    // Ambil data transaksi
    $q_cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'");
    if ($data = mysqli_fetch_assoc($q_cek)) {
        $tgl_sekarang = new DateTime();
        $tgl_tempo    = new DateTime($data['tanggal_jatuh_tempo']);
        
        $denda = 0;
        $status_denda = 'lunas'; // Default lunas jika tidak terlambat

        if ($tgl_sekarang > $tgl_tempo) {
            $diff = $tgl_sekarang->diff($tgl_tempo);
            $terlambat_hari = $diff->days;
            $denda = $terlambat_hari * 5000;
            $status_denda = 'belum_lunas'; // Ada denda yang harus dibayar
        }

        $update = mysqli_query($koneksi, "UPDATE transaksi 
                                          SET status_transaksi = 'dikembalikan', 
                                              tanggal_kembali = '$tgl_sekarang_str', 
                                              denda_terutang = '$denda',
                                              status_denda = '$status_denda' 
                                          WHERE id_transaksi = '$id_transaksi'");
        if ($update) {
            if ($denda > 0) {
                $alert_msg = "Buku telah dikembalikan! Terkena denda sebesar Rp " . number_format($denda, 0, ',', '.') . " (Status: Belum Lunas).";
                $alert_type = "warning";
            } else {
                $alert_msg = "Buku telah dikembalikan tepat waktu! Transaksi selesai.";
                $alert_type = "success";
            }
        } else {
            $alert_msg = "Gagal memproses pengembalian: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    }
}

// 3. PROSES PELUNASAN DENDA (Manual Ketika Siswa Membayar Denda)
if (isset($_GET['action']) && $_GET['action'] == 'lunasi' && isset($_GET['id_transaksi'])) {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_GET['id_transaksi']);

    $update_lunas = mysqli_query($koneksi, "UPDATE transaksi SET status_denda = 'lunas' WHERE id_transaksi = '$id_transaksi'");
    if ($update_lunas) {
        $alert_msg = "Pembayaran denda berhasil dilunasi!";
        $alert_type = "success";
    } else {
        $alert_msg = "Gagal memperbarui status denda: " . mysqli_error($koneksi);
        $alert_type = "danger";
    }
}

// 4. QUERY KATALOG BUKU
$search   = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

$query_buku = "SELECT p.*, 
                t.nama_peminjam,
                IF(t.id_transaksi IS NOT NULL, 'Tidak Tersedia', 'Tersedia') AS status_buku
              FROM penambahanbuku p
              LEFT JOIN (
                  SELECT ISBN, nama_peminjam, id_transaksi 
                  FROM transaksi 
                  WHERE status_transaksi = 'dipinjam'
              ) t ON p.ISBN = t.ISBN
              WHERE 1=1";

if ($search != '') {
    $query_buku .= " AND (p.judul_buku LIKE '%$search%' OR p.penulis LIKE '%$search%' OR p.ISBN LIKE '%$search%')";
}
if ($kategori != '' && $kategori != 'Semua Kategori') {
    $query_buku .= " AND p.penerbit LIKE '%$kategori%'"; 
}

$query_buku .= " ORDER BY p.judul_buku ASC";
$result_buku = mysqli_query($koneksi, $query_buku);

// 5. QUERY SISWA MEMINJAM AKTIF & TUNGGAKAN DENDA (status_transaksi = 'dipinjam' OR status_denda = 'belum_lunas')
$query_peminjam = "SELECT t.*, p.judul_buku 
                   FROM transaksi t 
                   JOIN penambahanbuku p ON t.ISBN = p.ISBN 
                   WHERE t.status_transaksi = 'dipinjam' OR t.status_denda = 'belum_lunas' 
                   ORDER BY t.status_transaksi ASC, t.tanggal_jatuh_tempo ASC";
$result_peminjam = mysqli_query($koneksi, $query_peminjam);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian & Data Peminjaman Buku</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-book-half me-2"></i>Perpustakaan RPL
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                <a class="nav-link active" href="pencarian.php"><i class="bi bi-search me-1"></i> Pencarian & Transaksi</a>
                <a class="nav-link" href="laporan.php"><i class="bi bi-file-earmark-text me-1"></i> Laporan</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-0">Pencarian & Transaksi Perpustakaan</h2>
                <p class="text-muted mb-0">Kelola peminjaman, pengembalian buku, dan pelunasan denda.</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-danger fw-bold shadow-sm">
                    <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Dashboard
                </a>
                <a href="PenambahanBuku.php" class="btn btn-success fw-bold shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Buku
                </a>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        <?php if(!empty($alert_msg)): ?>
            <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?php echo $alert_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Filter & Search Katalog -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="kategori" class="form-select">
                            <option>Semua Kategori</option>
                            <option value="RPL" <?php if($kategori=='RPL') echo 'selected'; ?>>RPL</option>
                            <option value="SEJ" <?php if($kategori=='SEJ') echo 'selected'; ?>>Sejarah</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari judul buku, penulis, kodebuku..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-search me-1"></i> Cari Katalog</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL 1: DAFTAR KATALOG BUKU -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-book me-2"></i>Katalog Buku</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Kode Buku</th>
                                <th class="text-center">Status Buku</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if($result_buku && mysqli_num_rows($result_buku) > 0) {
                                while($row = mysqli_fetch_assoc($result_buku)) {
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['judul_buku']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                <td><span class="badge bg-secondary font-monospace"><?php echo htmlspecialchars($row['ISBN']); ?></span></td>
                                <td class="text-center">
                                    <?php if ($row['status_buku'] === 'Tersedia'): ?>
                                        <span class="badge bg-success px-3 py-2">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status_buku'] === 'Tersedia'): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary fw-bold" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalPinjam"
                                                data-isbn="<?php echo htmlspecialchars($row['ISBN']); ?>"
                                                data-judul="<?php echo htmlspecialchars($row['judul_buku']); ?>">
                                            <i class="bi bi-journal-arrow-up me-1"></i> Pinjamkan
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">Sedang Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Data buku tidak ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TABEL 2: DAFTAR PEMINJAM AKTIF & TUNGGAKAN DENDA -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-people me-2"></i>Daftar Meminjam & Tunggakan Denda</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Judul Buku</th>
                                <th>Status Peminjaman</th>
                                <th>Jatuh Tempo</th>
                                <th>Informasi Denda</th>
                                <th>Status Denda</th>
                                <th class="text-center">Aksi / Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if($result_peminjam && mysqli_num_rows($result_peminjam) > 0) {
                                $tgl_sekarang = new DateTime();

                                while($p = mysqli_fetch_assoc($result_peminjam)) {
                                    $tgl_tempo = new DateTime($p['tanggal_jatuh_tempo']);
                                    
                                    // Hitung denda berjalan (jika belum dikembalikan)
                                    $denda_tampil = 0;
                                    $terlambat_hari = 0;

                                    if ($p['status_transaksi'] === 'dipinjam') {
                                        if ($tgl_sekarang > $tgl_tempo) {
                                            $diff = $tgl_sekarang->diff($tgl_tempo);
                                            $terlambat_hari = $diff->days;
                                            $denda_tampil = $terlambat_hari * 5000;
                                        }
                                    } else {
                                        // Jika buku sudah dikembalikan tapi denda belum dibayar
                                        $denda_tampil = $p['denda_terutang'];
                                    }
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($p['nama_peminjam']); ?></td>
                                <td><?php echo htmlspecialchars($p['judul_buku']); ?></td>
                                
                                <!-- Status Peminjaman -->
                                <td>
                                    <?php if ($p['status_transaksi'] === 'dipinjam'): ?>
                                        <span class="badge bg-primary">Sedang Meminjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Buku Sudah Kembali</span>
                                    <?php endif; ?>
                                </td>

                                <td><span class="badge bg-warning text-dark"><?php echo date("d/m/Y", strtotime($p['tanggal_jatuh_tempo'])); ?></span></td>

                                <!-- Informasi Denda -->
                                <td>
                                    <?php if ($denda_tampil > 0): ?>
                                        <span class="text-danger fw-bold">
                                            Rp <?php echo number_format($denda_tampil, 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status Denda -->
                                <td>
                                    <?php if ($p['status_denda'] === 'belum_lunas' && $denda_tampil > 0): ?>
                                        <span class="badge bg-danger">Belum Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Tidak Ada Denda / Lunas</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tombol Aksi Terpisah -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- 1. Tombol Kembalikan Buku (Hanya muncul jika buku masih dipinjam) -->
                                        <?php if ($p['status_transaksi'] === 'dipinjam'): ?>
                                            <a href="pencarian.php?action=kembali&id_transaksi=<?php echo $p['id_transaksi']; ?>" 
                                               class="btn btn-sm btn-warning fw-bold"
                                               onclick="return confirm('Kembalikan buku ini?')">
                                                <i class="bi bi-box-arrow-in-left me-1"></i> Kembalikan Buku
                                            </a>
                                        <?php endif; ?>

                                        <!-- 2. Tombol Bayar / Lunasi Denda (Hanya muncul jika ada tunggakan denda) -->
                                        <?php if ($p['status_denda'] === 'belum_lunas' && $denda_tampil > 0): ?>
                                            <a href="pencarian.php?action=lunasi&id_transaksi=<?php echo $p['id_transaksi']; ?>" 
                                               class="btn btn-sm btn-success fw-bold"
                                               onclick="return confirm('Konfirmasi bahwa siswa telah melunasi denda sebesar Rp <?php echo number_format($denda_tampil, 0, ',', '.'); ?>?')">
                                                <i class="bi bi-cash-stack me-1"></i> Bayar Denda
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center py-4 text-muted'>Tidak ada siswa yang sedang meminjam atau memiliki tunggakan denda.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POP-UP PEMINJAMAN BUKU -->
    <div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i>Form Peminjaman Buku</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="isbn" id="modal_isbn">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Buku</label>
                            <input type="text" id="modal_judul" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Siswa Peminjam</label>
                            <input type="text" name="nama_peminjam" class="form-control" placeholder="Ketik nama lengkap siswa..." required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Pinjam</label>
                            <input type="date" name="tanggal_pinjam" id="tgl_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Jatuh Tempo (Maks. 1 Bulan)</label>
                            <input type="date" name="tanggal_jatuh_tempo" id="tgl_tempo" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                            <small class="text-muted">*Batas waktu pengembalian sebelum dikenakan denda Rp 5.000/hari.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="proses_pinjam" class="btn btn-primary fw-bold">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script JavaScript Bootstrap (Wajib di bagian bawah sebelum </body>) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalPinjam = document.getElementById('modalPinjam');
            if (modalPinjam) {
                modalPinjam.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('modal_isbn').value = button.getAttribute('data-isbn');
                    document.getElementById('modal_judul').value = button.getAttribute('data-judul');
                });
            }
        });
    </script>
</body>
</html>