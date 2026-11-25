# 🛒 TOKOKU — Sistem Informasi Penjualan Toko

<p align="center">

**Sistem Informasi Penjualan Berbasis Web dengan PHP Native, MySQL/MariaDB, dan Bootstrap 5**

Mengelola operasional toko dalam satu aplikasi terintegrasi untuk Admin, Kasir, dan Pelanggan.

</p>

---

## 📌 Tentang Tokoku

**Tokoku** merupakan aplikasi sistem informasi penjualan toko berbasis web yang dirancang untuk membantu pengelolaan operasional toko secara terstruktur, efisien, dan terintegrasi.

Aplikasi ini dikembangkan menggunakan PHP Native, MySQL/MariaDB, dan Bootstrap 5 dengan pemisahan akses berdasarkan peran pengguna, yaitu Admin, Kasir, dan Pelanggan.

Tokoku menyediakan fitur pengelolaan data produk, stok barang, pelanggan, transaksi penjualan, pembayaran QRIS dengan verifikasi manual, laporan penjualan, pencetakan struk, hingga pengelolaan aktivitas pelanggan.

Aplikasi ini dirancang dengan antarmuka modern, responsif, dan mudah digunakan sehingga dapat dijadikan sebagai aplikasi operasional toko, media pembelajaran pemrograman web, maupun proyek pengembangan sistem informasi berbasis PHP dan MySQL.

---

## ✨ Fitur Unggulan

- 🔐 **Multi-Role Login** — Sistem autentikasi untuk Admin, Kasir, dan Pelanggan.
- 📊 **Dashboard Statistik** — Menampilkan ringkasan aktivitas dan penjualan.
- 📦 **Manajemen Produk** — Pengelolaan katalog, kategori, harga, stok, dan foto produk.
- 🏷️ **Barcode Produk** — Pembuatan dan pencetakan barcode CODE128.
- 🛍️ **Point of Sale (POS)** — Sistem transaksi kasir dengan pengelolaan keranjang.
- 🧾 **Cetak Struk** — Menampilkan dan mencetak bukti transaksi.
- 💳 **Pembayaran QRIS** — Pembayaran pelanggan dengan unggah bukti dan verifikasi manual.
- 👥 **Manajemen Pelanggan** — Pengelolaan data pelanggan dan profil.
- 👨‍💼 **Manajemen Kasir** — Pembuatan dan pengelolaan akun kasir.
- 📈 **Laporan Penjualan** — Laporan berdasarkan periode tanggal dan akun kasir.
- 🏬 **Manajemen Cabang** — Pengelolaan data cabang toko.
- 📝 **Log Aktivitas** — Pencatatan perubahan data pelanggan.
- 🛒 **Keranjang Belanja** — Pengelolaan produk sebelum checkout pelanggan.
- 📱 **Responsive UI/UX** — Antarmuka responsif menggunakan Bootstrap 5.
- 📡 **RFID Login (Opsional)** — Dukungan autentikasi melalui UID kartu RFID yang dikirim sebagai input keyboard.

---

## 🧰 Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| PHP Native | Backend dan logika aplikasi |
| MySQL | Database relasional |
| MariaDB | Database server pada XAMPP |
| HTML5 | Struktur halaman web |
| CSS3 | Styling antarmuka |
| Bootstrap 5 | Framework UI responsif |
| JavaScript | Interaksi dan fungsionalitas frontend |
| Bootstrap Icons | Ikon antarmuka |
| Chart.js | Visualisasi data dan grafik |
| Google Fonts | Tipografi antarmuka |
| mysqli | Koneksi dan interaksi database |
| CODE128 | Format barcode produk |
| QRIS | Metode pembayaran pelanggan |
| phpMyAdmin | Pengelolaan database |

---

## ⚙️ Teknologi dan Kebutuhan Sistem

### Perangkat Lunak

- Windows 10/11 atau sistem operasi setara.
- XAMPP.
- Apache Web Server.
- PHP versi 8.1 atau lebih baru.
- MySQL/MariaDB.
- phpMyAdmin.
- Visual Studio Code atau editor lainnya.
- Browser modern seperti Google Chrome, Microsoft Edge, atau Firefox.

### Ekstensi PHP

Pastikan ekstensi berikut aktif:

- `mysqli`
- `fileinfo`
- `mbstring`

