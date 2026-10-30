# TASK-042 — Verifikasi dokumen penyedia dan keputusan DPT (WF-007)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M4 — Pengadaan lanjutan dan penyedia
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan alur internal WF-007: pengajuan pendaftaran/pembaruan data penyedia, pemeriksaan kelengkapan dokumen menurut katalog kebijakan `vendor.dpt`, verifikasi (termasuk verifikasi lapangan tercatat), keputusan DPT, evaluasi kinerja berkala dan pengeluaran dari DPT dengan rujukan keputusan.

## WHY THIS EXISTS

PR 34/2023 Pasal 15–16, 59 dan Lampiran I menjadikan DPT syarat metode tertentu; kewenangan tetap kebijakan (GAP-026).

## DEPENDS ON

- TASK-031
- TASK-037

## BLOCKS

- TASK-043
- TASK-046

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-014, GAP-015, GAP-017, GAP-026) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- State WF-007 (TR-061–066)
- Katalog dokumen per bentuk usaha dari kebijakan
- Peringatan dokumen kedaluwarsa (`valid_until`)
- `vendor_performance_reviews` (jenis CONTRACT_COMPLETION dan PERIODIC_DPT; FK `(contract_id, company_id)`; satu FINAL per kontrak; kriteria dari `vendor.performance`) dengan alur evaluasi DPT berkala; penilaian pascapelaksanaan kontrak diwujudkan TASK-046
- Keputusan DPT oleh penugasan/kapabilitas `vendor.dpt.decide`

## OUT OF SCOPE

- Sanksi daftar hitam nasional

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-007](../03-workflows/WORKFLOW_CONTRACTS.md#wf-007)
- [STATE_TRANSITIONS WF-007](../03-workflows/STATE_TRANSITIONS.md#wf-007)
- [RT-007](../03-workflows/ROUTE_CONTRACTS.md#rt-007)
- [REGULATORY_SOURCE_REVIEW RG-03](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md)

## TRACEABILITY

- CAP: CAP-07
- AC/NFR: AC-09, AC-36
- WF/TR: WF-007 (TR-061–TR-066, 6 TR; BLOCKED_BY_GAP: TR-064) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-007
- IX: IX-004, IX-005, IX-006, IX-007, IX-008, IX-011, IX-012
- GAP: GAP-014, GAP-015, GAP-017, GAP-026
- Keputusan Owner: OD-27

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Vendor`
- `resources/js/pages/procurement/vendors`

## BUSINESS RULES

- [BR-007](../02-domain/BUSINESS_RULES.md#br-007)
- [BR-008](../02-domain/BUSINESS_RULES.md#br-008)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [BR-039](../02-domain/BUSINESS_RULES.md#br-039)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- RESTRICTED disamarkan; unduhan dokumen penyedia teraudit
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `vendor_performance_reviews`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Vendor
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
- Expected affected areas: app/Modules/Vendor, resources/js/pages/procurement/vendors

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
- Halaman: PG-120
- Primary action: Verifikasi / Putuskan DPT
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman

## ROUTE IMPACT

- RT-007

## INTERACTION CONTRACTS

- IX-004
- IX-005
- IX-006
- IX-007
- IX-008
- IX-011
- IX-012

## NAVIGATION CONTRACT

- Parent: Pengadaan → Penyedia
- Entry: Inbox verifikator
- Back action: Detail penyedia
- Cancel: Detail
- Success destination: Detail + status DPT
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Kartu dokumen

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Kelengkapan dokumen dicek terhadap katalog kebijakan per bentuk usaha
- TAC-02 Keputusan DPT dan pengeluaran tercatat sebagai peristiwa dengan rujukan
- TAC-03 Dokumen kedaluwarsa memunculkan pekerjaan pembaruan
- TAC-04 Penilaian untuk perusahaan selain penyedia kontrak atau penilaian FINAL kedua untuk kontrak yang sama ditolak; penilaian FINAL tidak dapat diubah (DI-29)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-007; DI-29 (uji negatif langsung DB)
- Authorization: vendor.dpt.decide
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Bagian J-15
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

- TASK-043

## DO NOT DO

- Jangan menetapkan DPT tanpa dasar kebijakan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
