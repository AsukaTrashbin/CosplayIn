# CosplayIn

CosplayIn adalah sistem penyewaan kostum cosplay online berbasis **Laravel 13 / PHP 8.3 / MySQL 8**.
Aplikasi mendukung tiga peran pengguna (guest/customer, tenant, admin) dengan alur pemesanan,
pembayaran (mock gateway + webhook bertanda tangan), persetujuan tenant, pengembalian, denda
keterlambatan, ulasan, dan pengaturan platform.


---

## 1. Teknologi

| Komponen | Versi / Pilihan |
| --- | --- |
| PHP | ^8.3 |
| Laravel | ^13.17 |
| Database | MySQL 8 (`cosplayin`) |
| Autentikasi | Breeze-style manual (session, verifikasi email, reset password) |
| Otorisasi | Role middleware + Laravel Policies |
| Pembayaran | Mock gateway dengan signature HMAC-SHA256 |
| Pengujian | PHPUnit 12 (SQLite in-memory) |

---

## 2. Fitur Utama

### Publik (guest)
- Beranda dengan banner statis dan kostum populer.
- Katalog kostum + filter pencarian, kota, kategori, gender, dan ukuran.
- Detail kostum: galeri, harga, deposit, stok per ukuran, ulasan, cek ketersediaan.
- Daftar dan profil tenant (toko) beserta kostumnya.
- Halaman Syarat & Ketentuan dan Kebijakan Privasi.

### Customer
- Registrasi, login, verifikasi email, lupa/reset password.
- Booking kostum dengan validasi ketersediaan dan penguncian tanggal (tabel `availability_blocks`).
- Upload KTP (disimpan privat, hanya bisa diakses customer terkait, tenant pemilik pesanan, dan admin).
- Pembayaran manual (transfer/e-wallet/QRIS mock) dan simulasi pembayaran sukses.
- Dashboard, detail pesanan, pembaruan status otomatis (meta refresh 30 detik).
- Pengajuan pengembalian kostum dan pemberian ulasan setelah pesanan selesai.

### Tenant
- Dashboard statistik toko (pendapatan, komisi, rating, permintaan masuk).
- CRUD kostum (soft delete), gambar, stok per ukuran, dan kategori milik tenant.
- Jadwal ketersediaan: blokir tanggal maintenance per ukuran + hapus blokir maintenance.
- Persetujuan/penolakan pengajuan booking (menolak otomatis melepas blokir tanggal).
- Proses pesanan: siap diproses → sedang disewa.
- Verifikasi pengembalian, pencatatan denda keterlambatan dan klaim kerusakan.
- Laporan pesanan dan pendapatan, profil toko, akses KTP pesanan terkait.
- Memberi ulasan kepada customer.

### Admin
- Dashboard platform: total pengguna, kostum, tenant aktif, pendapatan, komisi, rating.
- Persetujuan, aktivasi, dan penonaktifan tenant.
- Monitoring transaksi, verifikasi pembayaran manual, dan detail pesanan.
- Pengelolaan pengguna (aktivasi/nonaktivasi akun).
- Pengaturan platform (biaya sewa, deposit, ongkir, denda, komisi).
- Backup database manual melalui `php artisan cosplayin:backup`.

### Notifikasi
- Notifikasi in-app (tabel `notifications`) untuk customer, tenant, dan admin.
- Kanal email disiapkan melalui `CosplayInNotification` (mailer default: `log`).

---

## 3. Instalasi

Prasyarat: PHP 8.3+, Composer, MySQL 8, dan ekstensi PHP standar Laravel.

```bash
# 1. Dependensi
composer install

# 2. Salin konfigurasi environment
copy .env.example .env        # Windows
# cp .env.example .env        # Linux/macOS

# 3. Buat application key
php artisan key:generate

# 4. Buat database MySQL
#    CREATE DATABASE cosplayin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Sesuaikan kredensial database pada .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 6. Migrasi + data demo
php artisan migrate --seed

# 7. Symlink storage publik (gambar kostum)
php artisan storage:link

# 8. Jalankan server
php artisan serve
```

