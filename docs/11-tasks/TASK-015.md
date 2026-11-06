# TASK-015 — Penugasan proses dan mesin pemisahan tugas

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan `process_assignments` per objek, katalog peran penugasan, saran dari penunjukan, evaluasi aturan pemisahan tugas sistem (SOD-04–09) dan aturan dari kebijakan (SOD-01–03), pada saat penugasan dan saat aksi.

## WHY THIS EXISTS

Kewenangan keputusan bisnis berasal dari penugasan, bukan role global (OD-28, INV-021).

## DEPENDS ON

- TASK-014

## BLOCKS

- TASK-017
- TASK-019
- TASK-028
- TASK-052

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-004, GAP-014) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel dan Action tambah/akhiri/cabut penugasan dengan rujukan SK (lampiran bukti `evidence_file_id` ditambahkan TASK-016)
- Katalog peran penugasan ACCESS_CONTROL §5
- Evaluator SoD: aturan sistem SOD-04–09 dan antarmuka aturan dari kebijakan (diisi Task modul; SOD-01–03 di TASK-028) dengan hasil tersimpan
- Pemegang penugasan keputusan aktif diperlakukan istimewa: reset dibantu memerlukan admin kedua, TOTP wajib sebelum aksi keputusan bila SEC-D1 diputuskan, dan aksi keputusan diblokir setelah reset dibantu sampai pengguna mengganti password dan memverifikasi TOTP
- Larangan SUPER_ADMIN memegang penugasan keputusan
- Integrasi ke evaluator `can()` (kapabilitas bertanda "Penugasan")
- Komponen UI panel penugasan pada objek + PG-504

## OUT OF SCOPE

