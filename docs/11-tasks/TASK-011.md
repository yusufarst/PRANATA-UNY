# TASK-011 — Administrasi pengguna, aktivasi dan pemulihan dibantu admin

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan pengelolaan pengguna oleh admin (buat, ubah, suspend, nonaktifkan), aktivasi akun dengan kode sekali pakai, reset password dibantu admin dengan pemeriksaan identitas dan maker-checker untuk akun istimewa, daftar sesi aktif, serta reset email opsional bila SMTP ada. "Nonaktifkan" berarti status DEACTIVATED dengan pencabutan semua sesi; baris pengguna dan riwayat auditnya tetap (OD-38).

## WHY THIS EXISTS

Pemulihan akun harus selalu tersedia tanpa ketergantungan email institusi (FUT-03, GAP-015 provisional).

## DEPENDS ON

- TASK-010
- TASK-009

## BLOCKS

- TASK-012
- TASK-013

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: SEC-D6 SMTP opsional
- Keputusan/prasyarat Owner: SEC-D7 kanal penyerahan kode aktivasi penyedia
- GAP terkait (GAP-015) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- PG-502 daftar/detail/form pengguna (role_group, status, lokal)
- `identity_password_resets` dan alur reset dibantu admin (password sementara sekali tampil, kedaluwarsa, wajib ganti)
- Maker-checker reset akun SUPER_ADMIN/istimewa melalui penentu "akun istimewa" yang dapat diperluas (TASK-015 menambah pemegang penugasan keputusan)
- Aktivasi akun baru dengan kode sekali pakai
- Halaman sesi aktif pengguna + cabut sesi oleh admin
- Penonaktifan akun ([OD-38](../00-governance/DECISION_LOG.md#od-38)): status DEACTIVATED dan `deactivated_at` diisi, semua sesi pengguna dicabut, `remember_token` diganti sehingga cookie remember-me lama tidak berlaku, peristiwa tercatat di `audit_events`; tidak ada baris `users` yang dihapus dan riwayat tetap ([OPERATIONS_RUNBOOK RB-13](../09-operations/OPERATIONS_RUNBOOK.md#rb-13-offboarding-pengguna))
- Reset via email bila mail dikonfigurasi (dinonaktifkan otomatis bila tidak)
- Notifikasi in-app ke pengguna setelah reset
- Audit semua aksi

## OUT OF SCOPE

- Grant akses (TASK-014)
- Akun penyedia via portal (TASK-043 memakai mekanisme ini)
- Penghapusan permanen akun atau data pengguna: tidak tersedia sampai kebijakan retensi dan penghapusan institusi tervalidasi (GAP-023, OD-38)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §3.1](../05-security/SECURITY_MODEL.md#31-pemulihan-akun)
- [OPERATIONS_RUNBOOK RB-08](../09-operations/OPERATIONS_RUNBOOK.md#rb-08-reset-password-dibantu-admin)
- [INFORMATION_ARCHITECTURE §3.6](../07-ux-design/INFORMATION_ARCHITECTURE.md#36-administrasi)
- [OPERATIONS_RUNBOOK RB-13](../09-operations/OPERATIONS_RUNBOOK.md#rb-13-offboarding-pengguna)
- [DATA_MODEL §5](../04-architecture/DATA_MODEL.md#5-identity)
- [OD-38](../00-governance/DECISION_LOG.md#od-38)

## TRACEABILITY

- CAP: CAP-01, CAP-02
- AC/NFR: AC-01, AC-04
- WF/TR: WF-001 (TR-001–TR-003, 3 TR; BLOCKED_BY_GAP: TR-003)
- RT: RT-001, RT-002
- IX: IX-001, IX-002, IX-007
- GAP: GAP-015, GAP-023
- Keputusan Owner: OD-10, OD-11, OD-38

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Identity`
- `app/Modules/Administration`
- `resources/js/pages/admin/users`

## BUSINESS RULES

- [INV-016](../02-domain/BUSINESS_RULES.md#inv-016)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- SEC-20
- Admin tidak dapat mereset dirinya sendiri lewat alur dibantu
- Password sementara tidak masuk log/audit
- Penonaktifan tanpa hak DELETE pada `users` ([DATA_MODEL §2](../04-architecture/DATA_MODEL.md#2-peran-basis-data-dan-hak))
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `identity_password_resets`; `users` (status, `deactivated_at`) dan `sessions` (pencabutan) tanpa penghapusan baris `users`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Identity, app/Modules/Administration
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
- Expected affected areas: app/Modules/Identity, app/Modules/Administration, resources/js/pages/admin/users

## CONTEXT7

- Topics to verify: Laravel password reset broker; Laravel mail configuration
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
- Halaman: PG-502, PG-002, PG-007
- Primary action: Buat pengguna / Reset dibantu
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Dialog reset menampilkan password sementara sekali

## ROUTE IMPACT

- RT-001
- RT-002

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-007

## NAVIGATION CONTRACT

- Parent: Administrasi → Pengguna
- Entry: Menu Pengguna
- Back action: Daftar pengguna (filter terjaga)
- Cancel: Kembali ke detail
- Success destination: Detail pengguna dengan toast
- Error behavior: Galat di form
- State preservation: Filter daftar di URL
- Mobile behavior: Tabel → kartu

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Admin membuat akun INTERNAL/VENDOR tanpa password diketahui admin (aktivasi)
- TAC-02 Reset dibantu: password sementara sekali tampil, kedaluwarsa 24 jam, wajib ganti, tercatat dengan metode verifikasi
- TAC-03 Reset akun istimewa membutuhkan admin kedua
- TAC-04 Pengguna melihat dan mencabut sesi aktifnya; admin dapat mencabut sesi pengguna
- TAC-05 Tanpa SMTP, tautan reset email tidak ditampilkan
- TAC-06 Admin menonaktifkan akun: status DEACTIVATED dan `deactivated_at` terisi, setiap sesi aktif pengguna dicabut saat itu (permintaan berikutnya dengan sesi lama ditolak), cookie remember-me yang diterbitkan sebelum penonaktifan tidak memulihkan akses terautentikasi (selaras dengan TASK-010 TAC-02; PCR003-SR-C1), peristiwa tercatat di `audit_events`, baris `users` dan riwayat auditnya tetap ada, dan akun tidak dapat masuk (TASK-010 TAC-02)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Alur reset/aktivasi/maker-checker/sesi dan penonaktifan (status, pencabutan sesi, audit, baris tetap)
- Authorization: Hanya admin.users.manage
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-18
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: SEC-20
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

- Test outputs: run [41625273188](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41625273188) pada HEAD `da765f1`: `changes`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)` dan `ci-gate` sukses; `docs-check` dilewati (tanpa perubahan dokumen); log Pest per uji tidak dibaca, keberhasilan job adalah buktinya. TAC-01: `UserAdministrationTest` (akun INTERNAL/VENDOR dibuat dengan kode aktivasi sekali tampil, ditolak saat masuk setelah 24 jam, habis dipakai oleh ganti password wajib). TAC-02: reset dibantu dengan metode pemeriksaan identitas tercatat, password sementara sekali tampil, kedaluwarsa 24 jam, wajib ganti; rahasia yang kedaluwarsa atau dicabut ditolak saat masuk dan saat ganti password wajib; tanpa cookie remember selama ganti password wajib. TAC-03: akun istimewa butuh admin kedua yang bukan peminta dan bukan target; email akun istimewa tidak dapat diubah oleh satu admin; pemilik akun tidak dapat membatalkan reset untuk dirinya. TAC-04: `SessionAndPasswordTest` dan `UserAdministrationTest` (pengguna melihat dan mencabut sesinya, admin mencabut sesi pengguna). TAC-05: `PasswordResetTest` (tanpa SMTP tautan reset email tidak tampil dan rutenya 404 lewat `EnsureEmailResetIsAvailable`). TAC-06: penonaktifan mengisi DEACTIVATED dan `deactivated_at`, mengakhiri semua sesi, mengganti `remember_token`, mencabut rahasia terbuka, tercatat di `audit_events` dengan alasan; baris `users` tetap; cek basis data `users_deactivated_at_matches_status`. Migrasi `identity_password_resets` dan `notifications` lulus `migrate:fresh` dan rollback di `ci-fast`.
- Build result: `npm run build` lulus di `ci-fast`; Pint, Larastan, ESLint, Prettier, TypeScript, `translations:check` dan pemindaian rahasia lulus.
- Route evidence: `RouteIntegrityTest` lulus dengan rute PG-502 di bawah `auth` + `can:MANAGE_USERS`; alamat GET reset dan persetujuan reset mengalihkan ke detail akun tanpa rahasia.
- Interaction evidence: filter daftar di URL; tabel pada layar lebar, kartu pada layar sempit; Action per perintah (buat, ubah, suspend, aktifkan kembali, nonaktifkan) dengan konflik `lock_version` 409; rahasia yang diterbitkan dihapus dari riwayat Inertia (`router.replaceProp`) sehingga Back tidak menampilkannya lagi; notifikasi in-app setelah reset.
- Browser evidence: J-18 `tests/e2e/journeys/account-recovery.spec.ts` (reset dibantu, persetujuan admin kedua, ganti password wajib, Back tidak menampilkan rahasia, axe) lulus di semua proyek browser pada kedua shard.
- Localization evidence: teks baru di `lang/{id,en}/{auth,identity}.php` dan `resources/js/locales/`; `translations:check` lulus.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Review independen HIGH pada `66523ff`: P1 suspend/aktifkan kembali belum ada; P1 satu admin dapat mengubah email akun istimewa lalu mengambil alih lewat reset email; P2 rahasia sementara tertinggal di riwayat; P2 pemilik dapat membatalkan reset dibantu untuk dirinya; semuanya diperbaiki di `f5634b0` dengan uji di `e1c5cbd` dan `4d04993`. Codex review 5751037269 pada `4d04993` (`COMPLETED_WITH_FINDINGS`): P2 sesi remember dapat menyelesaikan ganti password wajib dengan rahasia kedaluwarsa; P2 password sementara tertinggal di alamat POST reset/persetujuan (405 saat muat ulang atau ganti bahasa); keduanya diperbaiki di `da765f1`, diverifikasi delta dan dibalas (4453872423, 4453873162); thread dibiarkan unresolved untuk Owner karena token eksekutor tidak dapat me-resolve; Codex tidak diminta ulang. P3 yang ditunda: reset dibantu tidak menghapus penguncian masuk (kedaluwarsa sendiri); reset email berjalan untuk akun DEACTIVATED/SUSPENDED (masuk tetap menolaknya); email yang dimasukkan admin ditandai terverifikasi untuk akun biasa; pembuatan bersamaan dengan username atau email sama memberi 500 alih-alih galat field (constraint unik tetap menahan); sesi kedaluwarsa masih terdaftar sampai dibersihkan garbage collector.
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 74 berkas di [PR #40](https://github.com/yusufarst/PRANATA-UNY/pull/40), terutama `app/Modules/Identity/` (38 berkas: Action, kontroler, request, `OneTimeSecret`, model dan migrasi `identity_password_resets`), `app/Modules/Audit/` (listener administrasi akun), migrasi `notifications`, `config/auth.php`, `resources/js/pages/`, `resources/js/components/`, `resources/js/locales/`, `lang/{id,en}/`, serta uji di `tests/Feature/Identity`, `tests/Feature/Auth`, `tests/Feature/RouteIntegrityTest.php` dan `tests/e2e/journeys`. Tanpa perubahan `.github/workflows` atau `.gitleaks.toml`.
- PR / CI run: [PR #40](https://github.com/yusufarst/PRANATA-UNY/pull/40) MERGED; run di atas. Run gagal yang tercatat: 41621177943 pada `f5634b0` (satu uji mengharapkan kunci izin lama, diperbaiki di `e1c5cbd`), 41622182480 pada `e1c5cbd` (J-18 mengharapkan URL yang salah setelah Back, diperbaiki di `4d04993`). Run hijau antara: 41623080072 (`4d04993`).
- Commit SHA: `da765f1004761669c6af7ba4ab77093825ad7d0f`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode AUTONOMOUS (opsi A).
- Notes: TAC-01–TAC-06 terbukti di CI. Default SEC-D6 (SMTP opsional) dan SEC-D7 (kode aktivasi diserahkan langsung) diterapkan. Tidak ada baris `users` yang dihapus (OD-38). Modul `app/Modules/Administration` tidak dibuat; administrasi akun berada di modul Identity.

## SAFE NEXT TASK

- TASK-012
- TASK-013

## DO NOT DO

- Jangan mengirim password lewat email
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
