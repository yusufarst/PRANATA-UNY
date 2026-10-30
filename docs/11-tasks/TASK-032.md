# TASK-032 — Pemilihan, lingkup penghargaan, skema evaluasi, kegiatan dan undangan (WF-006)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan persiapan pemilihan: pemilihan per penetapan metode, lingkup penghargaan (paket/lot/item) dengan penempatan item, skema evaluasi dari dokumen pengadaan (tahap, kriteria, bobot/ambang, bukti yang diminta) yang dibekukan sebelum pembukaan, kegiatan dan jadwal, undangan dengan kanal resmi, penjajakan Pengadaan Langsung, dan strip jumlah penyedia.

## WHY THIS EXISTS

OD-35/OD-36, BR-036–BR-038, CAP-06/AC-08/AC-33–AC-35: evaluasi yang andal dimulai dari lingkup dan kriteria yang ditetapkan sebelum penawaran dibuka (PR 34/2023 Lampiran III B.3.a.1.c, C.4.a.1.c–e).

## DEPENDS ON

- TASK-030
- TASK-026
- TASK-021
- TASK-031

## BLOCKS

- TASK-033

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-013, GAP-017, GAP-027, GAP-029) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `proc_award_scopes`, kolom `proc_package_items.award_scope_id` (migrasi pemilik tabel tujuan, DATA_MODEL §19 aturan 5), `proc_selections`, `proc_selection_scopes`, `proc_evaluation_schemes`, `proc_evaluation_stages`, `proc_evaluation_criteria`, `proc_evaluation_criterion_scopes`, `proc_submission_deadlines`, `proc_activities` (dengan `selection_id`), `proc_activity_participants`, `proc_invitations` (per pemilihan), `proc_market_inquiries`
- Lingkup PACKAGE bawaan; pembagian lot/item sebelum skema dibekukan; trigger eksklusivitas lingkup dan penolakan perubahan sesudah FROZEN (DI-21)
- Skema: metode evaluasi yang diizinkan dari `procurement.evaluation`, tahap per peserta atau per lingkup beserta sampulnya, kriteria dari dokumen pengadaan beserta sumbernya dan penerapan lingkup eksplisit untuk setiap kriteria per lingkup (semua lingkup disimpan per lingkup), validasi kelengkapan, pembekuan oleh `POKJA_CHAIR`/`PEJABAT_PENGADAAN`, penggantian hanya dengan rujukan adendum sebelum pembukaan (DI-22)
- Batas akhir pemasukan per pemilihan dan sampul: versi dari dokumen pengadaan, adendum yang terbit sebelum batas lewat, dan keputusan perpanjangan sesudah batas lewat hanya sebagai versi tercatat bila kebijakan yang berlaku resmi mengizinkannya; trigger `check_deadline_version()` di bawah kunci pemilihan memakai jam basis data dan menolak versi tidak berurutan, versi sesudah batas beku, perubahan sesudah batas lewat yang tidak memperpanjang atau tanpa keputusan tercatat, dan pemajuan yang membuat kiriman tercatat terlambat (DI-31)
- State WF-006 (TR-051–055); undangan dengan pemeriksaan minimum yang bersumber aturan (Quotation ≥ 3 DPT) dan praktik setempat yang informatif
- PG-109 kalender + daftar, PG-110 (undangan, penjajakan, strip jumlah), PG-122 (skema dan lingkup)
- Kolom lingkup `package_id`/`selection_id` tidak dapat diubah setelah insert (DI-17)
- Notulen kegiatan sebagai dokumen draf

## OUT OF SCOPE

