# TASK-003 — Lingkungan pengembangan terisolasi `pranata` dan pengaman basis data uji

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan alur pengembangan dan uji tanpa Docker lokal (OD-37): pengaman yang menolak perintah destruktif pada basis data selain `pranata_*`, konfigurasi pengujian untuk basis data `pranata_*`, dan verifikasi integrasi PostgreSQL (ekstensi dan semantik yang dipakai produksi) serta Gotenberg pada service container runner GitHub-hosted melalui job workflow bootstrap ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)). Pengaman eksekutor diselaraskan: tidak ada basis data lokal yang dapat dijangkau.

## WHY THIS EXISTS

Mesin Owner menjalankan proyek lain; PRANATA tidak boleh bertabrakan atau merusak lingkungan mereka (GOV-12, U-007). Sejak OD-37 PRANATA tidak memakai Docker, basis data atau layanan lokal; perilaku PostgreSQL dan Gotenberg dibuktikan di runner GitHub-hosted.

## DEPENDS ON

- TASK-002

## BLOCKS

- TASK-004

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-016) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Pengaman: guard sebelum `migrate:fresh`/`migrate:refresh`/`db:wipe`/seed destruktif memeriksa nama basis data dan `APP_ENV` (CONCURRENCY_AND_INTEGRITY §8, TEST_STRATEGY §4)
- `.env.example` dan konfigurasi pengujian untuk `pranata_test` dan `pranata_test_<token>` paralel; tanpa kredensial nyata
- Job `db-integration` di `.github/workflows/bootstrap.yml`: PostgreSQL service container dengan major yang dipatok di TOOLCHAIN (≥ 16, sama dengan target staging/produksi), pembuatan ekstensi `pg_trgm`, `ltree`, `btree_gist`, uji semantik `numeric` dan `timestamptz` (UTC), Pest (termasuk `--parallel`) terhadap `pranata_test`, uji DatabaseGuard, `migrate:fresh` + rollback
- Gotenberg service container (image dipatok) di job yang sama: health check dan konversi berkas sintetis kecil melalui route LibreOffice; hanya dijangkau dari job
- Penyelarasan pengaman eksekutor dengan OD-37: `.claude/hooks/lib/policy.ps1` dan `scripts/executor/tests/guard.tests.ps1` menolak klien dan URL basis data lokal (tidak ada PostgreSQL PRANATA di mesin Owner); larangan Docker lokal tetap
- README pengembang: alur tanpa Docker (cabang → PR draft → run GitHub-hosted → hasil dan artefak dibaca dengan `gh`), alat lokal yang diizinkan, dan larangan OD-37
- Pengukuran durasi job GitHub-hosted (dicatat di TOOLCHAIN) sebagai pengganti pengukuran bind mount

## OUT OF SCOPE

- Staging/produksi
- Redis/Valkey
- Docker, Docker Compose, basis data atau Gotenberg lokal (dilarang OD-37)
- `ci.yml` dan `ci-gate` (TASK-005)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi)
- [CONCURRENCY_AND_INTEGRITY §8](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#8-isolasi-basis-data-uji)
- [TEST_STRATEGY §4](../08-testing/TEST_STRATEGY.md#4-isolasi-basis-data-uji)
- [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)
- [TOOLCHAIN](../00-governance/TOOLCHAIN.md#docker-free-development-topology-pcr-002-od-37)

## TRACEABILITY

- AC/NFR: AC-27
- GAP: GAP-016
- Keputusan Owner: GOV-12

## EXPECTED MODULES / FILE AREAS

- `.github/workflows/bootstrap.yml`
- `.env.example`
- `phpunit.xml`
- `tests/Support/DatabaseGuard.php`
- `.claude/hooks/lib/policy.ps1`, `scripts/executor/tests/guard.tests.ps1` (aturan basis data lokal saja)
- README pengembang
- `docs/00-governance/TOOLCHAIN.md`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Basis data uji berprefiks `pranata_`; tidak ada kontainer, volume atau jaringan Docker lokal (OD-37)

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada basis data atau layanan PRANATA yang berjalan di mesin Owner; service container hanya di runner GitHub-hosted dan hanya dijangkau dari job
- Kata sandi service container adalah nilai uji sekali pakai, bukan rahasia; tidak ada secret repositori; workflow mengikuti aturan [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Perintah reset hanya untuk `pranata_test*` di service container sekali pakai; guard menolak nama lain dan `APP_ENV=production`

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
- Expected affected areas: .github/workflows/bootstrap.yml, .env.example, phpunit.xml, tests/Support/DatabaseGuard.php, .claude/hooks/lib/policy.ps1, scripts/executor/tests/guard.tests.ps1, README pengembang, TOOLCHAIN

## CONTEXT7

- Topics to verify: GitHub Actions service containers (health check, port, pin image); Laravel parallel testing databases; image resmi PostgreSQL (ekstensi contrib); Gotenberg health dan route LibreOffice
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

- TAC-01 Tidak ada berkas atau perintah Docker/Compose untuk PRANATA di repositori, skrip dan README pengembang; verifikasi tidak memerlukan kontainer, basis data atau layanan di mesin Owner; pengaman eksekutor menolak Docker lokal dan klien/URL basis data lokal (uji guard)
- TAC-02 Guard menolak reset terhadap basis data bernama lain dan saat APP_ENV=production (diuji)
- TAC-03 Pest berjalan terhadap `pranata_test` (dan `pranata_test_<token>` dengan `--parallel`) pada PostgreSQL service container di runner GitHub-hosted dengan major yang dipatok dan ekstensi `pg_trgm`, `ltree`, `btree_gist` tersedia; bukti: URL run, ID run, SHA HEAD dan versi server
- TAC-04 Gotenberg service container dapat dijangkau dari job (health dan konversi berkas sintetis berhasil) di infrastruktur GitHub-hosted, tidak dipublikasikan ke luar runner, dan tidak berjalan di mesin Owner
- TAC-05 Dokumentasi singkat cara menjalankan dan memverifikasi di README pengembang (alur PR draft → run GitHub-hosted, alat lokal yang diizinkan, larangan OD-37)

## TEST REQUIREMENTS

- Lokasi (OD-37): pemeriksaan yang memerlukan PHP, PostgreSQL, Gotenberg atau browser hanya berjalan di runner GitHub-hosted ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)); pemeriksaan Node ringan boleh lokal; uji yang tidak dijalankan dicatat NOT RUN, bukan lulus
- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Uji DatabaseGuard positif dan negatif (nama asing, `APP_ENV=production`) di job `db-integration`
- Integration: Job `db-integration`: versi major, ekstensi, `numeric`/`timestamptz`, Pest paralel, health dan konversi Gotenberg
- Production build: npm run build
- Other: Uji guard eksekutor (PowerShell lokal, ringan) untuk penolakan Docker lokal dan klien/URL basis data lokal

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
- PR ke `main` dengan setiap job workflow `bootstrap` sukses pada SHA HEAD PR terakhir (bukti run GitHub-hosted, [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)) dan review sesuai REVIEW REQUIREMENT; merge mode bootstrap disaksikan Owner karena `ci-gate` belum ada ([EXECUTOR_OPERATING_MODEL §8.4](EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada))
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

- TASK-004

## DO NOT DO

- Jangan menjalankan perintah Docker apa pun di mesin Owner, termasuk baca-saja, dan jangan menyentuh Docker, WSL atau layanan bersama (OD-37)
- Jangan memasang atau menjalankan PostgreSQL, Gotenberg atau runner GitHub Actions self-hosted di mesin Owner; jangan mengubah PATH, variabel lingkungan sistem atau layanan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
