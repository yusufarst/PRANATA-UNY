# TASK-021 — Mesin dokumen: template, generasi, PDF, revisi, tanda tangan dan paket

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M2 — Platform lintas modul
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan DOCUMENT_ENGINE: template DOCX berversi dengan validasi placeholder dan aktivasi maker-checker, kontrak `DocumentDataProvider`, job generasi, konversi PDF Gotenberg, banner DRAF/SIMULASI, finalisasi bernomor, revisi/supersession, bukti tanda tangan, paket dokumen dan fungsi format/terbilang Indonesia.

## WHY THIS EXISTS

CAP-08/OD-29: dokumen dihasilkan dari data sekali-input dengan asal yang dapat dijelaskan.

## DEPENDS ON

- TASK-016
- TASK-018
- TASK-020

## BLOCKS

- TASK-032
- TASK-034
- TASK-036
- TASK-038
- TASK-045
- TASK-053
- TASK-065

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-013, GAP-017, GAP-019) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `document_templates`, `document_template_versions`, `generated_documents`, `document_signatures`, `document_packs`, `document_pack_items`
- Validasi placeholder terhadap skema provider; tolak DOCM
- Pemilihan template deterministik (template ACTIVE paling spesifik; satu versi berlaku per tanggal generasi; EXCLUDE jendela) dan aktivasi versi atomik di bawah kunci template memakai helper jendela TASK-018, tanpa tanggal mundur
- Job antrean `documents` + klien Gotenberg + status konversi + ulang
- Formatter uang/tanggal/terbilang Indonesia teruji tabel
- Siklus isi dokumen DRAFT → FINAL; penerbitan dan bukti tanda tangan sebagai tindakan terpisah yang wajib/urutannya ditentukan per jenis dokumen oleh kebijakan (tanpa urutan bawaan)
- DocumentCard, PG-010 penampil dokumen dan riwayat versi, PG-508 template
- Provider contoh untuk uji (tidak resmi)

## OUT OF SCOPE

- Template resmi dan provider pengadaan (TASK-038/048)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md)
- [ADR-0005](../adr/ADR-0005-document-generation.md)
- [WF-028](../03-workflows/WORKFLOW_CONTRACTS.md#wf-028)
- [OPERATIONS_RUNBOOK RB-11](../09-operations/OPERATIONS_RUNBOOK.md#rb-11-gotenberg-bermasalah)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-08, CAP-19
- AC/NFR: AC-11, AC-22, AC-32
- WF/TR: WF-028 (TR-271–TR-276, 6 TR; BLOCKED_BY_GAP: TR-274)
- RT: RT-028
- IX: IX-010, IX-011, IX-013
- GAP: GAP-013, GAP-017, GAP-019
- Keputusan Owner: OD-08, OD-29

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Documents`
- `resources/js/components/app/DocumentCard.tsx`
- `resources/js/pages/documents`
- `resources/js/pages/admin/templates`

## BUSINESS RULES

- [BR-004](../02-domain/BUSINESS_RULES.md#br-004)
- [BR-009](../02-domain/BUSINESS_RULES.md#br-009)
- [BR-010](../02-domain/BUSINESS_RULES.md#br-010)
- [BR-026](../02-domain/BUSINESS_RULES.md#br-026)
- [INV-005](../02-domain/BUSINESS_RULES.md#inv-005)
- [INV-006](../02-domain/BUSINESS_RULES.md#inv-006)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Tidak ada pemanggilan Gotenberg dalam transaksi
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- SEC-14
- Gotenberg tanpa akses keluar
- Unduhan via otorisasi subjek
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `document_templates`, `document_template_versions`, `generated_documents`, `document_signatures`, `document_packs`, `document_pack_items`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Documents
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: YES
- Replaceable adapter/contract required: YES — Konverter PDF (Gotenberg) di balik layanan Documents
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
- Expected affected areas: app/Modules/Documents, resources/js/components/app/DocumentCard.tsx, resources/js/pages/documents, resources/js/pages/admin/templates

## CONTEXT7

- Topics to verify: PhpWord TemplateProcessor (setValue, cloneRow, cloneBlock); Gotenberg LibreOffice and merge routes
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
- Halaman: PG-010, PG-508
- Primary action: Buat dokumen / Finalisasi
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Operasi panjang memakai OperationStatus

## ROUTE IMPACT

- RT-028

## INTERACTION CONTRACTS

- IX-010
- IX-011
- IX-013

## NAVIGATION CONTRACT

- Parent: Objek induk
- Entry: DocumentCard pada objek
- Back action: Objek induk
- Cancel: Objek induk
- Success destination: Penampil dokumen
- Error behavior: Status konversi gagal dengan ulangi
- State preservation: Revisi terpilih di URL
- Mobile behavior: Daftar versi

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Template dengan placeholder tak dikenal/hilang ditolak
- TAC-02 Dokumen DRAF dan SIMULASI berbanner; FINAL non-simulasi tanpa banner
- TAC-03 Gotenberg mati → DOCX tetap tersedia, konversi dapat diulang
- TAC-04 Revisi baru tidak menimpa revisi lama
- TAC-05 Tanpa urutan dari kebijakan, penerbitan dan tanda tangan dapat dicatat dalam urutan apa pun; dengan urutan kebijakan, urutan lain ditolak
- TAC-06 Terbilang dan format lulus tabel uji
- TAC-07 Paket dokumen menandai item wajib yang ada/kurang dan dapat dirakit menjadi PDF gabungan
- TAC-08 Pemilihan template dan versinya deterministik; jendela tumpang tindih, aktivasi bersamaan dan aktivasi bertanggal mundur ditolak; revisi dokumen lama memakai versi template tersimpan kecuali regenerasi eksplisit (DI-12)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Formatter/terbilang, pemilihan template
- Feature: Template emas, revisi, nomor, otorisasi unduh, DI-12 (uji negatif langsung DB)
- Integration: Gotenberg (nightly)
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Generate dan unduh dokumen contoh
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB
- Other: Uji konkurensi aktivasi versi template (DI-12)

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

- TASK-022
- TASK-024

## DO NOT DO

- Jangan menyalin nama/nomor/nilai dari dokumen historis ke template
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
