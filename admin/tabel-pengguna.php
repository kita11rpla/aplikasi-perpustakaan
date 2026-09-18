<?php
require_once __DIR__ . '/../config/koneksi.php';

// Query mengambil data pengguna
$result = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");

if (!$result) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

$total_users = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - Perpustakaan</title>
    
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
            width: 38px;
            height: 38px;
            background-color: #e9ecef;
            color: #1e3c72;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Table Styling */
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
            transform: scale(1.002);
        }

        /* Custom Input Search */
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

        /* Back Button Animation */
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

    <div class="container my-4" style="max-width: 1000px;">
        
        <!-- Header Banner & Statistik -->
        <div class="header-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Daftar Pengguna</h3>
                <p class="mb-0 text-white-50">Kelola dan lihat informasi seluruh pengguna terdaftar di sistem.</p>
            </div>
            <div class="bg-white bg-opacity-10 px-4 py-2 rounded-3 text-center border border-white border-opacity-25">
                <span class="d-block small text-white-50">Total Pengguna</span>
                <span class="fs-4 fw-bold text-white"><?= $total_users; ?></span>
            </div>
        </div>

        <!-- Card Tabel & Pencarian -->
        <div class="card custom-card bg-white">
            <div class="card-body p-4">
                
                <!-- Filter Search Bar -->
                <div class="row mb-3 align-items-center justify-content-between">
                    <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                        <div class="search-container">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari nama atau email pengguna...">
                        </div>
                    </div>
                    <div class="col-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Menampilkan seluruh data pengguna aktif
                    </div>
                </div>

                <!-- Table Data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="userTable">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col" class="text-center" style="width: 70px;">No</th>
                                <th scope="col">Pengguna</th>
                                <th scope="col">Email</th>
                                <th scope="col" class="text-center">Role / Status</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php 
                            if ($total_users > 0) :
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) : 
                                    // Ambil huruf pertama nama untuk avatar
                                    $nama = htmlspecialchars($row['name'] ?? $row['nama'] ?? 'User');
                                    $email = htmlspecialchars($row['email'] ?? '-');
                                    $role = htmlspecialchars($row['role'] ?? 'Admin');
                                    $initial = strtoupper(substr($nama, 0, 1));
                            ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle">
                                            <?= $initial; ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= $nama; ?></span>
                                            <small class="text-muted font-monospace">ID: #<?= sprintf("%03d", $row['id'] ?? $no-1); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary"><i class="bi bi-envelope me-2 text-primary"></i><?= $email; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (strtolower($role) === 'admin'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i> Siswa
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data pengguna yang terdaftar.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Tombol Navigasi Kembali -->
        <div class="d-flex justify-content-center mt-4">
            <a href="dashboard.php" class="btn btn-danger btn-back fw-bold px-5 py-2 shadow-sm">
                <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Dashboard
            </a>
        </div>

    </div>

    <!-- Live Search JavaScript -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>