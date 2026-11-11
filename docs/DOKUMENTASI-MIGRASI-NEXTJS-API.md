# Master Implementation Plan & Dokumentasi Arsitektur: Migrasi TTC ERP ke Next.js + Laravel API

Dokumen ini menyajikan panduan teknis, arsitektur sistem, strategi migrasi aman tanpa *downtime* (**Strangler Fig / Parallel Run Pattern**), standar kontrak API, struktur folder proyek, serta tahapan eksekusi (*roadmap*) untuk memisahkan **Frontend (Next.js + TypeScript + Tailwind CSS + shadcn/ui)** dan **Backend API (Laravel 10 TTC Eksisting)**.

---

## 1. Mengapa Proyek TTC Saat Ini Sangat Siap untuk Dimigrasi?

Berdasarkan audit arsitektur kode pada repositori `c:\xampp\htdocs\ttc`, proyek ini memiliki keunggulan fondasi yang sangat kuat sehingga **tidak perlu menulis ulang (rewrite) logika bisnis dari nol**:

1. **Service Layer Sudah Terpisah (`app/Services/*`):**
   Lebih dari **90% logika bisnis inti** (transaksi database, penguncian stok `lockForUpdate()`, jurnal akuntansi COA, kalkulasi HPP Flexo, subkontrak, hingga pengiriman) sudah berada di dalam class Service murni yang tidak bergantung pada Filament UI:
   - **Costing & Marketing:** `FlexoHppCalculatorService`, `MarketingWorkflowService`, `SalesOrderService`
   - **Purchasing:** `PurchaseRequestService`, `PurchaseOrderService`, `PenerimaanBarangService`
   - **Inventory & Warehouse:** `StockService`, `FinishGoodStockService`, `IssueMaterialService`, `InternalReturnService`, `ExternalReturnService`
   - **Shop Floor & Production:** `ProductionService`, `IMRPrintingService`, `ImrFinishService`, `SubcontractService`
   - **Dispatch & Finance:** `SuratJalanService`, `InvoiceService`, `InvoicePaymentService`, `PoBillingService`, `PoBillPayService`, `TreasuryService`
   - **HRD & Payroll:** `PayrollService`
2. **Laravel Sanctum & Spatie Permission Sudah Terinstal:**
   - Di `composer.json`, `laravel/sanctum` (`^3.3`) sudah terpasang dan `User.php` sudah memakai trait `HasApiTokens` dan `HasRoles`.
   - Seluruh 65+ Policy di `app/Policies/*` sudah siap digunakan langsung oleh Controller API melalui `$this->authorize('viewAny', Model::class)`.
3. **Engine Cetak PDF Sudah Berdiri Sendiri:**
   - Seluruh 11 Controller PDF di `app/Http/Controllers/*PdfController.php` (`InvoicePdfController`, `SuratJalanPdfController`, `OrderConfirmationPdfController`, `BeritaAcaraPdfController`, dll.) dapat langsung dipanggil melalui endpoint API untuk mengembalikan *stream* PDF ke browser Next.js.

---

## 2. Strategi Migrasi Paling Aman: *Strangler Fig Pattern* (Dual-UI / Parallel Run)

> [!IMPORTANT]
> **Jangan pernah mematikan Filament Admin (`/admin`) sebelum modul di Next.js benar-benar lolos uji operasional.**
> Gunakan metode **Strangler Fig Pattern**: Biarkan Filament (`/admin`) dan Next.js berjalan **berdampingan** mengakses database dan `app/Services` yang sama.

### Keuntungan Strategi Ini:
1. **Zero Data Migration Risk:** Database MySQL tidak berubah; data yang diinput dari Next.js detik itu juga terlihat di Filament, begitu pula sebaliknya.
2. **Migrasi Modul per Modul (Bertahap):** Tim (kamu & Juan) bisa memindahkan modul **Marketing** terlebih dahulu ke Next.js, sementara **Warehouse, Produksi, dan Finance** masih memakai Filament. Setelah Marketing stabil, lanjut ke Warehouse, dst.
3. **Instant Rollback:** Jika ada bug UI di halaman Next.js pada suatu modul, user masih bisa membuka `/admin` (Filament) kapan saja tanpa menghentikan operasional pabrik.

---

## 3. Spesifikasi Tech Stack & Library Pendukung (Frontend & Backend)

### A. Frontend Stack (`ttc-web-next`)
| Layer | Teknologi / Library | Fungsi Spesifik di ERP TTC |
| :--- | :--- | :--- |
| **Framework** | **Next.js (App Router)** + **React 19** | Routing modular, layout bersarang (Sidebar ERP), optimasi bundle, & *client-side navigation* instan. |
| **Language** | **TypeScript (Strict Mode)** | Menjamin keamanan tipe data nominal uang (`Rp`), kuantitas desimal (`kg`, `meter`, `pcs`), dan status dokumen. |
| **UI System** | **Tailwind CSS** + **shadcn/ui** + **Lucide Icons** | Komponen UI cepat, ringan, *accessible* (Radix UI), mendukung Dark/Light Mode seperti Filament. |
| **Data Fetching & Cache** | **TanStack Query v5 (React Query)** + **Axios** | Menyimpan cache data tabel di RAM browser (*stale-while-revalidate*), *optimistic updates*, dan *auto-retry*. |
| **Data Table** | **TanStack Table v8** | Menggantikan tabel Filament: *server-side pagination*, *multi-column sorting*, *column visibility*, *row selection*, & *sticky header*. |
| **Form & Repeater** | **React Hook Form** + **Zod** (`useFieldArray`) | Menggantikan *Wizard* & *Repeater* Filament yang berat di Livewire. Dengan `useFieldArray`, tambah/hapus 50 baris item BOM/OC terjadi dalam **0 ms** di sisi browser tanpa *network roundtrip* ke server! |
| **Global Client State** | **Zustand** | Menyimpan state ringan di client: *sidebar collapsed*, profil user aktif, daftar *permission* RBAC, dan *theme*. |
| **Charts & Visualisasi** | **Recharts** (via shadcn/ui chart) | Menggantikan widget grafik Pemasukan, Pengeluaran, dan Monitoring Produksi SPK. |