### Koneksi Internet

Internet diperlukan untuk memuat beberapa library dan aset eksternal melalui CDN, seperti:

- Bootstrap 5.
- Bootstrap Icons.
- Chart.js.
- Google Fonts.

---

## 🚀 Instalasi dan Konfigurasi

### 1. Clone Repository

Jalankan perintah berikut pada Command Prompt atau terminal:

```bash
git clone https://github.com/11davinyuhantama-bot/tokoku.git
```

### 2. Pindahkan ke Folder XAMPP

Pastikan folder project berada di:

```text
C:\xampp\htdocs\tokoku_php_bootstrap
```

Jika nama folder hasil clone berbeda, sesuaikan nama folder agar sesuai dengan URL yang akan digunakan.

### 3. Jalankan XAMPP

Buka XAMPP Control Panel, kemudian aktifkan:

- Apache
- MySQL

Pastikan kedua layanan berjalan dengan status aktif.

### 4. Buat Database

Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Buat database baru dengan nama:

```sql
db_tokoko
```

### 5. Import Database Utama

1. Pilih database `db_tokoko`.
2. Klik menu **Import**.
3. Pilih file `db_tokoku.sql`.
4. Klik tombol **Import** atau **Go**.
5. Tunggu hingga proses selesai.

### 6. Import File Setup Tambahan

Setelah dump database utama berhasil diimpor, lakukan import file setup tambahan secara berurutan.

| File | Fungsi |
|---|---|
| `setup_login.sql` | Menyiapkan tabel akun, role, dan akun demo |
| `setup_produk.sql` | Menyiapkan struktur katalog produk |
| `setup_pembayaran.sql` | Menyiapkan skema pembayaran, relasi produk transaksi, dan ID kasir |
| `setup_rfid.sql` | Menyiapkan konfigurasi login RFID (opsional) |

<blockquote>

**Penting:** Semua file setup harus diimpor ke database `db_tokoko`. Pastikan dump utama telah berhasil diimpor sebelum menjalankan file setup tambahan.

</blockquote>

### 7. Konfigurasi Koneksi Database

Buka file:

```text
config/database.php
```

Sesuaikan konfigurasi koneksi database:

```php
host     = 127.0.0.1
username = root
password = 
database = db_tokoko
port     = 3306
```

Konfigurasi tersebut merupakan konfigurasi umum XAMPP lokal. Sesuaikan dengan pengaturan MySQL/MariaDB yang digunakan.

### 8. Jalankan Aplikasi

Buka browser dan akses:

```text
http://localhost/tokoku_php_bootstrap/
```

Aplikasi Tokoku siap digunakan setelah database, konfigurasi koneksi, dan layanan Apache/MySQL berjalan dengan benar.

---

## 👤 Akun Demo

Berikut akun demo yang tersedia setelah setup login berhasil dijalankan.

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Kasir | `kasir` | `kasir123` |
| Pelanggan | `pelanggan` | `pelanggan123` |

<blockquote>

Akun demo digunakan untuk pengujian aplikasi lokal. Untuk penggunaan nyata, ganti kredensial demo dengan akun baru dan gunakan kata sandi yang aman.

</blockquote>

---

## 🔐 Sistem Hak Akses Pengguna

Tokoku menerapkan sistem pembagian hak akses berdasarkan peran pengguna.

### 👑 Admin

Admin merupakan pengguna yang memiliki akses ke fitur pengelolaan sistem toko.

Fitur Admin:

- Dashboard statistik penjualan.
- Melihat ringkasan transaksi dan aktivitas toko.
- Mengelola data pelanggan.
- Mengelola data cabang.
- Mengelola katalog produk.
- Menambah, mengubah, dan menghapus produk.
- Mengelola kategori produk.
- Mengelola stok barang.
- Mengunggah foto produk.
- Membuat dan mencetak barcode produk.
- Mengelola akun kasir.
- Melihat transaksi seluruh toko.
- Melihat detail transaksi.
- Melihat dan mencetak struk.
- Memverifikasi pembayaran QRIS.
- Menyetujui pembayaran.
- Menolak pesanan.
- Mengelola laporan penjualan.
- Memfilter laporan berdasarkan kasir.
- Memfilter laporan berdasarkan periode tanggal.
- Melihat log aktivitas perubahan data pelanggan.

### 🧑‍💼 Kasir

