# TASK-007 — Harness E2E Playwright, axe dan seeder skenario

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyiapkan Playwright (Chromium dan WebKit), integrasi axe, pengumpulan galat konsol/jaringan, basis data E2E per run yang terisolasi, `globalSetup` yang mendeteksi kegagalan setup, `ScenarioSeeder` awal dan job `ci-e2e`.

## WHY THIS EXISTS

Perjalanan kritis harus dibuktikan dengan browser nyata (AICWDF §26), dan kegagalan fixture bersama tidak boleh terbaca sebagai banyak cacat UI (TEST_STRATEGY §9).

## DEPENDS ON

- TASK-005
- TASK-006

## BLOCKS

- TASK-008
- TASK-041
- TASK-075

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN

## IN SCOPE

- Konfigurasi Playwright proyek chromium/webkit, dua viewport, dua lokal
- Fixture: kumpulkan galat konsol dan permintaan gagal, gagal bila kritis
- `globalSetup` yang memverifikasi basis data, seeder dan state login per peran, dan menghentikan run dengan pesan SETUP FAILURE
- Uji asap fixture bersama yang berjalan pertama
- Skrip siapkan/hapus `pranata_e2e_<run>` dengan guard
- `ScenarioSeeder` kerangka (organisasi sintetis, pengguna per profil, identitas unik per run) yang diperluas Task berikutnya
- Job `ci-e2e` di `ci.yml` dengan sharding dan artefak trace

## OUT OF SCOPE

- Perjalanan bisnis (ditambah oleh Task modul)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [TEST_STRATEGY §5](../08-testing/TEST_STRATEGY.md#5-e2e-browser)
- [TEST_STRATEGY §9](../08-testing/TEST_STRATEGY.md#9-keandalan-fixture-dan-kegagalan-setup)
- [USER_JOURNEYS](../07-ux-design/USER_JOURNEYS.md)

## TRACEABILITY

- CAP: CAP-20
- AC/NFR: AC-24
- RT: RT-001, RT-002

## EXPECTED MODULES / FILE AREAS

- `playwright.config.ts`
- `tests/e2e/`
- `database/seeders/ScenarioSeeder.php`
- `.github/workflows/ci.yml`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- —

## SECURITY / PERMISSION REQUIREMENTS

- Data seeder sintetis saja

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: Hanya basis data pranata_e2e_*

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
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: playwright.config.ts, tests/e2e/, database/seeders/ScenarioSeeder.php, .github/workflows/ci.yml

## CONTEXT7

- Topics to verify: Playwright config projects, webkit on Linux CI; @axe-core/playwright
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
- Halaman: PG-001, PG-003
- Primary action: —
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Uji asap shell

## ROUTE IMPACT

- RT-001
- RT-002

## INTERACTION CONTRACTS

- N/A

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Uji asap E2E (buka masuk, shell kosong) lulus di Chromium dan WebKit di CI
- TAC-02 Galat konsol yang disuntikkan membuat uji gagal (uji negatif)
- TAC-03 Seeder yang sengaja dirusak menghasilkan satu kegagalan SETUP FAILURE yang jelas, bukan banyak kegagalan uji UI
- TAC-04 Run berulang pada basis data yang sama tidak bertabrakan identitas unik
- TAC-05 Basis data E2E dibuat dan dihapus per run
- TAC-06 axe berjalan dan melaporkan pelanggaran

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Browser E2E: Uji asap
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe
- Production build: npm run build
- Other: Uji negatif konsol; uji kegagalan setup; run berulang

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
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.2 UI
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

- TASK-008

## DO NOT DO

- Jangan memakai basis data dev/test untuk E2E
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
