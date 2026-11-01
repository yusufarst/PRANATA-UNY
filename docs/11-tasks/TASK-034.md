# TASK-034 — Ruang kerja evaluasi: putaran, hasil kriteria dan tahap, klarifikasi, negosiasi dan opsi katalog (WF-009)

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan ruang kerja evaluasi yang menjadi prioritas Owner (OD-36): putaran evaluasi atas skema yang dibekukan; hasil per kriteria dan per tahap untuk setiap peserta dan lingkup dengan temuan dan rujukan bukti; klarifikasi dan negosiasi tertaut; opsi katalog E-Purchasing; perbandingan per lingkup menurut skema (terhadap HPS, harga terkoreksi, skor berbobot atau biaya kepemilikan) berlabel simulasi; penyelesaian putaran dan evaluasi ulang beralasan — tanpa sistem menetapkan pemenang.

## WHY THIS EXISTS

BR-036/INV-023 dan PR 34/2023 Lampiran III B.3.b.6–7, C.4.b.1.g–h, D.2.b.5–6: evaluasi menurut kriteria yang ditetapkan, isi penawaran tidak berubah, ketidaklulusan beralasan; staf pengadaan menyebut evaluasi sebagai bagian tersulit pekerjaannya.

## DEPENDS ON

- TASK-033
- TASK-021

## BLOCKS

- TASK-035

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-013, GAP-017, GAP-019, GAP-026) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `proc_evaluation_rounds`, `proc_evaluation_criterion_results`, `proc_evaluation_stage_results`, `proc_clarifications`, `proc_negotiations`, `proc_catalog_options` dengan FK komposit ke pemilihan, skema, peserta, penerapan lingkup kriteria, lingkup yang ditawar dan entri pembukaan yang dibuka (DI-07, DI-31)
- Penerapan lingkup: tahap per peserta tanpa lingkup, tahap per lingkup selalu dengan lingkup eksplisit (termasuk lingkup PACKAGE tunggal); matriks hanya menampilkan kriteria yang berlaku untuk lingkup aktif (DI-07)
- Layanan evaluasi: hasil tahap diturunkan dari hasil kriteria VALID menurut skema beku; tahap gugur menghentikan tahap berikutnya; tanpa penimpaan manual; koreksi = VOIDED + hasil baru; penyelesaian dan evaluasi ulang beralasan (DI-25)
- Perbandingan per lingkup dari kebijakan `procurement.evaluation` (pembanding HPS, koreksi aritmetik, skor berbobot, biaya kepemilikan) berlabel simulasi bila DRAFT; tanpa aksi tetapkan pemenang
- Negosiasi per peserta dan lingkup; opsi katalog E-Purchasing dan urutan negosiasi termurah lebih dahulu dari kebijakan (DI-28)
- State WF-009 (TR-081–085)
- PG-111 ruang kerja (INTERACTION_PATTERNS §10): matriks, panel kriteria dengan penampil penawaran baca-saja teraudit, kemajuan dan kekurangan, perbandingan, klarifikasi, negosiasi
- Kolom `selection_id` putaran dan hasil tidak dapat diubah setelah insert (DI-17)

## OUT OF SCOPE

