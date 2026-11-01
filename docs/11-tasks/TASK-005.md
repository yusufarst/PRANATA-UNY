# TASK-005 — Pipeline CI dan pemeriksa dokumentasi

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: VERIFYING
EXECUTION STAGE: VERIFYING
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: YES
```

## OBJECTIVE

Membuat workflow GitHub Actions `ci.yml` (job `changes`, `ci-fast`, `docs-check`, kerangka `ci-e2e`, dan agregat `ci-gate`) serta kerangka `ci-nightly.yml`, dengan cache, concurrency, filter path di dalam workflow dan service PostgreSQL, serta instruksi proteksi `main` untuk diterapkan Owner. Workflow bootstrap sementara (`bootstrap.yml`, [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)) digabung ke `ci.yml` lalu dihapus.

## WHY THIS EXISTS

Setiap PR Task harus diverifikasi otomatis dan cepat tanpa run ganda atau check wajib yang menggantung (QUALITY_GATES §4).

## DEPENDS ON

- TASK-004

## BLOCKS

- TASK-007
- TASK-040

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Owner menerapkan proteksi cabang di GitHub bila token eksekutor tidak berhak

## IN SCOPE

- `.github/workflows/ci.yml` dengan job `changes`, `ci-fast`, `docs-check`, kerangka `ci-e2e` dan `ci-gate` (selalu berjalan; gagal bila job yang diperlukan gagal/dibatalkan)
- `.github/workflows/ci-nightly.yml` kerangka
- Konsolidasi `bootstrap.yml` menurut tabel klasifikasi [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara): pemeriksaan PR-wajib ke `ci-fast` atau `ci-e2e` (asap HTTP, smoke Gotenberg dan Pest grup `gotenberg`), integrasi Gotenberg mendalam ke `ci-nightly.yml` tanpa diklaim dicakup `ci-gate`; `bootstrap.yml` dihapus pada PR yang sama; pemetaan dicatat di COMPLETION EVIDENCE
- Aturan keamanan workflow QUALITY_GATES §4 berlaku untuk `ci.yml` dan `ci-nightly.yml`: `pull_request` (bukan `pull_request_target`), permission minimum, action dipatok SHA, tanpa secret repositori untuk uji, runner GitHub-hosted saja (OD-37)
- Pemicu: PR ke `main` dan push ke `main`; tidak pada setiap push cabang kerja
- Cache Composer/npm, concurrency per PR dengan cancel-in-progress; run `main` tidak dibatalkan
- PostgreSQL service versi sama dengan produksi; Pest paralel
- composer audit, npm audit, gitleaks
- Pemeriksa dokumen (tautan/anchor/ID dan konsistensi TASK_PLAN) dipindahkan dari skrip perencanaan ke `scripts/docs-check`
- Filter `ci-e2e` mencakup kode aplikasi, `config/`, `bootstrap/`, lockfile dependensi, `vite.config.*`, `playwright.config.*`, `tsconfig*.json` dan berkas workflow, serta setiap jalur implementasi, template, fixture, konfigurasi dan uji yang peka Gotenberg, termasuk uji di luar `tests/e2e/` (misalnya uji grup `gotenberg` di `tests/**`) (PCR002-SR-C1)
- Instruksi ruleset `main` sesuai keputusan D-08 (opsi A: status check `ci-gate` wajib, riwayat linear, tanpa force push/penghapusan, tanpa aturan PR wajib; opsi B: PR wajib + `ci-gate`) dan perlindungan tag `v*`; Owner menerapkannya setelah `ci-gate` terbukti pada PR uji

## OUT OF SCOPE

- Deploy (TASK-040/076)
- Rahasia produksi

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [QUALITY_GATES §4](../08-testing/QUALITY_GATES.md#4-pipeline-ci-github-actions)
- [RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang)

## TRACEABILITY

- AC/NFR: AC-27
- Keputusan Owner: GOV-12

## EXPECTED MODULES / FILE AREAS

- `.github/workflows/`
- `docs/09-operations/`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- —

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada rahasia di workflow; permission token minimum; tanpa `pull_request_target`; action dipatok SHA; tanpa runner self-hosted (OD-37)

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: —
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
- Authorization change: NONE
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
- Post-Task deactivation/cleanup: tutup proses lokal yang dimulai Task; tidak ada kontainer lokal (OD-37); jangan sentuh proyek lain

## TOOL REQUIREMENTS

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: N/A
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek saja (tanpa instalasi global, perubahan PATH, variabel lingkungan sistem atau layanan; OD-37), catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: .github/workflows/, docs/09-operations/

## CONTEXT7

- Topics to verify: GitHub Actions caching, concurrency, services
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- N/A (tanpa antarmuka pengguna)

## ROUTE IMPACT

- N/A

## INTERACTION CONTRACTS

- N/A

## NAVIGATION CONTRACT

- N/A

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 PR contoh memicu `ci-fast` dan lulus ≤ 15 menit pada run pertama
- TAC-02 PR yang hanya mengubah docs hanya menjalankan `docs-check` dan `ci-gate` lulus tanpa menunggu check lain
- TAC-03 PR dengan job wajib yang gagal membuat `ci-gate` gagal (uji negatif)
- TAC-04 Run kedua lebih cepat karena cache; run usang pada PR dibatalkan
- TAC-05 Ruleset diterapkan Owner dan push ke `main` atas commit yang belum lulus `ci-gate` ditolak (uji)
- TAC-06 `bootstrap.yml` tidak ada lagi; setiap pemeriksaan yang dibuktikan TASK-002–TASK-004 berpindah menurut tabel klasifikasi QUALITY_GATES §4.1 (pemetaan dicatat); `ci-gate` gagal bila pemeriksaan PR-wajib di `ci-fast` atau `ci-e2e` yang terpicu gagal (uji negatif); pemeriksaan `ci-nightly` tidak diklaim dicakup `ci-gate`
- TAC-07 Uji regresi filter (PCR002-SR-C1): PR uji yang hanya mengubah satu uji Gotenberg di luar `tests/e2e/` memicu `ci-e2e` dengan pemeriksaan Gotenberg wajibnya, dan `ci-gate` tidak lulus tanpa pemeriksaan itu berjalan dan lulus; perilaku dicatat di COMPLETION EVIDENCE

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Integration: Run CI nyata pada PR uji (positif dan negatif)
- Production build: npm run build
- Other: Uji filter path dan agregasi `ci-gate`

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [x] Lint: Pint, ESLint, Prettier di `ci-fast`
- [x] Typecheck: Larastan dan `tsc` di `ci-fast`
- [x] Relevant automated tests: run nyata pada PR uji, positif dan negatif
- N/A Authorization: tanpa aksi pengguna baru
- [x] Route integrity: dipindahkan ke `ci-fast`
- N/A Interaction behavior: tanpa antarmuka
- N/A Browser E2E: harness dibuat TASK-007; `ci-e2e` memuat asap HTTP dan Gotenberg
- N/A Responsive/adaptive: tanpa antarmuka
- N/A Visual breathing room/density: tanpa antarmuka
- [x] Production build: `npm run build` di `ci-fast` dan `ci-e2e`
- [x] Security checks: `composer audit`, `npm audit`, gitleaks; tanpa `pull_request_target`, secret atau runner self-hosted; semua action dipatok SHA
- [x] Database safety review: pengaman reset dan `pranata_test` di `ci-fast`
- [x] Final diff review

## REVIEW REQUIREMENT

STANDARD — swa-review terstruktur + review PR otomatis (Codex); lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task
- PR ke `main` lulus `ci-gate` pada HEAD terakhir dan review sesuai REVIEW REQUIREMENT, di-merge, lalu `main` remote diverifikasi memuat merge tersebut ([EXECUTOR_OPERATING_MODEL §8](EXECUTOR_OPERATING_MODEL.md#8-merge-dan-rekonsiliasi-done))
- TASK_PLAN dan CURRENT_HANDOFF diperbarui; checklist dicentang hanya bila DONE

## COMPLETION EVIDENCE

- Test outputs: run [41342512596](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41342512596) pada HEAD `9449672`: semua job sukses. Run push `main` 41342932721 sukses. TAC-01: `ci-fast` lulus 112 dtk pada run pertama 41341172993. TAC-02: [PR #17](https://github.com/yusufarst/PRANATA-UNY/pull/17), run 41342978443: `ci-fast` dan `ci-e2e` skipped, `docs-check` dan `ci-gate` success. TAC-03 dan TAC-06: [PR #13](https://github.com/yusufarst/PRANATA-UNY/pull/13) `ci-fast` failure, [PR #14](https://github.com/yusufarst/PRANATA-UNY/pull/14) `ci-e2e` failure, [PR #15](https://github.com/yusufarst/PRANATA-UNY/pull/15) `docs-check` failure; ketiganya `ci-gate` failure. TAC-04: dengan cache `ci-fast` 61 dtk dan `ci-e2e` 67 dtk pada run 41341485098; [PR #16](https://github.com/yusufarst/PRANATA-UNY/pull/16): run usang 41342163489 cancelled, run baru 41342183129 success. TAC-07: [PR #18](https://github.com/yusufarst/PRANATA-UNY/pull/18) hanya mengubah uji Gotenberg: `ci-e2e` berjalan dan success, `ci-gate` success; [PR #19](https://github.com/yusufarst/PRANATA-UNY/pull/19) dengan uji itu gagal: `ci-e2e` failure, `ci-gate` failure. **TAC-05 NOT RUN: menunggu Owner menerapkan ruleset.**
- Build result: `npm run build` lulus di `ci-fast` dan `ci-e2e`.
- Route evidence: `RouteIntegrityTest` di `ci-fast`; asap HTTP `/login` 200 dan `/register` 404 di `ci-e2e`.
- Interaction evidence: N/A.
- Browser evidence: N/A sampai TASK-007.
- Localization evidence: pemeriksa kunci terjemahan di `ci-fast`.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Codex pada `9449672`: COMPLETED_NO_FINDINGS. Review independen tidak diwajibkan untuk kelas STANDARD.
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: `.github/workflows/ci.yml`, `.github/workflows/ci-nightly.yml`; `.github/workflows/bootstrap.yml` dihapus; `scripts/docs-check.mjs`, `scripts/executor/tests/ci.probe.ps1`, `tests/Feature/Documents/GotenbergSmokeTest.php`, `docs/09-operations/MAIN_RULESET.md`, `docs/00-governance/TOOLCHAIN.md`, `README.md`.
- PR / CI run: [PR #12](https://github.com/yusufarst/PRANATA-UNY/pull/12) MERGED; run di atas. Pemetaan bootstrap menurut QUALITY_GATES §4.1: `app-boot` (install, build, migrasi, Pest, Pint, ESLint, tsc) ke `ci-fast`; boot server dan asap HTTP ke `ci-e2e`; screenshot tidak dipindahkan (bukti sekali TASK-002, diganti E2E TASK-007); `db-integration` (versi major, ekstensi, `numeric`/`timestamptz`, Pest paralel, DatabaseGuard) ke `ci-fast`; Gotenberg health dan konversi ke `ci-e2e` beserta Pest grup `gotenberg`; `quality` (qa, uji negatif, audit, gitleaks) ke `ci-fast`; pemeriksa tautan ke `docs-check`. `ci-nightly` memuat suite penuh dan migrasi, dan tidak diklaim dicakup `ci-gate`.
- Commit SHA: `9449672aa7bad77defb55ec6c0677e09587f96ee`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`.
- Notes: Task tetap VERIFYING sampai Owner menerapkan [MAIN_RULESET](../09-operations/MAIN_RULESET.md) dan eksekutor membuktikan TAC-05. PR uji #13–#19 ditutup tanpa merge; cabangnya tidak dihapus. Kode gerbang `-Bootstrap` dan `c16.probe.ps1` tidak terpakai lagi setelah `bootstrap.yml` dihapus; dibiarkan sebagai bukti C-16, dan `-Bootstrap` tidak menerima Task setelah TASK-004.

## SAFE NEXT TASK

- TASK-007

## DO NOT DO

- Jangan menyimpan rahasia di workflow
- Jangan menonaktifkan pemeriksaan wajib
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan memakai runner GitHub Actions self-hosted atau layanan CI berbayar (OD-37)
- Jangan menambahkan atribusi AI pada commit/PR
