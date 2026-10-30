# TASK-033 — Partisipasi, penawaran imutabel, pembukaan dan pembacaan teraudit (WF-008, IX-024)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan partisipasi per pemilihan, pencatatan penawaran yang diterima melalui kanal resmi (default `EXTERNAL_RECORDED`) sebagai rekaman imutabel per lingkup dengan rincian harga dan sidik jari berkas, penolakan penawaran terlambat, pembukaan tanpa aksi gugur, klasifikasi rahasia, dan pembacaan teraudit oleh Super Admin (IX-024).

## WHY THIS EXISTS

INV-023/BR-036: isi penawaran tidak boleh diubah dan pembukaan tidak menggugurkan (PR 34/2023 Lampiran III B.3.b.5–6); OD-28: penawaran rahasia, Super Admin hanya baca teraudit.

## DEPENDS ON

- TASK-032
- TASK-031

## BLOCKS

- TASK-034
- TASK-043

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-014, GAP-017, GAP-029) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `proc_participations` (per pemilihan; `uq(id, selection_id)`, `uq(id, package_id)`, `uq(id, company_id)`), `proc_submissions`, `proc_submission_scopes`, `proc_submission_lines`, `proc_opening_records`, `proc_opening_entries`; pemeriksaan DPT saat partisipasi
- Imutabilitas: hak kolom `UPDATE (status, opened_at, withdrawn_at, withdrawal_evidence_file_id)`, trigger `forbid_offer_change()`, lingkup/rincian/entri pembukaan append-only, tautan berkas OFFER tidak dapat dilepas (DI-23)
- Pembukaan atau penutupan penerimaan per sampul tanpa kolom atau aksi gugur (DI-24), hanya sesudah batas akhir versi tertinggi; waktu terima dari jam basis data (kanal PRANATA) atau bukti terima (kanal eksternal); keterlambatan dihitung dari batas akhir tanpa kolom penanda; entri hanya untuk kiriman tepat waktu; `opened = false` hanya untuk sampul harga peserta yang gugur lebih dahulu (DI-24); constraint trigger tertunda `check_opening_consistency()` menyelaraskan status (DI-31); penggantian skema sesudah pembukaan ditolak di bawah kunci pemilihan (DI-22)
- State WF-008 (TR-071–077)
- PG-110 (penerimaan penawaran) dan PG-123 (pembukaan); berkas penawaran CONFIDENTIAL
- Alur IX-024: alasan wajib, audit gagal tertutup, read-only, tanpa ekspor
- Gerbang kebijakan kanal: kiriman di PRANATA nonaktif secara default (GAP-029)
- Kolom `package_id`/`selection_id` tidak dapat diubah setelah insert (DI-17)

## OUT OF SCOPE

