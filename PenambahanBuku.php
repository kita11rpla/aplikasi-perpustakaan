<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Penambahan Buku</title>
    
    <!-- HTML5 QR / Barcode Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        /* CSS Tambahan untuk Tombol Scan & Pop-up Modal Kamera */
        .btn-scan {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 5px;
        }
        .btn-scan:hover { background-color: #218838; }
        
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
        }
        .btn-close-modal {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .input-group-custom {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h3>Penambahan Buku</h3>

    <form action="" method="post">
        <table>
            <tr>
                <td width="130">Judul Buku</td>
                <td><input type="text" name="judul_buku" required></td>
            </tr>
            <tr>
                <td width="130">Penulis</td>
                <td><input type="text" name="penulis" required></td>
            </tr>
            <tr>
                <td width="130">Penerbit Lokal</td>
                <td><input type="text" name="penerbit" required></td>
            </tr>
            <tr>
                <td width="130">Tahun Terbit</td>
                <td><input type="date" name="tahun_terbit" required></td>
            </tr>
            <tr>
                <td width="130">Kode Buku (ISBN)</td>
                <td>
                    <div class="input-group-custom">
                        <input type="text" name="ISBN" id="isbn_input" required placeholder="Scan / Ketik ISBN...">
                        <button type="button" class="btn-scan" onclick="bukaScanner()">📷 Scan Barcode</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="Simpan">
                        <input type="submit" value="Simpan" name="proses">
                    </div>
                </td>
            </tr>
        </table>
    </form>

    <?php
    include "Koneksi.php";

    if (isset($_POST['proses'])){
        // Amankan data input dengan mysqli_real_escape_string agar aman dari error tanda petik
        $judul_buku   = mysqli_real_escape_string($koneksi, $_POST['judul_buku']);
        $penulis      = mysqli_real_escape_string($koneksi, $_POST['penulis']);
        $penerbit     = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
        $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
        $ISBN         = mysqli_real_escape_string($koneksi, $_POST['ISBN']);

        $query = mysqli_query($koneksi, "INSERT INTO penambahanbuku (judul_buku, penulis, penerbit, tahun_terbit, ISBN) 
                                         VALUES ('$judul_buku', '$penulis', '$penerbit', '$tahun_terbit', '$ISBN')");

        if($query) {
            echo "<b><div class='alert-success' style='margin: 15px auto; max-width: 100%; display: block;'>Data baru telah tersimpan</div></b>";
        } else {
            echo "<div style='color:red;'>Gagal menyimpan data: ".mysqli_error($koneksi)."</div>";
        }
    }
    ?>

    <div class="Back" style="margin-top: 10px;">
        <input type="button" value="Kembali" onclick="window.location.href='Pencarian.php'">
    </div>
</div>

<!-- MODAL POPUP CAMERA SCANNER -->
<div id="modalScanner" class="modal-scanner">
    <div class="modal-content-scanner">
        <h4>Scan Barcode Buku (ISBN)</h4>
        <p style="font-size: 12px; color: #666;">Arahkan kamera ke barcode buku</p>
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
    // 1. Isikan hasil scan barcode langsung ke input Kode Buku (ISBN)
    document.getElementById('isbn_input').value = decodedText;
    
    // 2. Otomatis matikan kamera dan tutup popup scanner
    tutupScanner();
}

function onScanFailure(error) {
    // Abaikan kegagalan pembacaan frame kamera
}
</script>

</body>
</html>