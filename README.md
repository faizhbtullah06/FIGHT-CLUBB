# Fight Club Ticketing System

Website digitalisasi penjualan tiket event Fight Club. PHP native (tanpa framework) + MySQL, dibuat berdasarkan dokumen Analisis Proses Bisnis & Elisitasi.

## Fitur

**Penonton**
- Register & Login
- Melihat Jadwal Pertandingan (event aktif)
- Order Tiket (pilih kategori Reguler/VIP/VVIP) + Pembayaran via QR Code
- Konfirmasi "Sudah Bayar" → generate QR Code tiket & nomor tiket unik otomatis
- Halaman "My Tiket"

**Admin**
- Login admin
- Dashboard ringkasan (jumlah event, tiket terjual, pendapatan)
- Kelola Jadwal Event: buat/edit event + atur kategori tiket & kuota (Reguler/VIP/VVIP)
- Nonaktifkan/aktifkan penjualan tiket suatu event
- Lihat daftar transaksi & rekapitulasi laporan penjualan (bisa difilter per event)

## Cara Install (XAMPP)

1. Copy folder `fightclub_ticketing` ke dalam `htdocs` XAMPP lu, misal:
   `D:/xampp/htdocs/fightclub_ticketing/`

2. Buka **phpMyAdmin**, lalu import file `database.sql` (ini akan otomatis membuat
   database `fightclub_ticketing`, semua tabel, akun admin default, dan 1 contoh event).
   Atau lewat terminal:
   ```
   mysql -u root -p < database.sql
   ```

3. Cek konfigurasi koneksi database di `config/db.php` (default sudah cocok untuk
   XAMPP standar: host `localhost`, user `root`, password kosong).

4. Jalankan Apache & MySQL dari XAMPP Control Panel.

5. Akses di browser:
   ```
   http://localhost/fightclub_ticketing/
   ```

## Akun Default

**Admin**
- Email: `admin@fightclub.com`
- Password: `admin123`

**Penonton**
- Daftar akun baru lewat halaman Register.

## Struktur Folder

```
fightclub_ticketing/
├── database.sql              # Schema + seed data
├── config/db.php             # Koneksi database & session
├── includes/
│   ├── auth.php              # Helper login/role check
│   ├── functions.php         # Helper format, QR code, generate kode unik
│   ├── header.php / footer.php
├── assets/css/style.css      # Tema dark + red (combat sport)
├── index.php                 # Landing page
├── register.php / login.php / logout.php
├── jadwal.php                # List event aktif (Penonton)
├── event_detail.php          # Detail event + form order tiket
├── order.php                 # Proses bikin order (status pending)
├── payment.php               # Tampilkan QR pembayaran
├── konfirmasi_bayar.php      # Proses tombol "Sudah Bayar" -> generate tiket
├── my_tiket.php              # Daftar tiket milik penonton
└── admin/
    ├── sidebar.php
    ├── dashboard.php
    ├── event_list.php / event_form.php / event_toggle.php
    └── transaksi.php         # Daftar transaksi & rekap laporan
```

## Catatan Teknis

- QR Code (baik QR pembayaran maupun QR tiket) digenerate lewat layanan gratis
  `api.qrserver.com` — butuh koneksi internet saat halaman dibuka di browser
  (tidak perlu install library QR apapun di server).
- Pembayaran disimulasikan: begitu penonton menekan tombol "Sudah Bayar",
  sistem langsung mengonfirmasi transaksi dan generate tiket (sesuai
  requirement Elisitasi Final). Untuk produksi nyata, bagian ini bisa
  diganti dengan integrasi payment gateway sungguhan (Midtrans/Xendit, dst).
- Semua query database pakai prepared statement (mysqli) untuk mencegah SQL Injection.
- Password disimpan dengan `password_hash()` (bcrypt).
