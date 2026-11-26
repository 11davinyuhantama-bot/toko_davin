-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Nov 2026 pada 06.55
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
-- Database: `db_tokoko`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CariPelanggan` (IN `p_id` INT)   BEGIN
    SELECT *
    FROM tbl_pelanggan
    WHERE id_pelanggan = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `HitungPelanggan` (OUT `jumlah` INT)   BEGIN
    SELECT COUNT(*)
    INTO jumlah
    FROM tbl_pelanggan;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `LaporanTransaksi` (IN `p_id_pelanggan` INT)   BEGIN
    SELECT
        p.id_pelanggan,
        p.nama_pelanggan,
        p.kota,
        t.id_transaksi,
        t.tanggal,
        t.total_belanja
    FROM tbl_pelanggan p
    INNER JOIN tbl_transaksi t
    ON p.id_pelanggan = t.id_pelanggan
    WHERE p.id_pelanggan = p_id_pelanggan;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahPelanggan` (IN `p_nama` VARCHAR(100), IN `p_kota` VARCHAR(50))   BEGIN
    INSERT INTO tbl_pelanggan
    (nama_pelanggan, kota)
    VALUES
    (p_nama, p_kota);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TampilPelanggan` ()   BEGIN
    SELECT * FROM tbl_pelanggan;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `HitungDiskon` (`harga` DECIMAL(12,2), `diskon` DECIMAL(5,2)) RETURNS DECIMAL(12,2) DETERMINISTIC NO SQL BEGIN
    RETURN harga - (harga * diskon / 100);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `HitungPajak` (`harga` DECIMAL(12,2)) RETURNS DECIMAL(12,2) DETERMINISTIC NO SQL BEGIN
    RETURN harga * 0.11;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `HitungTotal` (`harga` DECIMAL(12,2), `jumlah` INT) RETURNS DECIMAL(12,2) DETERMINISTIC NO SQL BEGIN
    RETURN harga * jumlah;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `KategoriBelanja` (`total` DECIMAL(12,2)) RETURNS VARCHAR(30) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC NO SQL BEGIN
    RETURN CASE
        WHEN total >= 1000000 THEN 'Pelanggan Gold'
        WHEN total >= 500000 THEN 'Pelanggan Silver'
        ELSE 'Pelanggan Reguler'
    END;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `KonversiNilai` (`nilai` INT) RETURNS VARCHAR(5) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC NO SQL BEGIN
    RETURN CASE
        WHEN nilai >= 90 AND nilai <= 100 THEN 'A'
        WHEN nilai >= 80 THEN 'B'
        WHEN nilai >= 70 THEN 'C'
        WHEN nilai >= 60 THEN 'D'
        ELSE 'E'
    END;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_pelanggan`
--

