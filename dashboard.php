<?php
include "Koneksi.php";

// 1. Memulai session untuk membaca data user yang login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. PROTEKSI HALAMAN: Cek apakah session 'user' ada dan tidak kosong
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    
    // PENTING: Deteksi otomatis jalur file agar bebas error redirect 404 / terpental
    // Jika file dashboard ini ditaruh di dalam sub-folder (misal: folder 'admin'), gunakan '../log-admin.php'
    // Jika ditaruh di folder utama (sejajar log-admin.php), cukup gunakan 'log-admin.php'
    
    if (file_exists('../log-admin.php')) {
        header("Location: ../log-admin.php");
    } else {
        header("Location: log-admin.php");
    }
    exit();
}

// 3. Mengambil data user dari session yang valid dari proses Sign In
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body{
            display: flex;
            min-height: 100vh;
            background: #f4f6fb;
        }

        .sidebar{
            width: 240px;
            background: #1e3c72;
            color: white;
            padding: 30px 20px;
        }

        .sidebar h2{
            margin-bottom: 35px;
        }

        .sidebar a{
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: .3s;
        }

        .sidebar a:hover{
            background: rgba(255, 255, 255, .15);
        }

        .main{
            flex: 1;
            padding: 40px;
        }

        .card{
            background: lightblue;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        .card h2{
            margin-bottom: 10px;
        }

        .stats{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .stat{
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,.08);
        }

        .stat h3{
            color: #03543f;
            margin-bottom: 10px;
        }

        .logout{
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #e53935;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }

        .logout:hover{
            background: #c62828;
        }

        @media(max-width:768px){
            body{
                flex-direction: column;
            }
            .sidebar{
                width: 100%;
            }
            .main{
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="Pencarian.php">🔎 Pencarian Buku</a>
    <a href="TabelPengguna.php">👤 Users</a>
    <a href="#">📈 Statistik</a>
    <a href="laporan.php">📝 Laporan Buku</a>
    
    <!-- URL Logout otomatis mendeteksi folder -->
    <a class="logout" href="<?php echo file_exists('../logout.php') ? '../logout.php' : 'logout.php'; ?>">
        Logout
    </a>
</div>

<div class="main">
    <div class="card">
        <!-- Pengaman htmlspecialchars menghindari serangan XSS dari nama input database -->
        <h2>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?> 👋</h2>
        <p>
            You are successfully logged in as 
            <strong><?php echo htmlspecialchars($user['email'] ?? '-'); ?></strong>
        </p>
    </div>
    <div class="stats">
        <div class="stat">
            <h3>Jumlah Pengguna Admin</h3>
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM users");
            $jumlah_data = mysqli_num_rows($query);
            echo "<h1>$jumlah_data</h1>"  ;
            ?>
        </div>
            <div class="stat">
            <h3>Jumlah Pengguna Siswa</h3>
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM siswa");
            $jumlah_data = mysqli_num_rows($query);
            echo "<h1>$jumlah_data</h1>"  ;
            ?>
        </div>
        <div class="stat">
            <h3>Data Buku</h3>
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM PenambahanBuku");
            $jumlah_data = mysqli_num_rows($query);
            echo "<h1>$jumlah_data</h1>"  ;
            ?>
        </div>
        <div class="stat">
            <h3>Statistik Buku</h3>
            <h1>0</h1>
        </div>
    </div>
</div>

</body>
</html>