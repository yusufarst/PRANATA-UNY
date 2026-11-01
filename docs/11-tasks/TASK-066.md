# TASK-066 — Penyusutan dan amortisasi (WF-022)

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: BLOCKED
EXECUTION STAGE: — (BLOCKED: gerbang publikasi PCR-001 — menunggu penerimaan Second Review, terbit di `origin/main` dan commit transisi status)
RISK CLASS: HIGH
MILESTONE: M7 — Nilai aset, KDP, intake dan rekonsiliasi
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan evaluator kebijakan `asset.depreciation` (metode, masa manfaat per klasifikasi, awal penyusutan, pembulatan, cutoff) dan run penyusutan per periode yang berjalan per chunk di antrean, menulis `asset_depreciation_lines` berpartisi, memperbarui proyeksi hanya untuk run non-simulasi, dan menampilkan keluaran yang dapat dijelaskan.

## WHY THIS EXISTS

CAP-13/AC-16; INV-013/022: ketidakpastian akuntansi tidak menjadi angka resmi.

## DEPENDS ON

- TASK-060

## BLOCKS

- TASK-071
- TASK-073

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Resolusi GAP-007 sebelum run resmi di produksi
- GAP terkait (GAP-007, GAP-008, GAP-009, GAP-013) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `asset_depreciation_runs`, `asset_depreciation_lines` (partisi tahunan) + job pembuat partisi; kolom proyeksi `last_depreciation_period`/`last_depreciation_run_id` pada `asset_assets` (DATA_MODEL §19 aturan 5)
- State WF-022 (TR-211–214)
- Run simulasi vs resmi; satu run resmi per periode dijaga constraint DB untuk status QUEUED/RUNNING/FAILED/COMPLETED; run resmi berurutan menurut periode
- Protokol run resmi CONCURRENCY_AND_INTEGRITY §6.1: fase VALIDATION, LINES dan PROJECTION per chunk dengan checkpoint tahan lama; proyeksi menambahkan `amount` dengan penjaga `last_depreciation_period`; lanjutan FAILED → RUNNING pada run yang sama atas permintaan pengguna berwenang (compare-and-set, bukan retry otomatis); worker usang tidak dapat menulis; CANCELLED hanya tanpa baris; tanpa run pengganti
- PG-309 daftar run, detail per aset dengan penjelasan perhitungan
- Uji tabel metode garis lurus dan pembulatan dengan kebijakan DRAFT
- Evaluator mengikuti bentuk `asset.depreciation.uny` (POLICY_REGISTRY §5): jangkar perolehan, mulai bulan sesudah jangkar, masa manfaat dari record aset lalu klasifikasi, residu nol, pembulatan kumulatif, catch-up pada periode pembukuan, cakupan flag; nilai bawaan DRAFT dari LEGACY_REPORTING_EVIDENCE §4 dan §9
- Jadwal lima tahun per aset pada detail PG-309 (akumulasi awal, beban, akumulasi, nilai buku per tahun)

## OUT OF SCOPE

