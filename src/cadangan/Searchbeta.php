<?php
include "Koneksi.php";

$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $query = mysqli_query($koneksi, "SELECT * FROM penambahanbuku WHERE 
              judul_buku LIKE '%$keyword%' OR 
              penulis LIKE '%$keyword%' OR 
              ISBN LIKE '%$keyword%'");
} else {
    $query = mysqli_query($koneksi, "SELECT * FROM penambahanbuku");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Perpustakaan Modern</title>
    <!-- Mengimpor font premium Google Fonts Inter & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- KUNCI ESTETIKA UTAMA --- */
        :root {
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --primary: #0b8e09;
            --primary-hover: #003a05;
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
            --glass-white: rgba(255, 255, 255, 0.85);
            --shadow-soft: 0 8px 32px 0 rgba(31, 38, 135, 0.06);
            --radius: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            padding: 50px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            background: var(--glass-white);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.4);
            width: 100%;
            max-width: 950px;
            animation: fadeIn 0.6s ease-in-out;
        }

        /* Tipografi Estetik Klasik & Modern */
        .header-section {
            text-align: center;
            margin-bottom: 35px;
        }

        h3 {

            font-size: 32px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #1a252f;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Desain Kolom Pencarian yang Elegan */
        .search-box {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            background: #ffffff;
            padding: 6px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }

        .search-box input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: none;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-main);
            outline: none;
        }

        .search-box input[type="text"]::placeholder {
            color: #a0aec0;
        }

        .btn-cari, .btn-reset {
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cari {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(74, 111, 165, 0.2);
        }

        .btn-cari:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-reset {
            background-color: #edf2f7;
            color: #4a5568;
        }

        .btn-reset:hover {
            background-color: #e2e8f0;
        }

        /* Desain Tabel Premium */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background-color: #f8fafc;
            color: #000001;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.8px;
            padding: 16px 20px;
            border-bottom: 2px solid #edf2f7;
            text-align: left;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
            transition: background 0.2s ease;
        }

        /* Menonjolkan Judul Buku */
        .title-cell {
            font-weight: 600;
            color: #0f172a;
        }

        /* Badge Estetik untuk ISBN */
        .isbn-badge {
            background-color: #eff6ff;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            border: 1px solid #dbeafe;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .kosong {
            text-align: center;
            color: var(--text-muted);
            padding: 40px;
            font-style: italic;
        }

        /* Animasi masuk halaman */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /*Tombol Untuk Kembali*/
        input[type="button"] {
    width: 20%;
    background-color: #f60606;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    margin-left: 10px;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
    margin-top: 10px;
}

/* Efek saat tombol diarahkan kursor (Hover) */
input[type="button"]:hover {
    background-color: #7c2323;
}

/* Efek saat tombol diklik */
input[type="button"]:active {
    transform: scale(0.98);
}

    </style>
</head>
<body>
<div class="container">
        <div class="Back">
            <input type="button" value="Kembali" name="proses" onclick="window.location.href='MainHalaman.php'">
        </div>
    <div class="header-section">
        <h3>Arsip & Koleksi Buku</h3>
        <p class="subtitle">Kelola dan telusuri seluruh data buku perpustakaan dengan mudah</p>
    </div>

    <!-- Form Pencarian Minimalis -->
    <form action="" method="get" class="search-box">
        <input type="text" name="keyword" placeholder="Ketik judul, nama penulis, atau kode ISBN..." value="<?php echo htmlspecialchars($keyword); ?>">
        <input type="submit" name="cari" value="Cari Data" class="btn-cari">
    <?php if($keyword != ""): ?>
    <!-- PHP akan otomatis mengisi nama file yang benar -->
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-reset">Reset</a>
    <?php endif; ?>
    </form>

    <!-- Pembungkus Tabel Modern -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th>Tahun Terbit</th>
                    <th>ISBN</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<tr>";
                        // Judul dibuat lebih tebal agar hierarki informasinya jelas
                        echo "<td class='title-cell'>" . htmlspecialchars($row['judul_buku']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['penulis']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['penerbit']) . "</td>";
                        // Memformat tampilan tanggal agar lebih rapi secara visual
                        $tanggal = date("d M Y", strtotime($row['tahun_terbit']));
                        echo "<td>" . $tanggal . "</td>";
                        // ISBN dibungkus badge estetik khusus
                        echo "<td><span class='isbn-badge'>" . htmlspecialchars($row['ISBN']) . "</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='kosong'>Maaf, koleksi buku yang kamu cari tidak ditemukan.</td></tr>";
                }

                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>