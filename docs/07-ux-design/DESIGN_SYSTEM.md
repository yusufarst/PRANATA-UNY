# Design system — PRANATA UNY

Status: P7 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P7 | Dasar: [OD-21](../00-governance/DECISION_LOG.md#od-21), [OD-33](../00-governance/DECISION_LOG.md#od-33), [OD-34](../00-governance/DECISION_LOG.md#od-34), [PRODUCT_EXPERIENCE_DIRECTION](../01-product/PRODUCT_EXPERIENCE_DIRECTION.md)

Dokumen ini memiliki **token visual, tipografi, ruang, bentuk, ikon, gerak, inventaris komponen, presentasi status dan syarat aksesibilitas**. Informasi arsitektur dan halaman dimiliki [INFORMATION_ARCHITECTURE](INFORMATION_ARCHITECTURE.md); pola interaksi dimiliki [INTERACTION_PATTERNS](INTERACTION_PATTERNS.md); bahasa dimiliki [LOCALIZATION](LOCALIZATION.md). Seluruh Task frontend mewarisi dokumen ini.

## 1. Arah desain

PRANATA terasa **putih, bersih, lapang, tenang dan profesional**, dengan Deep Navy sebagai identitas yang dikenali dan Ice Blue sebagai kanvas pendukung (OD-34). Dari referensi utama SRC-047/VIS-04 diterjemahkan: shell dengan sidebar tetap, kartu modular dengan hierarki tenang, satu kartu gelap yang menonjol untuk informasi terpenting, pencarian berbentuk palette, detail dengan panel konteks, dan gerak yang membantu orientasi — **tanpa menyalin warna, nama atau data referensi**.

| Prinsip | Penerapan |
|---|---|
| Kerja dulu | Setiap halaman menjawab "apa yang perlu saya kerjakan sekarang" melalui kartu Next-Action, inbox dan antrean |
| Putih mendominasi area kerja | Kartu, tabel, form, dialog dan panel memakai permukaan putih; Ice Blue hanya untuk kanvas, area sekitar dan bagian pendukung |
| Navy untuk identitas dan aksi utama | Sidebar aktif, tombol utama, judul kartu menonjol; tidak ada permukaan gelap berlebihan |
| Data mudah dipindai | Tabel padat-nyaman, angka rata kanan dengan angka tabular, kode memakai monospace |
| Status jujur | Status selalu teks + ikon + warna; simulasi, blokir dan ketidakpastian dinyatakan |
| Tenang | Tanpa gradien dekoratif, tanpa animasi yang mengganggu, aksen warna terbatas |

## 2. Token warna

### 2.1 Merek dan permukaan

| Token | Hex | Pemakaian |
|---|---|---|
| `brand-950` (primary) | `#172554` | Tombol utama, indikator navigasi aktif, kartu sorot, judul merek |
| `brand-900` | `#1E3A8A` | Hover/pressed tombol utama |
| `brand-800` | `#1E40AF` | Teks info pada latar terang |
| `brand-700` | `#1D4ED8` | Tautan, ikon aktif |
| `brand-600` | `#2563EB` | Cincin fokus, elemen interaktif sekunder |
| `brand-200` | `#BFDBFE` | Border elemen terpilih, sorot baris aktif |
| `brand-100` | `#DBEAFE` | Latar badge info, hover item navigasi |
| `canvas` (Ice Blue) | `#EFF6FF` | Latar halaman, area di sekitar konten, bagian pendukung |
| `surface` (Pure White) | `#FFFFFF` | Kartu, tabel, form, dialog, panel data, antarmuka dokumen |
| `surface-subtle` | `#F8FAFC` | Header tabel, baris zebra opsional, area input nonaktif |
| `border` | `#E2E8F0` | Border kartu/tabel/input |
| `border-strong` | `#CBD5E1` | Pemisah tegas, border input saat hover |

Skala merek sama dengan skala biru Tailwind sehingga mudah dipakai, tetapi nilai hex di atas ditetapkan eksplisit sebagai variabel CSS agar warna Owner terjamin tepat.

### 2.2 Teks

| Token | Hex | Kontras pada putih | Kontras pada `canvas` |
|---|---|---|---|
| `text-primary` | `#0F172A` | 17,9:1 | 16,4:1 |
| `text-secondary` | `#334155` | 10,4:1 | 9,5:1 |
| `text-muted` | `#475569` | 7,6:1 | 7,0:1 |
| `text-on-brand` | `#FFFFFF` di atas `#172554` | 14,7:1 | — |
| `link` | `#1D4ED8` | 6,7:1 | 6,2:1 |

Teks abu yang lebih terang dari `#475569` tidak dipakai untuk teks yang perlu dibaca; misalnya `#64748B` hanya 4,4:1 di atas `canvas`. Cincin fokus `#2563EB` memiliki 5,2:1 pada putih dan 4,7:1 pada `canvas` (syarat non-teks 3:1). Nilai kontras adalah perhitungan perencanaan; implementasi memverifikasinya dengan uji otomatis.

### 2.3 Semantik

| Makna | Teks | Latar | Ikon (lucide) | Kontras teks/latar |
|---|---|---|---|---|
| Sukses/selesai | `#047857` | `#ECFDF5` | `circle-check` | 5,2:1 |
| Peringatan/menunggu | `#92400E` | `#FFFBEB` | `clock` / `triangle-alert` | 6,8:1 |
| Bahaya/ditolak | `#B91C1C` | `#FEF2F2` | `circle-x` / `octagon-alert` | 5,9:1 |
| Informasi | `#1E40AF` | `#EFF6FF` | `info` | 8,0:1 |
| Netral/draf | `#334155` | `#F1F5F9` | `file-pen` | 9,5:1 |
| Terblokir kebijakan/GAP | `#5B21B6` | `#F5F3FF` | `lock` | 8,2:1 |
| Simulasi | `#78350F` | `#FEF3C7` | `flask-conical` | 8,1:1 |

### 2.4 Visualisasi data

Grafik memakai urutan seri: `#172554`, `#2563EB`, `#0EA5E9`, `#10B981`, `#F59E0B`, `#64748B`; selalu disertai label/legenda teks dan tabel alternatif yang dapat diakses. Tidak ada informasi yang hanya dibedakan warna.

### 2.5 Pemetaan ke token shadcn/ui (Tailwind CSS 4)

| Variabel shadcn | Nilai |
|---|---|
| `--background` | `canvas` |
| `--foreground` | `text-primary` |
| `--card`, `--popover` | `surface` |
| `--primary` / `--primary-foreground` | `brand-950` / `#FFFFFF` |
| `--secondary` / `--secondary-foreground` | `brand-100` / `brand-950` |
| `--muted` / `--muted-foreground` | `surface-subtle` / `text-muted` |
| `--accent` / `--accent-foreground` | `brand-100` / `brand-950` |
| `--destructive` | `#B91C1C` |
| `--border`, `--input` | `border` |
| `--ring` | `brand-600` |
| `--sidebar` / `--sidebar-primary` | `surface` / `brand-950` |

Variabel didefinisikan sekali di CSS global (`@theme` Tailwind 4) dan dipakai melalui kelas utilitas; tidak ada warna hex lepas di komponen.

## 3. Tipografi

| Unsur | Aturan |
|---|---|
| Keluarga utama | **Plus Jakarta Sans** (variable, lisensi SIL OFL 1.1), di-host sendiri melalui paket npm, tanpa CDN font eksternal |
| Monospace | **JetBrains Mono** (OFL) untuk kode, nomor dokumen, ID dan nilai teknis |
| Angka | `font-variant-numeric: tabular-nums` pada tabel, uang dan kuantitas (dukungan figur tabular Plus Jakarta Sans v2.600+ diverifikasi saat TASK desain) |
| Skala (rem/px) | display 1,875/30 semibold; h1 1,5/24 semibold; h2 1,25/20 semibold; h3 1,125/18 semibold; body 0,9375/15 regular; body-sm 0,875/14; caption 0,8125/13; minimal teks informatif 12px |
| Tinggi baris | 1,5 untuk teks; 1,25 untuk judul; tabel 1,4 |
| Berat | 400, 500, 600; 700 hanya untuk angka KPI |

## 4. Ruang, tata letak dan bentuk

| Unsur | Aturan |
|---|---|
| Satuan ruang | Basis 4 px: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64 |
| Shell | Sidebar 264 px (desktop), dapat diciutkan ke 72 px; header 64 px; konten maksimum 1440 px dengan padding 24–32 px |
| Kartu | Padding 20–24 px; jarak antar kartu 16–24 px; radius 12 px; border `border` 1 px; bayangan sangat halus (`0 1px 2px rgba(15,23,42,0.06)`) |
| Input dan tombol | Tinggi 40 px (36 px pada mode padat tabel); radius 8 px; target sentuh minimal 44 px pada mobile, 24 px minimum desktop (WCAG 2.5.8) |
| Badge/pill | Radius penuh; tinggi 22–24 px |
| Kepadatan | Default nyaman; tabel besar menyediakan mode padat yang diingat per pengguna |
| Breakpoint | sm 640, md 768, lg 1024, xl 1280, 2xl 1536 (bawaan Tailwind) |

## 5. Ikon dan ilustrasi

Ikon `lucide-react`, ukuran 16/20/24, stroke 1,75–2. Aksi toolbar memakai ikon + label kecuali aksi universal (cari, tutup, menu) yang tetap memiliki `aria-label`. Ilustrasi kosong memakai gaya garis sederhana bernuansa navy/ice blue; tidak ada aset pihak ketiga yang dilindungi.

## 6. Gerak

| Token | Nilai | Pemakaian |
|---|---|---|
| `motion-fast` | 120 ms | Hover, tekan, toggle |
| `motion-base` | 200 ms | Dropdown, tooltip, toast |
| `motion-panel` | 240 ms | Sheet/panel konteks, dialog |
| `motion-page` | 280 ms | Transisi konten halaman (opacity + translate 4–8 px) |
| Easing | `cubic-bezier(0.2, 0, 0, 1)` masuk; `cubic-bezier(0.4, 0, 1, 1)` keluar | |

Reveal bertahap hanya untuk kartu dashboard (maks. 6 elemen, jeda 30 ms). Tabel, form dan hasil perintah tidak dianimasikan secara dekoratif. `prefers-reduced-motion: reduce` mematikan translate/stagger dan mempertahankan perubahan opacity instan; informasi status tetap sama.

## 7. Inventaris komponen

### 7.1 Primitif shadcn/ui yang dipakai

Button, Input, Textarea, Select, Combobox (Command + Popover), Checkbox, RadioGroup, Switch, Form + Label, DatePicker (Calendar + Popover), Dialog, AlertDialog, Sheet, Drawer (mobile), Tabs, Table, Card, Badge, Alert, Sonner (toast), Tooltip, DropdownMenu, Breadcrumb, Pagination, Skeleton, Progress, Separator, ScrollArea, Sidebar, Avatar, Accordion, HoverCard, Command (palette), Chart. Primitif baru hanya dibuat bila shadcn/ui tidak menyediakan padanannya.

### 7.2 Komposit PRANATA

| Komponen | Fungsi |
|---|---|
| `AppShell` | Sidebar per ruang kerja, header (pencarian, inbox, bahasa, akun), konten |
| `WorkspaceSwitcher` | Menampilkan hanya ruang kerja yang diizinkan; mempertahankan konteks bila memungkinkan |
| `PageHeader` | Judul, breadcrumb, status objek, aksi utama tunggal + aksi sekunder dalam menu |
| `NextActionCard` | Pemilik tindakan, pihak yang ditunggu, tindakan berikut, bukti yang kurang, tenggat bila tervalidasi, alasan blokir/GAP |
| `StatusBadge` | Teks + ikon + warna dari kategori status |
| `StateTimeline` | Riwayat transisi (kode TR tersembunyi bagi pengguna, tersedia di detail audit) |
| `DataTable` | Tabel server-driven: filter, sort whitelist, keyset pagination, pilihan kolom, mode padat, ekspor sesuai filter (Filter-First) |
| `FilterBar` + `SavedViews` | Filter dengan chip, simpan tampilan per pengguna |
| `MoneyCell` / `QuantityCell` / `CodeCell` | Format Indonesia, rata kanan, angka tabular, monospace untuk kode |
| `KeyValueList` | Atribut ringkas pada panel detail |
| `EvidenceList` + `FileDropzone` | Bukti terkait, unggah dengan validasi, pratinjau aman; aksi lepas hanya untuk tautan tidak bersegel yang diizinkan modul pemilik — lampiran kiriman penawaran ditampilkan terkunci tanpa aksi lepas atau ganti |
| `DocumentCard` | Status dokumen (draf/final/terbit/bertanda tangan/simulasi), aksi generate/unduh/unggah tanda tangan |
| `ConfirmActionDialog` | Konfirmasi aksi material dengan ringkasan akibat dan kolom alasan bila wajib |
| `ConflictDialog` | Konflik versi: tampilkan perubahan terbaru, pilih muat ulang |
| `SimulationBanner` | Banner tetap "Simulasi — kebijakan belum disahkan" pada halaman/keluaran terkait |
| `BlockedReasonPanel` | Penjelasan aksi yang belum tersedia: kebijakan/GAP, siapa yang perlu memutuskan, langkah aman |
| `OperationStatus` | Status operasi panjang: diterima, berjalan (progres/elapsed), selesai, gagal + tindak lanjut |
| `EmptyState` / `ErrorState` / `ForbiddenState` / `NotFoundState` | Keadaan wajib dengan teks sederhana dan aksi lanjut |
| `StepperForm` | Formulir bertahap untuk alur panjang (paket, HPS) dengan draf tersimpan |
| `EvaluationMatrix` | Matriks peserta × tahap/kriteria per lingkup dengan sel berstatus teks + ikon, navigasi keyboard, kolom beku dan penanda bukti ([INTERACTION_PATTERNS §10](INTERACTION_PATTERNS.md#10-ruang-kerja-evaluasi)) |
| `CriterionPanel` + `OfferViewer` | Persyaratan dan sumbernya, temuan, rujukan bukti; penampil berkas penawaran baca-saja berwatermark dan teraudit |
| `ScopeTabs` + `SelectionCountsStrip` | Tab lingkup penghargaan dan strip jumlah dijajaki/diundang/menawar/memenuhi syarat/dievaluasi |
| `AwardScopeCard` | Hasil satu lingkup: peringkat, pemenang, cadangan, kegagalan beralasan, status reviu dan dokumen hilir |

## 8. Kategori status

| Kategori | Contoh status teknis | Label (id) | Gaya |
|---|---|---|---|
| Draf | DRAFT | Draf | Netral |
| Menunggu orang lain | SUBMITTED, IN_REVIEW, AWAITING_CONFIRMATION | Menunggu … | Peringatan |
| Perlu tindakan saya | (dari item pekerjaan) | Perlu tindakan Anda | Informasi tegas (navy) |
| Perlu perbaikan | RETURNED, REVISION_REQUESTED | Perlu perbaikan | Peringatan |
| Disetujui/ditetapkan | APPROVED, DETERMINED, SET | Disetujui / Ditetapkan | Sukses |
| Selesai | COMPLETED, SIGNED, POSTED | Selesai | Sukses |
| Ditolak/dibatalkan/ditarik | REJECTED, CANCELLED, WITHDRAWN | Ditolak / Dibatalkan / Ditarik | Bahaya/netral |
| Terblokir | (gerbang kebijakan/GAP) | Menunggu pengesahan kebijakan | Terblokir |
| Simulasi | is_simulation | Simulasi | Simulasi |

Label persis per workflow ditetapkan di katalog terjemahan dan mengikuti makna state P3.

## 9. Aksesibilitas (target WCAG 2.2 AA)

- Kontras teks ≥ 4,5:1 (teks besar ≥ 3:1), komponen/fokus ≥ 3:1.
- Fokus selalu terlihat (cincin 2 px `brand-600` + offset 2 px), urutan fokus logis, tidak ada jebakan fokus kecuali dialog modal yang mengembalikan fokus saat ditutup.
- Semua fungsi dapat dijalankan dengan keyboard; pintasan palette `Ctrl/⌘ K` dan `/` untuk pencarian tidak menimpa input.
- Label form eksplisit, pesan galat terhubung (`aria-describedby`), ringkasan galat di atas form panjang.
- Pesan status dan hasil operasi diumumkan melalui region `aria-live`.
- Target sentuh sesuai bagian 4; zoom 200% dan reflow 320 px tanpa kehilangan fungsi.
- Status tidak hanya warna; ikon dekoratif `aria-hidden`.
- Tabel memiliki header, caption tersembunyi untuk pembaca layar dan alternatif kartu pada layar kecil.
- `prefers-reduced-motion` dihormati.

Verifikasi: axe pada setiap halaman dalam E2E, pemeriksaan keyboard pada perjalanan kritis, dan audit manual sebelum UAT ([TEST_STRATEGY](../08-testing/TEST_STRATEGY.md)).

## 10. Mode gelap

Tidak dirilis pada V1 untuk menjaga fokus kualitas dan cakupan uji; token disusun sebagai variabel sehingga mode gelap dapat ditambahkan kelak tanpa mengubah komponen.

## 11. Yang dihindari

Permukaan gelap berlebihan, biru jenuh berlebihan, gradien dekoratif, animasi yang mengganggu, status yang hanya dibedakan warna, tombol tanpa fungsi, aksi ganda yang membingungkan, teks abu tipis di atas Ice Blue, dan kepadatan yang membuat tabel sulit dibaca.
