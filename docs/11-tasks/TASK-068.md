# TASK-068 — Kerangka intake historis (WF-026)

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M7 — Nilai aset, KDP, intake dan rekonsiliasi
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan intake: unggah sumber, profil pemetaan, parse streaming ke `intake_rows` (nilai mentah tetap), validasi struktur/makna berbasis aturan, isu, keputusan per baris/kelompok, penerimaan per bagian melalui kontrak modul tujuan, kelanjutan setelah gagal, dan larangan membawa kredensial.

## WHY THIS EXISTS

CAP-16/AC-19; INV-015/016.

## DEPENDS ON

- TASK-016
- TASK-019

## BLOCKS

- TASK-069

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-011, GAP-012, GAP-013, GAP-019) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `intake_batches`, `intake_rows`, `intake_issues`; satu batch aktif per target
- Parser CSV/XLSX streaming (OpenSpout)
- Kerangka `IntakeTarget` (validator, normalizer, acceptor)
- State WF-026 (TR-251–257)
- PG-312: unggah, ringkasan validasi, daftar isu, keputusan massal, kemajuan

## OUT OF SCOPE

- Adapter target spesifik (TASK-069)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-026](../03-workflows/WORKFLOW_CONTRACTS.md#wf-026)
- [STATE_TRANSITIONS WF-026](../03-workflows/STATE_TRANSITIONS.md#wf-026)
- [MIGRATION_AND_CUTOVER §2](../10-release/MIGRATION_AND_CUTOVER.md#2-alur-intake)
- [PERFORMANCE_AND_SCALE PB-08](../06-api-performance/PERFORMANCE_AND_SCALE.md#2-anggaran-performa)

## TRACEABILITY

- CAP: CAP-16
- AC/NFR: AC-19
- WF/TR: WF-026 (TR-251–TR-257, 7 TR; BLOCKED_BY_GAP: TR-255, TR-256) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-026
- IX: IX-003, IX-006, IX-007, IX-008, IX-011, IX-015
- GAP: GAP-011, GAP-012, GAP-013, GAP-019
- Keputusan Owner: OD-14

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Intake`
- `resources/js/pages/intake`

## BUSINESS RULES

- [BR-024](../02-domain/BUSINESS_RULES.md#br-024)
- [BR-029](../02-domain/BUSINESS_RULES.md#br-029)
- [INV-015](../02-domain/BUSINESS_RULES.md#inv-015)
- [INV-016](../02-domain/BUSINESS_RULES.md#inv-016)
- [INV-017](../02-domain/BUSINESS_RULES.md#inv-017)

## ARCHITECTURE RULES

- Penerimaan hanya melalui kontrak modul tujuan
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Berkas sumber RESTRICTED; tidak ada kredensial dibawa
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `intake_batches`, `intake_rows`, `intake_issues`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Staging terpisah dari data final

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Intake
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
- Expected affected areas: app/Modules/Intake, resources/js/pages/intake

## CONTEXT7

- Topics to verify: OpenSpout reader
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
- Primary action: Unggah / Putuskan / Terima
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: OperationStatus

## ROUTE IMPACT

- RT-026

## INTERACTION CONTRACTS

- IX-003
- IX-006
- IX-007
- IX-008
- IX-011
- IX-015

## NAVIGATION CONTRACT

- Parent: Aset → Intake
- Entry: Menu
- Back action: Daftar batch
- Cancel: Batch
- Success destination: Ringkasan batch
- Error behavior: Isu per baris
- State preservation: Filter isu di URL
- Mobile behavior: Ringkasan

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Nilai mentah tersimpan apa adanya; normalisasi terpisah
- TAC-02 Duplikasi kandidat dan ambigu tidak diterima diam-diam
- TAC-03 Kolom berpola kredensial ditolak dan tidak pernah masuk akun
- TAC-04 Penerimaan sebagian dapat dilanjutkan setelah kegagalan
- TAC-05 PB-08 terpenuhi

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Pipeline intake
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: Kredensial tidak terbawa
- Performance: 100 ribu baris
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

- TASK-069

## DO NOT DO

- Jangan mengimpor langsung ke tabel final
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
