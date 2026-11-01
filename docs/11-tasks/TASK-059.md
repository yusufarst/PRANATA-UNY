# TASK-059 — Register aset, transaksi aset dan daftar skala besar

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M6 — Aset inti
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan `asset_assets` (proyeksi kini) dan `asset_transactions` append-only, layanan `AssetTransactionService`, daftar register server-driven untuk 1 juta baris dengan indeks dan keyset, detail aset bertab dan penyedia pencarian aset.

## WHY THIS EXISTS

CAP-11/18, AC-14/21, OD-16/22.

## DEPENDS ON

- TASK-058
- TASK-022

## BLOCKS

- TASK-060
- TASK-062
- TASK-063
- TASK-064
- TASK-065
- TASK-069
- TASK-070
- TASK-071
- TASK-073

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-008, GAP-011, GAP-016) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel dan indeks DATA_MODEL §16
- Layanan transaksi aset (sebelum/sesudah) dengan identitas sumber unik; pembalikan tunggal hanya atas transaksi terakhir aset yang belum dibalik (`check_asset_reversal()`), selain itu melalui koreksi WF-020
- PG-302 daftar (filter unit/status/klasifikasi/lokasi/kata kunci), PG-303 detail
- Scope unit subtree; isolasi dari Pengadaan (AUTHZ-03)
- Uji jumlah kueri dan EXPLAIN pada data representatif (≥ 100.000 baris dari factory)

## OUT OF SCOPE

- Alur mutasi/penghapusan (Task berikut)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DATA_MODEL §16](../04-architecture/DATA_MODEL.md#16-asset)
- [PERFORMANCE_AND_SCALE §2–3](../06-api-performance/PERFORMANCE_AND_SCALE.md#2-anggaran-performa)
- [RT-017](../03-workflows/ROUTE_CONTRACTS.md#rt-017)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-11, CAP-18
- AC/NFR: AC-14, AC-21, AC-31
- WF/TR: WF-017 (TR-161–TR-166, 6 TR; BLOCKED_BY_GAP: TR-164, TR-165)
- RT: RT-017, RT-030, RT-031
- IX: IX-012, IX-017
- GAP: GAP-008, GAP-011, GAP-016
- Keputusan Owner: OD-16, OD-22

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Asset`
- `resources/js/pages/assets`

## BUSINESS RULES

- [BR-014](../02-domain/BUSINESS_RULES.md#br-014)
- [BR-015](../02-domain/BUSINESS_RULES.md#br-015)
- [BR-026](../02-domain/BUSINESS_RULES.md#br-026)
- [INV-008](../02-domain/BUSINESS_RULES.md#inv-008)
- [INV-017](../02-domain/BUSINESS_RULES.md#inv-017)

## ARCHITECTURE RULES

- Proyeksi hanya diubah AssetTransactionService
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-03
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `asset_assets`, `asset_transactions`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Asset
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
- Expected affected areas: app/Modules/Asset, resources/js/pages/assets

## CONTEXT7

- Topics to verify: PostgreSQL GIN trigram, BRIN
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
- Halaman: PG-301, PG-302, PG-303
- Primary action: Cari/filter
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Tabel padat dengan angka tabular

## ROUTE IMPACT

- RT-017
- RT-030
- RT-031

## INTERACTION CONTRACTS

- IX-012
- IX-017

## NAVIGATION CONTRACT

- Parent: Aset → Register
- Entry: Menu
- Back action: Register (filter terjaga)
- Cancel: Tidak berlaku
- Success destination: Tidak berlaku
- Error behavior: ErrorState dengan request id dan langkah aman berikutnya
- State preservation: Filter/cursor di URL
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Rencana kueri daftar aset memakai indeks yang dirancang pada data representatif; PB-01 pada 1 juta aset dibuktikan di TASK-073
- TAC-02 Riwayat aset dari transaksi append-only; proyeksi konsisten
- TAC-03 Pengguna Pengadaan tanpa grant aset tidak dapat mencari/membuka aset
- TAC-04 Pembalikan ganda atau tidak cocok dan transaksi ganda dari sumber yang sama ditolak DB (DI-11)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Transaksi, scope; DI-11 (uji negatif langsung DB dan konkurensi)
- Authorization: AUTHZ-03
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-12
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Performance: EXPLAIN pada data representatif (PB-01 penuh di TASK-073)
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

- TASK-060

## DO NOT DO

- Jangan memuat seluruh aset ke browser
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
