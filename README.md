# Polling Insan Statistik Teladan (IST) — PHP + MySQL

Aplikasi polling sederhana, tanpa framework, siap upload ke hosting PHP+MySQL
mana pun (cPanel/shared hosting). Login pegawai pakai **Nama + NIP** (tanpa
password terpisah), form 4 kategori penilaian sesuai lembar fisik, plus
panel admin untuk rekap hasil.

## Cara deploy (± 15 menit)

1. **Buat database MySQL** di cPanel hosting kamu (Setup MySQL Database),
   catat: nama database, username, password.
2. **Import `schema.sql`** lewat phpMyAdmin (tab Import → pilih file) — ini
   otomatis membuat semua tabel + mengisi kandidat, kategori, dan indikator
   sesuai form kamu.
3. **Edit `config.php`** — isi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   sesuai data dari cPanel.
4. **Upload semua file** (via File Manager cPanel atau FTP) ke folder
   `public_html/` atau subfolder, misalnya `public_html/polling/`.
5. **Isi daftar pegawai (voter)** — buka phpMyAdmin, tabel `pegawai`, hapus
   baris contoh, lalu import daftar Nama + NIP pegawai kamu (bisa lewat
   fitur Import CSV di phpMyAdmin, atau insert manual/lewat query).
6. **Buat akun admin**: buka `https://domainkamu.com/setup_admin.php` di
   browser, isi username & password admin, submit.
   **Setelah itu HAPUS file `setup_admin.php` dari server** (penting, demi
   keamanan).
7. Selesai. Bagikan link `https://domainkamu.com/login.php` ke pegawai,
   dan `https://domainkamu.com/admin/login.php` untuk panel rekap admin.

## Struktur

```
config.php          -> kredensial database
schema.sql           -> struktur tabel + seed data kandidat/kategori/indikator
includes/db.php       -> koneksi PDO
includes/functions.php-> helper (auth, query bantu)
login.php / logout.php-> login pegawai (nama+nip)
vote.php              -> form penilaian multi-step (4 kategori + essay)
thanks.php            -> halaman setelah submit
setup_admin.php       -> buat akun admin (HAPUS setelah dipakai)
admin/login.php        -> login admin
admin/dashboard.php    -> rekap rata-rata skor + daftar catatan essay
admin/export.php       -> download CSV data mentah semua penilaian
assets/style.css      -> styling
```

## Catatan penting

- Satu pegawai hanya bisa submit **satu kali** (dicek lewat kolom
  `sudah_vote`); setelah submit final, semua input terkunci.
- Progress per kategori otomatis tersimpan ke database begitu pegawai klik
  "Lanjut", jadi kalau koneksi putus di tengah jalan, data yang sudah diisi
  tidak hilang saat login ulang (asalkan belum submit final).
- Kalau jumlah kandidat atau indikator berubah, cukup edit isi tabel
  `kandidat` / `kategori` / `indikator` — tidak perlu ubah kode PHP.
- Untuk menambah data pegawai lebih cepat, siapkan file CSV (kolom: nama,
  nip, unit_kerja) lalu import lewat phpMyAdmin (Import → pilih tabel
  `pegawai` → format CSV).

## Cara cepat cek dashboard

1. Jalankan server PHP di folder project:

```powershell
php -S 127.0.0.1:8000
```

2. Buka browser:
   - User/karyawan: `http://127.0.0.1:8000/`
   - Admin: `http://127.0.0.1:8000/admin/login.php`

3. Contoh login untuk testing:
   - User: `Nama = Contoh Nama Pegawai`, `NIP = 199001012015011001`
   - Admin: gunakan `setup_admin.php` sekali untuk membuat akun admin.

4. Untuk membuat akun admin pertama:
   - Buka `http://127.0.0.1:8000/setup_admin.php`
   - Isi `username` dan `password`, submit.
   - Setelah berhasil, akses `admin/login.php`.

5. Jika kamu ingin langsung lihat tampilan dashboard admin:
   - login sebagai admin terlebih dahulu
   - buka `admin/dashboard.php`

6. Jika kamu ingin langsung lihat halaman user:
   - login sebagai pegawai dengan data contoh di atas
   - kemudian akan diarahkan ke `vote.php?step=1`
   - setelah submit, akan muncul `thanks.php`

> Catatan: `index.php` sudah tersedia di root agar `http://127.0.0.1:8000/` otomatis membuka halaman login user.
