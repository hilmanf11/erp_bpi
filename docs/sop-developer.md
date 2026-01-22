# 🛠️ Standard Operating Procedure (SOP) Developer

SOP ini wajib diikuti oleh seluruh pengembang sistem ERP agar kode tetap bersih, dokumentasi teratur, dan kolaborasi berjalan lancar.

---

## 1. Persiapan Lingkungan (Git Clone)
Sebelum memulai pengerjaan, pastikan lingkungan lokal Anda sesuai dengan standar produksi:
* **Versi PHP**: Wajib menggunakan **PHP 7.3** pada XAMPP atau web service local anda.
* **Clone Repo**: Gunakan perintah `git clone https://github.com/hilmanf11/erp_bpi.git`.
* **Database**: Buat database di local anda dengan nama **erp_bpi**, kemudian import .sql database existing dari dummy / live (Hubungi Pak Hilman).
* **Koneksi Database**: Sesuaikan `application/config/database.php` dengan database lokal Anda, namun **jangan pernah melakukan commit** pada perubahan file config pribadi.

## 2. Alur Kerja Git (Branching)
* **Main Branch**: Digunakan hanya untuk kode yang sudah stabil.
<!-- * **Development**: Selalu buat branch baru untuk setiap fitur atau perbaikan:
  `git checkout -b feature/nama-fitur` atau `git checkout -b fix/nama-bug`. -->

## 3. Aturan Pesan Commit (Semantic Commits)
Pesan commit adalah sumber data utama untuk **Changelog** kita. Gunakan format: `type: description`.

### Jenis-Jenis Type:
| Type | Keterangan | Contoh |
| :--- | :--- | :--- |
| **feat** | Menambah fitur baru | `feat: tambah modul export excel di AP Payment` |
| **fix** | Memperbaiki bug/error | `fix: perbaikan perhitungan PPN di invoice` |
| **chore** | Pemeliharaan rutin (bukan fitur/bug) | `chore: update library dompdf` |
| **docs** | Perubahan dokumentasi saja | `docs: update user guide modul payroll` |
| **style** | Perubahan tampilan (CSS/Layout) | `style: perbaikan tombol simpan agar responsif` |
| **refactor** | Merapikan kode tanpa ubah fungsi | `refactor: optimasi query query_get_kas()` |

## 4. Prosedur Sebelum Push
Sebelum melakukan `git push`, pastikan Anda telah melakukan:
1. **Self-Review**: Cek kembali apakah ada `var_dump()` atau `print_r()` yang tertinggal di kode.
2. **Pull Terbaru**: Lakukan `git pull origin main` untuk menghindari *conflict*!
3. **Dokumentasi**: Jika ada perubahan struktur database, catat .sql di folder root `/db_updates` agar rekan lain bisa melakukan *import* di local mereka.

## 5. Update Changelog
Setelah melakukan *push*, pastikan pesan commit Anda sudah muncul di:
* **Docsify**: `http://localhost/erp/docs/#/changelog-jan-2026`
* Pastikan nama Author muncul dengan benar.

---
> **Penting**: Dokumentasi yang rapi adalah bentuk tanggung jawab kita kepada klien dan rekan sejawat.