- Kiriman dari portal (TASK-043)
- Evaluasi (TASK-034)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-008](../03-workflows/WORKFLOW_CONTRACTS.md#wf-008)
- [STATE_TRANSITIONS WF-008](../03-workflows/STATE_TRANSITIONS.md#wf-008)
- [RT-008](../03-workflows/ROUTE_CONTRACTS.md#rt-008)
- [IX-024](../03-workflows/INTERACTION_CONTRACTS.md#ix-024)
- [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)
- [INV-023](../02-domain/BUSINESS_RULES.md#inv-023)
- [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05, CAP-07
- AC/NFR: AC-09, AC-10, AC-31, AC-33
- WF/TR: WF-008 (TR-071–TR-077, 7 TR; BLOCKED_BY_GAP: TR-076) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-008
- IX: IX-003, IX-004, IX-005, IX-011, IX-012, IX-024
- GAP: GAP-003, GAP-014, GAP-017, GAP-029
- Keputusan Owner: OD-28, OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Participation, Offers, Opening)`
- `resources/js/pages/procurement/participants`
- `resources/js/pages/procurement/opening`

## BUSINESS RULES

- [BR-007](../02-domain/BUSINESS_RULES.md#br-007)
- [BR-008](../02-domain/BUSINESS_RULES.md#br-008)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [INV-003](../02-domain/BUSINESS_RULES.md#inv-003)
- [INV-004](../02-domain/BUSINESS_RULES.md#inv-004)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)
- [INV-023](../02-domain/BUSINESS_RULES.md#inv-023)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-31, AUTHZ-32, AUTHZ-46 (AUTHZ-30/33/48 di TASK-043)
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_participations`, `proc_submissions`, `proc_submission_scopes`, `proc_submission_lines`, `proc_opening_records`, `proc_opening_entries`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Participation, Offers, Opening)
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
- Expected affected areas: app/Modules/Procurement (Participation, Offers, Opening), resources/js/pages/procurement/participants, resources/js/pages/procurement/opening

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
- Halaman: PG-110, PG-123
- Primary action: Catat penawaran / Catat pembukaan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Panel akses rahasia; pembukaan tanpa tombol gugur

## ROUTE IMPACT

- RT-008

## INTERACTION CONTRACTS

- IX-003
- IX-004
- IX-005
- IX-011
- IX-012
- IX-024

## NAVIGATION CONTRACT

- Parent: Detail paket tab Pemilihan
- Entry: Tab
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Partisipasi
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Penawaran eksternal tercatat dengan bukti terima, waktu terima, kanal, lingkup yang ditawar, rincian harga dan sidik jari berkas (DI-23)
- TAC-02 Penawaran terlambat yang disisipkan langsung ke basis data berstatus RECEIVED tidak dapat diberi entri pembukaan atau menjadi OPENED, dan pembukaan yang tidak menandainya REJECTED_LATE ditolak saat commit; penawaran tepat waktu tidak dapat dikecualikan dari pembukaan; pembukaan sebelum batas akhir atau dengan versi batas yang bukan tertinggi ditolak DB; kanal PRANATA memakai jam basis data (DI-31)
- TAC-03 Kiriman berbatas sebelum versi 1 batas akhirnya tercatat atau yang disisipkan langsung berstatus OPENED ditolak; kiriman tepat waktu kedua dari peserta yang sama untuk sampul yang sama tanpa `supersedes_id` ditolak; penarikan kanal PRANATA memakai jam basis data dan tidak pernah sebelum waktu terima (DI-31)
- TAC-04 UPDATE isi penawaran, lingkup atau rinciannya dan pelepasan berkas penawaran ditolak hak kolom dan trigger, termasuk lewat koneksi `pranata_app` langsung (DI-23)
- TAC-05 Pembukaan mencatat nilai yang dibacakan dan kelengkapan; penawaran tidak lengkap tetap berstatus sama dan tidak ada aksi gugur; entri tidak dibuka hanya untuk sampul harga peserta yang gugur lebih dahulu dan selalu beralasan (DI-24); penggantian skema sesudah pembukaan ditolak dan pembukaan bersamaan pembekuan diserialkan (DI-22)
- TAC-06 Petugas tanpa penugasan tidak dapat membuka penawaran dan tidak seorang pun dapat mengubah penawaran asli (AUTHZ-46); isolasi antar-penyedia diuji di TASK-043
- TAC-07 Super Admin membuka penawaran hanya setelah alasan; audit tercatat; gagal audit = tidak tampil
- TAC-08 Jawaban klarifikasi dan revisi sebelum batas akhir tertaut ke penawaran asal tanpa mengubahnya
- TAC-09 Kolom `package_id`/`selection_id` partisipasi dan penawaran tidak dapat diubah setelah insert (DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-008, IX-024; DI-17, DI-22, DI-23, DI-24, DI-31 (uji negatif langsung DB; DI-22 dan DI-31 dengan dua koneksi)
- Authorization: AUTHZ-31, AUTHZ-32, AUTHZ-46
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-16 bagian baca teraudit; J-19 bagian pembukaan
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

- TASK-034

## DO NOT DO

- Jangan mengaktifkan kiriman penawaran di PRANATA tanpa versi kebijakan kanal yang berlaku resmi
- Jangan menyimpan atau mengubah penanda keterlambatan; pengecualian hanya berupa keputusan perpanjangan tercatat
- Jangan menyediakan aksi gugur pada pembukaan
- Jangan menyimpan koreksi aritmetik pada rekaman penawaran
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