### B. Backend API Stack (`c:\xampp\htdocs\ttc`)
| Layer | Teknologi / Fitur Laravel | Fungsi Spesifik |
| :--- | :--- | :--- |
| **Authentication** | **Laravel Sanctum** (SPA Cookie Auth / Bearer Token) | Mendukung login aman dari Next.js (`POST /api/v1/auth/login`) dan proteksi route `auth:sanctum`. |
| **Authorization (RBAC)** | **Spatie Permission** + **Policies** (`app/Policies/*`) | Mengirim daftar permission user ke Next.js saat login & memvalidasi hak akses di setiap endpoint API. |
| **Request Validation** | **Laravel FormRequest** (`app/Http/Requests/Api/V1/*`) | Memindahkan validasi dari form Filament ke layer HTTP Request standar. |
| **Response Formatting** | **Eloquent API Resources** (`app/Http/Resources/V1/*`) | Menstandarkan output JSON (termasuk *computed attributes* seperti `current_stock`, `sisa_tagihan`, `fulfillment`). |

---

## 4. Daftar Endpoint API V1 yang Sudah Aktif di Backend TTC (`/api/v1/*`)

| Method | Endpoint URI | Controller | Fungsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | `Api\V1\AuthController@login` | Login user & terbitkan Sanctum Bearer Token + daftar Role & Permission Spatie |
| `GET` | `/api/v1/auth/me` | `Api\V1\AuthController@me` | Ambil profil user aktif beserta seluruh Role & Permission |
| `POST` | `/api/v1/auth/logout` | `Api\V1\AuthController@logout` | Cabut token Sanctum aktif |
| `GET` | `/api/v1/dashboard/summary` | `Api\V1\DashboardController@summary` | Ringkasan KPI Finance, Grafik Bulanan, & Monitoring SPK Produksi |
| `POST` | `/api/v1/costing/flexo-hpp/calculate` | `Api\V1\Costing\HppCalculatorController@calculate` | Kalkulasi HPP & Kertas Flexo Zonten via `FlexoHppCalculatorService` |
| `GET` | `/api/v1/warehouse/material-stocks` | `Api\V1\Warehouse\MaterialStockController@index` | Daftar Stok Material (On Hand, Allocation, Outstanding) + Search & Pagination |
| `GET` | `/api/v1/warehouse/material-stocks/{id}/track` | `Api\V1\Warehouse\MaterialStockController@track` | Detail Kartu Stok / Mutasi Material |
| `GET` | `/api/v1/warehouse/finish-good-stocks` | `Api\V1\Warehouse\FinishGoodStockController@index` | Daftar Stok Barang Jadi (On Hand, Blocked) + Search & Pagination |
| `GET` | `/api/v1/warehouse/finish-good-stocks/{id}/track` | `Api\V1\Warehouse\FinishGoodStockController@track` | Ringkasan Packaging, Subcount In, Surat Jalan & 15 Mutasi Terakhir |
| `POST` | `/api/v1/warehouse/finish-good-stocks/{id}/adjust` | `Api\V1\Warehouse\FinishGoodStockController@adjust` | Penyesuaian Stok Barang Jadi (Stock Opname) via `FinishGoodStockService` |
| `GET` | `/api/v1/warehouse/finish-good-ledgers` | `Api\V1\Warehouse\FinishGoodStockController@ledgers` | Riwayat Kartu Stok Barang Jadi (Pagination & Filter) |

---

## 5. Roadmap Implementasi Bertahap (Phase-by-Phase Execution Plan)

1. **Fase 0 — Fondasi Backend API & Auth (Selesai Diterapkan):**
   - Konfigurasi CORS (`config/cors.php`), `AuthController`, `DashboardController`, `HppCalculatorController`, `MaterialStockController`, dan `FinishGoodStockController`.
2. **Fase 1 — Inisialisasi Project Next.js & Core UI Kit:**
   - Setup Next.js + TypeScript + Tailwind + shadcn/ui, AppSidebar 13 Grup Navigasi TTC, Reusable TanStack Table, Login Page, dan Dashboard.
3. **Fase 2 — Modul Master Data & Manajemen Akses:**
   - Customer, Supplier, Satuan, Category/Type Item, Master Item, Finish Good Item (BOM), Users & Roles.
4. **Fase 3 — Modul Marketing (End-to-End):**
   - Approval Form (AF), Order Confirmation (OC) + Modal Kalkulator HPP Flexo, Sales Order (SO).
5. **Fase 4 — Modul Warehouse & Purchasing:**
   - Material Stocks, Finish Good Stocks, PR, PO, Penerimaan Barang, Pengeluaran Material, Return.
6. **Fase 5 — Modul PPIC, Produksi & Subcount:**
   - Master SPK, Pra Cetak, Printing, IMR Printing, Finishing, IMR Finish, Packaging, Subcount Out & Subcount In.
7. **Fase 6 — Modul Dispatch, Finance & HRD:**
   - Surat Jalan, Berita Acara, Invoice, Inv Payment, PO Billing, PO Bill Pay, Master COA, Kas/Bank, Report, HRD/Payroll.
8. **Fase 7 — Cutover & Decommissioning Filament:**
   - Uji paralel selesai dan transisi penuh ke Next.js.

