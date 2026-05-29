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
    
Strategi Implementasi Arsitektur Sistem Terpadu (Project.md)

Dalam lanskap pengembangan perangkat lunak tahun 2025, efisiensi bukan lagi sekadar pilihan, melainkan keharusan strategis. Sebagai Senior Software Architect, saya melihat pergeseran fundamental dari kompleksitas yang dipaksakan menuju kesederhanaan yang terukur. Laporan ini merinci strategi teknis yang menyatukan metodologi "Models as Code", antarmuka yang berpusat pada produktivitas, hingga optimasi infrastruktur berbasis data untuk memastikan sistem tetap tangguh, skalabel, dan bebas dari pembusukan dokumentasi (documentation decay).


--------------------------------------------------------------------------------


1. Metodologi Pemodelan Arsitektur: Pendekatan C4 Model & Structurizr

Dokumentasi arsitektur sering kali menjadi artefak yang mati segera setelah kode ditulis. Untuk melawan ini, kita meninggalkan alat diagram UI konvensional dan mengadopsi pendekatan "Models as Code" menggunakan Structurizr DSL. Keunggulan utamanya adalah konsistensi semantik; tidak seperti gambar statis, model ini memahami hierarki arsitektur, memastikan bahwa setiap perubahan desain dapat diverifikasi melalui pull request dan tetap sinkron dengan implementasi teknis.

Strategi Pemodelan dengan Structurizr DSL & AI

Kita akan memanfaatkan Structurizr DSL yang dikombinasikan dengan MCP (Model Context Protocol) Server. Strategi ini sangat krusial bagi efisiensi tim karena LLM (Large Language Models) jauh lebih mahir dalam menghasilkan dan memvalidasi teks DSL dibandingkan memanipulasi file GUI XML. Penggunaan MCP Server memungkinkan validasi otomatis terhadap aturan C4 dan inspeksi elemen yang hilang secara real-time, menciptakan sumber kebenaran tunggal (Single Source of Truth) yang dinamis.

Daftar Periksa Hierarki C4 Model

Setiap level dalam hierarki C4 dirancang untuk audiens spesifik guna menjaga relevansi informasi:

* [ ] Level 1: System Context
  * Audiens: Pemangku kepentingan non-teknis dan pemilik bisnis.
  * Tujuan: Visualisasi sistem sebagai satu entitas tunggal dan interaksinya dengan pengguna serta sistem eksternal.
* [ ] Level 2: Container
  * Audiens: Arsitek dan pengembang.
  * Tujuan: Membedah sistem menjadi unit yang dapat dideploy (API, Database, Web App).
* [ ] Level 3: Component
  * Audiens: Pengembang internal.
  * Tujuan: Menunjukkan modul internal di dalam satu kontainer.
* [ ] Level 4: Code
  * Audiens: Insinyur perangkat lunak.
  * Tujuan: Dokumentasi tingkat rendah (misalnya Class Diagram). Wajib dihasilkan secara otomatis dari kode untuk menghindari redundansi manual.

Sintesis Alat: Structurizr vs LikeC4

Meskipun LikeC4 menawarkan fleksibilitas, Structurizr tetap menjadi pilihan utama untuk kepatuhan standar C4. Structurizr, yang diciptakan oleh Simon Brown (pencipta C4 Model), memberikan aturan hierarki yang ketat secara bawaan, mencegah elemen ditempatkan pada level abstraksi yang salah—risiko yang sering terjadi pada LikeC4 jika spesifikasinya tidak dikonfigurasi dengan sempurna.

Kejelasan struktur model ini menjadi pondasi bagi implementasi antarmuka pengguna yang akan kita bahas selanjutnya.


--------------------------------------------------------------------------------


2. Strategi UI/UX: Inovasi Bento Grid dan Command Palette

Bagi pengguna profesional, antarmuka adalah alat kerja. Desain harus mengutamakan kejelasan visual dan efisiensi navigasi untuk meminimalkan beban kognitif saat menangani alur kerja yang kompleks.

Panduan Desain Bento UI Grid

Kita akan mengadopsi Bento UI Grid, sebuah tata letak yang terinspirasi dari kompartmentalisasi kotak bekal Jepang. Prinsip ini meningkatkan kegunaan melalui:

