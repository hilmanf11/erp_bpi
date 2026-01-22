# 🗓️ Riwayat Perubahan - Januari 2026

Halaman ini mencatat semua pembaruan, perbaikan bug, dan perubahan teknis pada sistem ERP selama bulan Januari 2026.

---

## [22 Januari 2026]

### Fixing Bug Account Number Modul AP Payments

#### 🚀 Main Goal 
- **Modul**: AP Payment
- **Objectives**: 
    - Fixing Bug Add New AP Payment dari Purchase / Other, Account Number yang di get harus dari Category=Account Payable 
    - Fixing Bug Add to Journal agar journal dan nilai sesuai banyaknya data transaksi (disamakan dengan ABBI)
- **Requested By**: Bu Nina
- **Requested At**: 2026-01-22
- **Developer**: Rizki

#### 🛠️ Bug Fixes
- **Controller**: 
    - Memperbaiki query dan mapping data di function `datatablesTemp()` pada controller `AP_payments.php` 
    - Memperbaiki error undefined `Journal Type` pada controller `Journal_posting.php` saat mengolah datatable Auto Posting Journal dari AP Payment.
- **View**: Tidak ada.

#### 📝 Perubahan Teknis
- **Database**: 
  - Tidak ada
- **Template Excel**
  - Tidak ada