- Penetapan kebijakan penyusutan resmi (GAP-007)
- Jurnal akuntansi
- Koreksi atau penggantian run resmi (menunggu kebijakan BR-017, GAP-007/008; melalui perubahan rencana)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-022](../03-workflows/WORKFLOW_CONTRACTS.md#wf-022)
- [STATE_TRANSITIONS WF-022](../03-workflows/STATE_TRANSITIONS.md#wf-022)
- [RT-022](../03-workflows/ROUTE_CONTRACTS.md#rt-022)
- [POLICY_REGISTRY §5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi)
- [LEGACY_REPORTING_EVIDENCE](../00-governance/evidence/LEGACY_REPORTING_EVIDENCE.md)
- [PERFORMANCE_AND_SCALE PB-09](../06-api-performance/PERFORMANCE_AND_SCALE.md#2-anggaran-performa)
- [CONCURRENCY_AND_INTEGRITY §6.1](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#61-protokol-run-penyusutan-resmi)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-13
- AC/NFR: AC-16
- WF/TR: WF-022 (TR-211–TR-214, 4 TR; BLOCKED_BY_GAP: TR-212) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-022
- IX: IX-018, IX-012
- GAP: GAP-007, GAP-008, GAP-009, GAP-013
- Keputusan Owner: OD-14, OD-15, OD-30

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Asset/Depreciation`
- `resources/js/pages/assets/depreciation`

## BUSINESS RULES

- [BR-017](../02-domain/BUSINESS_RULES.md#br-017)
- [BR-022](../02-domain/BUSINESS_RULES.md#br-022)
- [INV-013](../02-domain/BUSINESS_RULES.md#inv-013)
- [INV-018](../02-domain/BUSINESS_RULES.md#inv-018)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Run per chunk; lanjutan memakai run yang sama dari checkpoint; setiap transaksi chunk memeriksa status dan percobaan run
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- asset.depreciation.run
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `asset_depreciation_runs`, `asset_depreciation_lines`, asset_assets (kolom proyeksi penyusutan)
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Proyeksi nilai hanya oleh run resmi; penerapan sekali per aset per periode

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Asset/Depreciation
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
- Expected affected areas: app/Modules/Asset/Depreciation, resources/js/pages/assets/depreciation

## CONTEXT7

- Topics to verify: Laravel job batching
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
- Halaman: PG-309
- Primary action: Jalankan run
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Label simulasi tegas

## ROUTE IMPACT

- RT-022

## INTERACTION CONTRACTS

- IX-018
- IX-012

## NAVIGATION CONTRACT

- Parent: Aset → Penyusutan
- Entry: Menu
- Back action: Daftar run
- Cancel: Tidak berlaku
- Success destination: Detail run
- Error behavior: Alasan gagal
- State preservation: Filter, urutan, tab dan cursor di URL; dipulihkan saat kembali
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Run simulasi tidak mengubah proyeksi aset
- TAC-02 Run resmi kedua untuk periode yang memiliki run QUEUED/RUNNING/FAILED/COMPLETED dan run di luar urutan periode ditolak saat dibuat; permintaan run pengganti/koreksi ditolak dengan penjelasan GAP-007/008 (DI-04)
- TAC-03 Gagal setelah sebagian baris tertulis atau di tengah proyeksi dilanjutkan pada run yang sama tanpa baris ganda, proyeksi ganda atau penimpaan transaksi aset lain (DI-01, DI-02)
- TAC-04 Dua permintaan lanjutan bersamaan: satu berjalan, satu 409; worker usang tidak menulis (DI-03)
- TAC-05 Aset yang tidak dapat dihitung terdeteksi sebelum baris pertama; run FAILED tanpa baris dapat dibatalkan; setiap run FAILED memiliki jalan lanjut (DI-01, DI-04)
- TAC-06 Run berjalan per chunk tanpa mengunci pengguna lain pada data representatif; PB-09 pada 1 juta aset dibuktikan di TASK-073
- TAC-07 Setiap angka dapat dijelaskan dari aset, kebijakan dan periode
- TAC-08 Pada data sintetis, akumulasi awal, beban periode, akumulasi dan nilai buku per aset untuk periode Y, S1, S2, Q1–Q4 sama dengan oracle legacy (LEGACY_REPORTING_EVIDENCE §4) dengan toleransi pembulatan ≤ Rp1, termasuk masa manfaat 0, perolehan sesudah tahun laporan dan ekstrakomptabel; jumlah baris periode sama dengan akumulasi kumulatif yang dibulatkan
- TAC-09 Perolehan bertanggal mundur menghasilkan catch-up pada periode pembukuannya secara sama untuk semua jenis periode; aset hasil migrasi memakai masa manfaat recordnya

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Metode/pembulatan; oracle legacy LEGACY_REPORTING_EVIDENCE §4
- Feature: Run, partisi dan protokol lanjutan DI-01, DI-02, DI-04 (termasuk uji langsung DB)
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Performance: Uji chunk pada data representatif (PB-09 penuh di TASK-073)
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB
- Other: Uji konkurensi lanjutan ganda dan worker usang (DI-03)

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

- TASK-067

## DO NOT DO

- Jangan menyusutkan per hari tanpa kebijakan
- Jangan memakai formula sistem lama sebagai otoritas
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
