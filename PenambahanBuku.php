<?php
session_start();

// Proteksi Halaman Login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    if (file_exists('../log-admin.php')) {
        header("Location: ../log-admin.php");
    } else {
        header("Location: log-admin.php");
    }
    exit();
}

include "Koneksi.php";

$alert_msg = "";
$alert_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses'])) {
    $judul_buku   = mysqli_real_escape_string($koneksi, trim($_POST['judul_buku']));
    $penulis      = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $penerbit     = mysqli_real_escape_string($koneksi, trim($_POST['penerbit']));
    $tahun_terbit = mysqli_real_escape_string($koneksi, trim($_POST['tahun_terbit']));
    $ISBN         = mysqli_real_escape_string($koneksi, trim($_POST['ISBN']));
    $stok         = (int)$_POST['stok'];

    $berhasil = false;

    // Cek apakah ISBN sudah terdaftar di tabel penambahanbuku
    $cek_isbn = mysqli_query($koneksi, "SELECT * FROM penambahanbuku WHERE ISBN = '$ISBN'");

    if (mysqli_num_rows($cek_isbn) > 0) {
        // ----------------------------------------------------
        // KONDISI 1: BUKU SUDAH ADA (UPDATE STOK)
        // ----------------------------------------------------
        $update = mysqli_query($koneksi, "UPDATE penambahanbuku SET stok = stok + $stok WHERE ISBN = '$ISBN'");
        if ($update) {
            $berhasil = true;
            $alert_msg = "Buku dengan ISBN ($ISBN) sudah terdaftar. Stok dan $stok unit eksemplar baru berhasil ditambahkan!";
        } else {
            $alert_msg = "Gagal memperbarui stok: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    } else {
        // ----------------------------------------------------
        // KONDISI 2: BUKU BARU (INSERT MASTER)
        // ----------------------------------------------------
        $query = mysqli_query($koneksi, "INSERT INTO penambahanbuku (judul_buku, penulis, penerbit, tahun_terbit, ISBN, stok) 
                                         VALUES ('$judul_buku', '$penulis', '$penerbit', '$tahun_terbit', '$ISBN', '$stok')");
        if ($query) {
            $berhasil = true;
            $alert_msg = "Buku baru berhasil tersimpan beserta $stok unit eksemplar!";
        } else {
            $alert_msg = "Gagal menyimpan data: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    }

    // ----------------------------------------------------
    // PEMBUATAN EKSEMPLAR AUTOMATIS
    // (Jalan jika penambahan/update master buku berhasil)
    // ----------------------------------------------------
    if ($berhasil) {
        // Ambil nomor urut eksemplar terakhir untuk penomoran kontinu
        $q_last = mysqli_query($koneksi, "SELECT kode_buku FROM eksemplar_buku ORDER BY id_eksemplar DESC LIMIT 1");
        $last_num = 0;

        if ($q_last && mysqli_num_rows($q_last) > 0) {
            $row_last = mysqli_fetch_assoc($q_last);
            $last_num = (int) filter_var($row_last['kode_buku'], FILTER_SANITIZE_NUMBER_INT);
        }

        // Generate unit eksemplar baru sebanyak $stok yang diinputkan
        for ($i = 1; $i <= $stok; $i++) {
            $last_num++;
            $kode_buku_baru = "BK-" . str_pad($last_num, 3, "0", STR_PAD_LEFT);
            mysqli_query($koneksi, "INSERT INTO eksemplar_buku (ISBN, kode_buku, status) VALUES ('$ISBN', '$kode_buku_baru', 'Tersedia')");
        }

        $alert_type = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penambahan Buku</title>
    
    <!-- HTML5 QR / Barcode Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 550px;
        }

        .form-container h3 {
            color: #1e3c72;
            margin-bottom: 20px;
            font-size: 22px;
            text-align: center;
            border-bottom: 2px solid #eef2f5;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 10px 0;
            vertical-align: middle;
            font-size: 14px;
            color: #444;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus {
            border-color: #2a987b;
            box-shadow: 0 0 5px rgba(42, 82, 152, 0.2);
        }

        .input-group-custom {
            display: flex;
            gap: 8px;
        }

        .Simpan input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 14px;
            transition: background 0.3s;
        }

        .Simpan input[type="submit"]:hover {
            background-color: #005e16;
        }

        .Back input[type="button"] {
            background-color: #ff0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 14px;
            transition: background 0.3s;
        }

        .Back input[type="button"]:hover {
            background-color: #8a0000;
        }

        .btn-scan {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
            white-space: nowrap;
            transition: background 0.2s;
        }
        
        .btn-scan:hover { 
            background-color: #218838; 
        }
        
        .modal-scanner {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6);
        }

        .modal-content-scanner {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            width: 90%;
            max-width: 450px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-close-modal {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-close-modal:hover { 
            background-color: #c82333; 
        }

        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="form-container">
    <h3>Penambahan Buku</h3>

    <!-- Pesan Alert Status -->
    <?php if (!empty($alert_msg)): ?>
        <div class="alert alert-<?= $alert_type ?>">
            <?= $alert_msg ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <table>
            <tr>
                <td width="130">Judul Buku</td>
                <td><input type="text" name="judul_buku" required placeholder="Judul buku"></td>
            </tr>
            <tr>
                <td>Penulis</td>
                <td><input type="text" name="penulis" required placeholder="Nama penulis"></td>
            </tr>
            <tr>
                <td>Penerbit Lokal</td>
                <td><input type="text" name="penerbit" required placeholder="Nama penerbit"></td>
            </tr>
            <tr>
                <td>Tahun Terbit</td>
                <td><input type="date" name="tahun_terbit" required></td>
            </tr>
            <tr>
                <td>Kode Buku (ISBN)</td>
                <td>
                    <div class="input-group-custom">
                        <input type="text" name="ISBN" id="isbn_input" required placeholder="Scan / Ketik ISBN...">
                        <button type="button" class="btn-scan" onclick="bukaScanner()">📷 Scan Barcode</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Jumlah Stok</td>
                <td><input type="number" name="stok" min="1" value="1" required placeholder="Jumlah eksemplar"></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="Simpan">
                        <input type="submit" value="Simpan Buku" name="proses">
                    </div>
                </td>
            </tr>
        </table>
    </form>

    <div class="Back" style="margin-top: 10px;">
        <input type="button" value="Kembali" onclick="window.location.href='Pencarian.php'">
    </div>
</div>

<!-- MODAL POPUP CAMERA SCANNER -->
<div id="modalScanner" class="modal-scanner">
    <div class="modal-content-scanner">
        <h4>Scan Barcode Buku (ISBN)</h4>
        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Arahkan kamera ke barcode buku</p>
        <div id="reader" style="width: 100%;"></div>
        <button type="button" class="btn-close-modal" onclick="tutupScanner()">Tutup Kamera</button>
    </div>
</div>

<!-- JAVASCRIPT SCANNER -->
<script>
let html5QrcodeScanner = null;

function bukaScanner() {
    document.getElementById('modalScanner').style.display = 'block';
    
    html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
        fps: 10, 
        qrbox: { width: 250, height: 150 } 
    });

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function tutupScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().then(() => {
            document.getElementById('modalScanner').style.display = 'none';
        }).catch(error => {
            console.error("Gagal menghentikan scanner", error);
            document.getElementById('modalScanner').style.display = 'none';
        });
    } else {
        document.getElementById('modalScanner').style.display = 'none';
    }
}

function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('isbn_input').value = decodedText;
    tutupScanner();
}

function onScanFailure(error) {
    // Abaikan error per frame
}
</script>

</body>
</html>