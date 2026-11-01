# TASK-004 — Perkakas kualitas dan uji arsitektur

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Memasang dan mengonfigurasi Pint, Larastan, ESLint, Prettier, TypeScript strict, Vitest, Pest (paralel), uji arsitektur batas modul, uji integritas rute dasar, pemeriksa kunci terjemahan dan pemeriksa tautan dokumentasi. Perintah gabungan dijalankan dan dibuktikan di runner GitHub-hosted melalui job `quality` workflow bootstrap (OD-37).

## WHY THIS EXISTS

Gerbang kualitas QUALITY_GATES membutuhkan perkakas yang konsisten sejak awal.

## DEPENDS ON

- TASK-003

## BLOCKS

- TASK-005
- TASK-006
- TASK-008

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN

## IN SCOPE

- Konfigurasi Pint, Larastan (level awal 5, target ≥ 6), ESLint flat config (termasuk larangan teks literal JSX, react-hooks), Prettier
- Pest + plugin arch: aturan dependensi modul ARCHITECTURE §5
- Uji integritas rute: setiap rute bernama memiliki middleware auth (kecuali allowlist publik) dan policy/authorize
- Skrip pemeriksa kunci terjemahan id/en
- Skrip pemeriksa tautan/anchor dokumentasi (`scripts/docs/check-links`)
- Perintah gabungan `composer qa` dan `npm run qa`
- Job `quality` di `.github/workflows/bootstrap.yml` ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)) yang menjalankan `composer qa`, `npm run qa`, uji negatif arch/rute, `composer audit`, `npm audit` dan gitleaks (biner sumber terbuka tanpa lisensi berbayar); pemeriksaan Node ringan (ESLint/Prettier/tsc/Vitest berkas berubah) boleh dijalankan lokal

## OUT OF SCOPE

- `ci.yml`, `ci-gate` dan ruleset (TASK-005)
- E2E (TASK-007)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [TEST_STRATEGY §1](../08-testing/TEST_STRATEGY.md#1-lapisan-dan-alat)
- [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done)
- [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)
- [ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)

## TRACEABILITY

- CAP: CAP-19, CAP-20
- AC/NFR: AC-23

## EXPECTED MODULES / FILE AREAS

- `pint.json`
- `phpstan.neon`
- `eslint.config.js`
- `tests/Arch/`
- `tests/Feature/RouteIntegrityTest.php`
- `scripts/`
- `.github/workflows/bootstrap.yml`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Aturan arch test mencerminkan arah dependensi modul

## SECURITY / PERMISSION REQUIREMENTS

- Pemindai rahasia (gitleaks) berjalan di job `quality` GitHub-hosted dan disiapkan untuk `ci-fast`

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
- Expected affected areas: pint.json, phpstan.neon, eslint.config.js, tests/Arch/, tests/Feature/RouteIntegrityTest.php, scripts/, .github/workflows/bootstrap.yml

## CONTEXT7

- Topics to verify: Pest arch testing; Larastan configuration; ESLint flat config untuk React + TS
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

- TAC-01 `composer qa` dan `npm run qa` lulus pada kode saat ini di job `quality` runner GitHub-hosted; bukti: URL run, ID run dan SHA HEAD
- TAC-02 Arch test gagal bila modul mengimpor modul yang dilarang (uji contoh negatif)
- TAC-03 Uji integritas rute gagal bila ada rute tanpa otorisasi (uji contoh negatif)
- TAC-04 Pemeriksa terjemahan dan tautan dokumentasi berjalan dan lulus

## TEST REQUIREMENTS

- Lokasi (OD-37): pemeriksaan yang memerlukan PHP, PostgreSQL, Gotenberg atau browser hanya berjalan di runner GitHub-hosted ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)); pemeriksaan Node ringan boleh lokal; uji yang tidak dijalankan dicatat NOT RUN, bukan lulus
- Lint: Pint, ESLint, Prettier
- Typecheck: tsc strict
- Unit: Pest/Vitest contoh
- Production build: npm run build
- Other: Uji negatif arch/route (contoh pelanggaran yang wajib gagal) di job `quality`

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [x] Lint: Pint, ESLint, Prettier
- [x] Typecheck: `tsc` strict dan Larastan level 7
- [x] Relevant automated tests: Pest 132 lulus, Vitest 2 lulus
- [x] Authorization: uji integritas rute, dengan uji negatif
- [x] Route integrity: `tests/Feature/RouteIntegrityTest.php`
- N/A Interaction behavior: tanpa antarmuka baru
- N/A Browser E2E: tidak diwajibkan kontrak
- N/A Responsive/adaptive: tanpa antarmuka baru
- N/A Visual breathing room/density: tanpa antarmuka baru
- [x] Production build: `npm run build`
- [x] Security checks: gitleaks seluruh riwayat (72 commit, tanpa temuan), `composer audit`, `npm audit`
- N/A Database safety review: tanpa perubahan basis data
- [x] Final diff review

