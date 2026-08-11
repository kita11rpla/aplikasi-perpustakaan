<?php
include 'Koneksi.php';

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn = mysqli_real_escape_string($koneksi, trim($_POST['nisn']));
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $password = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    // Cek apakah NISN sudah terdaftar
    $cek_nisn = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nisn='$nisn'");
    
    if (mysqli_num_rows($cek_nisn) > 0) {
        $pesan = "<div class='alert error'>⚠️ NISN sudah terdaftar!</div>";
    } else {
        // Simpan data siswa baru
        $query = "INSERT INTO siswa (nisn, nama, password) VALUES ('$nisn', '$nama', '$password')";
        if (mysqli_query($koneksi, $query)) {
            $pesan = "<div class='alert success'>🎉 Pendaftaran berhasil! <a href='login-siswa.php'>Login di sini</a></div>";
        } else {
            $pesan = "<div class='alert error'>❌ Gagal mendaftar. Coba lagi!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Siswa - Perpustakaan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .card h2 {
            color: #1e3c72;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px; /* MEMBERI JARAK YANG PAS ANTA KOTAK */
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e1e5ee;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-group input:focus {
            border-color: #2a5298;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.12);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(42, 82, 152, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .footer-text {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .footer-text a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .alert.error {
            background-color: #fde8e8;
            color: #9b1c1c;
            border: 1px solid #fbd5d5;
        }

        .alert.success {
            background-color: #def7ec;
            color: #03543f;
            border: 1px solid #bcf0da;
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Daftar Akun</h2>
        <p class="subtitle">Silakan isi data diri siswa untuk mendaftar</p>

        <?php echo $pesan; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nisn">NISN</label>
                <input type="text" id="nisn" name="nisn" placeholder="Masukkan NISN kamu" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password akun" required>
            </div>

            <button type="submit" class="btn-submit">Daftar Sekarang</button>
        </form>

        <p class="footer-text">Sudah punya akun? <a href="login-siswa.php">Login di sini</a></p>
    </div>

</body>
</html>