Kasir bertugas melakukan transaksi penjualan secara langsung melalui sistem POS.

Fitur Kasir:

- Dashboard kasir.
- Ringkasan transaksi penjualan.
- Melihat katalog produk.
- Melihat informasi stok.
- Memilih pelanggan.
- Menambahkan produk ke keranjang transaksi.
- Mengubah jumlah produk.
- Menghapus produk dari keranjang.
- Menghitung total transaksi.
- Menyelesaikan checkout.
- Menyimpan transaksi penjualan.
- Melihat riwayat transaksi pribadi.
- Melihat detail transaksi.
- Mencetak struk.
- Melihat laporan penjualan milik akun kasir sendiri.

### 🛍️ Pelanggan

Pelanggan dapat melakukan pembelian produk melalui website.

Fitur Pelanggan:

- Registrasi akun pelanggan.
- Login pelanggan.
- Melihat katalog produk.
- Mencari produk.
- Melihat informasi produk.
- Mengelola keranjang belanja.
- Menambah dan mengurangi jumlah produk.
- Membuat pesanan.
- Melakukan pembayaran QRIS.
- Mengunggah bukti pembayaran.
- Melihat status pembayaran.
- Melihat detail pesanan.
- Melihat riwayat pembelian.

---

## 🛒 Alur Transaksi Penjualan

### A. Transaksi melalui Kasir

1. Kasir login ke sistem.
2. Kasir membuka menu POS.
3. Kasir memilih pelanggan.
4. Kasir menambahkan produk ke keranjang.
5. Kasir mengatur jumlah barang.
6. Sistem menghitung subtotal setiap produk.
7. Sistem menghitung total transaksi.
8. Kasir melakukan checkout.
9. Sistem menyimpan data transaksi.
10. Sistem menyimpan detail barang yang dibeli.
11. Sistem mengurangi stok produk.
12. Sistem mencatat ID kasir yang melakukan transaksi.
13. Sistem menampilkan struk transaksi.

### B. Transaksi Online melalui Pelanggan

1. Pelanggan login ke aplikasi.
2. Pelanggan membuka katalog produk.
3. Pelanggan memilih produk.
4. Pelanggan menambahkan produk ke keranjang.
5. Pelanggan melakukan checkout.
6. Sistem membuat pesanan.
7. Sistem menyimpan detail produk pesanan.
8. Sistem mengurangi stok produk.
9. Sistem menampilkan informasi pembayaran QRIS.
10. Pelanggan melakukan pembayaran.
11. Pelanggan mengunggah bukti pembayaran.
12. Admin memeriksa bukti pembayaran dan dana yang diterima.
13. Admin menyetujui atau menolak pembayaran.
14. Sistem memperbarui status pesanan sesuai hasil verifikasi.

---

## 💳 Sistem Pembayaran QRIS

Tokoku menggunakan metode pembayaran QRIS statis dengan proses verifikasi manual oleh admin.

### Proses Pembayaran

- Pelanggan melakukan checkout.
- Sistem membuat pesanan dengan status menunggu pembayaran.
- Pelanggan melakukan pembayaran melalui QRIS merchant.
- Pelanggan mengunggah bukti pembayaran.
- Admin memeriksa bukti dan transaksi masuk.
- Admin melakukan konfirmasi pembayaran.

### Status Pembayaran

| Status | Keterangan |
|---|---|
| Menunggu Pembayaran | Pesanan menunggu pembayaran pelanggan |
| Menunggu Verifikasi | Bukti pembayaran telah diunggah |
| Lunas | Pembayaran telah dikonfirmasi admin |
| Ditolak | Bukti pembayaran tidak dapat diterima |
| Dibatalkan | Pesanan dibatalkan sesuai mekanisme aplikasi |

### Verifikasi Admin

Admin memiliki kewenangan untuk:

- Memeriksa bukti pembayaran.
- Mencocokkan pembayaran dengan transaksi masuk.
- Mengonfirmasi pembayaran.
- Menolak bukti pembayaran.
- Membatalkan pesanan sesuai mekanisme aplikasi.

<blockquote>

**Catatan:** QRIS statis tidak memberikan konfirmasi pembayaran otomatis kepada aplikasi. Verifikasi harus dilakukan dengan memastikan dana benar-benar diterima oleh merchant.

</blockquote>

