<?php
declare(strict_types=1);
// scan.php — halaman utama untuk petugas melakukan scan barcode buku.
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Scan Barcode Buku — Perpustakaan Sekolah</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    :root {
        --bg: #f5f6f8;
        --card: #ffffff;
        --border: #dde1e6;
        --text: #1c2128;
        --muted: #5b6470;
        --accent: #2b6cb0;
        --ok: #1a7f37;
        --err: #c0342c;
    }
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
        margin: 0;
        padding: 32px 16px;
    }
    .wrap { max-width: 560px; margin: 0 auto; }
    h1 { font-size: 20px; margin-bottom: 4px; }
    p.sub { color: var(--muted); margin-top: 0; font-size: 14px; }

    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 16px;
    }

    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }

    input[type=text] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 15px;
    }
    input[type=text]:focus { outline: 2px solid var(--accent); border-color: transparent; }

    .toggle {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }
    .toggle button {
        flex: 1;
        padding: 10px;
        border: 1px solid var(--border);
        background: var(--card);
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
    }
    .toggle button.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .field { margin-bottom: 14px; }

    .status {
        font-size: 14px;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 14px;
        display: none;
    }
    .status.show { display: block; }
    .status.ok { background: #e6f4ea; color: var(--ok); }
    .status.err { background: #fbeae8; color: var(--err); }

    .buku-info { display: none; }
    .buku-info.show { display: block; }
    .buku-info h2 { font-size: 17px; margin: 0 0 4px; }
    .buku-info dl {
        display: grid;
        grid-template-columns: 110px 1fr;
        row-gap: 4px;
        font-size: 14px;
        margin: 10px 0 0;
    }
    .buku-info dt { color: var(--muted); }

    button.primary {
        width: 100%;
        padding: 12px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        cursor: pointer;
        margin-top: 14px;
    }
    button.primary:disabled { background: #9db6cc; cursor: not-allowed; }
    button.primary:hover:not(:disabled) { background: #245a92; }
</style>
</head>
<body>
<div class="wrap">
    <h1>Scan Barcode Buku</h1>
    <p class="sub">Arahkan scanner ke barcode buku. Field di bawah otomatis fokus menangkap hasil scan.</p>

    <div class="card">
        <div class="toggle">
            <button type="button" id="btn-pinjam" class="active">Pinjam</button>
            <button type="button" id="btn-beli">Beli</button>
        </div>

        <div class="field">
            <label for="nis_nip">NIS / NIP Anggota</label>
            <input type="text" id="nis_nip" placeholder="Scan / ketik NIS atau NIP" autocomplete="off">
        </div>

        <div class="field">
            <label for="barcode">Barcode Buku</label>
            <input type="text" id="barcode" placeholder="Scan barcode buku di sini" autocomplete="off">
        </div>

        <div id="status" class="status"></div>

        <div id="buku-info" class="buku-info">
            <h2 id="buku-judul"></h2>
            <dl>
                <dt>Penulis</dt><dd id="buku-penulis"></dd>
                <dt>Barcode</dt><dd id="buku-barcode"></dd>
                <dt id="dt-extra-label">Stok</dt><dd id="buku-extra"></dd>
            </dl>
        </div>

        <button type="button" id="btn-proses" class="primary" disabled>Proses Transaksi</button>
    </div>
</div>

<script>
(function () {
    const btnPinjam = document.getElementById('btn-pinjam');
    const btnBeli = document.getElementById('btn-beli');
    const inputBarcode = document.getElementById('barcode');
    const inputNis = document.getElementById('nis_nip');
    const statusBox = document.getElementById('status');
    const bukuInfo = document.getElementById('buku-info');
    const btnProses = document.getElementById('btn-proses');

    let jenis = 'pinjam';
    let bukuTervalidasi = null; // menyimpan data buku terakhir yang lolos validasi

    function setJenis(j) {
        jenis = j;
        btnPinjam.classList.toggle('active', j === 'pinjam');
        btnBeli.classList.toggle('active', j === 'beli');
        resetHasil();
    }
    btnPinjam.addEventListener('click', () => setJenis('pinjam'));
    btnBeli.addEventListener('click', () => setJenis('beli'));

    function resetHasil() {
        bukuTervalidasi = null;
        statusBox.className = 'status';
        statusBox.textContent = '';
        bukuInfo.className = 'buku-info';
        btnProses.disabled = true;
    }

    function showStatus(ok, message) {
        statusBox.className = 'status show ' + (ok ? 'ok' : 'err');
        statusBox.textContent = message;
    }

    function showBuku(buku) {
        document.getElementById('buku-judul').textContent = buku.judul;
        document.getElementById('buku-penulis').textContent = buku.penulis || '-';
        document.getElementById('buku-barcode').textContent = buku.barcode;

        if (jenis === 'pinjam') {
            document.getElementById('dt-extra-label').textContent = 'Stok pinjam';
            document.getElementById('buku-extra').textContent = buku.stok_pinjam + ' eksemplar';
        } else {
            document.getElementById('dt-extra-label').textContent = 'Harga';
            document.getElementById('buku-extra').textContent =
                'Rp ' + Number(buku.harga).toLocaleString('id-ID') + ' (stok: ' + buku.stok_jual + ')';
        }

        bukuInfo.className = 'buku-info show';
    }

    // Barcode scanner USB umumnya bertindak seperti keyboard: mengetik
    // karakter dengan sangat cepat lalu diakhiri Enter. Kita cukup
    // dengarkan event 'change'/'keydown Enter' pada input, tidak perlu
    // library khusus di sisi browser.
    async function validasiBarcode() {
        const barcode = inputBarcode.value.trim();
        if (!barcode) return;

        showStatus(true, 'Memvalidasi...');
        bukuInfo.className = 'buku-info';
        btnProses.disabled = true;

        try {
            const res = await fetch('api/validate_barcode.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ barcode, jenis }),
            });
            const data = await res.json();

            showStatus(data.valid, data.message);

            if (data.buku) {
                showBuku(data.buku);
            }

            if (data.valid) {
                bukuTervalidasi = data.buku;
                btnProses.disabled = false;
            } else {
                bukuTervalidasi = null;
            }
        } catch (err) {
            showStatus(false, 'Gagal terhubung ke server.');
        }
    }

    inputBarcode.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            validasiBarcode();
        }
    });

    btnProses.addEventListener('click', async () => {
        const nis = inputNis.value.trim();
        if (!nis) {
            showStatus(false, 'Isi / scan dulu NIS-NIP anggota.');
            inputNis.focus();
            return;
        }
        if (!bukuTervalidasi) return;

        btnProses.disabled = true;
        btnProses.textContent = 'Memproses...';

        try {
            const res = await fetch('api/process_transaksi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    barcode: bukuTervalidasi.barcode,
                    jenis,
                    nis_nip: nis,
                    petugas: 'admin', // ganti dengan nama petugas login jika sudah ada sistem auth
                }),
            });
            const data = await res.json();
            showStatus(data.success, data.message);

            if (data.success) {
                inputBarcode.value = '';
                bukuInfo.className = 'buku-info';
                bukuTervalidasi = null;
            }
        } catch (err) {
            showStatus(false, 'Gagal terhubung ke server.');
        } finally {
            btnProses.textContent = 'Proses Transaksi';
            btnProses.disabled = !bukuTervalidasi;
        }
    });

    inputBarcode.focus();
})();
</script>
</body>
</html>
