# Rules Refactoring Aplikasi Perpustakaan
- Proyek ini menggunakan PHP Native & MySQL.
- Pisahkan logika database (Koneksi.php) dari tampilan HTML.
- Struktur folder harus rapi: `/config`, `/includes`, `/admin`, `/user`, `/assets`.
- File `.env` TIDAK BOLEH ditaruh di tempat yang bisa diakses publik secara langsung.
- Gunakan Naming Convention yang konsisten (camelCase atau snake_case, jangan dicampur kebab-case & PascalCase).


"Tolong bantu refactor proyek PHP Native ini. Pertama, buatkan proposal pemindahan file dari root ke dalam folder yang rapi (/config, /includes, /admin, /user). Sesuaikan semua fungsi include atau require di tiap file agar path-nya tidak broke. Tolong samakan juga standar penamaan filenya menjadi lowercase-kebab-case."