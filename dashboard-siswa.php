<?php
session_start();
if (!isset($_SESSION['siswa'])) {
    header('Location: login-siswa.php');
    exit;
}

include 'Koneksi.php';
$user = $_SESSION['siswa'];

// Mengambil data buku dari database
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku"); // Pastikan tabel buku sudah dibuat di database
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
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigasi */
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
        }

        .menu a {
            display: block;
            color: #e0e0e0;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 500;
            transition: 0.3s;
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
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        /* Konten Utama */
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

        /* Card Informasi Status */
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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

        /* Tabel Daftar Buku */
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .table-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
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

        .btn-pinjam {
            background-color: #27ae60;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-pinjam:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>

    <!-- Sidebar Kiri -->
    <div class="sidebar">
        <div>
            <h2>📚 PerpusApp</h2>
            <div class="menu">
                <a href="#" class="active">Dashboard</a>
                <a href="#">Buku Dipinjam</a>
                <a href="#">Riwayat</a>
            </div>
        </div>
        <a href="logout-siswa.php" class="btn-logout">Keluar</a>
    </div>

    <!-- Area Konten Utama -->
    <div class="main-content">
        <div class="header">
            <h1>Selamat Datang, <?php echo htmlspecialchars($user['nama']); ?>! 👋</h1>
        </div>

        <!-- Ringkasan Card -->
        <div class="cards-container">
            <div class="info-card">
                <h3>Buku Sedang Dipinjam</h3>
                <div class="number">1</div>
            </div>
            <div class="info-card" style="border-left-color: #27ae60;">
                <h3>Total Riwayat Pinjam</h3>
                <div class="number">5</div>
            </div>
            <div class="info-card" style="border-left-color: #e67e22;">
                <h3>Denda Aktif</h3>
                <div class="number">Rp 0</div>
            </div>
        </div>

        <!-- Tabel Katalog Buku -->
        <div class="table-container">
            <h2>Katalog Buku Tersedia</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query_buku)) {
                        $status = $row['stok'] > 0 ? '<span style="color: green; font-weight: 600;">Tersedia</span>' : '<span style="color: red; font-weight: 600;">Dipinjam</span>';
                        $disabled = $row['stok'] > 0 ? '' : 'disabled style="background-color: #ccc; cursor: not-allowed;"';
                        $buttonLabel = $row['stok'] > 0 ? 'Pinjam Buku' : 'Tidak Tersedia';
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pengarang']) . "</td>";
                        echo "<td>$status</td>";
                        echo "<td><button class=\"btn-pinjam\" $disabled>$buttonLabel</button></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>