<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Penambahhan Buku</title>
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
            <td width="130">Kode Buku</td>
            <td><input type="text" name="ISBN" required></td>
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
    mysqli_query($koneksi,"insert into penambahanbuku set
    judul_buku='$_POST[judul_buku]',
    penulis='$_POST[penulis]',
    penerbit='$_POST[penerbit]',
    tahun_terbit='$_POST[tahun_terbit]',
    ISBN='$_POST[ISBN]'");

    echo "<b><div class='alert-success' style='margin: 15px auto; max-width: 100%; display: block;'>Data baru telah tersimpan</div></b>";
}
?>

  <tr>
            <td></td>
            <td>
                <div class="Back">
                <input type="button" value="Kembali" name="proses" onclick="window.location.href='Pencarian.php'">
                </div>
            </td>
        </tr>

</body>
</html>

