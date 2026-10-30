# TASK-003 — Lingkungan pengembangan terisolasi `pranata` dan pengaman basis data uji

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan Docker Compose proyek `pranata` (app, web, postgres, gotenberg, mailpit opsional) dengan port, volume dan jaringan khusus, batas sumber daya, serta pengaman yang menolak perintah destruktif pada basis data selain `pranata_*`.

## WHY THIS EXISTS

Mesin Owner menjalankan proyek lain; PRANATA tidak boleh bertabrakan atau merusak lingkungan mereka (GOV-12, U-007).

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

- `compose.yaml` dengan `name: pranata`, layanan dan port sesuai INFRASTRUCTURE §2
- Dockerfile app PHP dengan ekstensi yang dibutuhkan
- Skrip `scripts/dev/*` (up, down, reset-db yang hanya menyentuh `pranata_dev`)
- Pengaman: guard sebelum `migrate:fresh`/`db:wipe` memeriksa nama basis data dan `APP_ENV`
- Basis data `pranata_dev` dan `pranata_test` dibuat otomatis
- Pengukuran kinerja bind mount di Windows dan catatan rekomendasi

## OUT OF SCOPE

- Staging/produksi
- Redis/Valkey

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi)
- [CONCURRENCY_AND_INTEGRITY §8](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#8-isolasi-basis-data-uji)
- [TEST_STRATEGY §4](../08-testing/TEST_STRATEGY.md#4-isolasi-basis-data-uji)

## TRACEABILITY

- AC/NFR: AC-27
- GAP: GAP-016
- Keputusan Owner: GOV-12

## EXPECTED MODULES / FILE AREAS

- `compose.yaml`
- `docker/`
- `scripts/dev/`
- `.env.example`
- `tests/Support/DatabaseGuard.php`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Nama kontainer/volume/jaringan berprefiks `pranata`

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada port basis data yang terbuka selain port khusus PRANATA di localhost
- Rahasia hanya di `.env` lokal

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Perintah reset hanya untuk pranata_dev/test

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
- Expected affected areas: compose.yaml, docker/, scripts/dev/, .env.example, tests/Support/DatabaseGuard.php

## CONTEXT7

- Topics to verify: Docker Compose resource limits; Laravel parallel testing databases
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

- TAC-01 `docker compose up` hanya membuat sumber daya `pranata-*`
- TAC-02 Guard menolak reset terhadap basis data bernama lain dan saat APP_ENV=production (diuji)
- TAC-03 Pest berjalan terhadap `pranata_test`
- TAC-04 Gotenberg dapat dijangkau dari app dan tidak terekspos ke host
- TAC-05 Dokumentasi singkat cara menjalankan di README pengembang

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Uji DatabaseGuard
- Integration: Smoke test layanan compose
- Production build: npm run build
- Other: Pemeriksaan bentrok port sebelum start

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

- TASK-004

## DO NOT DO

- Jangan menjalankan prune/restart Docker global
- Jangan memakai port default yang dipakai proyek lain tanpa pemeriksaan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
