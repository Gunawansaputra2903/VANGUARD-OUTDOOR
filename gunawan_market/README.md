# Vanguard Outdoor

Marketplace perlengkapan outdoor berbasis PHP Native, MySQLi, Bootstrap, dan MySQL.

## Fitur
- Katalog produk outdoor dan pencarian produk.
- Detail produk, keranjang, dan checkout untuk pembeli yang sudah login.
- Satu halaman Masuk untuk admin dan pembeli; sistem mengalihkan berdasarkan role akun.
- Pendaftaran akun pembeli (role pembeli ditetapkan oleh sistem).
- Pesanan Saya dan pelacakan status pesanan.
- Dashboard admin dengan navigasi sidebar, kelola produk, pesanan masuk, dan data pembeli.
- Unggah gambar produk untuk admin.

## Instalasi XAMPP
1. Salin folder `gunawan_market` ke `C:\xampp\htdocs\`. Nama folder dan nama database `gunawan_market` tetap dipertahankan agar URL dan konfigurasi lama tidak rusak.
2. Jalankan Apache dan MySQL.
3. Gunakan database lama yang sudah ada. Jangan impor ulang `database.sql` ke database yang sudah berisi data.
4. Buka `http://localhost/gunawan_market/`.
5. Pembeli membuat akun melalui tombol Daftar. Admin masuk melalui tombol Masuk dengan akun ber-role `admin`.

## Data katalog awal
Untuk instalasi database baru, `database.sql` berisi kategori dan produk contoh bertema outdoor. Untuk database lama dari proyek sebelumnya, file `migrasi_vanguard_outdoor.sql` bersifat opsional dan hanya mengganti nama kategori serta produk contoh lama yang masih memiliki nama bawaan. Jalankan hanya bila data contoh bawaan tersebut masih ada dan memang ingin diubah. File migrasi tidak menghapus akun atau transaksi.

## Akun admin
Gunakan akun admin yang sudah dibuat sebelumnya melalui `buat_admin.php`. Form pendaftaran publik hanya membuat akun pembeli dan tidak menyediakan pilihan role admin. Setelah akun admin tersedia, hapus `buat_admin.php` dari server.

## Catatan
- Login admin dan pembeli menggunakan halaman yang sama: `login.php`.
- Akun admin diarahkan ke `admin/dashboard.php`; akun pembeli diarahkan ke toko atau Pesanan Saya.
- Kata sandi disimpan dengan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Jangan mengunggah password, kredensial hosting, atau file konfigurasi rahasia ke repositori publik.


## Logo
Logo resmi Vanguard Outdoor disimpan pada `assets/images/vanguard-logo.jpeg` dan digunakan pada navbar toko, halaman masuk/daftar, serta panel admin. Untuk mengganti logo di kemudian hari, ganti file tersebut dengan nama file yang sama.


## Pembaruan foto katalog
- Foto kategori memakai aset lokal pada `assets/images/products/`.
- Foto produk contoh dipilih berdasarkan nama produk melalui `includes/product_image.php`, sehingga foto tas, botol minum, jaket, headlamp, sepatu, dan tenda tidak tertukar.
- Produk lain tetap menggunakan foto yang diunggah admin jika file-nya tersedia.
- Foto produk yang dipakai juga sama pada halaman detail produk.
