# TASK-035 — Hasil dan penetapan pemenang (WF-010)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan usulan dan penetapan hasil (pemenang dan cadangan) oleh fungsi yang berwenang menurut nilai pada kebijakan `procurement.authority`, pengumuman (referensi kanal) dan kegagalan pemilihan.

## WHY THIS EXISTS

WF-010; kewenangan penetapan bergantung nilai (PR 34 Pasal 9; Perpres Pasal 9/13).

## DEPENDS ON

- TASK-034

## BLOCKS

- TASK-036

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Jawaban D-26 tentang jumlah pemenang per paket (GAP-003/013/017), atau penerimaan Owner yang tercatat atas asumsi V1 satu pemenang per paket; bila praktik resmi memerlukan beberapa pemenang, perubahan rencana atas TASK-035–TASK-037 lebih dahulu
- GAP terkait (GAP-003, GAP-013, GAP-017, GAP-027) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `proc_results`, `proc_result_rankings` dengan FK komposit hasil dan partisipasi pada paket yang sama; satu WINNER dan peringkat unik per hasil (asumsi rancangan V1: satu pemenang per paket, menunggu D-26)
- State WF-010 (TR-091–094)
- Evaluator `WinnerAuthority` dari TASK-024
- PG-112; dokumen surat penetapan/laporan sebagai draf

## OUT OF SCOPE

- SPPBJ (TASK-036)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-010](../03-workflows/WORKFLOW_CONTRACTS.md#wf-010)
- [STATE_TRANSITIONS WF-010](../03-workflows/STATE_TRANSITIONS.md#wf-010)
- [RT-010](../03-workflows/ROUTE_CONTRACTS.md#rt-010)
- [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05
- AC/NFR: AC-07
- WF/TR: WF-010 (TR-091–TR-094, 4 TR; BLOCKED_BY_GAP: TR-092) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-010
- IX: IX-003, IX-006, IX-007, IX-008, IX-009
- GAP: GAP-003, GAP-013, GAP-017, GAP-027
- Keputusan Owner: OD-27

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Result)`
- `resources/js/pages/procurement/result`

## BUSINESS RULES

- [BR-004](../02-domain/BUSINESS_RULES.md#br-004)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-41
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_results`, `proc_result_rankings`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Result)
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
- Expected affected areas: app/Modules/Procurement (Result), resources/js/pages/procurement/result

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
- Halaman: PG-112
- Primary action: Tetapkan hasil
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman

## ROUTE IMPACT

- RT-010

## INTERACTION CONTRACTS

- IX-003
- IX-006
- IX-007
- IX-008
- IX-009

## NAVIGATION CONTRACT

- Parent: Detail paket
- Entry: Tab Hasil
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Tab Hasil
- Error behavior: Gerbang kewenangan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Penetap hasil sesuai ambang nilai kebijakan (uji batas)
- TAC-02 Pemilihan gagal tercatat dengan alasan dan langkah berikut
- TAC-03 Satu hasil ditetapkan per paket
- TAC-04 Peringkat atau pemenang dari partisipasi paket lain, dua WINNER, atau peringkat ganda dalam satu hasil ditolak aplikasi dan DB; kolom `package_id` hasil tidak dapat diubah (DI-05, DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-010; DI-05 (uji negatif langsung DB)
- Authorization: AUTHZ-41
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-03, J-05
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

- TASK-036

## DO NOT DO

- Jangan menetapkan pemenang otomatis dari peringkat
- Jangan mengakali beberapa pemenang dalam satu paket di luar model yang disetujui
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
