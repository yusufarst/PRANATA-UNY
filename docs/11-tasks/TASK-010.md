# TASK-010 — Autentikasi lokal dan sesi

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan masuk dengan username atau email + password (AICWDF-COMPAT-8), throttle dan penguncian, status akun, sesi database aman dengan timeout, keluar, ganti password wajib, dan halaman masuk sesuai DESIGN_SYSTEM.

## WHY THIS EXISTS

CAP-01/AC-01 dan ADR-0007.

## DEPENDS ON

- TASK-006
- TASK-008

## BLOCKS

- TASK-011

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: SEC-D2 timeout idle dan SEC-D3 profil password (default diterapkan bila belum diputuskan, dapat dikonfigurasi)
- GAP terkait (GAP-015) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Kolom `users` sesuai DATA_MODEL §5 (role_group, username, status, locale, must_change_password, dst.)
- Login username/email, pesan generik, throttle 5/menit identitas+IP, penguncian 10/15 menit
- Sesi database, regenerasi saat login, cookie aman, timeout idle/absolut terkonfigurasi
- Ganti password wajib dan profil password
- Halaman PG-001/PG-002 dan menu akun dasar; preferensi bahasa tersimpan
- Audit peristiwa autentikasi

## OUT OF SCOPE

- Pembuatan akun oleh admin (TASK-011)
- TOTP (TASK-012)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §3–4](../05-security/SECURITY_MODEL.md#3-autentikasi)
- [ADR-0007](../adr/ADR-0007-local-authentication.md)
- [WF-001](../03-workflows/WORKFLOW_CONTRACTS.md#wf-001)

## TRACEABILITY

- CAP: CAP-01
- AC/NFR: AC-01
- WF/TR: WF-001 (TR-001–TR-003, 3 TR; BLOCKED_BY_GAP: TR-003)
- RT: RT-001
- IX: IX-019
- GAP: GAP-015
- Keputusan Owner: OD-11, OD-12

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Identity`
- `resources/js/pages/auth`
- `config/session.php`
- `config/auth.php`

## BUSINESS RULES

- [BR-027](../02-domain/BUSINESS_RULES.md#br-027)
- [INV-016](../02-domain/BUSINESS_RULES.md#inv-016)

## ARCHITECTURE RULES

- Autentikasi terpisah dari otorisasi (AICWDF §4A.8)
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- SEC-01–SEC-06, SEC-20 sebagian
- Tidak ada registrasi publik
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `users`, `sessions`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Identity
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
- Expected affected areas: app/Modules/Identity, resources/js/pages/auth, config/session.php, config/auth.php

## CONTEXT7

- Topics to verify: Laravel authentication starter kit; Laravel rate limiting; Password validation rule
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
- Halaman: PG-001, PG-002, PG-007
- Primary action: Masuk
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Halaman masuk tenang, tanpa login sosial

## ROUTE IMPACT

- RT-001

## INTERACTION CONTRACTS

- IX-019

## NAVIGATION CONTRACT

- Parent: Tidak berlaku
- Entry: /login
- Back action: Tidak berlaku
- Cancel: Tidak berlaku
- Success destination: Beranda atau tujuan aman yang tersimpan
- Error behavior: Pesan generik di form
- State preservation: Identitas yang diketik dipertahankan setelah gagal
- Mobile behavior: Satu kolom penuh

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Masuk dengan username dan dengan email berhasil untuk akun ACTIVE
- TAC-02 Akun SUSPENDED/LOCKED/DEACTIVATED tidak dapat masuk dengan pesan aman
- TAC-03 Throttle dan penguncian bekerja sesuai angka
- TAC-04 Ganti password wajib memblokir akses lain sampai selesai
- TAC-05 Keluar mengakhiri sesi; sesi lain dicabut setelah ganti password
- TAC-06 Halaman masuk lolos axe dan tampil benar dalam dua bahasa dan dua viewport

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Login/throttle/lockout/status/sesi
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-01 sebagian
- Localization/language switch: Halaman masuk dua bahasa
- Responsive: 390×844 dan 1440×900
- Accessibility: axe halaman masuk
- Production build: npm run build
- Security: SEC-01–06
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB

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

- TASK-011

## DO NOT DO

- Jangan menambahkan login Google/SSO
- Jangan menyimpan password sementara dalam bentuk dapat dibaca
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