Buka **http://127.0.0.1:8000**.

Untuk memulai ulang data demo dari nol:

```bash
php artisan migrate:fresh --seed
```

---

## 4. Akun Demo

Semua akun menggunakan password: **`password`**

| Peran | Email | Username |
| --- | --- | --- |
| Admin | `admin@cosplayin.test` | `admin` |
| Tenant | `tenant@cosplayin.test` | `tenant` |
| Customer | `customer@cosplayin.test` | `customer` |

---

## 5. Aturan Bisnis & Konfigurasi

Nilai yang belum final sengaja dipusatkan di `config/cosplayin.php`, `.env`, dan tabel `settings`
agar mudah diubah tanpa menyentuh kode.

| Pengaturan | Key | Default |
| --- | --- | --- |
| Lama sewa minimum | `RENTAL_MIN_DAYS` | 1 hari |
| Lama sewa maksimum | `RENTAL_MAX_DAYS` | 14 hari |
| Deposit default | `DEFAULT_DEPOSIT` | Rp100.000 |
| Ongkir default | `DEFAULT_SHIPPING_FEE` | Rp25.000 |
| Denda per hari | `LATE_FEE_PER_DAY` | Rp50.000 |
| Denda maksimum | `MAX_LATE_FEE` | Rp500.000 |
| Komisi platform | `PLATFORM_COMMISSION_PERCENT` | 10% |
| Interval backup | `DB_BACKUP_INTERVAL_HOURS` | 24 jam |

Catatan implementasi:
- **Ketersediaan** dihitung dari stok per ukuran dikurangi jumlah baris `availability_blocks`
  pada rentang tanggal. Satu baris = satu unit terpakai per tanggal/ukuran.
- **Deposit** hanya dipotong untuk klaim kerusakan; denda keterlambatan dicatat terpisah.
- **Soft delete** diterapkan pada kostum dan tenant; transaksi tidak pernah dihapus permanen.
- **KTP** disimpan di disk privat (`storage/app/private/ktp`) dan diakses melalui route ber-otorisasi.

---

## 6. Mock Payment & Webhook

- Endpoint webhook: `POST /webhook/payment` (dikecualikan dari CSRF).
- Header signature: `X-Mock-Signature`.
- Formula: `hash_hmac('sha256', "{external_id}|{status}", MOCK_PAYMENT_KEY)`.
- Status yang didukung: `success`, `failed`, `expired`, `pending`.
- Jika `amount` dikirim dan tidak sama dengan nilai pembayaran, webhook merespons `422`.

Contoh pembuatan signature:

```bash
php artisan tinker
>>> hash_hmac('sha256', 'MOCK-XXXXXXXX|success', config('cosplayin.payment.mock.key'));
```

Contoh pemanggilan:

```bash
curl -X POST http://127.0.0.1:8000/webhook/payment \
  -H "Content-Type: application/json" \
  -H "X-Mock-Signature: <hasil-hmac>" \
  -d "{\"external_id\":\"MOCK-XXXXXXXX\",\"status\":\"success\",\"amount\":425000}"
```

Selama `MOCK_PAYMENT_ALLOW_SUCCESS=true`, tombol **Simulasi Pembayaran Berhasil** juga tersedia
pada halaman pembayaran customer.

---

## 7. Pengujian

```bash
php artisan test
```

Cakupan saat ini: **40+ pengujian** (registrasi, login/intended redirect, booking & double booking,
webhook pembayaran, denda keterlambatan, pengembalian, alur tenant/admin, jadwal maintenance,
dan smoke test dashboard tiap peran).

Perintah bantu lain:

```bash
php artisan view:cache        # validasi kompilasi seluruh Blade
php artisan route:list         # daftar 75 route aplikasi
php artisan cosplayin:backup   # backup database manual (mysqldump)
```

---

## 8. Ringkasan Tahap Pengerjaan

1. **Fondasi & konfigurasi** — konfigurasi aplikasi, locale Indonesia, helper `rupiah()` dan
   `tanggal_indonesia()`, serta pemetaan seluruh nilai bisnis yang belum final.