---

## 📊 Dashboard Sistem

Tokoku menyediakan dashboard yang menampilkan informasi ringkas aktivitas penjualan sesuai peran pengguna.

### Dashboard Admin

- Ringkasan penjualan.
- Informasi transaksi.
- Data pelanggan.
- Data produk.
- Statistik aktivitas toko.
- Grafik penjualan.

### Dashboard Kasir

- Ringkasan transaksi kasir.
- Informasi produk.
- Statistik penjualan kasir.
- Aktivitas transaksi.

Dashboard menampilkan informasi sesuai akses dan data yang tersedia pada sistem.

---

## 📈 Laporan Penjualan

Tokoku menyediakan laporan penjualan untuk membantu pengelolaan dan evaluasi aktivitas toko.

### Laporan Admin

Admin dapat:

- Melihat laporan penjualan seluruh toko.
- Melihat transaksi yang telah lunas.
- Memfilter laporan berdasarkan tanggal.
- Memfilter laporan berdasarkan akun kasir.
- Melihat rincian transaksi.
- Memeriksa data transaksi penjualan.

### Laporan Kasir

Kasir dapat:

- Melihat laporan transaksi milik akun sendiri.
- Melihat transaksi berdasarkan periode.
- Melihat rincian transaksi.
- Melihat riwayat penjualan.
- Mencetak struk transaksi.

### Pembatasan Data Laporan

Laporan kasir dibatasi berdasarkan ID akun kasir yang sedang login.

Transaksi kasir yang dibuat setelah kolom `id_kasir` tersedia akan tercatat berdasarkan kasir pembuatnya.

Transaksi lama dan transaksi online pelanggan yang tidak memiliki ID kasir tetap dapat ditampilkan pada laporan lengkap admin, tetapi tidak dimasukkan ke laporan per kasir.

---

## 🏷️ Manajemen Produk dan Barcode

Fitur pengelolaan produk tersedia untuk admin.

### Pengelolaan Produk

- Menambah produk.
- Mengubah data produk.
- Menghapus produk.
- Mengatur harga.
- Mengatur stok.
- Mengelola kategori.
- Mengunggah foto produk.
- Melakukan pencarian produk.

### Barcode Produk

Tokoku menyediakan pembuatan barcode menggunakan format CODE128.

Barcode dapat digunakan sebagai identitas produk untuk membantu proses pengelolaan katalog dan transaksi.

Fitur barcode mendukung:

- Pembuatan barcode produk.
- Identifikasi produk.
- Tampilan barcode.
- Pencetakan barcode.

---

## 📸 Upload Gambar Produk

Gambar produk disimpan pada direktori:

```text
assets/img/produk/
```

### Ketentuan Upload

| Ketentuan | Keterangan |
|---|---|
| Format | JPG, PNG, WEBP |
| Maksimal ukuran | 2 MB |
| Direktori | `assets/img/produk/` |
| Hak akses | Direktori harus dapat ditulis oleh PHP |

Jika proses upload gagal, periksa konfigurasi PHP:

```ini
file_uploads = On
upload_max_filesize = 2M
post_max_size = 2M
```

Pastikan Apache/PHP dapat menulis ke direktori gambar produk.

---

## 🏬 Manajemen Cabang

Tokoku menyediakan fitur pengelolaan data cabang toko.

Fitur ini digunakan untuk menyimpan dan mengelola informasi cabang yang tersedia dalam sistem.

Pengelolaan cabang dapat mencakup:

- Penambahan data cabang.
- Pengubahan data cabang.
- Penghapusan data cabang.
- Pengelolaan informasi cabang.

---

## 📝 Log Aktivitas Pelanggan

Sistem menyediakan pencatatan aktivitas perubahan data pelanggan untuk membantu pengelolaan dan pemeriksaan data.

Log aktivitas digunakan untuk mendukung pemantauan perubahan informasi pelanggan yang dicatat oleh aplikasi.

Data log dapat digunakan sebagai bagian dari dokumentasi aktivitas sistem.

---

## 📡 Login RFID (Opsional)

Tokoku mendukung fitur login RFID apabila setup RFID telah diaktifkan.

### Persyaratan

- Reader RFID.
- Kartu RFID.
- Konfigurasi sistem RFID.
- Input UID dari perangkat pembaca.