* Usability: Membagi informasi ke dalam blok-blok konten yang berbeda untuk memandu perhatian pengguna secara hierarkis.
* Skalabilitas: Menggunakan CSS Grid untuk memastikan konten tetap proporsional dan responsif di berbagai ukuran layar.
* Organisasi: Memastikan setiap fitur memiliki ruang yang terdefinisi dengan jelas tanpa tumpang tindih visual.

Spesifikasi Command Palette (CMD+K)

Command Palette bertindak sebagai pusat kendali "omni-bar" untuk mendukung alur kerja power-user. Komponen ini terdiri dari input pencarian yang memungkinkan eksekusi perintah, navigasi halaman, dan pencarian global tanpa menyentuh mouse. Fitur ini wajib diimplementasikan pada modul dengan kompleksitas tinggi untuk menghindari navigasi manual yang membebani.

Perbandingan Varian Command Palette

Varian	Dampak Fungsional & Pengalaman Pengguna
Keyboard Shortcuts	Akses instan untuk aksi frekuensi tinggi; mengurangi beban kognitif jika ditampilkan sebagai petunjuk visual.
Tabs	Kategorisasi data yang kompleks; membantu pengguna memfilter tipe informasi (misal: "Dokumen" vs "Aksi").
Chips	Ideal untuk seleksi tag dan filter konten; memberikan konfirmasi visual instan dan kemudahan penghapusan pilihan.

Efisiensi antarmuka ini secara langsung bergantung pada performa akses data di sisi frontend, yang membawa kita pada arsitektur local-first.


--------------------------------------------------------------------------------


3. Arsitektur Frontend: Local-First dan Penegakan Dependensi

Kita beralih menuju arsitektur "Local-first" menggunakan Expo. Strategi ini memastikan aplikasi tetap berfungsi penuh meski tanpa jaringan, memberikan pengalaman instan bagi pengguna karena interaksi tidak lagi terikat oleh latensi jaringan (network-bound).

Alat Persistensi dan Sinkronisasi Data

Kombinasi alat berikut dipilih untuk mengoptimalkan sinkronisasi real-time dan resolusi konflik:

* Legend-State: Memberikan fine-grained reactivity untuk performa render React yang maksimal.
* TinyBase: Bertindak sebagai reactive data store lokal yang menjembatani persistensi dan sinkronisasi.
* SQLite (via expo-sqlite): Menangani persistensi data fisik yang andal di perangkat.
* Yjs: Implementasi CRDT (Conflict-free Replicated Data Types) yang krusial untuk sinkronisasi dokumen kolaboratif. SQLite menyediakan penyimpanan, sementara Yjs mengelola lapisan sinkronisasi antar perangkat.

"Architecture Fitness Function" dengan Dependency-cruiser

Untuk mencegah degradasi struktur kode atau "kematian karena seribu luka" (death by a thousand cuts), kita menerapkan dependency-cruiser. Alat ini memastikan isolasi folder spesifik-halaman dan mencegah impor yang tidak sengaja yang dapat merusak desain sistem.

Tiga Aturan Utama Dependency-cruiser:

1. Isolasi Peer Folder: Modul di satu halaman dilarang mengimpor dari halaman lain (harus dipindahkan ke shared).
2. Validasi Shared Code: Memastikan kode di folder shared memang digunakan oleh lebih dari satu modul untuk menghindari polusi folder umum.
3. Deteksi Circular Dependencies: Menghentikan siklus impor yang mempersulit testing dan meningkatkan kopling.

Isolasi kode frontend ini mencerminkan struktur modular yang juga kita terapkan di sisi backend.


--------------------------------------------------------------------------------


4. Struktur Backend: Modular Monolith dan Strategi Observabilitas

Berdasarkan data survei CNCF 2025, industri sedang mengalami koreksi besar: 42% organisasi mengkonsolidasi kembali mikrolayanan mereka ke unit penyebaran yang lebih besar (Modular Monolith). Keputusan ini didorong oleh fakta bahwa debugging pada sistem terdistribusi memakan waktu 35% lebih lama.

Strategi "Monolith First" & Logical Boundaries

