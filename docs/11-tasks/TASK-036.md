# TASK-036 — SPPBJ dengan empat tanggung jawab (WF-011)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan SPPBJ dengan penyusun, pemeriksa, penerbit dan penanda tangan yang terpisah menurut kebijakan, dokumen SPPBJ bernomor, tenggat jaminan pelaksanaan dari hari kerja bila kebijakan menetapkan, dan pembatalan beralasan.

## WHY THIS EXISTS

WF-011/GAP-004: empat tanggung jawab tidak boleh disamakan.

## DEPENDS ON

- TASK-035
- TASK-021

## BLOCKS

- TASK-037

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-004, GAP-013, GAP-017) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `proc_sppbj` (FK komposit `(result_id, package_id)`; `package_id` tidak dapat diubah) dan `proc_sppbj_acts` (kontribusi PREPARED/CHECKED/ISSUED/SIGNED terpisah dengan pelaku, penugasan pada paket yang sama — diperiksa constraint trigger — dan bukti)
- Urutan antarkontribusi tidak dipaksakan kecuali kebijakan `procurement.authority` menetapkannya (WF-011: TR-102–104 tidak saling menjadi prasyarat universal)
- State WF-011 (TR-101–TR-106)
- Perhitungan tenggat jaminan memakai `ref_calendar_days` bila kebijakan menyatakan
- PG-113

## OUT OF SCOPE

- Kontrak (TASK-037)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-011](../03-workflows/WORKFLOW_CONTRACTS.md#wf-011)
- [STATE_TRANSITIONS WF-011](../03-workflows/STATE_TRANSITIONS.md#wf-011)
- [RT-011](../03-workflows/ROUTE_CONTRACTS.md#rt-011)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05, CAP-08
- AC/NFR: AC-07, AC-11
- WF/TR: WF-011 (TR-101–TR-106, 6 TR; BLOCKED_BY_GAP: TR-102, TR-103, TR-104, TR-105) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-011
- IX: IX-003, IX-006, IX-007, IX-010
- GAP: GAP-003, GAP-004, GAP-013, GAP-017

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Sppbj)`
- `resources/js/pages/procurement/sppbj`

## BUSINESS RULES

- [BR-009](../02-domain/BUSINESS_RULES.md#br-009)
- [BR-010](../02-domain/BUSINESS_RULES.md#br-010)
- [BR-011](../02-domain/BUSINESS_RULES.md#br-011)
- [INV-005](../02-domain/BUSINESS_RULES.md#inv-005)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-42
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_sppbj`, `proc_sppbj_acts`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Sppbj)
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
- Expected affected areas: app/Modules/Procurement (Sppbj), resources/js/pages/procurement/sppbj

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
- Halaman: PG-113
- Primary action: Catat kontribusi yang menjadi tanggung jawab pengguna
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Satu aksi utama sesuai penugasan pengguna; status lengkap diturunkan dari kontribusi wajib

## ROUTE IMPACT

- RT-011

## INTERACTION CONTRACTS

- IX-003
- IX-006
- IX-007
- IX-010

## NAVIGATION CONTRACT

- Parent: Detail paket
- Entry: Tab Hasil
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: SPPBJ
- Error behavior: Penjelasan gerbang (kebijakan/izin/SoD) beserta siapa yang dapat bertindak
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Satu orang tidak dapat menjalankan dua tanggung jawab yang dipisah kebijakan
- TAC-02 Tanpa urutan dari kebijakan, pemeriksaan, penerbitan dan penandatanganan dapat dicatat dalam urutan apa pun; SPPBJ lengkap bila semua kontribusi wajib tercatat
- TAC-03 Dokumen SPPBJ DRAF→FINAL bernomor unik
- TAC-04 Tenggat jaminan dihitung hanya bila kebijakan menetapkannya
- TAC-05 SPPBJ untuk hasil paket lain dan kontribusi dengan penugasan paket lain ditolak aplikasi dan DB; kolom `package_id` SPPBJ tidak dapat diubah (DI-06, DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-011; DI-06 (uji negatif langsung DB)
- Authorization: AUTHZ-42
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-05
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

- TASK-037

## DO NOT DO

- Jangan memaksakan PPK sebagai keempat fungsi
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
