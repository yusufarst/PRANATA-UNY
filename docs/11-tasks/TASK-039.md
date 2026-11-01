# TASK-039 — Dasbor, inbox dan laporan pengadaan

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan dasbor Pengadaan per peran, inbox pengadaan, dan laporan pengadaan awal (paket per tahap/metode/jenis, dokumen tertunda, rekap kontrak) dengan ekspor sesuai filter.

## WHY THIS EXISTS

OD-19/29: pengguna melihat tanggung jawab, kemajuan, bukti yang kurang dan laporan relevan.

## DEPENDS ON

- TASK-037
- TASK-023

## BLOCKS

- TASK-041
- TASK-071

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-013) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- PG-101 kartu dasbor, termasuk kartu evaluasi per pemilihan (hasil belum diisi, bukti kurang, klarifikasi terbuka, putaran siap diselesaikan, reviu menunggu)
- Laporan jumlah penyedia per pemilihan (dijajaki, diundang, menawar, memenuhi syarat, dievaluasi)
- Laporan PG-121 (definisi laporan pengadaan awal)
- Penyedia pencarian pengadaan untuk palette

## OUT OF SCOPE

- Laporan resmi berformat institusi (GAP-013)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [INFORMATION_ARCHITECTURE §4](../07-ux-design/INFORMATION_ARCHITECTURE.md#4-dasbor-per-peran)
- [WF-027](../03-workflows/WORKFLOW_CONTRACTS.md#wf-027)

## TRACEABILITY

- CAP: CAP-03, CAP-17, CAP-18
- AC/NFR: AC-05, AC-20, AC-21, AC-33
- WF/TR: WF-027 (TR-261–TR-265, 5 TR; BLOCKED_BY_GAP: TR-262)
- RT: RT-002, RT-027
- IX: IX-017, IX-018
- GAP: GAP-013
- Keputusan Owner: OD-19, OD-29

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement/Queries`
- `resources/js/pages/procurement/dashboard`

## BUSINESS RULES

- [BR-003](../02-domain/BUSINESS_RULES.md#br-003)
- [BR-022](../02-domain/BUSINESS_RULES.md#br-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Angka dalam cakupan pengguna
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement/Queries
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
- Expected affected areas: app/Modules/Procurement/Queries, resources/js/pages/procurement/dashboard

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
- Halaman: PG-101, PG-121
- Primary action: Buka antrean
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman

## ROUTE IMPACT

- RT-002
- RT-027

## INTERACTION CONTRACTS

- IX-017
- IX-018

## NAVIGATION CONTRACT

- Parent: Pengadaan
- Entry: Menu Dasbor
- Back action: Tidak berlaku
- Cancel: Tidak berlaku
- Success destination: Daftar terfilter
- Error behavior: ErrorState dengan request id dan langkah aman berikutnya
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Kartu bertumpuk

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Setiap kartu menuju daftar terfilter yang sama angkanya
- TAC-02 Laporan dapat diekspor sesuai filter
- TAC-03 Kartu kosong menjelaskan alasan

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Query dasbor/laporan scope
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Dasbor
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

- TASK-041

## DO NOT DO

- Jangan menampilkan angka tanpa sumber yang dapat ditelusuri
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
