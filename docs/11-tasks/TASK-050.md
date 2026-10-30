# TASK-050 — Ledger stok, saldo dan kartu stok

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M5 — Gudang
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan `inv_stock_movements` append-only dan `inv_stock_balances` dengan CHECK tidak negatif, layanan posting bertransaksi dengan urutan kunci, record pembalik, job verifikasi saldo harian, dan kartu stok per barang per gudang.

## WHY THIS EXISTS

INV-009/019/020 dan ADR-0004.

## DEPENDS ON

- TASK-049
- TASK-009

## BLOCKS

- TASK-051
- TASK-052
- TASK-055
- TASK-056

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-005, GAP-006, GAP-025) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel ledger dan saldo + trigger/hak append-only; kolom `is_simulation` dan `policy_version_id`
- Aturan pencatatan terlambat: diterima hanya bila saldo menurut `occurred_at` tetap ≥ 0 pada titik itu dan setiap titik sesudahnya; OPENING hanya sebelum gerakan lain item–gudang
- `StockPostingService` (kunci terurut, `balance_after`, kebijakan) dengan identitas sumber unik di DB `(source_type, source_id, source_line_id, movement_type)`
- Kerangka pemeriksa sumber: `CHECK` jenis sumber terdaftar dan constraint trigger per jenis sumber yang dipasang Task pemilik tabel sumber
- Pembalikan penuh tunggal (`uq(reversal_of_id)`, `check_stock_reversal()`) dengan sumber koreksi dan alasan wajib; koreksi sebagian sebagai entri koreksi tersendiri
- Job verifikasi saldo vs ledger → insiden
- PG-206 stok dan kartu stok (keyset)

## OUT OF SCOPE

