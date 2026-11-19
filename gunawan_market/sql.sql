-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Nov 2026 pada 07.43
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
-- Database: `gunawan_market`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `carts`
--

CREATE TABLE `carts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_details`
--

CREATE TABLE `cart_details` (
  `id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(2, 'Pakaian Outdoor'),
(3, 'Perlengkapan Camping'),
(1, 'Perlengkapan Penerangan'),
(4, 'Tas dan Aksesori');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_address` text NOT NULL,
  `status` enum('menunggu','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_price`, `shipping_address`, `status`, `created_at`) VALUES
(1, 2, 'GM-20261117-573BD275', 75000.00, 'Gg. Sentot Alibasyah X, Bajak, Kec. Tlk. Segara, Kota Bengkulu, Bengkulu 38115', 'dibatalkan', '2026-11-17 08:50:06'),
(4, 4, 'GM-20261117-6C33361D', 150000.00, 'jl.rumah yuda', 'diproses', '2026-11-17 09:27:55'),
(5, 4, 'GM-20261118-45D1DFCF', 89000.00, 'gang sentot', 'menunggu', '2026-11-18 08:54:04'),
(6, 4, 'GM-20261118-B14996F8', 75000.00, 'alamat', 'menunggu', '2026-11-18 09:03:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 2, 'Kaos Polos', 75000.00, 1, 75000.00),
(4, 4, 2, 'Kaos Polos', 75000.00, 2, 150000.00),
(5, 5, 4, 'Daypack Outdoor 20L', 89000.00, 1, 89000.00),
(6, 6, 2, 'Jaket Outdoor Ringan', 75000.00, 1, 75000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `category_id`, `product_name`, `description`, `price`, `stock`, `image`, `created_at`) VALUES
(1, 1, 'Headlamp LED Outdoor', 'Lampu kepala untuk membantu penerangan saat berkemah dan berjalan di area outdoor.', 32000.00, 50, '283a82cebc41b7a238740c5b7df0ff48.jpg', '2026-11-17 07:57:12'),
(2, 2, 'Jaket Outdoor Ringan', 'Jaket ringan untuk kegiatan luar ruang dan perjalanan.', 250000.00, 50, 'e7a66cef2f1b1d9f8f03afe765d1049c.jpg', '2026-11-17 07:57:12'),
(3, 3, 'Botol Minum Camping', 'Botol minum pakai ulang untuk menemani aktivitas outdoor.', 50000.00, 50, '91f22062eb5340a54efbd693cfbcb222.webp', '2026-11-17 07:57:12'),
(4, 4, 'Daypack Outdoor 20L', 'Tas daypack untuk membawa perlengkapan harian dan perjalanan singkat.', 150000.00, 50, '4d83f1b2d520d1ca49a40a8254b4812e.webp', '2026-11-17 07:57:12'),
(5, 4, 'Daypack Outdoor 60L', 'Tas daypack untuk membawa perlengkapan harian dan perjalanan singkat.', 300000.00, 50, 'eca0ee99c6c109c2c0cf1fe9e088c635.jpg', '2026-11-19 06:06:39'),
(6, 2, 'sepatu Outdoor REI', 'sepatu Outdoor REI Sepatu Gunung sepatu tracking gunung sepatu boot gunung', 600000.00, 50, '5794eea9b0dd7627df1517e3cb6ae49c.jpg', '2026-11-19 06:12:22'),
(7, 1, 'Senter LED', 'SENTER LED ZOOM INDIKATOR PLUS COB LF-S56 - MODE POWERBANK - TYPE C', 115000.00, 50, '518e2557e403f38c1373815e717756d0.webp', '2026-11-19 06:17:04'),
(8, 3, 'CucuLemon', 'CucuLemon Termos Stainless Steel Tumbler Tahan Panas Dingin Botol Minum 1 Liter Air Bottles', 105000.00, 50, '1d477bca9fc9ac8b5e8295ddb1021c0f.webp', '2026-11-19 06:23:25'),
(9, 2, 'Merrell Moab 3 GORE-TEX® hiking shoes', 'Masuki dunia legenda, kini lebih baik dari sebelumnya. Merrell Moab 3 GTX tetap memegang gelar sebagai sepatu hiking terlaris di dunia, menghadirkan kenyamanan dan ketahanan yang sudah terkenal sejak pertama kali dipakai. Generasi terbaru ini dilengkapi membran tahan air GORE-TEX® untuk menjaga kaki Anda tetap kering, midsole yang lebih empuk dan berlapis bantalan, serta insole yang lebih menopang untuk kenyamanan sepanjang hari. Cengkeram medan apa pun dengan sol luar Vibram® terbaik di kelasnya.', 2190000.00, 50, '10437481728be2d46669d5411a71cec0.webp', '2026-11-19 06:28:39'),
(10, 2, 'MS LORENTZ XG 3L JACKET', 'MS Lorentz XG 3L Jacket dirancang untuk menghadapi tantangan di gunung. Menggunakan bahan Pertex® Shield yang waterproof dan breathable, serta ventilasi ritsleting di area ketiak, jaket hardshell ini menjaga tubuh tetap kering dan bebas lembap saat hujan maupun salju. Dilengkapi pula saku dada dengan ritsleting water-resistant dan saku dalam untuk penyimpanan yang aman dan mudah dijangkau.', 2610000.00, 50, 'b65c7cd2fce64765b5f2b7049853b456.jpg', '2026-11-19 06:31:52'),
(11, 3, 'Antarestar – Tenda Berkemah 200 x 200', 'Tenda Camping 200 x 200 ANTARESTAR\r\n\r\nUkuran paling besar 200cm x 200cm bisa untuk sampai 4 orang\r\nBahan material polyester yang berkualitas\r\nTahan terhadap udara dan cuaca panas\r\nTidak mudah sobek\r\nMemiliki berat yang ringan sehingga mudah dibawa kemana saja\r\nSudah termasuk pasak 4 pcs dan tas tenda', 400000.00, 50, '12b1cc24283d15eb22741336a5cd33a5.jpg', '2026-11-19 06:34:58'),
(12, 3, 'Tenda Camping ALLTREK AZTECA Tunnel 5P Tiga Lapis Tahan Air', 'ALLTREK Tenda Camping AZTECA Tunnel 5P Triple Layer Waterproof Tent\r\n\r\nAZTECA merupakan tenda triple layer model tunnel yang memiliki ukuran sangat besar! Ukurannya yang mencapai 600 x 330 x 210 cm pada bagian luar, dan 310 x 220 x 175 cm pada bagian dalam. Dilengkapi material luar 150D Oxford PU 2000MM UPF50+, bagian dalam 190T Polyester PU 2000MM, dan lantai 150D Oxford PU 2000MM. Framenya juga terbuat dari Aluminium Alloy yang kokoh. Tenda ini memiliki kapasitas 5 orang pada bagian dalamnya dan jika bagian depan ditambah Footprint, kapasitas dapat diperluas menjadi 10 orang. Tenda ini juga memiliki Extra Storage Pockets, Extra Tools Storage Pockets, Dedicated Power Outlet Slot, serta ventilasi yang dapat dibuka tutup, sehingga sirkulasi udara di tenda tertutup dapat terjaga selama pemakaian!', 10500000.00, 50, '8e4c208b62931720614bf2a594301733.jpg', '2026-11-19 06:39:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pembeli') NOT NULL DEFAULT 'pembeli',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(2, 'jamal', 'jamal123@gmail.com', '$2y$10$zpKQZ5N9Djkx4kOxpmrQAeV5Q3PVdaIi4C.sZSZH3QtXclR7cN/GS', 'pembeli', '2026-11-17 08:50:06'),
(4, 'jumaidah', 'jumaidah@gmail.com', '$2y$10$bzfWO303.9Nx7fDXaDlWX.5r9HGQu26uzHVJUR8tpogw8wjxO5GLu', 'pembeli', '2026-11-17 09:27:11'),
(5, 'admintest', 'admintest@gmail.com', '$2y$12$MxXv8q3Ykrodz6Dv/dgHOO7bFEeRxP7uS.6I8K6.M6yHh2rHTXewi', 'admin', '2026-11-19 12:00:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cart_details`
--
ALTER TABLE `cart_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart_product` (`cart_id`,`product_id`),
  ADD KEY `fk_cart_details_product` (`product_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_details_order` (`order_id`),
  ADD KEY `fk_order_details_product` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cart_details`
--
ALTER TABLE `cart_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cart_details`
--
ALTER TABLE `cart_details`
  ADD CONSTRAINT `fk_cart_details_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_order_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
