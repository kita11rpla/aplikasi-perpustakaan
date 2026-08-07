<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Denda</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f7f6; 
            display: flex; 
            justify-content: center; 
            margin-top: 50px; 
        }
        .kalkulator { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            width: 300px; 
        }
        label { font-size: 14px; font-weight: bold; color: #333; }
        input { 
            width: 100%; 
            padding: 8px; 
            margin: 8px 0 15px 0; 
            box-sizing: border-box; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        button { 
            width: 100%; 
            padding: 10px; 
            background-color: #007acc; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        button:hover { background-color: #005f99; }
        .hasil { 
            margin-top: 20px; 
            padding: 15px; 
            background-color: #e9ecef; 
            border-radius: 5px; 
            text-align: center; 
        }
    </style>
</head>
<body>

    <div class="kalkulator">
        <h2 style="text-align: center; margin-top: 0;">Hitung Denda</h2>
        
        <label for="jatuhTempo">Tanggal Jatuh Tempo:</label>
        <input type="date" id="jatuhTempo">
        
        <label for="tglKembali">Tanggal Pengembalian:</label>
        <input type="date" id="tglKembali">
        
        <label for="tarif">Tarif Denda per Hari (Rp):</label>
        <input type="number" id="tarif" value="5000">
        
        <button onclick="hitungDenda()">Kalkulasi Denda</button>
        
        <div class="hasil" id="tampilHasil">
            Hasil akan muncul di sini.
        </div>
    </div>

    <script>
        function hitungDenda() {
            // Mengambil nilai dari input
            const tglTempo = new Date(document.getElementById('jatuhTempo').value);
            const tglKembali = new Date(document.getElementById('tglKembali').value);
            const tarif = parseInt(document.getElementById('tarif').value);
            const tampilHasil = document.getElementById('tampilHasil');
            
            // Validasi jika tanggal kosong
            if (isNaN(tglTempo) || isNaN(tglKembali)) {
                tampilHasil.innerHTML = "<span style='color:red;'>Harap isi kedua tanggal!</span>";
                return;
            }

            // Menghitung selisih waktu dalam milidetik
            const selisihWaktu = tglKembali.getTime() - tglTempo.getTime();
            
            // Konversi milidetik ke hari (1000 ms * 60 detik * 60 menit * 24 jam)
            const selisihHari = Math.floor(selisihWaktu / (1000 * 60 * 60 * 24));

            // Logika denda
            if (selisihHari > 0) {
                const totalDenda = selisihHari * tarif;
                tampilHasil.innerHTML = 
                    `Terlambat: <strong>${selisihHari} hari</strong> <br> 
                     Total Denda: <strong style='color:red;'>Rp ${totalDenda.toLocaleString('id-ID')}</strong>`;
            } else {
                tampilHasil.innerHTML = 
                    `<strong style='color:green;'>Tepat Waktu!</strong> <br> Tidak ada denda.`;
            }
        }
    </script>

</body>
</html>