- Hasil dan penetapan pemenang (TASK-035)
- Template berita acara (TASK-038)
- Bobot atau metode evaluasi di luar dokumen pengadaan dan kebijakan

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-009](../03-workflows/WORKFLOW_CONTRACTS.md#wf-009)
- [STATE_TRANSITIONS WF-009](../03-workflows/STATE_TRANSITIONS.md#wf-009)
- [RT-009](../03-workflows/ROUTE_CONTRACTS.md#rt-009)
- [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [POLICY_REGISTRY §1](../04-architecture/POLICY_REGISTRY.md#1-keluarga-kebijakan)
- [INTERACTION_PATTERNS §10](../07-ux-design/INTERACTION_PATTERNS.md#10-ruang-kerja-evaluasi)
- [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05, CAP-08
- AC/NFR: AC-07, AC-33, AC-35
- WF/TR: WF-009 (TR-081–TR-085, 5 TR; BLOCKED_BY_GAP: TR-084) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-009
- IX: IX-004, IX-006, IX-007, IX-008, IX-011, IX-013
- GAP: GAP-003, GAP-013, GAP-017, GAP-019, GAP-026
- Keputusan Owner: OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Assessment)`
- `resources/js/pages/procurement/assessment`
- `resources/js/features/procurement/EvaluationMatrix.tsx`

## BUSINESS RULES

- [BR-004](../02-domain/BUSINESS_RULES.md#br-004)
- [BR-006](../02-domain/BUSINESS_RULES.md#br-006)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [BR-038](../02-domain/BUSINESS_RULES.md#br-038)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)
- [INV-023](../02-domain/BUSINESS_RULES.md#inv-023)

## ARCHITECTURE RULES

- Perhitungan desimal presisi; label simulasi dari PolicyHandle
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-46
- Hanya penugasan pemilihan; pembukaan berkas penawaran teraudit
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_evaluation_rounds`, `proc_evaluation_criterion_results`, `proc_evaluation_stage_results`, `proc_clarifications`, `proc_negotiations`, `proc_catalog_options`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Assessment)
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
- Expected affected areas: app/Modules/Procurement (Assessment), resources/js/pages/procurement/assessment, resources/js/features/procurement/EvaluationMatrix.tsx

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
- Halaman: PG-111
- Primary action: Catat hasil kriteria
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Matriks dengan alternatif daftar di ponsel; tanpa aksi tetapkan pemenang

## ROUTE IMPACT

- RT-009

## INTERACTION CONTRACTS

- IX-004
- IX-006
- IX-007
- IX-008
- IX-011
- IX-013

## NAVIGATION CONTRACT

- Parent: Detail paket tab Evaluasi
- Entry: Tab Evaluasi / kartu dasbor evaluasi
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Tab Evaluasi
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Lingkup dan sel aktif di URL
- Mobile behavior: Kartu per peserta

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Hasil per kriteria dan per tahap tercatat untuk setiap peserta dan lingkup; FAIL tanpa temuan atau alasan ditolak; dua hasil VALID untuk kriteria yang sama ditolak, termasuk dua evaluator bersamaan (DI-25)
- TAC-02 Tahap gugur per peserta yang gagal membuat tahap berikutnya NOT_EVALUATED pada semua lingkup peserta itu dan tahap gugur per lingkup hanya pada lingkup itu; hasil tahap tidak dapat ditimpa manual (DI-25)
- TAC-03 Penyelesaian putaran ditolak selama ada tahap PENDING; evaluasi ulang membuat putaran baru beralasan dan putaran lama tetap terbaca (DI-25)
- TAC-04 Klarifikasi dan negosiasi tertaut tanpa mengubah penawaran; koreksi aritmetik tersimpan pada hasil tahap beserta catatannya
- TAC-05 Perbandingan terhadap HPS, harga terkoreksi dan skor berbobot hanya menurut skema beku dan berlabel simulasi bila kebijakan DRAFT; tidak ada aksi menetapkan pemenang di ruang kerja
- TAC-06 Opsi katalog E-Purchasing dan negosiasinya mengikuti kebijakan; lingkup di luar pemilihan atau yang tidak dicakup penawaran OPENED peserta ditolak (DI-28)
- TAC-07 Pengguna tanpa penugasan tidak melihat nilai atau isi penawaran (AUTHZ-46)
- TAC-08 Hasil, klarifikasi atau negosiasi untuk partisipasi atau lingkup pemilihan lain, atau untuk kriteria skema lain, ditolak aplikasi dan DB (DI-07); kolom `selection_id` tidak dapat diubah (DI-17)
- TAC-09 Kriteria yang hanya berlaku untuk lot A tidak dapat dinilai untuk lot B; hasil untuk lingkup yang tidak ditawar peserta, hasil tanpa lingkup pada tahap per lingkup dan hasil berlingkup pada tahap per peserta ditolak aplikasi dan DB (DI-07)
- TAC-10 Penawaran terlambat, mundur atau tidak dibuka tidak dapat dievaluasi, diklarifikasi atau dinegosiasikan, termasuk lewat sisipan langsung ke basis data (DI-31)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Derivasi hasil tahap, skor berbobot dan perbandingan (desimal)
- Feature: WF-009; DI-07, DI-17, DI-25, DI-28, DI-31 bagian kelayakan evaluasi (uji negatif langsung DB; DI-25 dengan dua koneksi)
- Authorization: AUTHZ-46
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-19 bagian evaluasi; bagian J-03, J-06
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe dan navigasi keyboard matriks PG-111
- Production build: npm run build
- Performance: PB-12 pada data representatif
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

- TASK-035

## DO NOT DO

- Jangan menetapkan pemenang otomatis atau dari ruang kerja evaluasi
- Jangan membuat negosiasi wajib untuk semua metode
- Jangan mengubah penawaran asli untuk koreksi apa pun
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
