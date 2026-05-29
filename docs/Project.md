# Dokumen Definisi Proyek: Integrated Management System PT Quty Karunia (2026)

## Latar Belakang & Tujuan Proyek
Proyek ini bertujuan untuk mendigitalisasi dan mengotomatisasi proses booking fasilitas (ruang rapat, kendaraan) serta manajemen inventaris (ATK, sparepart) di lingkungan PT Quty Karunia. Sistem berbasis web dan mobile ini dirancang untuk menghilangkan proses manual, mempercepat alur persetujuan (approval), meminimalisir bentrok jadwal, dan menyajikan laporan penggunaan secara real-time guna meningkatkan produktivitas serta efisiensi anggaran perusahaan.

## Spesifikasi Teknologi
Infrastruktur akan dibangun dengan pendekatan arsitektur yang solid (direkomendasikan menggunakan pola Controller-Service-Repository di backend) untuk skalabilitas jangka panjang.
1. Frontend (Web): React.js dengan Redux untuk state management.
2. Frontend (Mobile): React Native.
3. Backend: Laravel (API-driven).
4. Database: MySQL.
5. Infrastruktur & Integrasi: Docker (Containerization), Axios (HTTP Client).
6. Rekomendasi tambahan: Redis untuk caching & queueing notifikasi.

## Modul & Fitur Utama
1. Modul Manajemen Fasilitas (Facility Booking)
    -   Meeting Room Management: Pemesanan ruang rapat dilengkapi visualisasi ketersediaan jadwal, pencegahan bentrok (conflict prevention), dan opsi tambahan (request konsumsi/peralatan).
    -   Car Management: Pemesanan kendaraan operasional perusahaan, termasuk estimasi jarak, waktu penggunaan, dan alokasi unit.
2. Modul Manajemen Inventaris (Inventory System)
    -   ATK & Sparepart Management: Pencatatan stok masuk/keluar, kategorisasi barang, dan batas minimum stok (low-stock alert).
    -   Request & Issuance: Fitur pengajuan permintaan ATK dan sparepart oleh staf ke divisi terkait.
3. Modul Alur Kerja & Sistem (Core System)
    -   Multi-tier Approval Workflow: Mesin persetujuan berjenjang yang melibatkan Manajer dan HRD-GA sesuai kebijakan perusahaan.
    -   Notification System: Notifikasi real-time (Email/Push Notification) untuk status pengajuan, jadwal booking, dan reminder.
    -   Reporting & Analytics: Dasbor pelaporan komprehensif mengenai serapan anggaran ATK, frekuensi penggunaan ruang/kendaraan, dan rekam jejak aset.
    -   User & Role Management: Pengaturan hak akses (RBAC - Role Based Access Control) secara granular oleh administrator.

## Pemetaan Hak Akses Pengguna (Role & Permissions)
Peran (Role): Hak Akses Utama
IT Developer : God Mode (Akses penuh ke seluruh sistem dan konfigurasi backend).
Admin Departemen Produksi : Mengajukan request ATK/Sparepart, booking ruang rapat & kendaraan untuk departemen.
Staff Perusahaan : Mengajukan request mandiri untuk ATK/Sparepart, ruang rapat, dan kendaraan.
Manager Produksi : Memberikan approval lapis pertama untuk request dari staf di bawah naungannya.
Staff / Manager HRD-GA : Memberikan approval final, mengatur ketersediaan aset, dan mengelola operasional kendaraan.
IT Support : Mengelola inventaris teknis, approval ruang rapat (terkait perangkat IT), dukungan teknis.
Finance : Akses read-only ke laporan penggunaan aset dan inventaris untuk manajemen anggaran.
Direktur : Akses read-only ke dashboard analitik tingkat tinggi untuk keputusan strategis.
Resepsionis : Fitur override/block ruang rapat untuk tamu VVIP, serta bantuan booking mendadak.
Driver (Next Update) : Melihat jadwal tugas, lokasi tujuan, dan konfirmasi penyelesaian tugas mengemudi.
Security (Next Update) : Verifikasi kendaraan keluar/masuk dan pengecekan otorisasi penggunaan aset di gerbang.

Ide Pengembangan Lanjutan (Deep Function Analysis)
Untuk memastikan sistem ini benar-benar efisien di skala enterprise, ada beberapa aspek teknis dan operasional yang bisa kita kembangkan lebih dalam:

    A. Matriks Approval Dinamis
    Jangan melakukan hardcode pada alur persetujuan. Buat tabel approval_rules di database sehingga jika ada perubahan struktur organisasi di PT Quty Karunia, admin IT dapat mengubah rute approval (misalnya: Staff -> Manager -> HRD) tanpa harus membongkar kode (seperti menyusun modul ERP kustom).
    B. Implementasi Barcode / QR Code Scanner (Mobile)
    Karena aplikasi mobile menggunakan React Native, manfaatkan modul kamera untuk melakukan scanning QR code pada barang inventaris atau sparepart. Ini akan sangat mempercepat proses check-out barang oleh IT Support atau GA dibandingkan mengetik manual.
    C. Pencegahan Bentrok & Overbooking (Concurrency Control)
    Pada Laravel, Anda harus mengimplementasikan Pessimistic Locking atau pembatasan transaksional (Database Transactions) pada fitur booking ruang rapat dan mobil. Ini penting agar jika ada dua pengguna yang menekan tombol booking di detik yang sama untuk ruangan yang sama, sistem tidak menghasilkan data ganda.
    D. Logika "Delegasi" untuk Resepsionis
    Fitur override untuk resepsionis harus memiliki log audit yang sangat kuat. Jika resepsionis membatalkan jadwal staf demi tamu VVIP, sistem harus otomatis men-nol-kan state pesanan sebelumnya dan memicu job queue (bisa menggunakan Redis) untuk mengirim email permohonan maaf/penjadwalan ulang otomatis ke staf yang terdampak.

## RBAC & Page Access Control
Dibuat bukan hardcode, melainkan berbasis database. Setiap halaman atau endpoint API akan memiliki tag permission tertentu (misalnya: "manage_inventory", "approve_booking"), dan setiap role akan memiliki daftar permission yang bisa diubah oleh admin tanpa perlu deploy ulang aplikasi.
Dengan pendekatan ini, jika suatu saat PT Quty Karunia ingin menambahkan peran baru atau mengubah hak akses, mereka bisa melakukannya langsung melalui dashboard admin tanpa harus menyentuh kode sumber.

## Delete unused code
Hapus kode yang tidak lagi digunakan, seperti fungsi getTicketConfig() di PagesController, atau rute yang sudah tidak relevan di routes/modules/meeting-rooms.php. Ini akan membantu menjaga kebersihan kode dan memudahkan pemeliharaan di masa depan. View yang tidak lagi digunakan, seperti resources/views/meeting-rooms/old-booking.blade.php, juga harus dihapus untuk menghindari kebingungan.

## Approval workflow

- User mengajukan request (booking ruang rapat, kendaraan, atau permintaan ATK/sparepart).

## Frontend

Menggunakan React.js untuk web dan React Native untuk mobile, dengan state management menggunakan Redux. Komunikasi dengan backend dilakukan melalui Axios untuk memastikan respons yang cepat dan efisien.

## Menu

Menampilkan menu utama yang terdiri dari beberapa modul, antara lain:

- Main Portal
    - Meeting Room Booking
    - Vehicle Booking
    - Inventory Management (ATK & Sparepart)
    - Approvals
    - User Management
    - Settings
    - Profile
    - Reporting

