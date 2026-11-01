# TASK-028 — Penugasan pejabat paket dan rencana langkah per metode

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan panel penugasan pejabat pada paket (PPK, Pokja, Pejabat Pengadaan, penetap metode, penetap HPS, dll.) dengan pemeriksaan SoD dari kebijakan rezim, serta "rencana langkah" paket yang dibangkitkan dari kebijakan `procurement.documents` menurut metode/jenis/rezim.

## WHY THIS EXISTS

Ekstensi metode WF-005 tidak boleh memaksakan satu urutan; langkah yang applicable berasal dari kebijakan (GAP-003).

## DEPENDS ON

- TASK-027
- TASK-015

## BLOCKS

- TASK-029
- TASK-030

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-004, GAP-014) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Panel penugasan paket memakai TASK-015 + aturan SOD-01–03 dari `procurement.authority`
- Model `proc_package_step_plans` (dibangkitkan; `reference_date`, `generation_reason`; `policy_version_id` keluarga utama `procurement.documents` dan semua versi penyumbang — `procurement.methods`, serta `procurement.authority` bila dipakai — di `policy_version_uses`)
- Pemicu regenerasi: perubahan metode/jenis/rezim paket, atau — setelah aktivasi versi salah satu keluarga penyumbang — job menemukan rencana aktif yang versi berlakunya pada `reference_date` berbeda dari yang tercatat; rencana versi baru dibuat, langkah terdampak ditandai untuk ditelaah, rencana lama beserta rujukan versinya tetap
- Tampilan langkah dan dokumen: wajib/opsional/bersyarat/tidak berlaku/menunggu validasi, dengan alasan, sumber, status dan dokumen terkait; kunci penerapan rezim × metode × varian × jenis × pita nilai; pita nilai dan varian ikut memicu regenerasi
- Label simulasi bila kebijakan DRAFT

## OUT OF SCOPE

- Pelaksanaan setiap langkah (Task berikut)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-005](../03-workflows/WORKFLOW_CONTRACTS.md#wf-005)
- [ACCESS_CONTROL §5–6](../05-security/ACCESS_CONTROL.md#5-penugasan-proses)
- [POLICY_REGISTRY §3](../04-architecture/POLICY_REGISTRY.md#3-resolusi-dan-evaluasi)
- [POLICY_REGISTRY §5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi)
- [DATA_MODEL §14.2](../04-architecture/DATA_MODEL.md#142-paket-hps-dan-penetapan-metode)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05, CAP-06
- AC/NFR: AC-07, AC-30, AC-35
- WF/TR: WF-005 (TR-041–TR-045, 5 TR; BLOCKED_BY_GAP: TR-044)
- RT: RT-005
- IX: IX-001, IX-021
- GAP: GAP-003, GAP-004, GAP-014
- Keputusan Owner: OD-27, OD-28, OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Assignments, StepPlan)`
- `resources/js/features/procurement/StepPlan.tsx`

## BUSINESS RULES

- [BR-002](../02-domain/BUSINESS_RULES.md#br-002)
- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-34
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_package_step_plans`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Assignments, StepPlan)
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
- Expected affected areas: app/Modules/Procurement (Assignments, StepPlan), resources/js/features/procurement/StepPlan.tsx

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
- Halaman: PG-105
- Primary action: Tugaskan pejabat
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Tab Ringkasan dan Pemilihan

## ROUTE IMPACT

- RT-005

## INTERACTION CONTRACTS

- IX-001
- IX-021

## NAVIGATION CONTRACT

- Parent: Detail paket
- Entry: Tab Ringkasan
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Detail paket
- Error behavior: Alasan SoD
- State preservation: Tab di URL
- Mobile behavior: Daftar langkah

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 PPK tidak dapat sekaligus Pokja/Pejabat Pengadaan pada paket sama (kebijakan PR 34/2023 DRAFT)
- TAC-02 Rencana langkah berbeda untuk PL dan Tender sesuai kebijakan
- TAC-03 Paket pekerjaan konstruksi menampilkan dokumen teknis tambahan menurut matriks (atau menunggu validasi beserta alasan), paket barang tidak; kombinasi tanpa aturan tampil menunggu validasi, tidak diam-diam wajib atau tidak berlaku
- TAC-04 Perubahan pita nilai (misalnya HPS ditetapkan) atau varian tender membangkitkan rencana versi baru
- TAC-05 Perubahan metode membangkitkan ulang rencana dan menandai langkah terdampak
- TAC-06 Versi baru `procurement.documents` atau `procurement.methods` yang berlaku pada tanggal acuan paket menghasilkan rencana versi baru; rencana lama mempertahankan rujukan semua versi penyumbangnya (DI-16)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: SoD paket, rencana langkah, regenerasi per keluarga kebijakan (DI-16)
- Authorization: AUTHZ-34
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
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

- TASK-029

## DO NOT DO

- Jangan menetapkan pejabat secara otomatis dari penunjukan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
