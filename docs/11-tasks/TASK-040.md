# TASK-040 — Lingkungan staging dan skrip deploy

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: YES
```

## OBJECTIVE

Menyiapkan staging (sesuai keputusan Owner atas server), skrip deploy idempoten, konfigurasi Nginx/PHP-FPM/worker/scheduler/Gotenberg, HTTPS, dan workflow deploy manual ke staging dengan persetujuan lingkungan.

## WHY THIS EXISTS

UAT-1 memerlukan staging yang setara produksi (RELEASE_PLAN).

## DEPENDS ON

- TASK-005

## BLOCKS

- TASK-041
- TASK-076

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Penyedia/spesifikasi server staging, domain staging, operator (INFRASTRUCTURE §4)
- GAP terkait (GAP-016) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Skrip provisioning terdokumentasi (bash/ansible sederhana) tanpa rahasia di repositori
- Skrip deploy RB-01 untuk staging
- Workflow GitHub `deploy-staging` manual dengan environment protection
- Health `/health/ready`
- Data staging: ScenarioSeeder + sintetis

## OUT OF SCOPE

- Produksi (TASK-076)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [INFRASTRUCTURE §4–6](../09-operations/INFRASTRUCTURE.md#4-topologi-staging-dan-produksi)
- [OPERATIONS_RUNBOOK RB-01](../09-operations/OPERATIONS_RUNBOOK.md#rb-01-deploy-rilis)

## TRACEABILITY

- AC/NFR: AC-27
- GAP: GAP-016
- Keputusan Owner: OD-18

## EXPECTED MODULES / FILE AREAS

- `deploy/`
- `.github/workflows/deploy-staging.yml`
- `app/Modules/Platform/Health`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- —

## SECURITY / PERMISSION REQUIREMENTS

- Kredensial staging hanya di server/environment secrets; agen tidak memegangnya

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: YES (staging)
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Staging saja

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Platform/Health
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
- Authorization change: NONE
- Provider/library-specific leakage into domain: NO

## COST IMPACT

- New recurring cost: infrastruktur yang dibiayai klien (server/domain/offsite) menurut keputusan Owner
- Paid third-party service: NO kecuali Owner memilih penyimpanan offsite/SMTP berbayar
- Free/open-source alternative: YES
- Estimated additional monthly production cost: 0 di luar infrastruktur yang dibiayai klien
- Owner approval required: YES untuk setiap layanan berbayar

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
- Expected affected areas: deploy/, .github/workflows/deploy-staging.yml, app/Modules/Platform/Health

## CONTEXT7

- Topics to verify: Laravel deployment, queue workers with systemd/supervisor
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

- TAC-01 Deploy staging berulang tanpa langkah manual tersembunyi
- TAC-02 HTTPS aktif; health ready melaporkan DB/antrean/disk/Gotenberg
- TAC-03 Worker dan scheduler berjalan dan pulih setelah restart

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Integration: Deploy nyata ke staging
- Production build: npm run build
- Security: Header keamanan dasar
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB
- Other: Smoke test RB-02

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
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.3 HIGH
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

- TASK-041

## DO NOT DO

- Jangan memakai data riil di staging tanpa persetujuan
- Jangan menyimpan kredensial di repositori
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
