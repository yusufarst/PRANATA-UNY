# TASK-018 — Policy Registry

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M2 — Platform lintas modul
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan Policy Registry sesuai POLICY_REGISTRY: paket, versi, skema JSON per keluarga, lifecycle DRAFT→IN_REVIEW→ACTIVE→SUPERSEDED/RETIRED, aktivasi maker-checker berbukti, kasus uji emas, resolver menurut konteks/tanggal, gerbang lingkungan dan label simulasi, beserta UI pengelolaan.

## WHY THIS EXISTS

Semua aturan resmi yang masih OPEN dipegang sebagai data berversi (ADR-0003, CAP-23, INV-022).

## DEPENDS ON

- TASK-014
- TASK-016

## BLOCKS

- TASK-019
- TASK-020
- TASK-021
- TASK-024
- TASK-049
- TASK-058

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-013, GAP-021) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `policy_packs`, `policy_versions` (EXCLUDE atas jendela ACTIVE/SUPERSEDED, CHECK maker-checker), `policy_test_cases`, `policy_version_uses` (append-only)
- Helper jendela berlaku bersama: aktivasi menutup jendela versi sebelumnya dan menandainya SUPERSEDED secara atomik di bawah kunci induk, tanpa tanggal mundur, keadaan WF-029 S4/S5/S6 diturunkan dari status dan jendela, job harian yang mencatat transisi keberlakuan dan penggantian WF-029 saat tanggal efektif tercapai; dipakai ulang oleh template (TASK-021) dan klasifikasi aset (TASK-058)
- Kontrak pencatatan `policy_version_uses` untuk record yang bergantung pada beberapa keluarga kebijakan
- Registry keluarga + validasi JSON Schema
- Lifecycle + Action ajukan/kembalikan/tolak/aktifkan/pensiunkan + audit + transisi
- Resolver + `PolicyOutcome` + gerbang `PRANATA_ALLOW_DRAFT_POLICIES`
- Komponen `SimulationBanner`, `BlockedReasonPanel`
- PG-507: daftar paket, editor formulir dari skema, rujukan sumber/GAP, unggah bukti, hasil kasus uji, diff versi, riwayat
- Keluarga awal: skema untuk semua keluarga di POLICY_REGISTRY §1 (isi diisi Task modul)
- Penyusunan dan aktivasi bercakupan keluarga kebijakan (grant `POLICY_FAMILY`)

## OUT OF SCOPE

- Isi kebijakan pengadaan/gudang/aset (Task modul)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [POLICY_REGISTRY](../04-architecture/POLICY_REGISTRY.md)
- [ADR-0003](../adr/ADR-0003-versioned-policy-registry.md)
- [WF-029 F4/F5/F8/F9](../03-workflows/STATE_TRANSITIONS.md#wf-029)
- [OPERATIONS_RUNBOOK RB-09](../09-operations/OPERATIONS_RUNBOOK.md#rb-09-aktivasi-kebijakan)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-23
- AC/NFR: AC-32
- WF/TR: WF-029 (TR-281–TR-288, 8 TR; BLOCKED_BY_GAP: TR-285)
- RT: RT-032
- IX: IX-001, IX-003, IX-004, IX-007, IX-008, IX-013
- GAP: GAP-013, GAP-021
- Keputusan Owner: OD-27, OD-30, OD-31

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Policy`
- `resources/js/pages/admin/policies`
- `resources/js/components/app/SimulationBanner.tsx`

## BUSINESS RULES

- [BR-025](../02-domain/BUSINESS_RULES.md#br-025)
- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)
- [INV-013](../02-domain/BUSINESS_RULES.md#inv-013)
- [INV-018](../02-domain/BUSINESS_RULES.md#inv-018)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Kode hanya mengenal struktur; nilai resmi di data
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-21
- AUTHZ-25
- admin.policy.author vs admin.policy.activate, bercakupan keluarga
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `policy_packs`, `policy_versions`, `policy_test_cases`, `policy_version_uses`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Policy
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
- Expected affected areas: app/Modules/Policy, resources/js/pages/admin/policies, resources/js/components/app/SimulationBanner.tsx

## CONTEXT7

- Topics to verify: JSON Schema validator PHP yang aktif dipelihara; PostgreSQL EXCLUDE constraint dengan btree_gist
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
- Halaman: PG-507
- Primary action: Ajukan / Aktifkan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Aktifkan hanya tampil untuk pengesah yang berhak

## ROUTE IMPACT

- RT-032

## INTERACTION CONTRACTS

- IX-001
- IX-003
- IX-004
- IX-007
- IX-008
- IX-013

## NAVIGATION CONTRACT

- Parent: Administrasi → Kebijakan
- Entry: Menu Kebijakan
- Back action: Daftar paket
- Cancel: Detail versi
- Success destination: Detail versi
- Error behavior: Galat validasi skema per field
- State preservation: Keluarga/paket di URL
- Mobile behavior: Editor satu kolom

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Isi tidak valid terhadap skema tidak dapat disimpan
- TAC-02 Pembuat tidak dapat mengaktifkan; aktivasi menuntut bukti GAP dan kasus uji lulus
- TAC-03 Jendela versi ACTIVE/SUPERSEDED yang tumpang tindih dan aktivasi bertanggal mundur ditolak; dua aktivasi bersamaan untuk paket yang sama → satu berhasil, satu 409 (DI-20)
- TAC-04 Resolver memilih satu-satunya versi ACTIVE/SUPERSEDED yang jendelanya memuat tanggal acuan (SUPERSEDED tetap dipakai untuk tanggal historis, RETIRED tidak pernah); DRAFT hanya bila lingkungan mengizinkan dan ditandai simulasi (DI-20)
- TAC-05 Di konfigurasi produksi, aksi resmi bergantung kebijakan non-ACTIVE tidak tersedia (uji)
- TAC-06 Record yang memakai kebijakan menyimpan policy_version_id dan is_simulation (uji kontrak)
- TAC-07 Record yang bergantung pada beberapa keluarga mencatat setiap versi penyumbang di `policy_version_uses`; UPDATE/DELETE ditolak (DI-16)
- TAC-08 Versi ACTIVE tanpa pengesah, atau dengan pengesah sama dengan pembuat/pengaju, ditolak constraint DB
- TAC-09 Pengesah hanya dapat mengaktifkan versi keluarga dalam cakupan grant-nya (AUTHZ-25)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Validasi skema, resolver jendela
- Feature: Lifecycle, maker-checker, EXCLUDE jendela, keadaan WF-029, gerbang lingkungan, `policy_version_uses` (DI-16, DI-20; uji negatif langsung DB)
- Authorization: AUTHZ-21, AUTHZ-25
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-16 bagian kebijakan
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB
- Other: Uji konkurensi aktivasi bersamaan (DI-20)

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

- TASK-019
- TASK-020

## DO NOT DO

- Jangan mengisi nilai ACTIVE dari dugaan
- Jangan menyediakan editor JSON bebas sebagai satu-satunya antarmuka
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