CREATE TABLE `log_pelanggan` (
  `id_log` int(11) NOT NULL,
  `aktivitas` varchar(50) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_pelanggan`
--

INSERT INTO `log_pelanggan` (`id_log`, `aktivitas`, `id_pelanggan`, `waktu`) VALUES
(1, 'INSERT PELANGGAN', 20, '2026-11-09 04:46:25'),
(2, 'INSERT PELANGGAN', 21, '2026-11-11 02:18:31'),
(3, 'INSERT PELANGGAN', 22, '2026-11-11 03:31:11'),
(4, 'INSERT PELANGGAN', 23, '2026-11-11 03:38:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_perubahan_pelanggan`
--

CREATE TABLE `log_perubahan_pelanggan` (
  `id_log` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `nama_lama` varchar(100) DEFAULT NULL,
  `nama_baru` varchar(100) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_perubahan_pelanggan`
--

INSERT INTO `log_perubahan_pelanggan` (`id_log`, `id_pelanggan`, `nama_lama`, `nama_baru`, `waktu`) VALUES
(1, 4, 'Andi', 'Rina Wijaya', '2026-11-09 04:47:15'),
(2, 19, 'Dewi', 'danu', '2026-11-11 02:18:09'),
(3, 18, 'Dewi', 'feril', '2026-11-11 02:18:17'),
(4, 6, 'Citra', 'akbar', '2026-11-11 02:18:40'),
(5, 7, 'Andi', 'nasaul', '2026-11-11 02:18:49'),
(6, 8, 'Budi', 'masrur', '2026-11-11 02:18:59'),
(7, 9, 'Citra', 'mandriva', '2026-11-11 02:19:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_cabang`
--

CREATE TABLE `tbl_cabang` (
  `id_cabang` int(11) NOT NULL,
  `nama_cabang` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_cabang`
--

INSERT INTO `tbl_cabang` (`id_cabang`, `nama_cabang`, `kota`) VALUES
(1, 'Cabang 1', 'Ponorogo'),
(2, 'Cabang 2', 'Madiun'),
(3, 'Cabang 3', 'Jakarta'),
(4, 'Cabang 4', 'Surabaya');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_transaksi`
--

CREATE TABLE `tbl_detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_detail_transaksi`
--

INSERT INTO `tbl_detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `nama_produk`, `jumlah`, `harga`) VALUES
(1, 1, NULL, 'Laptop', 1, 250000.00),
(2, 2, NULL, 'Mouse', 2, 175000.00),
(3, 3, NULL, 'Keyboard', 1, 150000.00),
(4, 4, NULL, 'Monitor', 1, 500000.00),
(5, 5, NULL, 'Flashdisk', 2, 100000.00),
(6, 6, NULL, 'Printer', 1, 300000.00),
(7, 7, NULL, 'Laptop', 1, 450000.00),
(8, 8, NULL, 'Mouse', 1, 175000.00),
(9, 9, NULL, 'Keyboard', 2, 137500.00),
(10, 10, NULL, 'Monitor', 1, 400000.00),
(11, 11, NULL, 'Laptop', 1, 550000.00),
(12, 12, NULL, 'Flashdisk', 1, 125000.00),
(13, 13, NULL, 'Printer', 1, 225000.00),
(14, 14, NULL, 'Mouse', 2, 162500.00),
(15, 15, NULL, 'Monitor', 1, 425000.00),
(16, 16, NULL, 'Keyboard', 1, 150000.00),
(17, 17, NULL, 'Laptop', 1, 250000.00),
(18, 18, NULL, 'Printer', 1, 350000.00),
(19, 19, NULL, 'Monitor', 1, 450000.00),
(20, 20, NULL, 'Laptop', 1, 500000.00),
(21, 30, NULL, 'Headset USB', 5, 150000.00),
(22, 31, NULL, 'Router WiFii', 1, 325000.00),
(23, 32, NULL, 'Router WiFii', 1, 325000.00),
(24, 33, NULL, 'Laptop Lenovo IdeaPad', 1, 7500000.00),
(25, 34, NULL, 'Flashdisk 64GB', 1, 95000.00),
(26, 35, NULL, 'Flashdisk 64GB', 1, 95000.00),
(27, 36, NULL, 'Iphone 15 Ibox', 1, 13499000.00),
(28, 37, NULL, 'Flashdisk 64GB', 1, 95000.00),
(29, 38, 5, 'Flashdisk 64GB', 1, 95000.00),
(30, 39, 5, 'Flashdisk 64GB', 1, 95000.00),
(31, 40, 5, 'Flashdisk 64GB', 1, 95000.00),
(32, 41, 5, 'Flashdisk 64GB', 1, 95000.00),
(33, 42, NULL, 'Router WiFii', 1, 325000.00),
(34, 43, 5, 'Flashdisk 64GB', 1, 95000.00),
(35, 44, 5, 'Flashdisk 64GB', 1, 95000.00),
(36, 45, 7, 'Headset USB', 1, 150000.00),
(37, 46, NULL, 'Flashdisk 64GB', 1, 95000.00),
(38, 47, NULL, 'Keyboard Mechanical', 1, 275000.00),
(39, 48, NULL, 'Keyboard Mechanical', 1, 275000.00),
(40, 49, 5, 'Flashdisk 64GB', 1, 95000.00),
(41, 50, 7, 'Headset USB', 1, 150000.00),
(42, 51, 5, 'Flashdisk 64GB', 1, 95000.00),
(43, 52, 5, 'Flashdisk 64GB', 1, 95000.00),
(44, 53, 5, 'Flashdisk 64GB', 1, 95000.00),
(45, 54, 5, 'Flashdisk 64GB', 1, 95000.00),
(46, 55, 5, 'Flashdisk 64GB', 1, 95000.00),
(47, 56, 5, 'Flashdisk 64GB', 1, 95000.00),
(48, 57, 5, 'Flashdisk 64GB', 1, 95000.00),
(49, 58, 7, 'Headset USB', 1, 150000.00),
(50, 59, 2, 'Mouse Logitech B100', 1, 85000.00),
(51, 60, 3, 'Keyboard Mechanical', 1, 275000.00),
(52, 61, 7, 'Headset USB', 1, 150000.00),
(53, 62, 12, 'Iphone 15 Ibox', 1, 13499000.00),
(54, 63, 12, 'Iphone 15 Ibox', 1, 13499000.00),
(55, 64, 7, 'Headset USB', 1, 150000.00),
(56, 65, 5, 'Flashdisk 64GB', 1, 95000.00),
(57, 66, 7, 'Headset USB', 1, 150000.00),
(58, 67, 5, 'Flashdisk 64GB', 1, 95000.00),
(59, 68, 5, 'Flashdisk 64GB', 1, 95000.00),
(60, 69, 5, 'Flashdisk 64GB', 1, 95000.00),
(61, 70, 5, 'Flashdisk 64GB', 1, 95000.00),
(62, 71, 4, 'Monitor LED 24 Inch', 1, 1850000.00),
(63, 72, 5, 'Flashdisk 64GB', 1, 95000.00),
(64, 73, 4, 'Monitor LED 24 Inch', 1, 1850000.00),
(65, 74, 3, 'Keyboard Mechanical', 1, 275000.00),
(66, 75, 5, 'Flashdisk 64GB', 1, 95000.00),
(67, 76, 2, 'Mouse Logitech B100', 1, 85000.00),
(68, 77, 7, 'Headset USB', 1, 150000.00),
(69, 78, 5, 'Flashdisk 64GB', 1, 95000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pelanggan`
--

CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `nama_pelanggan`, `kota`) VALUES
(1, 'Andi', 'Ponorogo'),
(2, 'Budi', 'Madiun'),
(3, 'Citra', 'Surabaya'),
(4, 'Rina Wijaya', 'Ponorogo'),
(5, 'Budi', 'Madiun'),
(6, 'akbar', 'Surabaya'),
(7, 'nasaul', 'Ponorogo'),
(8, 'masrur', 'Madiun'),
(9, 'mandriva', 'Surabaya'),
(10, 'Dina', 'Malang'),
(11, 'Eko', 'Kediri'),
(12, 'Fajar', 'Ngawi'),
(13, 'Gita', 'Magetan'),
(14, 'Hendra', 'Blitar'),
(15, 'Indah', 'Pacitan'),
(17, 'Dewi', 'Ponorogo'),
(18, 'feril', 'Ponorogo'),
(19, 'danu', 'Ponorogo'),
(20, 'Rina', 'Ponorogo'),
(21, 'akbar', 'Madiun'),
(22, 'Pelanggan Demo', 'Belum diisi'),
(23, 'davin', 'ponorogoon');

--
-- Trigger `tbl_pelanggan`
--
DELIMITER $$
CREATE TRIGGER `after_insert_pelanggan` AFTER INSERT ON `tbl_pelanggan` FOR EACH ROW BEGIN
    INSERT INTO log_pelanggan
    (aktivitas, id_pelanggan)
    VALUES
    ('INSERT PELANGGAN', NEW.id_pelanggan);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_pelanggan` AFTER UPDATE ON `tbl_pelanggan` FOR EACH ROW BEGIN
    INSERT INTO log_perubahan_pelanggan
    (
        id_pelanggan,
        nama_lama,
        nama_baru
    )
    VALUES
    (
        OLD.id_pelanggan,
        OLD.nama_pelanggan,
        NEW.nama_pelanggan
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_produk`
--

CREATE TABLE `tbl_produk` (
  `id_produk` int(11) NOT NULL,
  `kode_produk` varchar(30) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_produk`
--

INSERT INTO `tbl_produk` (`id_produk`, `kode_produk`, `nama_produk`, `kategori`, `harga`, `stok`, `deskripsi`, `gambar`, `created_at`) VALUES
(1, 'PRD001', 'Laptop Lenovo IdeaPad', 'Laptop', 7500000.00, 9, 'Laptop untuk kebutuhan sekolah dan pekerjaan.', NULL, '2026-11-11 00:34:36'),
(2, 'PRD002', 'Mouse Logitech B100', 'Aksesoris', 85000.00, 28, 'Mouse kabel USB.', 'eb046b383ee4404d3fef7645884cf6d1.jpg', '2026-11-11 00:34:36'),
(3, 'PRD003', 'Keyboard Mechanical', 'Aksesoris', 275000.00, 11, 'Keyboard untuk komputer desktop.', '7e9a5ccd0d56874401c7684faed71602.jpg', '2026-11-11 00:34:36'),
(4, 'PRD004', 'Monitor LED 24 Inch', 'Monitor', 1850000.00, 6, 'Monitor LED ukuran 24 inci.', '17b73db8c9f7578988bd3c0f8dcd9cbf.jpg', '2026-11-11 00:34:36'),
(5, 'PRD005', 'Flashdisk 64GB', 'Penyimpanan', 95000.00, 27, 'Flashdisk USB kapasitas 64GB.', 'acaebc2e4c6c6a9af051b14b2c5e23e6.jpg', '2026-11-11 00:34:36'),
(6, 'PRD006', 'Printer Epson L3210', 'Printer', 2350000.00, 6, 'Printer multifungsi.', '105815e7f4099d9d74f4db7d93b0b7fb.jpg', '2026-11-11 00:34:36'),
(7, 'PRD007', 'Headset USB', 'Aksesoris', 150000.00, 8, 'Headset dengan koneksi USB.', '099ed5fe8c3665107fda4ddbf5aa80aa.jpg', '2026-11-11 00:34:36'),
(8, 'PRD008', 'SSD 512GB', 'Penyimpanan', 650000.00, 12, 'SSD internal kapasitas 512GB.', '534c2a76c25cf1e506d22522af0fa53f.jpg', '2026-11-11 00:34:36'),
(9, 'PRD009', 'Webcam HD', 'Aksesoris', 220000.00, 14, 'Webcam untuk konferensi video.', '2906cea4fdbb3c12249769e356d087c0.jpg', '2026-11-11 00:34:36'),
(10, 'PRD010', 'Router WiFii', 'Jaringan', 325000.00, 6, 'Router nirkabel untuk jaringan lokal.', 'ad72d1ae6d94eb67515a346c7592da93.jpg', '2026-11-11 00:34:36'),
(12, 'TRX-16', 'Iphone 15 Ibox', 'Handphone', 13499000.00, 1, 'Second', 'c86cb10a0356961f8b5e658d77f49fe9.jpg', '2026-11-11 04:34:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_transaksi`
--

CREATE TABLE `tbl_transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_kasir` int(11) DEFAULT NULL,
  `total_belanja` decimal(12,2) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'paid',
  `payment_order_id` varchar(64) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `snap_redirect_url` text DEFAULT NULL,
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_url` varchar(500) DEFAULT NULL,
  `payment_channel` varchar(100) DEFAULT NULL,
  `payment_va_number` varchar(100) DEFAULT NULL,
  `payment_qr_string` text DEFAULT NULL,
  `payment_app_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_transaksi`
--

INSERT INTO `tbl_transaksi` (`id_transaksi`, `tanggal`, `id_pelanggan`, `id_kasir`, `total_belanja`, `payment_status`, `payment_order_id`, `snap_token`, `snap_redirect_url`, `payment_method`, `payment_proof`, `payment_reference`, `payment_url`, `payment_channel`, `payment_va_number`, `payment_qr_string`, `payment_app_url`) VALUES
(1, '2026-10-01', 1, NULL, 250000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '2026-10-02', 2, NULL, 350000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '2026-10-03', 1, NULL, 150000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '2026-10-04', 3, NULL, 500000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '2026-10-01', 1, NULL, 250000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '2026-10-02', 2, NULL, 350000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '2026-10-03', 1, NULL, 150000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, '2026-10-04', 3, NULL, 500000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, '2026-10-01', 1, NULL, 250000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '2026-10-02', 2, NULL, 350000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '2026-10-03', 1, NULL, 150000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '2026-10-04', 3, NULL, 500000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '2026-10-05', 4, NULL, 200000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '2026-10-06', 5, NULL, 300000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, '2026-10-07', 6, NULL, 450000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '2026-10-08', 7, NULL, 175000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '2026-10-09', 8, NULL, 275000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, '2026-10-10', 9, NULL, 400000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, '2026-10-11', 10, NULL, 550000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, '2026-10-12', 2, NULL, 125000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, '2026-10-13', 3, NULL, 225000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, '2026-10-14', 4, NULL, 325000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, '2026-10-15', 5, NULL, 425000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, '2026-10-16', 6, NULL, 150000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, '2026-10-17', 7, NULL, 250000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, '2026-10-18', 8, NULL, 350000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, '2026-10-19', 9, NULL, 450000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, '2026-10-20', 10, NULL, 500000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, '2026-11-01', 1, NULL, 150000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, '2026-11-11', 6, NULL, 750000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, '2026-11-11', 8, NULL, 325000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, '2026-11-11', 7, NULL, 325000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, '2026-11-11', 7, NULL, 7500000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, '2026-11-11', 23, NULL, 95000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, '2026-11-11', 22, NULL, 95000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, '2026-11-11', 23, NULL, 13499000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, '2026-11-23', 22, NULL, 95000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-38-B75EA8D1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-39-097EA0F7', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-40-5F481502', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-41-E5C1D129', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, '2026-11-23', 6, NULL, 325000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-43-1F24DB19', NULL, NULL, 'qris_static', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, '2026-11-23', 22, NULL, 95000.00, 'failed', 'TOKO-44-BC5AE76C', NULL, NULL, 'qris_static', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, '2026-11-23', 22, NULL, 150000.00, 'paid', 'TOKO-45-F658CEFD', NULL, NULL, 'qris_static', 'payment_proofs/2b5517479ef172bfe87b154348fe1673.jpg', NULL, NULL, NULL, NULL, NULL, NULL),
(46, '2026-11-25', 21, NULL, 95000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, '2026-11-25', 2, 2, 275000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, '2026-11-25', 12, 2, 275000.00, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, '2026-11-25', 22, NULL, 95000.00, 'paid', 'TOKO-49-5866558C', NULL, NULL, 'qris_static', 'payment_proofs/a04fbfd9b1e96c23394663b65e481801.png', NULL, NULL, NULL, NULL, NULL, NULL),
(50, '2026-11-25', 22, NULL, 150000.00, 'paid', 'TOKO-50-550057DF', NULL, NULL, 'qris_static', 'payment_proofs/f55a5c6ba3c51beaf74242c6377e2b97.jpg', NULL, NULL, NULL, NULL, NULL, NULL),
(51, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-51-6C74CDD6', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-52-53EEF687', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-53-0DDDEBC0', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-54-A0FC9502', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-55-95A30D9A', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-56-B2366CB3', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, '2026-11-26', 22, NULL, 95000.00, 'pending', 'TOKO-57-2F383CE3', NULL, NULL, 'duitku', NULL, 'DS3858926FSR84ZKQ3GX1VYK', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP2600NVH5DHC0LXJIJ', NULL, NULL, NULL, NULL),
(58, '2026-11-26', 22, NULL, 150000.00, 'pending', 'TOKO-58-910C9AA6', NULL, NULL, 'duitku', NULL, 'DS3858926PAT246SCE3BJRES', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP263PXWZBZJ5KQC8M5', NULL, NULL, NULL, NULL),
(59, '2026-11-26', 22, NULL, 85000.00, 'pending', 'TOKO-59-9B98F867', NULL, NULL, 'qris_static', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, '2026-11-26', 22, NULL, 275000.00, 'pending', 'TOKO-60-D9888B23', NULL, NULL, 'duitku', NULL, 'DS3858926V7QFNITA51TFTXW', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP2642VYWXQK3XBIRXY', NULL, NULL, NULL, NULL),
(61, '2026-11-26', 22, NULL, 150000.00, 'paid', 'TOKO-61-69D2C6FB', NULL, NULL, 'duitku', NULL, 'DS3858926PBTG8C8YFYD1FGQ', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP26J12Z4ILRX62XMTQ', NULL, NULL, NULL, NULL),
(62, '2026-11-26', 22, NULL, 13499000.00, 'failed', 'TOKO-62-2A808C23', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, '2026-11-26', 22, NULL, 13499000.00, 'pending', 'TOKO-63-5AB532DA', NULL, NULL, 'qris_static', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, '2026-11-26', 22, NULL, 150000.00, 'paid', 'TOKO-64-BF208982', NULL, NULL, 'duitku', NULL, 'DS3858926WV8AIV20KU4820T', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP26S6854UJ4IVDVTCQ', NULL, NULL, NULL, NULL),
(65, '2026-11-26', 22, NULL, 95000.00, 'pending', 'TOKO-65-2DE7775B', NULL, NULL, 'duitku', NULL, 'DS3858926CJ6TXLKZJXJA2VY', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26KK2QXUHH235YRV0', NULL, NULL, NULL, NULL),
(66, '2026-11-26', 22, NULL, 150000.00, 'pending', 'TOKO-66-7F40800F', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, '2026-11-26', 22, NULL, 95000.00, 'failed', 'TOKO-67-A29E91AE', NULL, NULL, 'duitku', NULL, 'DS38589268QLK2RQLPZVJMQG', 'https://m.sandbox.dana.id/link/pay?bizNo=20261126111212800110166473802885337&timestamp=1795658508980&originSourcePlatform=IPG&mid=216620000000887246413&did=216650000000992314417&sid=216660000000992318418&sign=VJ6ZjEuM4%2F8%2BlNdtlStHDr3%2FUqeBzp66NOMk%2FtMlfLxTge1lbqYrw5%2F2sZBhJjpnmHN2w1fQrKl7X0tMwkG%2FOxzDZ0FVvG99UEJ95z2zErwHakSoviX8G6hjjkFm7yE5qVy%2F0Vwxa5IWNC3arongv417lyEyeZ3Wyw4bYJ%2Belwq3O%2Fdc1LjYlRNwqpCRmrhlZ4BZIcnYxNchVyClF2FnM2zVlfCyS89f4gCLrDKDWOcliOQwRub66T6G9pIOob8UbWfhy8%2BAXCZeztd', NULL, NULL, NULL, NULL),
(68, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-68-2FA33AFD', NULL, NULL, 'duitku', NULL, 'DS3858926QPD3QR0O0DWZKKE', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26KTA8YADAEBKP3H6', NULL, NULL, NULL, NULL),
(69, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-69-26112603043767CC93', NULL, NULL, 'duitku', NULL, 'DS3858926BYO4TAHAB7KAV22', NULL, 'BRI Virtual Account', NULL, NULL, NULL),
(70, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-70-2611260314518EC548', NULL, NULL, 'duitku', NULL, 'DS3858926E3TVDWOFVLW6B2R', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26L8O6C63X40R35PR', NULL, NULL, NULL, NULL),
(71, '2026-11-26', 22, NULL, 1850000.00, 'paid', 'TOKO-71-261126032810D93F64', NULL, NULL, 'duitku', NULL, 'DS3858926IANOSJ5NPUDCG3R', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BC26RBKN8L2WPU00TFO', 'BCA VA', NULL, NULL, NULL),
(72, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-72-261126033902D99009', NULL, NULL, 'duitku', NULL, 'DS3858926HBYQG4Q1I5RZOP8', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26HGLN6K76RXZXLLR', 'BRI VA', NULL, NULL, NULL),
(73, '2026-11-26', 22, NULL, 1850000.00, 'paid', 'TOKO-73-2611260425487C6B32', NULL, NULL, 'duitku', NULL, 'DS38589263JLSC6U2NU5ZDCS', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26VLSBCJPQEWDPA0I', 'BRI VA', NULL, NULL, NULL),
(74, '2026-11-26', 22, NULL, 275000.00, 'paid', 'TOKO-74-261126045200A5B283', NULL, NULL, 'duitku', NULL, 'DS38589268L3XMC7Y0AA7R1O', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26Z54GGX8ZJY3BGPM', 'BRI VA', NULL, NULL, NULL),
(75, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-75-26112604532725E2EE', NULL, NULL, 'duitku', NULL, 'DS3858926AQGOO0XWLYQW1RZ', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=SP26BH1QDO5VP1BQRO7', 'SHOPEEPAY QRIS', NULL, NULL, NULL),
(76, '2026-11-26', 22, NULL, 85000.00, 'paid', 'TOKO-76-2611260505324B0883', NULL, NULL, 'duitku', NULL, 'DS3858926DRRVY3F1BL5HDLO', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=BR26MDWT5OCNRJ2KLHR', 'BRI VA', '1308200670948939', '', ''),
(77, '2026-11-26', 22, NULL, 150000.00, 'pending', 'TOKO-77-A47B5719', NULL, NULL, 'duitku', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, '2026-11-26', 22, NULL, 95000.00, 'paid', 'TOKO-78-2611260525211E6985', NULL, NULL, 'duitku', NULL, 'DS3858926TS4E71M5EX1GSS4', 'https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=I126LEVLFEMOJGWN3G0', 'BNI VA', '9884547017175392', '', '');

--
-- Trigger `tbl_transaksi`
--
DELIMITER $$
CREATE TRIGGER `before_insert_transaksi` BEFORE INSERT ON `tbl_transaksi` FOR EACH ROW BEGIN
    IF NEW.total_belanja < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
        'Total belanja tidak boleh negatif';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','pelanggan','pembeli') NOT NULL DEFAULT 'pelanggan',
  `rfid_uid` varchar(128) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `nama`, `username`, `password`, `role`, `rfid_uid`, `id_pelanggan`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$/F7OOdA66w.RCQ13VRAWiu1Q9ms8zry8PwPGMUIOMsx66I3PqfvKi', 'admin', '0008493873', NULL, '2026-11-11 01:35:28'),
(2, 'Kasir Toko', 'kasir', '$2y$10$xPG1AXnVS6siqtzHnOipfummRFIKyxiPSu67uTGMNCCQmVqyLeCQW', 'kasir', '0008305215', NULL, '2026-11-11 01:41:15'),
(3, 'Pelanggan Demo', 'pelanggan', '$2y$10$7siV.5qVT8.3Cj3YQQNqZeCG15ctYsS5CO94lWwdOgZ/TUDx.4qDe', 'pelanggan', '0008442193', 22, '2026-11-11 01:41:15'),
(11, 'davin', 'davin', '$2y$10$iaFBr5ZkkjktAp0/jrXjM.60SrsoFUA85K8Nm3FA9VspeYoTk9Bme', 'pelanggan', NULL, 23, '2026-11-11 03:38:17'),
(13, 'kasir 2', 'davinyou', '$2y$10$qYFqhK.Dpph8DkwpuycypOuCXr905faWatl5m9oN7xohB4pJ.2nOG', 'kasir', NULL, NULL, '2026-11-25 03:21:45'),
(14, 'kasir3', 'anggit', '$2y$10$wmGwC34FvlaTKgAplo0VWuSDqXXo7o1P23428pofhWPV7S0mu29VK', 'kasir', NULL, NULL, '2026-11-25 03:35:52');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_pelanggan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_pelanggan` (
`id_pelanggan` int(11)
,`nama_pelanggan` varchar(100)
,`kota` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_total_belanja`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_total_belanja` (
`id_pelanggan` int(11)
,`nama_pelanggan` varchar(100)
,`total_transaksi` bigint(21)
,`total_belanja` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_transaksi_pelanggan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_transaksi_pelanggan` (
`id_transaksi` int(11)
,`tanggal` date
,`nama_pelanggan` varchar(100)
,`kota` varchar(50)
,`total_belanja` decimal(12,2)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `view_pelanggan`
--
DROP TABLE IF EXISTS `view_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pelanggan`  AS SELECT `tbl_pelanggan`.`id_pelanggan` AS `id_pelanggan`, `tbl_pelanggan`.`nama_pelanggan` AS `nama_pelanggan`, `tbl_pelanggan`.`kota` AS `kota` FROM `tbl_pelanggan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_total_belanja`
--
DROP TABLE IF EXISTS `view_total_belanja`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_total_belanja`  AS SELECT `p`.`id_pelanggan` AS `id_pelanggan`, `p`.`nama_pelanggan` AS `nama_pelanggan`, count(`t`.`id_transaksi`) AS `total_transaksi`, coalesce(sum(`t`.`total_belanja`),0) AS `total_belanja` FROM (`tbl_pelanggan` `p` left join `tbl_transaksi` `t` on(`p`.`id_pelanggan` = `t`.`id_pelanggan` and `t`.`payment_status` = 'paid')) GROUP BY `p`.`id_pelanggan`, `p`.`nama_pelanggan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_transaksi_pelanggan`
--
DROP TABLE IF EXISTS `view_transaksi_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_transaksi_pelanggan`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`tanggal` AS `tanggal`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `p`.`kota` AS `kota`, `t`.`total_belanja` AS `total_belanja` FROM (`tbl_transaksi` `t` join `tbl_pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) WHERE `t`.`payment_status` = 'paid' ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `log_pelanggan`
--
ALTER TABLE `log_pelanggan`
  ADD PRIMARY KEY (`id_log`);

--
-- Indeks untuk tabel `log_perubahan_pelanggan`
--
ALTER TABLE `log_perubahan_pelanggan`
  ADD PRIMARY KEY (`id_log`);

--
-- Indeks untuk tabel `tbl_cabang`
--
ALTER TABLE `tbl_cabang`
  ADD PRIMARY KEY (`id_cabang`);

--
-- Indeks untuk tabel `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `tbl_produk`
--
ALTER TABLE `tbl_produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`);

--
-- Indeks untuk tabel `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indeks untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `log_pelanggan`
--
ALTER TABLE `log_pelanggan`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `log_perubahan_pelanggan`
--
ALTER TABLE `log_perubahan_pelanggan`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `tbl_cabang`
--
ALTER TABLE `tbl_cabang`
  MODIFY `id_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT untuk tabel `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `tbl_produk`
--
ALTER TABLE `tbl_produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD CONSTRAINT `tbl_detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`);

--
-- Ketidakleluasaan untuk tabel `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD CONSTRAINT `tbl_transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
