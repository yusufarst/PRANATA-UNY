# TASK-010 — Autentikasi lokal dan sesi

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
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
- GAP terkait (GAP-015) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Kolom `users` sesuai DATA_MODEL §5 (role_group, username, status, locale, must_change_password, dst.)
- Login username/email, pesan generik, throttle 5/menit identitas+IP, penguncian 10/15 menit
- Sesi database, regenerasi saat login, cookie aman, timeout idle/absolut terkonfigurasi
- Ganti password wajib dan profil password
- Halaman PG-001/PG-002 dan menu akun dasar; preferensi bahasa tersimpan
- Audit peristiwa autentikasi
- Penghapusan akun mandiri bawaan starter kit dihapus ([OD-38](../00-governance/DECISION_LOG.md#od-38)): rute `profile.destroy`, aksi kontrolernya dan UI hapus akun, tanpa pengganti berupa penghapusan lain; akun hanya berakhir sebagai DEACTIVATED melalui admin (TASK-011)

## OUT OF SCOPE

- Pembuatan akun oleh admin (TASK-011)
- TOTP (TASK-012)
- Penonaktifan akun oleh admin (TASK-011)
- Penghapusan permanen akun atau data pengguna: tidak tersedia sampai kebijakan retensi dan penghapusan institusi tervalidasi (GAP-023, OD-38)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §3–4](../05-security/SECURITY_MODEL.md#3-autentikasi)
- [ADR-0007](../adr/ADR-0007-local-authentication.md)
- [WF-001](../03-workflows/WORKFLOW_CONTRACTS.md#wf-001)
- [DATA_MODEL §2](../04-architecture/DATA_MODEL.md#2-peran-basis-data-dan-hak)
- [OD-38](../00-governance/DECISION_LOG.md#od-38)

## TRACEABILITY

- CAP: CAP-01
- AC/NFR: AC-01
- WF/TR: WF-001 (TR-001–TR-003, 3 TR; BLOCKED_BY_GAP: TR-003)
- RT: RT-001
- IX: IX-019
- GAP: GAP-015, GAP-023
- Keputusan Owner: OD-11, OD-12, OD-38

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Identity`
- `resources/js/pages/auth`
- `config/session.php`
- `config/auth.php`
- Rute pengaturan starter kit (`routes/settings.php`) dan UI profil di `resources/js/pages/settings`
- `tests/Feature/RouteIntegrityTest.php`, `tests/Feature/Settings/`

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
- `pranata_app` tidak memiliki dan tidak diberi hak DELETE pada `users` ([DATA_MODEL §2](../04-architecture/DATA_MODEL.md#2-peran-basis-data-dan-hak)); tidak ada penghapusan akun dari antarmuka
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
- `profile.destroy` dihapus (OD-38)

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
- TAC-07 Penghapusan akun mandiri tidak ada (OD-38): uji integritas rute membuktikan `profile.destroy` tidak terdaftar (dikeluarkan dari `OWN_ACCOUNT_ROUTES`), UI hapus akun tidak tampil, dan uji kit yang memanggil rute itu dihapus atau diganti; tidak adanya DELETE `users` untuk `pranata_app` dirujuk dari `DatabaseRolesTest` (TASK-008) tanpa uji baru

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Login/throttle/lockout/status/sesi
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute; `profile.destroy` tidak terdaftar
- Browser E2E: J-01 sebagian
- Localization/language switch: Halaman masuk dua bahasa
- Responsive: 390×844 dan 1440×900
- Accessibility: axe halaman masuk
- Production build: npm run build
- Security: SEC-01–06
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [x] Lint
- [x] Typecheck
- [x] Relevant automated tests
- [x] Authorization
- [x] Route integrity
- [x] Interaction behavior
- [x] Browser E2E if required
- [x] Responsive/adaptive if required
- [x] Visual breathing room/density if UI
- [x] Production build
- [x] Security checks if required
- [x] Database safety review
- [x] Final diff review

## REVIEW REQUIREMENT

HIGH — swa-review terstruktur + review PR otomatis (Codex) + review independen kedua; lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.2 UI + §2.3 HIGH
- PR ke `main` lulus `ci-gate` pada HEAD terakhir dan review sesuai REVIEW REQUIREMENT, di-merge, lalu `main` remote diverifikasi memuat merge tersebut ([EXECUTOR_OPERATING_MODEL §8](EXECUTOR_OPERATING_MODEL.md#8-merge-dan-rekonsiliasi-done))
- TASK_PLAN dan CURRENT_HANDOFF diperbarui; checklist dicentang hanya bila DONE

## COMPLETION EVIDENCE

- Test outputs: run [41597084810](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41597084810) pada HEAD `3e9c9eb`: `changes`, `docs-check`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)` dan `ci-gate` sukses; log Pest per uji tidak dibaca, keberhasilan job adalah buktinya. TAC-01: `AuthenticationTest` (masuk dengan username atau email tanpa memandang huruf besar, akun tanpa email masuk dengan username). TAC-02: `AuthenticationTest` (akun bukan ACTIVE tidak dapat masuk, identitas tak dikenal mendapat pesan yang sama dengan password salah) dan `SessionAndPasswordTest` (akun yang tidak lagi aktif kehilangan sesi pada permintaan berikutnya). TAC-03: batas 5/menit per identitas+alamat, penguncian setelah 10 gagal selama 15 menit, kegagalan di luar jendela tidak mengunci, masuk berhasil menghapus kegagalan. TAC-04: akun dengan ganti password wajib ditahan di PG-002 `/password/change` (303 untuk mutasi), keluar dan pilihan bahasa tetap terbuka. TAC-05: keluar mengakhiri sesi; ganti atau reset password mencabut sesi lain dan token remember; timeout idle (lebih pendek untuk SUPER_ADMIN) dan absolut. TAC-06: halaman masuk lolos axe dalam dua bahasa pada 390×844 dan 1440×900. TAC-07: `RouteIntegrityTest` membuktikan `profile.destroy` tidak terdaftar, UI hapus akun dihapus, uji kit diganti; tidak adanya DELETE `users` untuk `pranata_app` dirujuk dari `DatabaseRolesTest` (TASK-008). Migrasi kolom akun `users` lulus `migrate:fresh` dan rollback di `ci-fast`.
- Build result: `npm run build` lulus di `ci-fast`; Pint, Larastan, ESLint, Prettier dan TypeScript lulus.
- Route evidence: `RouteIntegrityTest` lulus; rute PG-002 `/password/change` terdaftar, `profile.destroy` tidak ada.
- Interaction evidence: identitas yang diketik dipertahankan setelah gagal; pesan generik; pengalihan 303 untuk mutasi yang kehabisan waktu atau tertahan ganti password.
- Browser evidence: alur E2E login, shell dan bahasa lulus di Chromium dan WebKit pada kedua shard; axe halaman masuk lulus.
- Localization evidence: teks baru di `lang/{id,en}/auth.php` dan `resources/js/locales/{id,en}/{auth,account}.json`; `translations:check` lulus; bahasa tersimpan `users.locale` disalin ke sesi saat masuk.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Codex review 5748507514 pada `9fca296` (`COMPLETED_WITH_FINDINGS`): P1 remember-me SUPER_ADMIN, P1 audit ganti password setelah commit, P2 rute terverifikasi untuk akun tanpa email; ketiganya valid dan diperbaiki di `3e9c9eb`, dibalas (4452001264, 4452002040, 4452002578); thread dibiarkan unresolved untuk Owner karena token eksekutor tidak dapat me-resolve; Codex tidak diminta ulang. Review independen HIGH pada `9fca296`: FAIL dengan P1 konfirmasi password lewat username Fortify `login`, P1 remember SUPER_ADMIN, P2 sesi LOCKED tetap hidup, P2 umur sesi sama dengan idle; semuanya diperbaiki di `3e9c9eb` bersama P3 pengalihan 303, waktu jalur terkunci dan retry `ChangePassword`. Review delta `9fca296..3e9c9eb`: PASS tanpa P0/P1/P2. P3 yang ditunda: cookie remember lama setelah promosi ke SUPER_ADMIN belum ditolak (perubahan peran TASK-011); race cek-lalu-hash penguncian; penulisan locale di luar Action; bahasa tamu diganti bahasa tersimpan saat masuk; `SESSION_SECURE_COOKIE=false` di `.env.example` perlu cek saat deploy; uji dua faktor berhenti di challenge; listener `PasswordChanged` harus tetap hanya basis data. Journey browser PG-002 opsional tidak ditambahkan.
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 61 berkas di [PR #38](https://github.com/yusufarst/PRANATA-UNY/pull/38), terutama `app/Modules/Identity/` (Action `ChangePassword`, middleware `EnsurePasswordIsChanged` dan `EnsureSessionIsCurrent`, `CredentialAuthenticator`, `LoginLockout`, migrasi kolom akun), `app/Modules/Audit/Listeners/RecordAuthenticationEvents.php`, `config/auth.php`, `config/session.php`, `config/fortify.php`, `resources/js/pages/auth/`, `routes/settings.php`, `.github/workflows/ci.yml`, `.gitleaks.toml`, serta uji di `tests/Feature/Auth`, `tests/Feature/Identity` dan `tests/Feature/Settings`.
- PR / CI run: [PR #38](https://github.com/yusufarst/PRANATA-UNY/pull/38) MERGED; run di atas. Run gagal sebelumnya: 41585417711 dan 41588911075 (probe arsitektur `ci-fast` menghapus modul Identity yang asli, diperbaiki di `561baa6`), 41594312253 (pemindaian rahasia menandai password uji sintetis, di-allowlist sempit di `fbd675b`). Run hijau antara: 41588481276 (`41b2820`), 41594982055 (`fbd675b`).
- Commit SHA: `3e9c9eb300fc2db16ef3c9c12d7cbcbc474d4749`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode SUPERVISED karena PR menyentuh `.github/workflows/ci.yml` (§8.1.6).
- Notes: TAC-01–TAC-07 terbukti di CI. Default SEC-D2 (timeout idle) dan SEC-D3 (profil password COMPAT-8) diterapkan dan dapat dikonfigurasi. Tanpa registrasi publik dan tanpa login Google.

## SAFE NEXT TASK

- TASK-011

## DO NOT DO

- Jangan menambahkan login Google/SSO
- Jangan menyimpan password sementara dalam bentuk dapat dibaca
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan memberi `pranata_app` hak DELETE pada `users` atau menambah penghapusan akun/hard delete pengguna (OD-38, GAP-023)
- Jangan menambahkan atribusi AI pada commit/PR