2. **Skema database & model** — migrasi, relasi Eloquent, soft delete, factory, dan seeder demo.
3. **Autentikasi & RBAC** — registrasi, login, verifikasi email, reset password, middleware peran,
   dan policies.
4. **Halaman publik** — beranda, katalog + filter, detail kostum, cek ketersediaan, daftar/profil tenant.
5. **Alur booking** — validasi tanggal, penguncian ketersediaan dalam transaksi database,
   upload KTP privat, dan pembuatan kode booking.
6. **Pembayaran** — mock gateway, instruksi pembayaran, webhook bertanda tangan, dan verifikasi manual admin.
7. **Alur tenant** — persetujuan/penolakan, proses pesanan, jadwal maintenance, pengembalian,
   denda, laporan, dan profil.
8. **Pengembalian & ulasan** — verifikasi pengembalian, denda keterlambatan, klaim kerusakan,
   serta ulasan dua arah.
9. **Dashboard & admin** — statistik tiap peran, moderasi konten, pengaturan platform, backup.
10. **Pengujian & penyelesaian** — feature test, smoke test, validasi Blade, dan dokumentasi ini.

---

## 9. TODO / Keputusan yang Belum Final

Item berikut sengaja ditandai `TODO` di kode dan menunggu keputusan pemilik produk:

1. **Payment gateway final** — pilih Midtrans atau Xendit, ganti `MockPaymentGateway`, isi kredensial,
   dan sesuaikan verifikasi signature webhook dengan dokumentasi gateway.
2. **Kebijakan pembatalan & refund** — teks kebijakan final, aturan refund, dan alur persetujuan.
3. **Formula denda keterlambatan** — tarif per hari, batas maksimum, dan mekanisme penagihan
   (auto-charge, invoice, atau potong deposit).
4. **Klaim kerusakan & pengembalian deposit** — bukti kerusakan, nominal potongan, dan alur refund deposit.
5. **Lama sewa minimum/maksimum** — nilai final di luar default 1–14 hari.
6. **Ongkir & integrasi kurir** — tarif per kota/berat dan integrasi API kurir.
7. **Persentase komisi platform** — nilai final dan mekanisme pencairan dana ke tenant.
8. **Kanal notifikasi** — finalisasi email/SMS/WhatsApp, template pesan, dan penyedia.
9. **Backup otomatis** — interval final, retensi, dan penyimpanan off-site; aktifkan scheduler di
   `routes/console.php`.
10. **Rekening pembayaran manual** — ganti data bank/e-wallet placeholder di `config/cosplayin.php`
    dengan rekening resmi.
11. **Kebijakan privasi & syarat ketentuan** — tinjauan hukum dan penyesuaian teks final.
12. **Tampilan** — styling sengaja belum ditambahkan sesuai kebutuhan; bila diinginkan, desain
    dapat ditambahkan tanpa mengubah struktur HTML semantik yang sudah ada.
13. **Hardening produksi** — rate limiting webhook/login, queue worker, monitoring, HTTPS,
    dan rotasi kunci `MOCK_PAYMENT_KEY`.

---

## 10. Struktur Direktori Penting

```
app/
  Console/Commands/BackupDatabase.php   # backup manual
  Http/Controllers/                     # publik, customer, tenant, admin, webhook
  Http/Requests/                        # FormRequest + pesan Bahasa Indonesia
  Models/                               # Eloquent + relasi + status
  Policies/                             # otorisasi per peran/kepemilikan
  Services/                             # availability, booking, payment, return, late fee, notifikasi
config/cosplayin.php                    # nilai bisnis & TODO
database/migrations|seeders|factories   # skema + data demo
lang/id/                                # validasi & pesan autentikasi Indonesia
resources/views/                        # Blade tanpa styling
routes/web.php                          # 75 route aplikasi
tests/Feature/                          # feature test
```

---

## Lisensi

Aplikasi ini dibangun di atas Laravel yang dirilis dengan lisensi [MIT](https://opensource.org/licenses/MIT).
