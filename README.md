# Baliswara Batik POS

Aplikasi inventory dan stok batik berbasis Laravel + Livewire untuk membantu pengelolaan produk, stok masuk, stok keluar, stock opname, dan laporan secara lebih rapi.

## Ringkasan

Project ini dibuat untuk kebutuhan operasional brand batik Baliswara dengan fokus pada:

- katalog produk dan stok yang mudah dipantau
- transaksi stok masuk dan stok keluar
- penandaan produk best seller
- import produk dan stok dari spreadsheet
- akses publik untuk halaman produk
- role user untuk admin, kasir, dan viewer

## Fitur Utama

- Dashboard monitoring produk dan aktivitas stok
- Halaman produk publik dengan pencarian, sorting, pagination, dan penanda best seller
- CRUD produk batik dengan upload gambar
- Import produk dan stok dari file `.xlsx`, `.xls`, atau `.csv`
- Transaksi stok masuk batch dalam satu kali simpan
- Transaksi stok keluar batch dalam satu kali simpan
- Auto search produk pada halaman stok masuk dan stok keluar
- Produk stok `0` tetap tampil di pencarian stok keluar tetapi tidak bisa dipilih
- Laporan ringkasan stok dan transaksi
- Export laporan CSV
- Role akses:
  - `admin`
  - `kasir`
  - `viewer`

## Teknologi

- PHP 8.3
- Laravel 13
- Livewire 4
- Laravel Breeze
- Tailwind CSS
- PhpSpreadsheet

## Hak Akses

- Guest:
  - bisa melihat halaman `/products`
- Viewer:
  - bisa melihat dashboard, produk, dan laporan
- Admin dan Kasir:
  - bisa mengelola produk
  - bisa import spreadsheet
  - bisa input stok masuk dan stok keluar

## Halaman Penting

- `/products` : katalog produk publik
- `/dashboard` : dashboard internal
- `/products/create` : tambah produk
- `/products/import` : import spreadsheet
- `/stock-in` : transaksi stok masuk
- `/stock-out` : transaksi stok keluar
- `/reports` : laporan

## Instalasi

```bash
git clone https://github.com/timbaliswara/batik-pos-app.git
cd batik-pos-app
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Lalu buka:

- `http://127.0.0.1:8000/products` untuk katalog publik
- `http://127.0.0.1:8000/login` untuk masuk ke panel admin

## Akun Demo

Setelah menjalankan seeder, akun demo yang tersedia:

- `admin@batikpos.test` / `password`
- `kasir@batikpos.test` / `password`
- `viewer@batikpos.test` / `password`

## Seeder Demo

Seeder demo akan membuat:

- 100 produk dummy
- campuran produk baju dan kain
- stok awal per ukuran
- data best seller
- histori transaksi awal untuk simulasi

Perintah reset data demo:

```bash
php artisan migrate:fresh --seed --force
```

## Format Import Spreadsheet

Kolom utama:

- `code`
- `name`
- `type`
- `description`
- `price`
- `best_seller`
- `low_stock_threshold`

Kolom stok:

- `stock_s`
- `stock_m`
- `stock_l`
- `stock_xl`
- `stock_xxl`
- `stock_none`

Contoh:

```csv
code,name,type,description,price,best_seller,low_stock_threshold,stock_s,stock_m,stock_l,stock_xl,stock_xxl,stock_none
BTK-PRM-010,Batik Parang Baru,baju,Kemeja batik premium,325000,yes,3,5,8,7,4,2,0
KAIN-MOT-020,Kain Batik Motif Baru,kain,Kain meteran untuk stock opname,175000,no,10,0,0,0,0,0,25
```

## Menjalankan Development

Jalankan backend:

```bash
php artisan serve
```

Jalankan Vite saat development:

```bash
npm run dev
```

Atau gunakan script Composer:

```bash
composer run dev
```

## Testing

Menjalankan test:

```bash
php artisan test
```

Build frontend:

```bash
npm run build
```

## Catatan Operasional

- Daftar batch pada stok masuk dan stok keluar belum tersimpan ke database sebelum tombol `Simpan Semua` ditekan
- Jika halaman di-refresh sebelum submit, daftar batch sementara akan hilang
- Halaman login memakai tampilan khusus brand Baliswara

## Repository

Remote GitHub project ini:

[https://github.com/timbaliswara/batik-pos-app](https://github.com/timbaliswara/batik-pos-app)
