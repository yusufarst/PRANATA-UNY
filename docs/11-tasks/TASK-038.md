# TASK-038 — Template dan provider dokumen pengadaan set awal

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan provider data dan template DRAFT untuk dokumen pengadaan set awal: spesifikasi, rincian HPS, nota penetapan metode, undangan, BA penjelasan/pembukaan/evaluasi/negosiasi, BA evaluasi-klarifikasi-negosiasi Penunjukan Langsung, BA hasil PL, lembar skema evaluasi, lembar evaluasi per lingkup, laporan hasil pemilihan dan surat penetapan pemenang per lingkup, SPPBJ dan kontrak per pemenang, SPK, surat pesanan.

## WHY THIS EXISTS

OD-29: dokumen terkait dalam beberapa klik dari data yang sama.

## DEPENDS ON

- TASK-021
- TASK-037

## BLOCKS

- TASK-041

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Owner/unit pengadaan menyediakan template kosong resmi bila tersedia
- GAP terkait (GAP-017, GAP-019) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Provider per jenis dokumen dengan DTO dan skema placeholder
- Template DOCX DRAFT generik (tanpa nama/nomor/nilai historis) dengan `${status_banner}`
- Pemetaan template ke rezim/metode/jenis via kebijakan `procurement.documents`
- Dokumen pemilihan memuat hasil per lingkup dan dapat mencakup beberapa lingkup; dokumen kontrak memuat lingkup pemenangnya
- Uji template emas per dokumen

## OUT OF SCOPE

- Template resmi yang diunggah petugas berwenang (operasional)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DOCUMENT_ENGINE §3](../04-architecture/DOCUMENT_ENGINE.md#3-katalog-jenis-dokumen-awal)
- [WF-028](../03-workflows/WORKFLOW_CONTRACTS.md#wf-028)

## TRACEABILITY

- CAP: CAP-08
- AC/NFR: AC-11, AC-33
- WF/TR: WF-028 (TR-271–TR-276, 6 TR; BLOCKED_BY_GAP: TR-274)
- RT: RT-028
- IX: IX-010, IX-013
- GAP: GAP-017, GAP-019
- Keputusan Owner: OD-08, OD-29

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement/Documents`
- `resources/templates/drafts/*.docx`

## BUSINESS RULES

- [BR-009](../02-domain/BUSINESS_RULES.md#br-009)
- [BR-010](../02-domain/BUSINESS_RULES.md#br-010)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada data historis di template
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: data seed document_templates
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement/Documents
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

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Procurement/Documents, resources/templates/drafts/*.docx

## CONTEXT7

- Topics to verify: PhpWord TemplateProcessor
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
- Halaman: PG-105, PG-010
- Primary action: Buat dokumen
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: DocumentCard di setiap tab

## ROUTE IMPACT

- RT-028

## INTERACTION CONTRACTS

- IX-010
- IX-013

## NAVIGATION CONTRACT

- Parent: Objek pengadaan
- Entry: DocumentCard
- Back action: Objek
- Cancel: Objek
- Success destination: Penampil dokumen
- Error behavior: Status konversi
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Daftar dokumen

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Setiap jenis dokumen set awal dapat dibuat dari data paket tanpa input ulang
- TAC-02 Banner DRAF/SIMULASI muncul sesuai status
- TAC-03 Placeholder lengkap terisi (uji emas)
- TAC-04 BA evaluasi memuat nama seluruh penawar, metode dan unsur evaluasi, lulus/tidak lulus per tahap beserta alasan, tanggal dan pernyataan gagal bila tidak ada penawaran yang memenuhi syarat — dari data putaran yang sama

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Template emas per dokumen
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-03 generate dokumen
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

- TASK-039

## DO NOT DO

- Jangan menyalin isi dokumen historis
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
