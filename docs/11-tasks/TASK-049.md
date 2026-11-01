# TASK-049 — Gudang, katalog barang dan paket kebijakan gudang DRAFT

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M5 — Gudang
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan master gudang (ALP, PSRT) melalui WF-029 F7, katalog barang dan barang per gudang, pemilih gudang di ruang kerja Gudang, grant bercakupan WAREHOUSE, serta skema dan isi DRAFT `warehouse.operations.alp/psrt` dan `inventory.codes` berlabel simulasi.

## WHY THIS EXISTS

OD-25/26/31: satu kemampuan multi-gudang dengan pemisahan per gudang.

## DEPENDS ON

- TASK-018
- TASK-017
- TASK-014

## BLOCKS

- TASK-050

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Konfirmasi ruang kerja Gudang
- GAP terkait (GAP-005, GAP-025) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `inv_warehouses`, `inv_items`, `inv_warehouse_items`
- Permintaan perubahan master gudang (F7) dengan gerbang GAP-025
- Ruang kerja Gudang + pemilih gudang
- Skema kebijakan gudang (jenis permintaan, rantai keputusan, bukti, penutupan, pemeriksaan pengembalian, transfer, penyesuaian) + isi DRAFT dari pengamatan SRC-058 berlabel tidak resmi
- PG-210 katalog barang
- PG-510 master gudang di Administrasi (ajukan perubahan F7)

## OUT OF SCOPE

- Ledger (TASK-050)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-029](../03-workflows/WORKFLOW_CONTRACTS.md#wf-029)
- [DATA_MODEL §15](../04-architecture/DATA_MODEL.md#15-inventory)
- [POLICY_REGISTRY §5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi)
- [OD-26](../00-governance/DECISION_LOG.md#od-26)

## TRACEABILITY

- CAP: CAP-14, CAP-21
- AC/NFR: AC-17, AC-28, AC-31
- WF/TR: WF-029 (TR-281–TR-288, 8 TR; BLOCKED_BY_GAP: TR-285)
- RT: RT-023, RT-032
- IX: IX-001, IX-002, IX-016
- GAP: GAP-005, GAP-025
- Keputusan Owner: OD-25, OD-26, OD-31

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Inventory`
- `resources/js/pages/warehouse/items`

## BUSINESS RULES

- [BR-030](../02-domain/BUSINESS_RULES.md#br-030)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-019](../02-domain/BUSINESS_RULES.md#inv-019)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-10
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `inv_warehouses`, `inv_items`, `inv_warehouse_items`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Inventory
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: N/A
- Replaceable adapter/contract required: NO
- Provider lock-in risk: NONE
- Future extension impact: LOW
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

- Context7: OPTIONAL (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Inventory, resources/js/pages/warehouse/items

## CONTEXT7

- Topics to verify: —
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
- Halaman: PG-201, PG-210, PG-510
- Primary action: Tambah barang
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Pemilih gudang di header

## ROUTE IMPACT

- RT-023
- RT-032

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-016

## NAVIGATION CONTRACT

- Parent: Gudang
- Entry: Menu Katalog
- Back action: Daftar barang
- Cancel: Kembali ke halaman asal tanpa menyimpan; konfirmasi bila ada perubahan belum tersimpan
- Success destination: Detail barang
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Gudang terpilih tersimpan
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Petugas dengan grant gudang ALP saja tidak melihat PSRT di mana pun
- TAC-02 Paket kebijakan gudang DRAFT tervalidasi skema dan berlabel simulasi
- TAC-03 Barang dapat diaktifkan per gudang

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Master gudang dan scope
- Authorization: AUTHZ-10
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Bagian J-11
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB

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

STANDARD — swa-review terstruktur + review PR otomatis (Codex); lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.2 UI
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

- TASK-050

## DO NOT DO

- Jangan menjadikan SRC-058 aturan ACTIVE
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