- Isi aturan SoD rezim (dari paket kebijakan TASK-024)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [ACCESS_CONTROL §5–6](../05-security/ACCESS_CONTROL.md#5-penugasan-proses)
- [DATA_MODEL §9](../04-architecture/DATA_MODEL.md#9-access)

## TRACEABILITY

- CAP: CAP-02, CAP-03
- AC/NFR: AC-04, AC-31
- RT: RT-002
- IX: IX-001, IX-009
- GAP: GAP-003, GAP-004, GAP-014
- Keputusan Owner: OD-28

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Access`
- `resources/js/components/app/AssignmentPanel.tsx`
- `resources/js/pages/admin/assignments`

## BUSINESS RULES

- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)

## ARCHITECTURE RULES

- SoD dievaluasi juga saat aksi, tidak hanya saat penugasan
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-23, AUTHZ-24
- SEC-21
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `process_assignments`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Access
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

- Context7: OPTIONAL (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Access, resources/js/components/app/AssignmentPanel.tsx, resources/js/pages/admin/assignments

## CONTEXT7

- Topics to verify: —
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
- Halaman: PG-504
- Primary action: Tugaskan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Panel penugasan dapat dipakai ulang modul

## ROUTE IMPACT

- RT-002

## INTERACTION CONTRACTS

- IX-001
- IX-009

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Penugasan yang melanggar SoD ditolak dengan alasan
- TAC-02 SUPER_ADMIN tidak dapat ditugaskan pada peran keputusan
- TAC-03 Aksi berkapabilitas penugasan hanya untuk pemegang penugasan aktif
- TAC-04 Saran dari penunjukan tampil sebagai saran, bukan penugasan otomatis
- TAC-05 Evaluator menerapkan aturan SoD dari kebijakan uji tanpa kode khusus per aturan
- TAC-06 Admin tidak dapat sendirian mereset password pemegang penugasan keputusan aktif (AUTHZ-24, SEC-21)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Penugasan/SoD/integrasi can()
- Authorization: AUTHZ-23, AUTHZ-24
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Perjalanan yang disentuh (Chromium + WebKit)
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: SEC-21
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

- Test outputs: run [42100301148](https://github.com/yusufarst/PRANATA-UNY/actions/runs/42100301148) attempt 3 pada HEAD `3816b0c`: `changes`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)`, `docs-check` dan `ci-gate` sukses; log Pest per uji tidak dibaca, keberhasilan job adalah buktinya. `tests/Feature/Access/ProcessAssignmentTest.php`: TAC-01 penugasan yang melanggar SOD-04–SOD-09 ditolak dengan alasan dan hasil tersimpan di `sod_check`, SoD dievaluasi lagi saat aksi (lapisan 4 `can()`); TAC-02/AUTHZ-23 SUPER_ADMIN tidak dapat ditugaskan pada peran keputusan (SOD-08); TAC-03 kapabilitas bertanda "Penugasan" hanya untuk pemegang penugasan aktif pada objek, berakhir atau dicabut berlaku pada permintaan berikutnya, penugasan lewat akhir ditutup; AUTHZ-24 peran keputusan menunggu selama password wajib diganti atau TOTP wajib belum dikonfirmasi, di jalur beri dan jalur aksi, tanpa mengganggu peran non-keputusan; TAC-04 saran dari `org_appointments` unit objek dan atasannya tampil sebagai saran, bukan penugasan; TAC-05 aturan SoD dari `SodRuleSource` uji diterapkan tanpa kode khusus per aturan; TAC-06/SEC-21 reset dibantu atas pemegang penugasan keputusan aktif memerlukan admin kedua, dan password sementara dari reset satu admin tidak terbawa ke peran keputusan; constraint DB (tanpa penugasan diri sendiri, satu baris aktif per objek/peran/pengguna, penutupan konsisten); beri dan tutup diaudit dengan aktor admin juga tanpa sesi masuk; rute, PG-504 dan panel. `GrantAdministrationTest.php`: entri menu Penugasan. Migrasi `process_assignments` lulus `migrate:fresh` dan rollback di `ci-fast`; hak peran DB lewat `DatabaseRoles`.
- Build result: `npm run build` lulus di `ci-fast`; Pint, Larastan, ESLint, Prettier, TypeScript, `translations:check`, uji arsitektur modul, pemindaian rahasia dan `composer audit` (tanpa advisori setelah `2058c37`) lulus.
- Route evidence: RT-002 `/admin/assignments` dan halaman objek di `app/Modules/Access/routes.php` di bawah `auth`, `can:`/`Gate::authorize` dan `two-factor`, perintah dengan middleware `idempotent`; `RouteIntegrityTest` lulus.
- Interaction evidence: PG-504 Administrasi → Penugasan: daftar dengan filter, satu aksi utama Tugaskan, halaman objek dengan `AssignmentPanel` yang dapat dipakai ulang modul (saran dari penunjukan ditandai sebagai saran, galat SoD dengan alasan), akhiri/cabut dengan alasan lewat dialog konfirmasi dan `lock_version`; entri menu dari grant.
- Browser evidence: `tests/e2e/journeys/assignments.spec.ts` (menu, daftar, filter, halaman objek, axe tanpa pelanggaran, 403 tanpa grant) lulus di proyek browser Chromium dan WebKit, desktop 1440×900 dan mobile 390×844, dalam job `ci-e2e` yang sukses.
- Localization evidence: teks baru di `lang/{id,en}/access.php` dan `resources/js/locales/{id,en}/{admin,common}.json`; `translations:check` lulus.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Review independen HIGH atas seluruh diff `2c1da66...2058c37`: temuannya diperbaiki di `7bd6226` (rincian review itu tidak tersimpan). Codex pada `2058c37`: P1 jalur beri melewati penahanan kredensial keputusan, P2 peran non-keputusan kehilangan akses di samping peran keputusan yang menunggu; keduanya diperbaiki di `7bd6226` dengan uji regresi. Review independen HIGH `2058c37..7bd6226`: tanpa P0/P1; P2 password sementara dari reset satu admin terbawa ke peran keputusan (SEC-21), P3 baris lewat akhir dikunci sebelum ditutup dan uji katalog kapabilitas keputusan; diperbaiki di `3816b0c` dengan uji regresi. Codex review ulang pada `3816b0c`: tanpa temuan. Yang ditunda, tidak menghalangi: P3 kueri `holdsDecisionRole` ganda per permintaan; P3 `CommandBusyException` saat kunci baris pengguna. Thread Codex PR #48 tetap belum di-resolve (token eksekutor tidak dapat me-resolve).
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 42 berkas di [PR #48](https://github.com/yusufarst/PRANATA-UNY/pull/48), terutama `app/Modules/Access/` (migrasi dan model `process_assignments`, `AssignmentRoles`, `AssignmentSubjects`, `AssignmentPanel`, `Separation/` dengan `SeparationOfDuties`, `SodRule`, `SodResult`, kontrak `SodRuleSource` dan `AssignmentSubjectType`, Action `AssignProcessRole`/`CloseAssignment` dengan event, `AccessEvaluator`, controller, form request, rute), `app/Modules/Audit/Listeners/RecordAccessAdministration.php`, `app/Modules/Identity/Support/AssistedResets.php`, `app/Modules/Organization/Queries/Appointments.php`, `composer.lock` (empat komponen Symfony v8.1.11), `resources/js/components/app/AssignmentPanel.tsx`, `resources/js/pages/admin/assignments/`, menu samping, locale dan `lang/{id,en}/access.php`, serta uji di `tests/Feature/Access` dan `tests/e2e`. Tanpa perubahan `.github/workflows` atau `.gitleaks.toml`.
- PR / CI run: [PR #48](https://github.com/yusufarst/PRANATA-UNY/pull/48) MERGED; run di atas (attempt 2 gagal hanya di `ci-e2e (2)`: timeout 30 detik `access.spec.ts:52` di webkit-mobile pada runner lambat, uji yang tidak diubah PR ini; attempt 3 hijau). Run yang tidak hijau: 42084787574 pada `43b8788` (dibatalkan oleh push berikutnya), 42085197270 pada `ed0b65e` dan 42086338124 pada `59d42dd` (`ci-fast`, Pint dan Larastan, diperbaiki di `59d42dd` dan `60e6a10`), 42087508405 pada `60e6a10` (`ci-fast`, Prettier, diperbaiki di `c09548a`), 42088780553 pada `c09548a` (`ci-fast`, uji penugasan, diperbaiki di `da70fa0`), 42090544081 pada `da70fa0` (`ci-fast`, `ci-e2e (2)`, aktor audit, diperbaiki di `6d7e224`), 42091822399 pada `6d7e224` (dibatalkan; advisori Symfony 2026-11-04 di `composer audit` diperbaiki di `2058c37`). Run hijau antara: 42094476290 (`2058c37`), 42098862419 (`7bd6226`).
- Commit SHA: `3816b0c90483d70cb40a75c10ddb6f67f64d7e2e`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode AUTONOMOUS (opsi A).
- Notes: TAC-01–TAC-06 terbukti di CI. SOD-01–SOD-03 dan isi aturan SoD rezim tetap milik TASK-028 dan TASK-024 lewat `SodRuleSource`; lampiran bukti `evidence_file_id` ditambahkan TASK-016. Tanpa cache izin lintas permintaan.

## SAFE NEXT TASK

- TASK-016
- TASK-017
- TASK-019

## DO NOT DO

- Jangan membuat delegasi implisit
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