Pembaca RFID harus dapat mengirim UID kartu sebagai input keyboard yang dapat diterima aplikasi.

### UID Demo

| Role | UID Demo |
|---|---|
| Admin | `0008493873` |
| Kasir | `0008305215` |
| Pelanggan | `0008442193` |

UID tersebut merupakan data demo untuk pengujian konfigurasi lokal.

Ganti atau hapus UID demo sebelum penggunaan di lingkungan nyata.

---

## 📁 Struktur Proyek

```text
tokoku_php_bootstrap/
│
├── config/
│   ├── database.php
│   ├── helper.php
│   └── ...
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar_admin.php
│   ├── sidebar_kasir.php
│   └── ...
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│       ├── produk/
│       └── qris_merchant.jpg
│
├── pelanggan/
│   ├── katalog/
│   ├── keranjang/
│   ├── pembayaran/
│   ├── riwayat/
│   └── ...
│
├── kasir/
│   ├── dashboard/
│   ├── transaksi/
│   ├── laporan/
│   ├── riwayat/
│   └── ...
│
├── produk/
│   ├── tambah.php
│   ├── edit.php
│   ├── index.php
│   └── ...
│
├── transaksi/
│   ├── detail.php
│   ├── struk.php
│   ├── verifikasi/
│   └── ...
│
├── users/
│   ├── index.php
│   ├── tambah.php
│   └── ...
│
├── laporan/
│   └── ...
│
├── cabang/
│   └── ...
│
├── aktivitas/
│   └── ...
│
├── db_tokoku.sql
├── setup_login.sql
├── setup_produk.sql
├── setup_pembayaran.sql
├── setup_rfid.sql
├── README.md
└── ...
```

Struktur di atas merupakan gambaran direktori utama. Nama dan isi file dapat berbeda sesuai versi pengembangan project.

---

## 🗄️ Struktur Database

Database utama Tokoku menggunakan nama:

```text
db_tokoko
```

### Tabel Utama

| Nama Tabel | Fungsi |
|---|---|
| `tbl_user` | Menyimpan akun pengguna dan peran |
| `tbl_pelanggan` | Menyimpan profil pelanggan |
| `tbl_transaksi` | Menyimpan data transaksi penjualan |
| `tbl_detail_transaksi` | Menyimpan rincian barang dalam transaksi |
| `tbl_produk` | Menyimpan katalog produk dan stok |
| `tbl_cabang` | Menyimpan informasi cabang |
| `log_pelanggan` | Menyimpan log pelanggan |
| `log_perubahan_pelanggan` | Menyimpan log perubahan data pelanggan |

### Relasi Database

Secara umum, sistem menggunakan relasi antara:

- Pengguna dengan profil pelanggan.
- Pelanggan dengan transaksi.
- Transaksi dengan detail transaksi.
- Detail transaksi dengan produk.
- Kasir dengan transaksi yang dibuat.

Database juga menggunakan objek database tambahan sesuai dump dan file setup, seperti:

- Stored Procedure.
- Function.
- Trigger.
- View.

Struktur database dapat berkembang sesuai kebutuhan sistem.

---

## 🧩 Objek Database Tambahan

Dump database Tokoku mendukung penggunaan objek database tambahan untuk membantu pengelolaan proses bisnis.

### Stored Procedure

Digunakan untuk menjalankan kumpulan perintah SQL dalam satu prosedur yang dapat dipanggil kembali.

### Function

Digunakan untuk menghasilkan nilai berdasarkan proses atau perhitungan tertentu di dalam database.

### Trigger

Digunakan untuk menjalankan proses otomatis ketika terjadi event tertentu pada tabel.

### View

Digunakan untuk menyediakan tampilan data berdasarkan query SQL yang telah didefinisikan.

---

## 🔄 Pengelolaan Stok

Tokoku mengintegrasikan stok produk dengan proses transaksi.

### Transaksi Kasir

Ketika checkout berhasil:

- Data transaksi disimpan.
- Detail transaksi disimpan.
- Stok produk diperbarui.

### Pesanan Pelanggan

Ketika checkout pelanggan:

- Pesanan dibuat.
- Detail pesanan disimpan.
- Stok diperbarui sesuai mekanisme aplikasi.

Ketika pesanan ditolak dan dibatalkan melalui proses verifikasi:

- Status pesanan diperbarui.
- Stok dikembalikan sesuai mekanisme pembatalan aplikasi.

---

## 🛡️ Keamanan dan Catatan Penggunaan

Untuk penggunaan nyata, perhatikan hal berikut:

- Ganti akun demo.
- Gunakan password yang kuat.
- Pastikan validasi input diterapkan.
- Gunakan prepared statements untuk query database.
- Validasi file upload.
- Batasi akses berdasarkan role.
- Pastikan transaksi hanya dapat diakses oleh pengguna yang berhak.
- Hindari menampilkan error database kepada pengguna.
- Backup database secara berkala.
- Jangan menyimpan kredensial sensitif pada repository publik.
- Gunakan HTTPS pada deployment yang mendukung akses jaringan.
- Pastikan verifikasi pembayaran dilakukan sebelum status lunas ditetapkan.

---

## ⚠️ Troubleshooting

### 1. Apache atau MySQL tidak berjalan

Periksa XAMPP Control Panel dan pastikan Apache serta MySQL dapat dijalankan.

Jika port bermasalah, periksa konfigurasi port layanan yang digunakan.

### 2. Koneksi Database Gagal

Periksa:

- Nama database.
- Username.
- Password.
- Host.
- Port MySQL.
- Status MySQL/MariaDB.

### 3. Tabel Tidak Ditemukan

Pastikan dump utama dan seluruh file setup yang dibutuhkan telah diimpor.

Periksa apakah nama tabel yang digunakan aplikasi sesuai dengan nama tabel di database.

### 4. Upload Foto Gagal

Periksa:

- `file_uploads`.
- `upload_max_filesize`.
- `post_max_size`.
- Hak akses direktori upload.
- Format file yang diizinkan.

### 5. Error DEFINER saat Import SQL

Jika import gagal karena `DEFINER`, sesuaikan definisi akun pada dump dengan akun MariaDB lokal atau hapus bagian `DEFINER` apabila sesuai dengan kebutuhan konfigurasi.

### 6. Login Gagal

Pastikan:

- Tabel akun telah tersedia.
- Data akun demo berhasil dimasukkan.
- Username sesuai.
- Password sesuai.
- Proses hashing dan verifikasi password sesuai implementasi aplikasi.

---

## 📌 Catatan Penting

- Project ini menggunakan PHP Native dan tidak membutuhkan Composer.
- Tidak membutuhkan proses build frontend.
- Bootstrap dan beberapa library frontend menggunakan CDN.
- QRIS yang digunakan bersifat statis.
- Verifikasi pembayaran dilakukan secara manual.
- RFID bersifat opsional.
- Database harus disiapkan sebelum aplikasi dijalankan.
- File setup tambahan harus diimpor ke database yang sama.
- Akun demo hanya untuk pengujian.
- Data transaksi dan stok harus diperiksa secara berkala.

---

## 🎯 Tujuan Pengembangan

Tokoku dikembangkan sebagai sistem informasi penjualan yang mengintegrasikan pengelolaan produk, transaksi, pelanggan, pembayaran, dan laporan dalam satu aplikasi berbasis web.

Project ini juga dapat digunakan sebagai media pembelajaran dan pengembangan kompetensi dalam:

- Pemrograman PHP Native.
- Pengembangan aplikasi web.
- Database MySQL/MariaDB.
- CRUD.
- Sistem login dan autentikasi.
- Multi-role authorization.
- Relasi database.
- Stored Procedure.
- Function.
- Trigger.
- View.
- Sistem POS.
- Pengelolaan stok.
- UI/UX.
- Integrasi frontend dan backend.

---

## 👨‍💻 Pengembang

**Tokoku — Sistem Informasi Penjualan Toko**

Dikembangkan menggunakan PHP Native, MySQL/MariaDB, dan Bootstrap 5.

<sub>Project pengembangan aplikasi sistem informasi penjualan berbasis web.</sub>

---

## 📜 Lisensi

Ketentuan lisensi mengikuti lisensi yang ditetapkan oleh pemilik repository.

Jika repository belum memiliki file lisensi, penggunaan, modifikasi, dan distribusi kode perlu mengikuti ketentuan yang ditetapkan oleh pemilik project.

---

<p align="center">

**🛒 TOKOKU — Solusi Digital Pengelolaan Penjualan Toko**

</p>
"# toko_davin" 