- Alur permintaan/penerimaan (Task berikut)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DATA_MODEL §15](../04-architecture/DATA_MODEL.md#15-inventory)
- [CONCURRENCY_AND_INTEGRITY §2–4](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md)
- [ADR-0004](../adr/ADR-0004-append-only-ledgers.md)
- [WF-023](../03-workflows/WORKFLOW_CONTRACTS.md#wf-023)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-14, CAP-21
- AC/NFR: AC-17, AC-28
- WF/TR: WF-023 (TR-221–TR-226, 6 TR; BLOCKED_BY_GAP: TR-224)
- RT: RT-023
- IX: IX-012
- GAP: GAP-005, GAP-006, GAP-025
- Keputusan Owner: OD-30

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Inventory/Ledger`
- `resources/js/pages/warehouse/stock`

## BUSINESS RULES

- [BR-019](../02-domain/BUSINESS_RULES.md#br-019)
- [BR-030](../02-domain/BUSINESS_RULES.md#br-030)
- [BR-033](../02-domain/BUSINESS_RULES.md#br-033)
- [INV-009](../02-domain/BUSINESS_RULES.md#inv-009)
- [INV-010](../02-domain/BUSINESS_RULES.md#inv-010)
- [INV-019](../02-domain/BUSINESS_RULES.md#inv-019)
- [INV-020](../02-domain/BUSINESS_RULES.md#inv-020)

## ARCHITECTURE RULES

- Satu-satunya jalur ubah saldo adalah StockPostingService
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Peran app tanpa UPDATE/DELETE ledger
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `inv_stock_movements`, `inv_stock_balances`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Inventory/Ledger
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: N/A
- Replaceable adapter/contract required: NO
- Provider lock-in risk: NONE
- Future extension impact: MEDIUM
- Speculative abstraction avoided: YES

## AUTH IMPACT

- Authentication change: NONE
- Authentication implementation: N/A
- Auth profile: N/A
- Google sign-in: OFF (OD-11/12)
- Google registration behavior: N/A
- Google credentials owner action required: NO
- Authorization change: PROPOSED (kapabilitas/aturan modul ditambahkan ke katalog ACCESS_CONTROL dan diuji)
- Provider/library-specific leakage into domain: NO

## COST IMPACT

- New recurring cost: NONE
- Paid third-party service: NO
- Free/open-source alternative: N/A
- Estimated additional monthly production cost: 0
- Owner approval required: NO

## MCP SESSION PROFILE

- Required MCP servers: tidak ada; Context7 bila tersedia untuk topik versi
- MCP servers that should remain OFF: cloud/infra, Figma, basis data jarak jauh, browser MCP (E2E memakai paket Playwright proyek)
- Native CLI/package preferred over MCP: YES (artisan, composer, npm, Playwright, gh)
- Post-Task deactivation/cleanup: tutup proses/kontainer dev yang dimulai Task; jangan sentuh proyek lain

## TOOL REQUIREMENTS

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Inventory/Ledger, resources/js/pages/warehouse/stock

## CONTEXT7

- Topics to verify: PostgreSQL row locking order
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- Follow global design rules: YES ([DESIGN_SYSTEM](../07-ux-design/DESIGN_SYSTEM.md), [INTERACTION_PATTERNS](../07-ux-design/INTERACTION_PATTERNS.md))
- Indonesian default: YES
- English support: YES
- Plain-language Indonesian: YES
- Language switch impact: konteks dan input dipertahankan
- Mobile-first: YES
- Responsive: YES
- Adaptive: YES
- Visual breathing room: REQUIRED
- Comfortable density: REQUIRED
- shadcn/ui reuse: primitif shadcn + komposit PRANATA
- Design reference: SRC-047/VIS-04 melalui DESIGN_SYSTEM; OD-34
- Halaman: PG-206
- Primary action: —
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Kartu stok dengan saldo berjalan

## ROUTE IMPACT

- RT-023

## INTERACTION CONTRACTS

- IX-012

## NAVIGATION CONTRACT

- Parent: Gudang → Stok
- Entry: Menu Stok
- Back action: Daftar stok (filter terjaga)
- Cancel: Tidak berlaku
- Success destination: Tidak berlaku
- Error behavior: ErrorState dengan request id dan langkah aman berikutnya
- State preservation: Filter, urutan, tab dan cursor di URL; dipulihkan saat kembali
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Dua posting bersamaan atas barang terakhir: satu sukses, satu ditolak; saldo tidak negatif
- TAC-02 UPDATE/DELETE ledger ditolak
- TAC-03 Saldo = jumlah ledger (job verifikasi); selisih membuat insiden
- TAC-04 Kartu stok diurutkan `(occurred_at, id)` dengan saldo berjalan dihitung saat kueri; `balance_after` mengikuti urutan posting untuk pemeriksaan integritas
- TAC-05 Pencatatan terlambat yang membuat saldo per tanggal negatif ditolak
- TAC-06 Gerakan berdasar versi kebijakan yang tidak berlaku resmi bertanda simulasi
- TAC-07 Posting kedua untuk identitas sumber yang sama ditolak walaupun kunci idempotensi berbeda (DI-09)
- TAC-08 Pembalikan ganda, tidak cocok, tanpa sumber koreksi atau alasan, atas pembalikan, atau yang membuat saldo negatif ditolak (DI-10)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Posting, pembalikan, verifikasi; DI-09, DI-10 (uji negatif langsung DB)
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: Hak peran
- Other: Uji konkurensi dua proses: barang terakhir, posting sumber ganda, pembalikan ganda

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [ ] Lint
- [ ] Typecheck
- [ ] Relevant automated tests
- [ ] Authorization
- [ ] Route integrity
- [ ] Interaction behavior
- [ ] Browser E2E if required
- [ ] Responsive/adaptive if required
- [ ] Visual breathing room/density if UI
- [ ] Production build
- [ ] Security checks if required
- [ ] Database safety review
- [ ] Final diff review

## REVIEW REQUIREMENT

HIGH — swa-review terstruktur + review PR otomatis (Codex) + review independen kedua; lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.2 UI + §2.3 HIGH
- PR ke `main` lulus `ci-gate` pada HEAD terakhir dan review sesuai REVIEW REQUIREMENT, di-merge, lalu `main` remote diverifikasi memuat merge tersebut ([EXECUTOR_OPERATING_MODEL §8](EXECUTOR_OPERATING_MODEL.md#8-merge-dan-rekonsiliasi-done))
- TASK_PLAN dan CURRENT_HANDOFF diperbarui; checklist dicentang hanya bila DONE

## COMPLETION EVIDENCE

- Test outputs:
- Build result:
- Route evidence:
- Interaction evidence:
- Browser evidence:
- Localization evidence:
- Review status (Codex / independen, temuan P0–P3):
- Cost evidence/exception if applicable:
- Files changed:
- PR / CI run:
- Commit SHA:
- Notes:

## SAFE NEXT TASK

- TASK-051
- TASK-052

## DO NOT DO

- Jangan mengubah saldo langsung
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
