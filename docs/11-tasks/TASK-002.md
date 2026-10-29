# TASK-002 — Bootstrap aplikasi Laravel React dan struktur modul

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Membuat kerangka aplikasi dari Laravel React starter kit resmi versi stabil terkini di root repositori tanpa menimpa dokumen tata kelola, mematikan registrasi publik, membentuk struktur `app/Modules`, dan mencatat versi aktual di TOOLCHAIN.

## WHY THIS EXISTS

Semua Task berikutnya membutuhkan fondasi yang sesuai ADR-0001 dan versi yang diverifikasi, bukan asumsi.

## DEPENDS ON

- TASK-001

## BLOCKS

- TASK-003

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN

## IN SCOPE

- Verifikasi versi Laravel, PHP, Inertia, React, TypeScript, Tailwind, shadcn/ui yang didukung saat ini dari dokumentasi resmi
- Pembuatan aplikasi dari starter kit React; penggabungan aman dengan berkas yang ada (AGENTS.md, CLAUDE.md, README, docs/, .gitignore) tanpa kehilangan isi
- Penonaktifan registrasi publik dan fitur yang bertentangan dengan OD-11 (registrasi terbuka, login sosial)
- Kerangka modul `app/Modules/<Module>` dengan service provider pemuat rute, migrasi dan policy per modul
- Konfigurasi dasar: zona waktu aplikasi UTC, lokal default `id`, fallback `en`, PostgreSQL sebagai koneksi default
- Pembaruan TOOLCHAIN dengan versi aktual dan hasil verifikasi

## OUT OF SCOPE

- Fitur bisnis
- Lingkungan Docker (TASK-003)
- CI (TASK-005)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [ARCHITECTURE §3–6](../04-architecture/ARCHITECTURE.md)
- [ADR-0001](../adr/ADR-0001-modular-monolith-stack.md)
- [TOOLCHAIN](../00-governance/TOOLCHAIN.md)
- [ENGINEERING_PRINCIPLES](../00-governance/ENGINEERING_PRINCIPLES.md)

## TRACEABILITY

- CAP: CAP-01
- AC/NFR: AC-01, AC-26
- WF/TR: WF-001 (TR-001–TR-003, 3 TR; BLOCKED_BY_GAP: TR-003)
- RT: RT-001
- IX: IX-019
- Keputusan Owner: OD-11, OD-12

## EXPECTED MODULES / FILE AREAS

- `composer.json`
- `package.json`
- `app/Modules/*`
- `bootstrap/`
- `config/`
- `routes/`
- `resources/js/`
- `docs/00-governance/TOOLCHAIN.md`

## BUSINESS RULES

- [BR-027](../02-domain/BUSINESS_RULES.md#br-027)

## ARCHITECTURE RULES

- Struktur modul sesuai ARCHITECTURE §3
- Tidak menambah paket di luar starter kit kecuali yang dicatat di ARCHITECTURE/ADR
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6

## SECURITY / PERMISSION REQUIREMENTS

- Registrasi publik tidak tersedia (uji rute)
- APP_KEY tidak dikomit; `.env` diabaikan Git
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `tabel kerangka Laravel`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/*
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: N/A
- Replaceable adapter/contract required: NO
- Provider lock-in risk: NONE
- Future extension impact: MEDIUM
- Speculative abstraction avoided: YES

## AUTH IMPACT

- Authentication change: PROPOSED
- Authentication implementation: LARAVEL_NATIVE
- Auth profile: AICWDF-COMPAT-8 (kecuali keputusan Owner lain)
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
- Expected affected areas: composer.json, package.json, app/Modules/*, bootstrap/, config/, routes/, resources/js/, docs/00-governance/TOOLCHAIN.md

## CONTEXT7

- Topics to verify: Laravel installer/starter kit React terkini; Inertia 2 + React; Tailwind CSS 4 + shadcn/ui
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
- Halaman: PG-001
- Primary action: Masuk
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Hanya kerangka bawaan; desain di TASK-006

## ROUTE IMPACT

- RT-001

## INTERACTION CONTRACTS

- IX-019

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Aplikasi berjalan lokal dan halaman masuk bawaan tampil
- TAC-02 Rute registrasi publik tidak ada (404) dan tidak ada tautan ke sana
- TAC-03 Dokumen tata kelola tidak berubah isinya selain penambahan yang dimaksud (diff ditinjau)
- TAC-04 Struktur `app/Modules` dengan minimal satu modul contoh terdaftar dan teruji
- TAC-05 TOOLCHAIN mencatat versi aktual dan sumber verifikasinya

## TEST REQUIREMENTS

- Lint: Pint, ESLint dasar
- Typecheck: tsc
- Feature: Uji rute masuk ada, registrasi tidak ada
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB
- Build: npm run build

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

- TASK-003

## DO NOT DO

- Jangan menimpa AGENTS.md, CLAUDE.md atau isi docs/
- Jangan memasang Sanctum/Passport/Socialite
- Jangan memperluas ruang lingkup tanpa TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
