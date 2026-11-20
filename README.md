# Bank Benih Pusaka (Laravel 12 + MySQL)

File di folder ini adalah **overlay**: salin ke project Laravel 12 yang baru.

## Instalasi

```bash
composer create-project laravel/laravel bank-benih-pusaka
cd bank-benih-pusaka
# salin isi folder ini (app, database, resources, routes) ke dalam project, timpa bila diminta
```

Atur `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_benih_pusaka
DB_USERNAME=root
DB_PASSWORD=
```

Buat database kosong `bank_benih_pusaka` di MySQL, lalu:

```bash
php artisan migrate --seed      # atau: php artisan migrate:fresh --seed
php artisan serve
```

Buka http://127.0.0.1:8000

## Struktur database (ERD ringkas)

```
plant_groups 1 ──< accessions 1 ── 1 collection_origins
                       │
                       └──< accession_storage_treatment >── storage_treatments
```

| Relasi | Eloquent |
|---|---|
| Aksesi → Kelompok tanaman (N:1) | `belongsTo` / `hasMany` |
| Aksesi → Asal pengumpulan (1:1) | `hasOne` / `belongsTo`, kolom `accession_id` **unique** |
| Aksesi ↔ Perlakuan (N:M) | `belongsToMany` + tabel pivot |

## Alur kerja (untuk dijelaskan)

1. **Routing**: `Route::resource('accessions', ...)` menghasilkan 7 route CRUD.
2. **Controller** (`AccessionController`) hanya menerima request, memanggil service, lalu mengembalikan view/redirect.
3. **Form Request** memvalidasi *sebelum* controller berjalan; gagal -> kembali ke form dengan pesan error dan `old()`.
4. **Service** (`AccessionService`) membungkus simpan aksesi + asal + perlakuan dalam `DB::transaction()`, sehingga atomik (semua berhasil atau semua dibatalkan).
5. **Model** menyediakan relasi dan *query scope* `search()` & `filter()` untuk pencarian/filter.
6. **Blade** (`layouts/app` + `accessions/*`) menampilkan data; form dipakai bersama lewat `_form.blade.php` untuk create & edit.

## Skenario uji

- Tambah aksesi dengan kode duplikat -> ditolak (`unique`).
- Kode `bbp 01` -> ditolak (`regex`); `bbp-01` otomatis jadi `BBP-01`.
- Tanggal pengumpulan di masa depan -> ditolak.
- Ubah aksesi, centang/lepas perlakuan -> pivot ter-`sync`.
- Cari "cianjur", filter kelompok/perlakuan/provinsi, lalu pindah halaman -> filter tetap terbawa (`withQueryString`).
- Hapus aksesi -> origin & pivot ikut terhapus dalam satu transaksi.

## Fitur interaktif (versi UI baru)

Aset ada di `public/css/app.css` dan `public/js/app.js` (tanpa build step, tidak perlu `npm`).

- **Pencarian langsung**: mengetik di kolom cari memuat ulang daftar lewat `fetch` (debounce 300 ms). Controller mengenali permintaan AJAX (`$request->ajax()`) dan hanya mengirim partial `_list.blade.php`.
- **Filter instan**: dropdown dan pil kelompok langsung diterapkan; ada chip filter aktif yang bisa dihapus satu per satu.
- **Urut kolom**: klik judul kolom Kode/Nama (kolom urut di-whitelist di service).
- **Tampilan tabel/kartu**: pilihan disimpan di `localStorage`.
- **Modal hapus** dan **toast** menggantikan `confirm()` dan alert biasa.
- **Form pintar**: pratinjau label aksesi real-time, bar kelengkapan, kode otomatis huruf besar + tombol "Otomatis", kartu perlakuan yang bisa diketuk, penghitung karakter, validasi klien, peringatan jika meninggalkan form yang belum disimpan.
- Pintasan: tekan `/` untuk fokus ke pencarian.

Validasi server (Form Request) tetap menjadi penentu akhir; validasi klien hanya membantu pengguna.

## Dasbor dan grafik

Halaman `/dashboard` (menu **Dasbor**) menampilkan lima grafik memakai Chart.js 4 (CDN, tanpa build step):

| Grafik | Jenis | Sumber data | Klik |
|---|---|---|---|
| Aksesi per kelompok tanaman | Donat | `PlantGroup::withCount('accessions')` | membuka daftar terfilter kelompok |
| Provinsi asal terbanyak | Batang horizontal | `collection_origins` GROUP BY `province` (8 teratas) | filter provinsi |
| Perlakuan penyimpanan | Batang horizontal | `StorageTreatment::withCount('accessions')` (lewat pivot) | filter perlakuan |
| Ketinggian lokasi | Batang | `CASE WHEN` pada `altitude` | - |
| Tren pengumpulan per bulan | Garis | `DATE_FORMAT(collected_at, '%Y-%m')`, bulan kosong diisi 0 | - |

Alur: `DashboardController` (invokable) memanggil `AccessionStatisticsService` -> view `dashboard/index.blade.php` menaruh data sebagai JSON di `<script type="application/json">` -> `public/js/charts.js` menggambar grafik. Tidak ada tabel baru.
