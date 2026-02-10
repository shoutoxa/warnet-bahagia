-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Feb 2026 pada 14.27
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manajemen_warnet`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `username`, `password`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bantuan_teknis`
--

CREATE TABLE `bantuan_teknis` (
  `id_bantuan` int(11) NOT NULL,
  `jenis_bantuan` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('menunggu','diproses','selesai') DEFAULT NULL,
  `id_konsumen` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bantuan_teknis`
--

INSERT INTO `bantuan_teknis` (`id_bantuan`, `jenis_bantuan`, `deskripsi`, `status`, `id_konsumen`) VALUES
(1, 'Lainnya', 'Sakit kaki', 'menunggu', 1),
(2, 'PC Bermasalah', 'Monitor ga nyala', 'selesai', 1),
(3, 'Jaringan & Internet', 'ggvhvchgg', 'menunggu', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `billing`
--

CREATE TABLE `billing` (
  `id_billing` int(11) NOT NULL,
  `durasi_jam` int(11) DEFAULT NULL,
  `harga_per_jam` decimal(10,2) DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL,
  `id_promo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `billing`
--

INSERT INTO `billing` (`id_billing`, `durasi_jam`, `harga_per_jam`, `total_harga`, `id_promo`) VALUES
(4, 1, 5000.00, 5000.00, NULL),
(5, 12, 5000.00, 60000.00, NULL),
(6, 1, 5000.00, 5000.00, NULL),
(7, 2, 100000.00, 200000.00, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_item` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_item`, `qty`, `harga_satuan`, `subtotal`) VALUES
(1, 14, 19, 1, 0.00, 3000.00),
(2, 15, 21, 1, 0.00, 5000.00),
(3, 16, 21, 5, 0.00, 25000.00),
(4, 17, 19, 6, 0.00, 18000.00),
(5, 18, 21, 2, 0.00, 10000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_fnb`
--

CREATE TABLE `item_fnb` (
  `id_item` int(11) NOT NULL,
  `nama_item` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_fnb`
--

INSERT INTO `item_fnb` (`id_item`, `nama_item`, `harga`, `stok`, `kategori`, `gambar`) VALUES
(14, 'Kopi Hitam', 10000.00, 40, 'Minuman', 'default.png'),
(15, 'Indomie Goreng', 5000.00, 47, 'Makanan', 'default.png'),
(16, 'Indomie Soto', 5000.00, 49, 'Makanan', 'default.png'),
(17, 'Mie Sedaap', 5000.00, 50, 'Makanan', 'default.png'),
(18, 'Nasi Goreng', 15000.00, 20, 'Makanan', 'default.png'),
(19, 'Aqua 600ml', 3000.00, 91, 'Minuman', 'default.png'),
(20, 'Teh Botol', 4000.00, 80, 'Minuman', '6982efda11ccc.png'),
(21, 'Coca Cola', 5000.00, 51, 'Minuman', '6982eef350516.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL,
  `status` enum('aktif','checkout','selesai') DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `id_item` int(11) DEFAULT NULL,
  `id_billing` int(11) DEFAULT NULL,
  `id_konsumen` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `total_harga`, `status`, `qty`, `id_item`, `id_billing`, `id_konsumen`) VALUES
(1, 5000.00, 'selesai', NULL, NULL, 0, NULL),
(2, 5000.00, 'selesai', 1, 15, NULL, NULL),
(3, 5000.00, 'selesai', 1, 16, NULL, NULL),
(4, 5000.00, 'selesai', 1, 15, NULL, NULL),
(5, 5000.00, 'selesai', 1, 15, NULL, NULL),
(6, 5000.00, 'selesai', 1, 15, NULL, NULL),
(7, 60000.00, 'selesai', NULL, NULL, 5, NULL),
(8, 5000.00, 'selesai', NULL, NULL, 6, NULL),
(9, 5000.00, 'selesai', NULL, NULL, 4, NULL),
(10, 5000.00, 'selesai', NULL, NULL, 4, NULL),
(11, NULL, 'selesai', NULL, NULL, NULL, NULL),
(12, 12000.00, 'selesai', 2, 24, NULL, NULL),
(13, 5000.00, 'selesai', 1, 21, NULL, NULL),
(14, 3000.00, 'selesai', 1, 19, NULL, NULL),
(15, 5000.00, 'selesai', NULL, NULL, 4, NULL),
(20, 5000.00, 'selesai', NULL, NULL, 4, NULL),
(21, 3000.00, 'selesai', 1, 19, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `konsumen`
--

CREATE TABLE `konsumen` (
  `id_konsumen` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `saldo` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `konsumen`
--

INSERT INTO `konsumen` (`id_konsumen`, `username`, `password`, `email`, `saldo`) VALUES
(1, 'user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com', 87000.00),
(2, 'user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@example.com', 75000.00),
(3, 'shoutoxa', '$2y$10$9zPkl4b5EqnnDLe2zd7EBeRUtC/wDJBCJ1orlP5jhvpydXsPKVNpS', 'mxzidanxa@gmail.com', 125500.00),
(4, 'Synn', '$2y$10$9oijsVRv29TNzIpy5eFdQeX8t8ZxST1pKmk88UDHR8ByZglvnmO.i', 'janfjkdasbfkjbs@gmail.com', 75000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pc`
--

CREATE TABLE `pc` (
  `id_pc` int(11) NOT NULL,
  `status_pc` enum('tersedia','digunakan','rusak') DEFAULT NULL,
  `id_konsumen` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pc`
--

INSERT INTO `pc` (`id_pc`, `status_pc`, `id_konsumen`, `id_admin`) VALUES
(1, 'digunakan', 3, 1),
(2, 'digunakan', NULL, 1),
(3, 'rusak', NULL, 1),
(4, 'tersedia', NULL, 1),
(5, 'tersedia', NULL, 1),
(6, 'tersedia', NULL, 1),
(7, 'rusak', NULL, 1),
(8, 'rusak', NULL, 1),
(9, 'tersedia', NULL, 1),
(10, 'digunakan', 4, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `key_name` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`key_name`, `value`) VALUES
('harga_per_jam', '5000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `waktu_pesanan` datetime DEFAULT NULL,
  `status_pesanan` enum('pending','dibayar','selesai','batal') DEFAULT NULL,
  `total_pesanan` decimal(12,2) DEFAULT NULL,
  `id_konsumen` int(11) DEFAULT NULL,
  `id_keranjang` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `id_promo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `waktu_pesanan`, `status_pesanan`, `total_pesanan`, `id_konsumen`, `id_keranjang`, `id_admin`, `id_promo`) VALUES
(1, '2026-02-02 22:20:58', 'selesai', 5000.00, 1, 0, 1, NULL),
(2, '2026-02-02 23:58:09', 'dibayar', 5000.00, 1, 2, NULL, NULL),
(3, '2026-02-02 23:58:58', 'dibayar', 5000.00, 1, 3, NULL, NULL),
(4, '2026-02-02 23:59:05', 'dibayar', 5000.00, 1, 4, NULL, NULL),
(5, '2026-02-02 23:59:42', 'dibayar', 5000.00, 1, 5, NULL, NULL),
(6, '2026-02-03 00:05:01', 'dibayar', 5000.00, 1, 6, NULL, NULL),
(7, '2026-02-03 00:13:12', 'pending', 60000.00, 1, 7, 1, NULL),
(8, '2026-02-03 00:17:27', 'pending', 5000.00, 1, 8, 1, NULL),
(9, '2026-02-03 00:36:59', 'batal', 5000.00, 1, 9, 1, NULL),
(13, '2026-02-03 16:25:25', 'dibayar', 11000.00, 3, NULL, 1, NULL),
(14, '2026-02-04 12:02:19', 'dibayar', 3000.00, 3, NULL, 1, NULL),
(15, '2026-02-04 14:03:06', 'dibayar', 5000.00, 3, NULL, 1, NULL),
(16, '2026-02-04 15:02:46', 'dibayar', 25000.00, 3, NULL, 1, NULL),
(17, '2026-02-04 15:03:30', 'dibayar', 18000.00, 3, NULL, 1, NULL),
(18, '2026-02-04 15:35:46', 'pending', 10000.00, 4, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo`
--

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `target` enum('billing','fnb','all') NOT NULL DEFAULT 'all',
  `persentase` int(11) NOT NULL DEFAULT 0,
  `min_transaksi` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kuota` int(11) NOT NULL DEFAULT 0,
  `valid_until` date NOT NULL,
  `gambar` varchar(255) DEFAULT 'default_promo.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promo`
--

INSERT INTO `promo` (`id_promo`, `nama_promo`, `deskripsi`, `kode_promo`, `target`, `persentase`, `min_transaksi`, `kuota`, `valid_until`, `gambar`) VALUES
(3, 'promo opening', 'promo opening', 'opening', 'billing', 50, 20000.00, 9, '2027-10-10', '6983018e8e021.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_pc`
--

CREATE TABLE `transaksi_pc` (
  `id_transaksi` int(11) NOT NULL,
  `id_konsumen` int(11) NOT NULL,
  `id_pc` int(11) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi_jam` int(11) NOT NULL,
  `total_bayar` decimal(12,2) NOT NULL,
  `status` enum('berjalan','selesai') DEFAULT 'berjalan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_pc`
--

INSERT INTO `transaksi_pc` (`id_transaksi`, `id_konsumen`, `id_pc`, `waktu_mulai`, `waktu_selesai`, `durasi_jam`, `total_bayar`, `status`) VALUES
(1, 3, 1, '2026-02-03 16:34:33', '2026-02-03 17:13:09', 1, 5000.00, 'selesai'),
(2, 3, 1, '2026-02-03 17:13:19', '2026-02-04 13:52:12', 1, 5000.00, 'selesai'),
(3, 3, 1, '2026-02-04 13:52:19', '2026-02-04 14:53:25', 5, 25000.00, 'selesai'),
(4, 3, 1, '2026-02-04 15:02:29', '2026-02-04 15:03:04', 0, 0.00, 'selesai'),
(5, 3, 10, '2026-02-04 15:03:11', '2026-02-04 15:03:38', 0, 0.00, 'selesai'),
(6, 3, 1, '2026-02-04 15:09:18', '2026-02-04 15:09:26', 5, 12500.00, 'selesai'),
(7, 3, 10, '2026-02-04 15:09:56', '2026-02-04 15:21:58', 7, 17500.00, 'selesai'),
(8, 1, 1, '2026-02-04 15:11:51', '2026-02-04 15:12:03', 2, 5000.00, 'selesai'),
(9, 1, 1, '2026-02-04 15:12:12', '2026-02-04 15:15:08', 2, 5000.00, 'selesai'),
(10, 1, 1, '2026-02-04 15:15:21', '2026-02-04 15:15:51', 2, 5000.00, 'selesai'),
(11, 3, 1, '2026-02-04 15:22:06', '2026-02-04 15:22:58', 5, 12500.00, 'selesai'),
(12, 4, 10, '2026-02-04 15:33:25', NULL, 3, 15000.00, 'berjalan'),
(13, 3, 1, '2026-02-05 09:07:22', '2026-02-05 09:13:48', 5, 25000.00, 'selesai'),
(14, 3, 1, '2026-02-05 09:13:52', NULL, 1, 5000.00, 'berjalan');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `bantuan_teknis`
--
ALTER TABLE `bantuan_teknis`
  ADD PRIMARY KEY (`id_bantuan`),
  ADD KEY `id_konsumen` (`id_konsumen`);

--
-- Indeks untuk tabel `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id_billing`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indeks untuk tabel `item_fnb`
--
ALTER TABLE `item_fnb`
  ADD PRIMARY KEY (`id_item`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_billing` (`id_billing`),
  ADD KEY `keranjang_ibfk_1` (`id_item`),
  ADD KEY `id_konsumen` (`id_konsumen`);

--
-- Indeks untuk tabel `konsumen`
--
ALTER TABLE `konsumen`
  ADD PRIMARY KEY (`id_konsumen`);

--
-- Indeks untuk tabel `pc`
--
ALTER TABLE `pc`
  ADD PRIMARY KEY (`id_pc`),
  ADD KEY `pc_ibfk_1` (`id_konsumen`),
  ADD KEY `pc_ibfk_2` (`id_admin`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`key_name`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_konsumen` (`id_konsumen`),
  ADD KEY `id_keranjang` (`id_keranjang`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indeks untuk tabel `transaksi_pc`
--
ALTER TABLE `transaksi_pc`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bantuan_teknis`
--
ALTER TABLE `bantuan_teknis`
  MODIFY `id_bantuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `billing`
--
ALTER TABLE `billing`
  MODIFY `id_billing` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `item_fnb`
--
ALTER TABLE `item_fnb`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `konsumen`
--
ALTER TABLE `konsumen`
  MODIFY `id_konsumen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pc`
--
ALTER TABLE `pc`
  MODIFY `id_pc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi_pc`
--
ALTER TABLE `transaksi_pc`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bantuan_teknis`
--
ALTER TABLE `bantuan_teknis`
  ADD CONSTRAINT `bantuan_teknis_ibfk_1` FOREIGN KEY (`id_konsumen`) REFERENCES `konsumen` (`id_konsumen`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
