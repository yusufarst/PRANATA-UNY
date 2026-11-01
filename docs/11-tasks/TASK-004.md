# TASK-004 — Perkakas kualitas dan uji arsitektur

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Memasang dan mengonfigurasi Pint, Larastan, ESLint, Prettier, TypeScript strict, Vitest, Pest (paralel), uji arsitektur batas modul, uji integritas rute dasar, pemeriksa kunci terjemahan dan pemeriksa tautan dokumentasi.

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

## OUT OF SCOPE

- CI (TASK-005)
- E2E (TASK-007)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [TEST_STRATEGY §1](../08-testing/TEST_STRATEGY.md#1-lapisan-dan-alat)
- [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done)
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

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Aturan arch test mencerminkan arah dependensi modul

## SECURITY / PERMISSION REQUIREMENTS

- Pemindai rahasia lokal (gitleaks) disiapkan untuk CI

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
- Expected affected areas: pint.json, phpstan.neon, eslint.config.js, tests/Arch/, tests/Feature/RouteIntegrityTest.php, scripts/

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

- TAC-01 `composer qa` dan `npm run qa` lulus pada kode saat ini
- TAC-02 Arch test gagal bila modul mengimpor modul yang dilarang (uji contoh negatif)
- TAC-03 Uji integritas rute gagal bila ada rute tanpa otorisasi (uji contoh negatif)
- TAC-04 Pemeriksa terjemahan dan tautan dokumentasi berjalan dan lulus

## TEST REQUIREMENTS

- Lint: Pint, ESLint, Prettier
- Typecheck: tsc strict
- Unit: Pest/Vitest contoh
- Production build: npm run build
- Other: Uji negatif arch/route

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
- PR ke `main` dengan bukti verifikasi lokal lengkap dan review sesuai REVIEW REQUIREMENT; merge mode bootstrap disaksikan Owner karena `ci-gate` belum ada ([EXECUTOR_OPERATING_MODEL §8.4](EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada))
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

- TASK-005
- TASK-006
- TASK-008

## DO NOT DO

- Jangan menurunkan level analisis untuk meloloskan kode
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