Mengikuti prinsip arsitektur Martin Fowler, kita akan mengadopsi Modular Monolith. Ini bukan "ball of mud" tradisional, melainkan sistem dengan batas-batas logis (logical boundaries) yang kuat seperti yang dipraktikkan oleh Shopify dan GitHub.

* Kasus Amazon Prime Video: Berhasil mengurangi biaya infrastruktur sebesar 90% dengan mengkonsolidasi layanan pemantauan dari mikrolayanan terdistribusi kembali ke monolit modular.
* Kasus InfluxDB: Melakukan rewrite total dari mikrolayanan ke monolit berbasis Rust untuk mencapai performa maksimal dan mengurangi overhead jaringan.

Instrumentasi dengan OpenTelemetry (tracing.js)

Untuk memantau batas-batas modul ini, kita menggunakan OpenTelemetry. Ini krusial karena:

* Mendeteksi latensi yang terjadi saat komunikasi lintas modul.
* Memberikan visibilitas jika suatu saat modul perlu dipisahkan menjadi mikrolayanan independen (Strangler Fig Pattern).
* Menurunkan biaya observabilitas secara signifikan dibandingkan sistem mikrolayanan murni yang memerlukan service mesh kompleks (yang adopsinya turun drastis dari 18% ke 8% di tahun 2025).

Struktur backend modular ini memerlukan sistem otorisasi yang sama canggihnya untuk menjaga integritas data lintas domain.


--------------------------------------------------------------------------------


5. Keamanan dan RBAC: Model Otorisasi Terperinci (Zanzibar/ReBAC)

Otorisasi kasar (coarse-grained) tidak lagi memadai. Kita mengimplementasikan Relationship-Based Access Control (ReBAC) yang terinspirasi oleh sistem Google Zanzibar.

SpiceDB vs Auth0 FGA: Mengatasi "New Enemy Problem"

Dua pemain utama dalam domain ini adalah SpiceDB dan OpenFGA. Perbedaan krusial terletak pada model konsistensi:

* SpiceDB: Menggunakan ZedTokens untuk menyelesaikan "New Enemy Problem". Ini memastikan bahwa pengecekan izin mencerminkan data terbaru, mencegah akses yang baru saja dicabut tetap terbuka karena caching yang tidak konsisten. SpiceDB mendukung mode fully_consistent untuk operasi keamanan kritis.
* OpenFGA (Auth0 FGA): Menawarkan kemudahan integrasi REST-first namun memiliki model konsistensi yang lebih rileks (HIGHER_CONSISTENCY) dibandingkan sistem token presisi SpiceDB.

Perbandingan Sintaksis ReBAC

Fitur	SpiceDB (Arrow Operator)	OpenFGA (from Keyword)
Navigasi Hubungan	parent_folder->view	view from parent_folder
Operasi Set	+ (Union), & (Intersection)	or, and

Sinkronisasi dengan Transactional Outbox Pattern

Masalah "Dual-Write" (menulis ke database aplikasi dan sistem otorisasi secara terpisah) adalah risiko integritas terbesar. Kita akan menggunakan Transactional Outbox Pattern: aplikasi menulis data bisnis dan event otorisasi ke dalam satu transaksi database lokal. Worker asinkron kemudian meneruskan event tersebut ke SpiceDB/OpenFGA, menjamin konsistensi meskipun terjadi kegagalan jaringan.


--------------------------------------------------------------------------------


6. Infrastruktur dan Database: Visualisasi DBML dan Manajemen Data

Sinkronisasi antara skema database dan dokumentasi adalah kunci untuk mencegah pembusukan teknis.

DBML dan Visualisasi Real-Time

Kita menggunakan DBML (Database Markup Language) sebagai standar pendefinisian skema. Melalui ekstensi DBML di VS Code, arsitek data dapat melihat pratinjau ERD (Entity-Relationship Diagram) secara real-time. Ini memfasilitasi kolaborasi instan dan memastikan dokumentasi selalu akurat terhadap kode SQL yang dihasilkan.

Platform Engineering dan Golden Paths

