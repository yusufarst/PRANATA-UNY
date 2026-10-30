# TASK-069 — Adapter intake: aset, barang, saldo awal persediaan dan organisasi

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M7 — Nilai aset, KDP, intake dan rekonsiliasi
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan target intake untuk aset (dengan klasifikasi/unit/lokasi/nilai), barang persediaan, saldo awal per gudang (kode mentah tetap, P01/P02 tidak dipetakan) dan unit organisasi, dengan profil pemetaan yang dapat dikonfigurasi dan rekonsiliasi total pasca-penerimaan.

## WHY THIS EXISTS

CAP-16 dan MIGRATION_AND_CUTOVER.

## DEPENDS ON

- TASK-068
- TASK-059
- TASK-051
- TASK-013

## BLOCKS

- TASK-078

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Kustodian sumber memberikan versi/tanggal sumber untuk intake produksi
- GAP terkait (GAP-005, GAP-006, GAP-011, GAP-012, GAP-022) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Adapter target dan aturan validasi masing-masing
- Profil pemetaan contoh berbasis struktur kolom yang diamati (tanpa nilai riil)
- Laporan rekonsiliasi total per unit/klasifikasi/gudang
- Uji dengan berkas sintetis berbentuk sama

## OUT OF SCOPE

- Impor ke produksi (dijalankan operator)
- Pemetaan kode resmi (GAP-005/006/022)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-026](../03-workflows/WORKFLOW_CONTRACTS.md#wf-026)
- [MIGRATION_AND_CUTOVER](../10-release/MIGRATION_AND_CUTOVER.md)
- [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md)

## TRACEABILITY

- CAP: CAP-16
- AC/NFR: AC-19
- WF/TR: WF-026 (TR-251–TR-257, 7 TR; BLOCKED_BY_GAP: TR-255, TR-256); WF-017 (TR-161–TR-166, 6 TR; BLOCKED_BY_GAP: TR-164, TR-165); WF-023 (TR-221–TR-226, 6 TR; BLOCKED_BY_GAP: TR-224)
- RT: RT-026, RT-017, RT-023
- IX: IX-007, IX-015, IX-021
- GAP: GAP-005, GAP-006, GAP-011, GAP-012, GAP-022
- Keputusan Owner: OD-14

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Intake/Targets`
- `app/Modules/Asset`
- `app/Modules/Inventory`
- `app/Modules/Organization`

## BUSINESS RULES

- [BR-020](../02-domain/BUSINESS_RULES.md#br-020)
- [BR-024](../02-domain/BUSINESS_RULES.md#br-024)
- [BR-029](../02-domain/BUSINESS_RULES.md#br-029)
- [INV-010](../02-domain/BUSINESS_RULES.md#inv-010)
- [INV-015](../02-domain/BUSINESS_RULES.md#inv-015)
- [INV-016](../02-domain/BUSINESS_RULES.md#inv-016)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Berkas riil tidak masuk repositori/CI
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: memakai tabel target
- Existing data transformation: PROPOSED (via intake terkendali)
- Direct production DB access: FORBIDDEN
- Data-loss risk: Penerimaan hanya lewat gerbang domain

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Intake/Targets, app/Modules/Asset, app/Modules/Inventory, app/Modules/Organization
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

- Context7: OPTIONAL (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Intake/Targets, app/Modules/Asset, app/Modules/Inventory, app/Modules/Organization

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
- Halaman: PG-312
- Primary action: Terima
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman

## ROUTE IMPACT

- RT-026
- RT-017
- RT-023

## INTERACTION CONTRACTS

- IX-007
- IX-015
- IX-021

## NAVIGATION CONTRACT

- Parent: Intake
- Entry: Batch
- Back action: Batch
- Cancel: Batch
- Success destination: Ringkasan + rekonsiliasi
- Error behavior: Isu
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Ringkasan

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Kode P01/P02 tersimpan mentah dan tidak dipetakan
- TAC-02 Rekonsiliasi total tersedia setelah penerimaan
- TAC-03 Baris ambigu identitas tidak diterima tanpa keputusan

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Adapter dengan berkas sintetis
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Performance: Aset 100 ribu baris

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

- TASK-070

## DO NOT DO

- Jangan membaca atau menyalin nilai reference-inputs ke repositori
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
