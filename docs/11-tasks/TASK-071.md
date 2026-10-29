# TASK-071 — Katalog laporan menurut makna waktu (WF-027)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M8 — Pelaporan, hardening dan rilis
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan katalog laporan V1 menurut empat makna waktu (posisi per tanggal, rentang transaksi, periodik, rekonsiliasi) untuk aset, gudang dan pengadaan, dengan parameter yang menjelaskan makna waktu, label simulasi bila bergantung kebijakan non-ACTIVE, dan format resmi hanya bila kebijakan `reporting.formats` ACTIVE.

## WHY THIS EXISTS

CAP-13/17/18, AC-16/17/20.

## DEPENDS ON

- TASK-023
- TASK-057
- TASK-059
- TASK-039

## BLOCKS

- TASK-072
- TASK-073

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Format laporan resmi bila tersedia
- GAP terkait (GAP-007, GAP-008, GAP-009, GAP-010, GAP-013, GAP-016, GAP-024) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Definisi laporan: posisi aset per tanggal, mutasi aset per rentang, rekap aset per unit/klasifikasi/kondisi, posisi stok per tanggal per gudang, mutasi stok per rentang, rekap pengadaan per metode/jenis/tahap, nilai kontrak per periode, status rekonsiliasi
- Halaman PG-121/211/313 terpadu
- Uji kebenaran terhadap data sintetis dengan nilai yang dihitung independen
- Entri katalog BMU berstatus "menunggu definisi resmi (GAP-024)" tanpa keluaran angka; definisi ditambahkan sebagai format `reporting.formats` setelah GAP-024 terselesaikan, melalui TASK PLAN CHANGE bila memerlukan kerja tambahan

## OUT OF SCOPE

- Isi laporan BMU sebelum definisi dan bentuknya diketahui (GAP-024)
- Neraca/laporan akuntansi resmi tanpa kebijakan ACTIVE (GAP-007–010/013)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-027](../03-workflows/WORKFLOW_CONTRACTS.md#wf-027)
- [STATE_TRANSITIONS WF-027](../03-workflows/STATE_TRANSITIONS.md#wf-027)
- [RT-027](../03-workflows/ROUTE_CONTRACTS.md#rt-027)
- [BR-022](../02-domain/BUSINESS_RULES.md#br-022)

## TRACEABILITY

- CAP: CAP-13, CAP-17, CAP-18
- AC/NFR: AC-16, AC-17, AC-20, AC-21
- WF/TR: WF-027 (TR-261–TR-265, 5 TR; BLOCKED_BY_GAP: TR-262)
- RT: RT-027
- IX: IX-018
- GAP: GAP-007, GAP-008, GAP-009, GAP-010, GAP-013, GAP-016, GAP-024
- Keputusan Owner: OD-14, OD-15

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Reporting/Definitions`
- `resources/js/pages/reports`

## BUSINESS RULES

- [BR-022](../02-domain/BUSINESS_RULES.md#br-022)
- [INV-013](../02-domain/BUSINESS_RULES.md#inv-013)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-43
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Reporting/Definitions
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
- Expected affected areas: app/Modules/Reporting/Definitions, resources/js/pages/reports

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
- Halaman: PG-121, PG-211, PG-313
- Primary action: Minta laporan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Makna waktu dijelaskan di form

## ROUTE IMPACT

- RT-027

## INTERACTION CONTRACTS

- IX-018

## NAVIGATION CONTRACT

- Parent: Laporan modul
- Entry: Menu Laporan
- Back action: Dasbor modul
- Cancel: Tidak berlaku
- Success destination: Status operasi
- Error behavior: Alasan
- State preservation: Parameter di URL
- Mobile behavior: Form satu kolom

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Setiap laporan menyatakan makna waktunya dan kebijakan yang dipakai
- TAC-02 Hasil cocok dengan perhitungan independen pada data sintetis
- TAC-03 Scope pengguna dihormati pada isi ekspor

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Kebenaran laporan
- Authorization: AUTHZ-43
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-17
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build

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

- TASK-072

## DO NOT DO

- Jangan menyajikan angka simulasi sebagai resmi
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