- Penerimaan dan pembukaan penawaran (TASK-033)
- Hasil evaluasi (TASK-034)
- Integrasi kalender eksternal

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-006](../03-workflows/WORKFLOW_CONTRACTS.md#wf-006)
- [STATE_TRANSITIONS WF-006](../03-workflows/STATE_TRANSITIONS.md#wf-006)
- [RT-006](../03-workflows/ROUTE_CONTRACTS.md#rt-006)
- [IX-020](../03-workflows/INTERACTION_CONTRACTS.md#ix-020)
- [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [BR-037](../02-domain/BUSINESS_RULES.md#br-037)
- [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)
- [INTERACTION_PATTERNS §10](../07-ux-design/INTERACTION_PATTERNS.md#10-ruang-kerja-evaluasi)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05, CAP-06
- AC/NFR: AC-08, AC-33, AC-34, AC-35
- WF/TR: WF-006 (TR-051–TR-055, 5 TR); WF-009 (TR-081–TR-085, 5 TR; BLOCKED_BY_GAP: TR-084) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-006, RT-008, RT-009
- IX: IX-001, IX-002, IX-003, IX-009, IX-011, IX-020
- GAP: GAP-003, GAP-013, GAP-017, GAP-027, GAP-029
- Keputusan Owner: OD-07, OD-35, OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Selection, Activities)`
- `resources/js/pages/procurement/selection`
- `resources/js/pages/procurement/activities`

## BUSINESS RULES

- [BR-006](../02-domain/BUSINESS_RULES.md#br-006)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [BR-037](../02-domain/BUSINESS_RULES.md#br-037)
- [BR-038](../02-domain/BUSINESS_RULES.md#br-038)
- [INV-002](../02-domain/BUSINESS_RULES.md#inv-002)
- [INV-024](../02-domain/BUSINESS_RULES.md#inv-024)

## ARCHITECTURE RULES

- Skema evaluasi adalah data dari dokumen pengadaan; tanpa bobot atau ambang bawaan aplikasi
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-47
- Peserta penyedia tidak melihat peserta lain bila kebijakan merahasiakan
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_award_scopes`, proc_package_items (kolom award_scope_id), `proc_selections`, `proc_selection_scopes`, `proc_evaluation_schemes`, `proc_evaluation_stages`, `proc_evaluation_criteria`, `proc_evaluation_criterion_scopes`, `proc_submission_deadlines`, `proc_activities`, `proc_activity_participants`, `proc_invitations`, `proc_market_inquiries`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Selection, Activities)
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
- Expected affected areas: app/Modules/Procurement (Selection, Activities), resources/js/pages/procurement/selection, resources/js/pages/procurement/activities

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
- Halaman: PG-109, PG-110, PG-122
- Primary action: Bekukan skema / Jadwalkan kegiatan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Kalender dengan alternatif daftar yang aksesibel; strip jumlah penyedia; pembekuan dengan dialog akibat

## ROUTE IMPACT

- RT-006
- RT-008
- RT-009

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-003
- IX-009
- IX-011
- IX-020

## NAVIGATION CONTRACT

- Parent: Detail paket / Jadwal
- Entry: Tab Pemilihan atau menu Jadwal
- Back action: Asal (paket/kalender dengan rentang)
- Cancel: Kembali ke halaman asal tanpa menyimpan; konfirmasi bila ada perubahan belum tersimpan
- Success destination: Detail pemilihan
- Error behavior: Galat validasi per field + ringkasan di atas form; input dipertahankan
- State preservation: Rentang kalender dan lingkup aktif di URL
- Mobile behavior: Daftar harian; skema sebagai daftar tahap

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Paket tanpa pembagian memiliki satu lingkup PACKAGE; paket dua lot menempatkan setiap item pada tepat satu lot; perubahan lingkup atau penempatan item sesudah skema FROZEN ditolak aplikasi dan DB (DI-21)
- TAC-02 Skema hanya dapat dibekukan dengan dokumen sumber, kriteria lengkap dan penerapan lingkup untuk setiap kriteria per lingkup; baris penerapan untuk kriteria per peserta ditolak; skema FROZEN beserta tahap, kriteria dan penerapan lingkupnya tidak dapat diubah atau ditambah, dan lingkup tidak dapat ditambahkan ke pemilihan sesudah skemanya FROZEN (DI-22)
- TAC-03 Batas akhir per pemilihan dan sampul tercatat dengan dasar dan waktu terbitnya sebelum kiriman pertama; perubahan digolongkan dengan jam basis data, bukan waktu terbit isian: versi yang dicatat sesudah batas lewat tanpa pengambil keputusan, penugasan, alasan dan bukti atau tanpa memperpanjang, pemajuan yang membuat kiriman tercatat terlambat, versi sesudah batas beku (termasuk sampul harga sesudah pembukaan sampul administrasi-teknis) dan nomor versi tidak berurutan ditolak DB (DI-31)
- TAC-04 Undangan Quotation memperingatkan bila < 3 penyedia DPT (kebijakan DRAFT); praktik tiga penawaran Pengadaan Langsung tampil informatif tanpa memblokir
- TAC-05 Strip jumlah dijajaki/diundang/menawar/memenuhi syarat/dievaluasi dihitung server dan konsisten dengan daftar
- TAC-06 Kegiatan terjadwal dan hasilnya tercatat terpisah; kalender dan daftar konsisten; dapat diakses keyboard
- TAC-07 Kolom `package_id`/`selection_id` pemilihan, lingkup dan undangan tidak dapat diubah setelah insert (DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-006; DI-17, DI-21, DI-22, DI-31 bagian batas akhir (uji negatif langsung DB)
- Authorization: AUTHZ-47
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Bagian J-04, J-19
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: Kalender, PG-122
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

- TASK-033

## DO NOT DO

- Jangan mewajibkan negosiasi/kegiatan untuk semua metode
- Jangan membuat bobot, ambang atau kriteria yang tidak ada dalam dokumen pengadaan
- Jangan menjadikan praktik tiga penawaran sebagai syarat
- Jangan mengubah batas akhir di tempat; setiap perubahan adalah versi baru beserta dasarnya
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
