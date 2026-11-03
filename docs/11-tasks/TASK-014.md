# TASK-014 — Model akses: kapabilitas, profil, grant bercakupan dan scope kueri

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan katalog kapabilitas di kode, profil akses, grant bercakupan dengan maker-checker untuk grant istimewa, evaluator `can()`, pembangun scope SQL `visibleTo`, visibilitas ruang kerja/menu, peta izin ke frontend, dan halaman Administrasi Akses.

## WHY THIS EXISTS

OD-28/BR-034 menuntut isolasi yang ditegakkan server, kueri, ekspor dan unduhan.

## DEPENDS ON

- TASK-013

## BLOCKS

- TASK-015
- TASK-016
- TASK-018
- TASK-022
- TASK-049

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Konfirmasi ruang kerja Gudang (perubahan dari OD-02)
- GAP terkait (GAP-014) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Enum/registry kapabilitas ACCESS_CONTROL §4
- `access_profiles`, `access_profile_capabilities`, `access_grants` (constraint maker-checker)
- Evaluator berlapis (ACCESS_CONTROL §1) + Gate/Policy integrasi
- Builder scope SQL: GLOBAL, ORG_UNIT, ORG_UNIT_SUBTREE (ltree), WAREHOUSE, WORKSPACE, POLICY_FAMILY (`scope_key`)
- Constraint grant istimewa: ACTIVE hanya dengan penyetuju yang berbeda dari pemberi (`requires_approval`)
- Props bersama: ruang kerja/menu yang diizinkan, peta izin
- PG-503 beri/setujui/cabut grant; profil awal ACCESS_CONTROL §9 sebagai seeder
- Uji matriks otomatis dari katalog

## OUT OF SCOPE

