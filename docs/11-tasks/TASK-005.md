# TASK-005 — Pipeline CI dan pemeriksa dokumentasi

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: YES
```

## OBJECTIVE

Membuat workflow GitHub Actions `ci.yml` (job `changes`, `ci-fast`, `docs-check`, kerangka `ci-e2e`, dan agregat `ci-gate`) serta kerangka `ci-nightly.yml`, dengan cache, concurrency, filter path di dalam workflow dan service PostgreSQL, serta instruksi proteksi `main` untuk diterapkan Owner.

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
- Pemicu: PR ke `main` dan push ke `main`; tidak pada setiap push cabang kerja
- Cache Composer/npm, concurrency per PR dengan cancel-in-progress; run `main` tidak dibatalkan
- PostgreSQL service versi sama dengan produksi; Pest paralel
- composer audit, npm audit, gitleaks
- Pemeriksa dokumen (tautan/anchor/ID dan konsistensi TASK_PLAN) dipindahkan dari skrip perencanaan ke `scripts/docs-check`
- Dokumen instruksi proteksi `main` (PR wajib, `ci-gate` wajib, cabang mutakhir, tanpa force push/penghapusan) dan perlindungan tag `v*` untuk diterapkan Owner

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

- Tidak ada rahasia di workflow; permission token minimum

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
- Post-Task deactivation/cleanup: tutup proses/kontainer dev yang dimulai Task; jangan sentuh proyek lain

## TOOL REQUIREMENTS

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: N/A
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

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
- TAC-05 Instruksi proteksi `main` dan tag tersedia untuk Owner

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Integration: Run CI nyata pada PR uji (positif dan negatif)
- Production build: npm run build
- Other: Uji filter path dan agregasi `ci-gate`

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
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task
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

- TASK-007

## DO NOT DO

- Jangan menyimpan rahasia di workflow
- Jangan menonaktifkan pemeriksaan wajib
- Jangan memperluas ruang lingkup tanpa TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
