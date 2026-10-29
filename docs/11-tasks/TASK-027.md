# TASK-027 — Paket, item, referensi anggaran dan komitmen (WF-005 inti)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan paket pengadaan dari usulan (data diturunkan, bukan diketik ulang), item paket, jenis pengadaan, referensi anggaran dan komitmen internal (peringatan tanpa blokir), status inti WF-005 dan halaman detail paket bertab.

## WHY THIS EXISTS

Paket adalah konteks bersama semua langkah pengadaan (CAP-05, BR-035).

## DEPENDS ON

- TASK-025
- TASK-024

## BLOCKS

- TASK-028
- TASK-029

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-013, GAP-028) **tidak menghalangi implementasi**; aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `proc_packages`, `proc_package_items`, `proc_package_need_links`, `proc_budget_references`, `proc_commitments`
- Stepper buat paket dari satu/beberapa usulan dengan salin baris
- Peringatan komitmen melebihi plafon tercatat (GAP-028)
- State WF-005 skeleton (TR-041–045) tanpa langkah metode
- PG-104/105/106; kapabilitas `procurement.package.*`; penyedia pencarian paket

## OUT OF SCOPE

- HPS (TASK-029)
- Metode (TASK-030)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-005](../03-workflows/WORKFLOW_CONTRACTS.md#wf-005)
- [STATE_TRANSITIONS WF-005](../03-workflows/STATE_TRANSITIONS.md#wf-005)
- [RT-005](../03-workflows/ROUTE_CONTRACTS.md#rt-005)
- [DATA_MODEL §14.1–14.2](../04-architecture/DATA_MODEL.md#14-procurement)
- [BR-035](../02-domain/BUSINESS_RULES.md#br-035)

## TRACEABILITY

- CAP: CAP-05
- AC/NFR: AC-05, AC-07
- WF/TR: WF-005 (TR-041–TR-045, 5 TR; BLOCKED_BY_GAP: TR-044) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-005
- IX: IX-001, IX-002, IX-003, IX-009, IX-011, IX-012, IX-017
- GAP: GAP-003, GAP-013, GAP-028
- Keputusan Owner: OD-06, OD-08, OD-30

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Packages, Budget)`
- `resources/js/pages/procurement/packages`

## BUSINESS RULES

- [BR-002](../02-domain/BUSINESS_RULES.md#br-002)
- [BR-005](../02-domain/BUSINESS_RULES.md#br-005)
- [BR-035](../02-domain/BUSINESS_RULES.md#br-035)
- [INV-006](../02-domain/BUSINESS_RULES.md#inv-006)

## ARCHITECTURE RULES

- Nilai uang desimal presisi
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Paket terlihat menurut grant unit/subtree atau penugasan
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_packages`, `proc_package_items`, `proc_package_need_links`, `proc_budget_references`, `proc_commitments`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Packages, Budget)
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
- Expected affected areas: app/Modules/Procurement (Packages, Budget), resources/js/pages/procurement/packages

## CONTEXT7

- Topics to verify: brick/math BigDecimal
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
- Halaman: PG-104, PG-105, PG-106
- Primary action: Buat paket
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Tab dimuat bertahap

## ROUTE IMPACT

- RT-005

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-003
- IX-009
- IX-011
- IX-012
- IX-017

## NAVIGATION CONTRACT

- Parent: Pengadaan → Paket
- Entry: Menu Paket / dari usulan
- Back action: Daftar paket (filter terjaga)
- Cancel: Kembali ke halaman asal tanpa menyimpan; konfirmasi bila ada perubahan belum tersimpan
- Success destination: Detail paket
- Error behavior: Galat per langkah
- State preservation: Langkah stepper dan filter di URL
- Mobile behavior: Stepper satu kolom

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Paket dibuat dari usulan tanpa mengetik ulang item; asal tertaut
- TAC-02 Komitmen melebihi plafon tercatat menampilkan peringatan, bukan blokir
- TAC-03 Detail paket menampilkan NextActionCard dan tab sesuai state
- TAC-04 Paket di luar cakupan tidak terlihat di daftar/pencarian/ekspor

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Kalkulasi total desimal
- Feature: WF-005 inti, komitmen, scope
- Authorization: AUTHZ-01/02
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Bagian J-03
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

- TASK-028

## DO NOT DO

- Jangan memblokir paket berdasarkan saldo keuangan yang tidak dimiliki PRANATA
- Jangan memperluas ruang lingkup tanpa TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
