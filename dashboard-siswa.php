<?php
session_start();
if (!isset($_SESSION['siswa'])) {
    header('Location: login-siswa.php');
    exit;
}

include 'Koneksi.php';
$user = $_SESSION['siswa'];

// KUERI BARU: MENGHITUNG TOTAL STOK, DIPINJAM, DAN SISA STOK REAL-TIME
$query_buku = mysqli_query($koneksi, "
    SELECT 
        p.*, 
        IFNULL(p.stok, 0) AS total_stok,
        IFNULL(t.total_dipinjam, 0) AS dipinjam,
        (IFNULL(p.stok, 0) - IFNULL(t.total_dipinjam, 0)) AS sisa_stok
    FROM penambahanbuku p
    LEFT JOIN (
        SELECT ISBN, COUNT(*) AS total_dipinjam 
        FROM transaksi 
        WHERE status_transaksi = 'dipinjam'
        GROUP BY ISBN
    ) t ON LOWER(TRIM(p.ISBN)) = LOWER(TRIM(t.ISBN))
    ORDER BY p.id_buku DESC
");

if (!$query_buku) {
    die("Gagal mengambil data dari database: " . mysqli_error($koneksi));
}

$total_buku = mysqli_num_rows($query_buku);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Perpustakaan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigasi Left Side */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .menu a {
            display: block;
            color: #e0e0e0;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu a:hover, .menu a.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .btn-logout {
            display: block;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Area Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            color: #1e3c72;
        }

        /* Card Statistik Ringkas */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .info-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #2a5298;
        }

        .info-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .info-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #1e3c72;
        }

        /* Container Tabel Data */
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-container h2 {
            font-size: 18px;
            color: #333;
            margin: 0;
        }

        /* Search Box Input */
        .search-box {
            padding: 8px 15px;
            width: 280px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 5px rgba(30, 60, 114, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8fafc;
            color: #444;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        /* Badge Status Ketersediaan Buku */
        .badge-tersedia {
            display: inline-block;
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #c8e6c9;
        }

        .badge-habis {
            display: inline-block;
            background-color: #ffebee;
            color: #c62828;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>

    <!-- Navigasi Utama -->
    <div class="sidebar">
        <div>
            <h2>📚 PerpusApp</h2>
            <div class="menu">
                <a href="#" class="active">Dashboard</a>
            </div>
        </div>
        <a href="logout-siswa.php" class="btn-logout">Keluar</a>
    </div>

    <!-- Konten Dashboard -->
    <div class="main-content">
        <div class="header">
            <h1>Selamat Datang, <?php echo htmlspecialchars($user['nama']); ?>! 👋</h1>
        </div>

        <!-- Kartu Ringkasan -->
        <div class="cards-container">
            <div class="info-card">
                <h3>Total Judul Buku</h3>
                <div class="number"><?php echo $total_buku; ?></div>
            </div>
            <div class="info-card" style="border-left-color: #27ae60;">
                <h3>Status Perpustakaan</h3>
                <div class="number" style="font-size: 18px; color: #27ae60; margin-top: 5px;">Buka (Aktif)</div>
            </div>
        </div>

        <!-- Tabel Daftar Buku -->
        <div class="table-container">
            <div class="table-header-flex">
                <h2>Daftar Koleksi Buku Perpustakaan</h2>
                <input type="text" id="searchTable" class="search-box" placeholder="🔍 Cari judul, penulis, ISBN...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode ISBN</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th style="text-align: center;">Sisa Stok</th>
                        <th style="text-align: center;">Status Buku</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    if ($total_buku > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($query_buku)) {
                            $sisa_stok = (int)$row['sisa_stok'];
                            $total_stok = (int)$row['total_stok'];

                            // Logika penentuan badge ketersediaan
                            if ($sisa_stok > 0) {
                                $badge = '<span class="badge-tersedia">Tersedia</span>';
                            } else {
                                $badge = '<span class="badge-habis">Stok Habis</span>';
                            }

                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td><code>" . htmlspecialchars($row['ISBN']) . "</code></td>";
                            echo "<td><strong>" . htmlspecialchars($row['judul_buku']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['penulis']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['penerbit']) . "</td>";
                            
                            // Kolom Sisa Stok
                            echo "<td style='text-align: center;'><strong>" . $sisa_stok . "</strong> / " . $total_stok . "</td>";
                            
                            // Kolom Status Buku
                            echo "<td style='text-align: center;'>" . $badge . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data buku di dalam sistem.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script Fitur Live Filter Pencarian -->
    <script>
    document.getElementById('searchTable').addEventListener('keyup', function() {
        let keyword = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(row => {
            let textContent = row.textContent.toLowerCase();
            if (textContent.includes(keyword)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
    </script>

</body>
</html>