Dalam arsitektur monolit modular, otonomi tim tetap dijaga melalui Platform Engineering. Kita menyediakan "Golden Paths"—jalur mandiri bagi tim untuk mengelola skema database mereka sendiri tanpa melanggar kebijakan keamanan global. Isolasi koneksi database per-modul dapat diterapkan jika beban kerja domain memerlukan performa yang berbeda, namun tetap dalam satu ekosistem orkestrasi yang kohesif.


--------------------------------------------------------------------------------


7. Ide Baru dan Inovasi Masa Depan

Arsitektur yang baik harus adaptif terhadap megatren AI dan efisiensi operasional.

* AI Validation via MCP: Memanfaatkan MCP Server untuk mengintegrasikan asisten AI langsung ke dalam proses validasi arsitektur C4 dan skema otorisasi secara otomatis.
* Serverless Complement: Menggunakan Serverless (seperti AWS Lambda) hanya untuk beban kerja yang didorong oleh peristiwa (event-driven) yang tidak menentu, melengkapi kestabilan Modular Monolith guna mengoptimalkan biaya idle.
* Keberlanjutan Sistem: Fokus pada otonomi tim, kejelasan batas logis, dan pemilihan alat berdasarkan bukti data (seperti penurunan penggunaan service mesh dan efisiensi monolit), bukan sekadar mengikuti tren industri.

Kesimpulan: Arsitektur yang kita bangun adalah tentang keseimbangan antara disiplin mikrolayanan dan kesederhanaan monolit. Dengan memperlakukan dokumentasi sebagai kode dan otorisasi sebagai hubungan, kita menciptakan sistem yang siap menghadapi skala masa depan tanpa mengorbankan kecepatan pengembang.

## Implementation Roadmap (Perbaikan Rencana)

Ringkasan: fokus pertama adalah menyelesaikan dan merapikan modul inti yang disepakati (Meeting Room, Vehicle, Inventory, Approval, RBAC), menghilangkan artefak legacy yang tidak lagi relevan (mis. subsystem tiket/helpdesk yang terfragmentasi), lalu menyiapkan infrastruktur lokal untuk pengembangan dan pengujian.

- **Scope - Keep:** Meeting Room, Vehicle, Inventory (ATK & sparepart), Multi-tier Approval, Notification, Reporting, User & Role Management.
- **Scope - Remove / Archive:** Legacy ticket/helpdesk views, controllers, routes, dan seeders yang tidak dipakai; old booking views; admin/system tooling endpoints that expose dangerous operations in production context.

Milestones (high-level):

1. Stabilize backend bootstrap
  - Tambah stub minimal services/repositories yang hilang untuk menghindari DI runtime errors.
2. Safe removal of legacy modules
  - Cari semua referensi `tickets.*`, `old-booking`, dan file terkait; simpan patch terpisah untuk penghapusan setelah review.
3. Refactor core modules to Controller-Service-Repository
  - Pastikan setiap controller tipis; bisnis logika di `app/Services`; DB akses di `app/Repositories`.
4. Seeders & Menu cleanup
  - Perbarui `database/seeders/MenuSeeder.php` dan `resources/views/layouts/partials/sidebar.blade.php` untuk mencerminkan scope baru.
5. Infrastructure (local)
  - Tambah `docker-compose.yml` untuk PHP-FPM, MySQL, Redis, RabbitMQ; pastikan artisan commands berjalan di container.
6. Frontend scaffolding
  - Scaffold React app (`frontend/`) with TypeScript + Redux and a minimal Portal page consuming API endpoints.
7. Tests & CI
  - Tambah feature tests untuk booking flow, approval flow, and inventory request; run with `phpunit` and headless JS tests for frontend.

First sprint (2 weeks) — immediate next steps:

- Run repository-wide search for `tickets`, `old-booking`, `getTicketConfig()` and list all files for removal review.
- Create a safety branch and apply removal patches in a single PR with a clear changelog entry.
- Implement missing minimal stubs discovered during bootstrap (already done for `DashboardService`/`MainPortalRepository`); convert stubs to real implementations.
- Update `docs/Project.md` (this section) and add a short README for local dev in the repo root.

Approval: before deleting any legacy code, create a review PR and keep the removed files archived in a branch or a zip to allow easy rollback.

