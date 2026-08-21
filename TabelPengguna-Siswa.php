<?php
session_start();

// Koneksi ke database
include 'Koneksi.php';

// Ambil seluruh data dari tabel `siswa`
$query_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");

if (!$query_siswa) {
    die("Gagal mengambil data dari database: " . mysqli_error($koneksi));
}

$total_siswa = mysqli_num_rows($query_siswa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Perpustakaan</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333;
        }

        /* Header Card Style */
        .header-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 15px;
            padding: 25px 30px;
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.15);
        }

        /* Avatar Inisial */
        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            color: #1e3c72;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        /* Custom Card Table */
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table > :not(caption) > * > * {
            padding: 14px 18px;
        }

        .table tbody tr {
            transition: all 0.2s ease-in-out;
        }

        .table tbody tr:hover {
            background-color: #f8fafc !important;
            transform: scale(1.001);
        }

        /* Input Search Box */
        .search-container {
            position: relative;
        }

        .search-container .bi-search {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #888;
        }

        .search-input {
            padding-left: 42px;
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #2a5298;
            box-shadow: 0 0 8px rgba(42, 82, 152, 0.2);
        }

        /* Tombol Kembali */
        .btn-back {
            border-radius: 10px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body class="py-4">

    <div class="container my-4" style="max-width: 900px;">
        
        <!-- Banner Header & Informasi Ringkas -->
        <div class="header-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-mortarboard-fill me-2"></i>Data Akun Siswa</h3>
                <p class="mb-0 text-white-50">Daftar seluruh akun siswa terdaftar di perpustakaan.</p>
            </div>
            <div class="bg-white bg-opacity-10 px-4 py-2 rounded-3 text-center border border-white border-opacity-25">
                <span class="d-block small text-white-50">Total Siswa</span>
                <span class="fs-4 fw-bold text-white"><?= $total_siswa; ?></span>
            </div>
        </div>

        <!-- Kartu Tabel Data -->
        <div class="card custom-card bg-white">
            <div class="card-body p-4">
                
                <!-- Filter Search Bar -->
                <div class="row mb-3 align-items-center justify-content-between">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="search-container">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari NISN atau Username...">
                        </div>
                    </div>
                    <div class="col-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Data dari tabel database: <code>siswa</code>
                    </div>
                </div>

                <!-- Tabel Informasi Siswa -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="siswaTable">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col" class="text-center" style="width: 70px;">No</th>
                                <th scope="col">NISN</th>
                                <th scope="col">Username Siswa</th>
                                <th scope="col" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php 
                            if ($total_siswa > 0) :
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($query_siswa)) : 
                                    $username = htmlspecialchars($row['nama']);
                                    $nisn = htmlspecialchars($row['nisn']);
                                    $initial = strtoupper(substr($username, 0, 1));
                            ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-3 py-2">
                                        <i class="bi bi-card-heading me-1 text-primary"></i><?= $nisn; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle">
                                            <?= $initial; ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= $username; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data siswa di dalam database.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="d-flex justify-content-center mt-4">
            <a href="dashboard.php" class="btn btn-danger btn-back fw-bold px-5 py-2 shadow-sm">
                <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Dashboard
            </a>
        </div>

    </div>

    <!-- Script Live Search JavaScript -->
    <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase().trim();
        let tableRows = document.querySelectorAll('#tableBody tr');

        tableRows.forEach(row => {
            let rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchValue)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>