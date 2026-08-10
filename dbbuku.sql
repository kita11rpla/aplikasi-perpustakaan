
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;




CREATE TABLE `penambahanbuku` (
  `id_buku` int(11) NOT NULL,
  `judul_buku` text NOT NULL,
  `penulis` text NOT NULL,
  `penerbit` text NOT NULL,
  `tahun_terbit` date NOT NULL,
  `ISBN` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `penambahanbuku` (`id_buku`, `judul_buku`, `penulis`, `penerbit`, `tahun_terbit`, `ISBN`) VALUES
(1, 'Mindset', 'Carol Dweck', 'Penerbit Baca', '2006-02-28', 'BK-001'),
(2, 'Atomic Habits', 'James Clear', 'Gramedia Pustaka Utama', '2019-09-16', 'BK-002'),
(3, 'The Psychology of Money', 'Morgan Housel', 'Penerbit Baca', '2021-12-28', 'BK-003'),
(4, 'Ikigai', 'Héctor García dan Francesc Miralles', 'Renebook', '2018-07-21', 'BK-004'),
(5, 'Folisofi Teras', 'Henry Manampiring', 'Buku Kompas', '0018-11-04', 'BK-005'),
(6, 'Start With Why', 'Simon Sinek', 'Gramedia Pustaka Utama', '2019-12-28', 'BK-006');



CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `ISBN` varchar(30) NOT NULL,
  `nama_peminjam` varchar(100) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status_transaksi` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  `status_denda` enum('belum_lunas','lunas') DEFAULT 'belum_lunas',
  `denda_terutang` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `transaksi` (`id_transaksi`, `ISBN`, `nama_peminjam`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status_transaksi`, `status_denda`, `denda_terutang`) VALUES
(1, 'BK-002', 'Bani israil', '2026-07-05', '2026-08-01', '2026-08-06', 'dikembalikan', 'lunas', 25000),
(2, 'BK-002', 'Gilang', '2026-08-01', '2026-08-03', '2026-08-06', 'dikembalikan', 'lunas', 15000),
(3, 'BK-002', 'israel', '2026-08-01', '2026-08-02', '2026-08-06', 'dikembalikan', 'lunas', 20000),
(4, 'BK-001', 'Alviar', '2026-08-06', '2026-08-13', '2026-08-06', 'dikembalikan', 'lunas', 0),
(5, 'BK-002', 'rafi', '2026-08-01', '2026-08-02', '2026-08-06', 'dikembalikan', 'lunas', 20000);



CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'qwe', 'qwe@gmail.com', '$2y$10$.SykcCNOZvKB6C1kB85Rfuvxe001xP6t9elTXCfeHEKtbUPmLGNji', '2026-08-06 06:37:27');


ALTER TABLE `penambahanbuku`
  ADD PRIMARY KEY (`id_buku`);

ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `penambahanbuku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