- Penugasan proses (TASK-015)
- Kapabilitas modul yang belum ada (ditambahkan oleh Task modul ke katalog)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [ACCESS_CONTROL](../05-security/ACCESS_CONTROL.md)
- [DATA_MODEL §9](../04-architecture/DATA_MODEL.md#9-access)

## TRACEABILITY

- CAP: CAP-02
- AC/NFR: AC-02, AC-04, AC-31
- WF/TR: WF-002 (TR-011–TR-014, 4 TR)
- RT: RT-002
- IX: IX-001, IX-007, IX-008, IX-016
- GAP: GAP-014
- Keputusan Owner: OD-09, OD-10, OD-20, OD-28

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Access`
- `resources/js/pages/admin/access`
- `resources/js/lib/access.ts`

## BUSINESS RULES

- [BR-002](../02-domain/BUSINESS_RULES.md#br-002)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-001](../02-domain/BUSINESS_RULES.md#inv-001)
- [INV-004](../02-domain/BUSINESS_RULES.md#inv-004)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)

## ARCHITECTURE RULES

- Tidak ada cache izin lintas permintaan
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-20, AUTHZ-22
- Admin tidak dapat memberi grant ke dirinya
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `access_profiles`, `access_profile_capabilities`, `access_grants`
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

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Access, resources/js/pages/admin/access, resources/js/lib/access.ts

## CONTEXT7

- Topics to verify: Laravel Gate/Policy; Inertia shared props
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
- Halaman: PG-503, PG-003
- Primary action: Beri akses
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Grant istimewa menunggu persetujuan

## ROUTE IMPACT

- RT-002

## INTERACTION CONTRACTS

- IX-001
- IX-007
- IX-008
- IX-016

## NAVIGATION CONTRACT

- Parent: Administrasi
- Entry: Menu Akses
- Back action: Daftar grant
- Cancel: Detail pengguna
- Success destination: Daftar grant + toast
- Error behavior: Galat validasi per field + ringkasan di atas form; input dipertahankan
- State preservation: Filter di URL
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Ruang kerja dan menu tampil hanya bila ada grant
- TAC-02 Scope subtree, unit, gudang dan global diterapkan di daftar, detail dan pencarian (uji)
- TAC-03 Grant istimewa berstatus PENDING_APPROVAL sampai disetujui orang lain
- TAC-04 Pencabutan berlaku pada permintaan berikutnya
- TAC-05 Akses langsung rute tanpa izin → 403/404
- TAC-06 Grant istimewa ACTIVE tanpa penyetuju atau dengan penyetuju = pemberi ditolak constraint DB
- TAC-07 Mekanisme scope diuji dengan resource uji; skenario AUTHZ-01/02 dibuktikan pada paket nyata di TASK-027 dan AUTHZ-03 pada aset di TASK-059

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Evaluator dan scope
- Authorization: Matriks kapabilitas×profil×cakupan
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Menu sesuai grant
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: AUTHZ-20, AUTHZ-22
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

- Test outputs: run [41688261173](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41688261173) pada HEAD `4bf4cb8`: `changes`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)`, `docs-check` dan `ci-gate` sukses; log Pest per uji tidak dibaca, keberhasilan job adalah buktinya. `tests/Feature/Access/AccessModelTest.php`: katalog kapabilitas (modul dan huruf cakupan), profil awal ACCESS_CONTROL §9 dan matriks profil×kapabilitas×cakupan dari katalog; setiap kapabilitas adalah ability Gate yang diputuskan evaluator; TAC-02/TAC-07 scope subtree (unit dan turunannya, bukan saudara), unit, gudang, keluarga kebijakan (termasuk kunci satu kata) dan global pada resource uji, daftar sama dengan cek detail, pencarian tidak pernah melebarkan scope, scope tanpa kolom menampilkan nol baris (fail closed); Super Admin tanpa kapabilitas bisnis (INV-021); akun nonaktif dan vendor tidak memegang apa pun; AUTHZ-20 tidak memberi grant ke diri sendiri; TAC-03 grant istimewa PENDING_APPROVAL sampai orang lain menyetujui; TAC-06 DB menolak grant istimewa ACTIVE tanpa penyetuju kedua atau dengan penyetuju = pemberi/penerima; TAC-04/AUTHZ-22 pencabutan berlaku pada permintaan berikutnya, grant berlaku dari hari pertama sampai sehari sebelum akhirnya, lewat akhir menjadi EXPIRED; persetujuan menolak grant kedaluwarsa atau penerima yang dinonaktifkan; beri/setujui/cabut diaudit dengan aktor dan alasan, perubahan yang ditolak tidak diaudit. `tests/Feature/Access/GrantAdministrationTest.php`: TAC-05 tamu ke halaman masuk, akun tanpa `admin.access.grant`/`admin.access.approve` tidak dapat membuka atau mengubah grant, grant tak dikenal 404; TAC-01 pengguna tanpa grant hanya melihat Beranda, menu tidak pernah mengarah ke halaman mati, admin akses terdelegasi mendapat entri Akses dan kehilangannya pada permintaan berikutnya setelah dicabut; form, daftar dengan filter di URL, detail, pencabutan beralasan dengan `lock_version` usang ditolak. Migrasi akses lulus `migrate:fresh` dan rollback di `ci-fast`; hak peran DB lewat `DatabaseRoles`.
- Build result: `npm run build` lulus di `ci-fast`; Pint, Larastan, ESLint, Prettier, TypeScript, `translations:check`, uji arsitektur modul dan pemindaian rahasia lulus.
- Route evidence: RT-002 di `app/Modules/Access/routes.php` di bawah `auth` + `can:access.administer` + `two-factor`, perintah dengan middleware `idempotent`; uji "every access route carries the access administration gate and confirmed TOTP" dan `RouteIntegrityTest` lulus.
- Interaction evidence: PG-503 Administrasi → Akses: daftar grant dengan filter pengguna dan status di URL (termasuk Kedaluwarsa), satu aksi utama Beri akses, form dengan profil aktif, kapabilitas yang dapat diberikan beserta cakupannya dan unit aktif (galat per field, input dipertahankan), detail dengan langkah persetujuan, Setujui untuk penyetuju lain dan Cabut dengan alasan lewat dialog konfirmasi; Beri akses dibuka dari detail pengguna PG-502 (tidak untuk akun sendiri, nonaktif atau vendor); menu dan ruang kerja mengikuti grant lewat props bersama dan `resources/js/lib/access.ts`; drawer ponsel menutup saat entri menu dipilih.
- Browser evidence: `tests/e2e/journeys/access.spec.ts` (akun tanpa grant tidak melihat entri administrasi dan tidak dapat membuka PG-503 langsung; admin akses membuka Akses dari menu, memberi grant biasa dan mencabutnya dengan alasan; axe tanpa pelanggaran di tiap langkah) lulus di semua proyek browser, desktop dan mobile, pada kedua shard.
- Localization evidence: teks baru di `lang/{id,en}/access.php` dan `resources/js/locales/{id,en}/{admin,common}.json`; `translations:check` lulus.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Review independen HIGH pertama pada `c7aa156`: P1 R0 beri/setujui/cabut grant belum diaudit (SECURITY_MODEL §9, T-04), grant lewat akhir tidak EXPIRED, persetujuan untuk penerima yang dinonaktifkan, grant/persetujuan oleh diri sendiri belum ditolak DB, unit nonaktif dalam scope, Beri akses untuk vendor, parameter `user` array menyebabkan galat; semuanya diperbaiki di `d22dafe` dan dikonfirmasi review ulang `origin/main...d22dafe`. Review independen kedua: P2 R1 kunci keluarga kebijakan satu kata (`reconciliation`, `numbering`, `retention`) tidak dapat dicakup (AUTHZ-25) dan P3 R4 parameter `q` array menyebabkan 500; keduanya diperbaiki di `4bf4cb8` dengan uji regresi. Codex review pada `4bf4cb8`: tanpa temuan. Yang ditunda, tidak menghalangi: **P2 R2 — pertanyaan untuk Owner/Planning**: admin akses terdelegasi (INTERNAL, profil ACCESS_ADMIN dengan `admin.users.manage` menurut ACCESS_CONTROL §9) dapat menangguhkan, menonaktifkan dan mengakhiri sesi akun SUPER_ADMIN, karena tidak ada dokumen yang melindungi target SUPER_ADMIN; perlu diputuskan apakah aksi identitas atas SUPER_ADMIN oleh non-SUPER_ADMIN ditolak. P3 R3 tanpa trigger yang membekukan kolom grant dan membuat transisi status satu arah, evaluator mempercayai `requires_approval` dan kapabilitas profil yang hidup (pertahanan berlapis); R5 detail grant menampilkan semua kapabilitas profil termasuk yang dibuang katalog untuk cakupan grant; R6 persetujuan tidak memeriksa ulang bahwa pemberi masih memegang `admin.access.grant` atau masih aktif; R7 tabel modul ARCHITECTURE hanya menyebut Identity sebagai dependensi Audit, sedangkan §5.3 mengizinkan Access (usulan koreksi dokumen ke Planning Agent).
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 59 berkas di [PR #46](https://github.com/yusufarst/PRANATA-UNY/pull/46), terutama `app/Modules/Access/` (katalog kapabilitas, `AccessEvaluator`, `AccessSubject`, `GrantScope`, `Navigation`, kueri `VisibleTo`, Action beri/setujui/cabut dengan event, model, controller, form request, migrasi `access_profiles`/`access_profile_capabilities`/`access_grants` dengan constraint maker-checker, rute), `app/Modules/Audit/Listeners/RecordAccessAdministration.php`, `app/Modules/Organization/Queries/UnitScope.php`, `app/Http/Middleware/HandleInertiaRequests.php`, `database/seeders/AccessProfileSeeder.php`, `resources/js/pages/admin/access/`, `resources/js/lib/access.ts`, menu samping, locale dan `lang/{id,en}/access.php`, serta uji di `tests/Feature/Access`, `tests/Arch/ModuleBoundaryTest.php` dan `tests/e2e`. Tanpa perubahan `.github/workflows` atau `.gitleaks.toml`.
- PR / CI run: [PR #46](https://github.com/yusufarst/PRANATA-UNY/pull/46) MERGED; run di atas. Run yang tidak hijau: 41672501833 pada `6d4249f` (dibatalkan oleh push berikutnya), 41673016995 pada `af66d43` (`ci-fast`, diperbaiki di `9f86ab3`), 41676959373 pada `6666ac9` (`ci-e2e (1)`, `ci-e2e (2)`, `ci-fast`, diperbaiki di `f602db6`), 41678257719 pada `f602db6` (`ci-fast`, diperbaiki di `c7aa156`), 41686851434 pada `d22dafe` (`ci-fast`, uji yang menonaktifkan pengguna tanpa `deactivated_at`, diperbaiki di `4bf4cb8`). Run hijau antara: 41674406861 (`9f86ab3`), 41679390980 (`c7aa156`).
- Commit SHA: `4bf4cb89167f713437f1dcf808b08ea8e398ae20`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode AUTONOMOUS (opsi A).
- Notes: TAC-01–TAC-07 terbukti di CI; skenario AUTHZ-01/02 pada paket nyata tetap milik TASK-027 dan AUTHZ-03 pada aset milik TASK-059. Tanpa cache izin lintas permintaan. Kapabilitas modul yang belum ada ditambahkan Task modulnya ke katalog. Ruang kerja Gudang terpisah sesuai D-05 (a). Profil awal ACCESS_CONTROL §9 dari seeder; menjalankan seeder lagi tidak menimpa profil yang diubah Super Admin.

## SAFE NEXT TASK

- TASK-015
- TASK-016

## DO NOT DO

- Jangan menegakkan izin hanya di frontend
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