## REVIEW REQUIREMENT

STANDARD — swa-review terstruktur + review PR otomatis (Codex); lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task
- PR ke `main` dengan setiap job workflow `bootstrap` sukses pada SHA HEAD PR terakhir (bukti run GitHub-hosted, [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)) dan review sesuai REVIEW REQUIREMENT; merge mode bootstrap disaksikan Owner karena `ci-gate` belum ada ([EXECUTOR_OPERATING_MODEL §8.4](EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada))
- TASK_PLAN dan CURRENT_HANDOFF diperbarui; checklist dicentang hanya bila DONE

## COMPLETION EVIDENCE

- Test outputs: run [41340121876](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41340121876) pada HEAD `150be89`, attempt 1, uji-merge `db26819`, base `f77810e`. `quality` success (98 dtk), `app-boot` success (106 dtk), `db-integration` success (84 dtk), `bootstrap-gate` success (6 dtk). `composer qa` dan `npm run qa` lulus: Pest 132, Vitest 2, translations 0 kunci (katalog belum ada), tautan dokumen 4723 tanpa masalah. Uji negatif `quality`: impor modul terlarang, rute tanpa masuk, rute tanpa otorisasi, `Gate::allows` yang diabaikan, teks literal JSX, kunci terjemahan JSON dan PHP, tautan dan anchor rusak, token acak pada gitleaks, semuanya gagal sebagaimana mestinya.
- Build result: `npm run build` lulus.
- Route evidence: seluruh rute bernama saat ini lulus `RouteIntegrityTest`; daftar publik dan akun sendiri tertulis di uji itu dan rute yang tidak ada membuat uji gagal.
- Interaction evidence: N/A.
- Browser evidence: N/A.
- Localization evidence: pemeriksa kunci berjalan; katalog dibuat TASK-006. `react/jsx-no-literals` aktif, dengan 17 berkas bawaan kit di `eslint.literal-baseline.json` sampai TASK-006.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Codex pada `b5c9f04`: COMPLETED_WITH_FINDINGS, dua P2 (katalog PHP tidak diperiksa; `Gate::allows` dianggap otorisasi), valid, diperbaiki di `150be89` dengan uji negatif. Delta diverifikasi dengan swa-review dan run di atas. Review independen tidak diwajibkan untuk kelas STANDARD.
- Cost evidence/exception if applicable: tanpa biaya; semua paket dan gitleaks sumber terbuka.
- Files changed: `eslint.config.js`, `eslint.literal-baseline.json`, `.prettierrc`, `.prettierignore`, `vitest.config.ts`, `vite.config.ts`, `package.json`, `package-lock.json`, `composer.json`, `phpunit.xml`, `.gitleaks.toml`, `tests/Arch/ModuleBoundaryTest.php`, `tests/Feature/RouteIntegrityTest.php`, `resources/js/lib/utils.test.ts`, `resources/js/pages/welcome.tsx`, `scripts/quality/check-translations.mjs`, `scripts/docs/check-links.mjs`, `.github/workflows/bootstrap.yml`, `docs/00-governance/TOOLCHAIN.md`.
- PR / CI run: [PR #10](https://github.com/yusufarst/PRANATA-UNY/pull/10) MERGED; run di atas.
- Commit SHA: `150be8980548e8081d345ab6ab7ffadd2cc18db6`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1 -Bootstrap`.
- Notes: TAC-01–TAC-04 terbukti. Gitleaks pertama melaporkan 8 temuan; semuanya nama profil `AICWDF-COMPAT-8`, diperiksa satu per satu, bukan kredensial; `.gitleaks.toml` hanya mengecualikan string itu. Semua `npm` berjalan di runner karena RAM bebas mesin Owner tinggal sekitar 50–70 MB. ESLint 9 dipakai karena `eslint-plugin-react` belum mendukung ESLint 10. Temuan untuk Claude Planning: [TOOLCHAIN](../00-governance/TOOLCHAIN.md#quality-tooling--task-004-2026-11-02).

## SAFE NEXT TASK

- TASK-005
- TASK-006
- TASK-008

## DO NOT DO

- Jangan menurunkan level analisis untuk meloloskan kode
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan memakai Docker lokal (termasuk perintah baca-saja), memasang PHP/Composer/PostgreSQL global, atau mengubah PATH, variabel lingkungan sistem atau layanan (OD-37)
- Jangan memakai runner GitHub Actions self-hosted; workflow tidak commit, push atau memakai data riil
- Jangan menambahkan atribusi AI pada commit/PR
