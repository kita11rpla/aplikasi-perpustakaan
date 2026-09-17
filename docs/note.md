planning refactor strcuture project nya jadi kayak gini:

aplikasi-perpustakaan/
├── config/
│   ├── db.php             <-- Isi dari Koneksi.php + .env loader
│   └── config.php         <-- Constant URL base, session start
├── includes/
│   ├── header.php         <-- Navbar / Layout atas (biar gak copas HTML)
│   ├── footer.php         <-- Footer / Layout bawah
│   └── auth_check.php     <-- Cek session login admin/siswa
├── public/                <-- (Optional) jika mau rapi banget
│   └── assets/            <-- CSS, JS, Gambar
├── modules/ (atau folder terpisah)
│   ├── admin/             <-- Semua halaman khusus Admin
│   │   ├── dashboard.php
│   │   ├── buku-tambah.php
│   │   └── laporan.php
│   └── siswa/             <-- Semua halaman khusus Siswa
│       ├── dashboard.php
│       └── pencarian.php
├── .env                   <-- Simpan DB_HOST, DB_USER, DB_PASS
├── .gitignore             <-- Abaikan .env biar gak ter-push ke GitHub
├── index.php              <-- Redirect ke login atau dashboard
└── dbbuku.sql             <-- Skema DB

yang harus di tambahkan:
1. filter katalog (sesuai genre buku dan klasifikasi buku)
2. memperbesar kolom pencarian
3. menunjukan bentuk cover dari buku