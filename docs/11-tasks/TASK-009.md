# TASK-009 — Kontrak perintah: idempotensi, versi optimistis, model galat dan request id

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan infrastruktur perintah bersama: middleware `X-Idempotency-Key` + tabel kunci, trait `lock_version`, kelas dasar Action bertransaksi dengan retry deadlock terbatas, pemetaan galat (422/403/404/409/429) dan `X-Request-Id` di log/audit.

## WHY THIS EXISTS

Semua aksi state memerlukan perilaku klik-ganda, konflik dan galat yang konsisten (COMMAND_AND_API_CONTRACTS).

## DEPENDS ON

- TASK-008

## BLOCKS

- TASK-011
- TASK-013
- TASK-050

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-016) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `platform_idempotency_keys` + middleware + pembersihan terjadwal
- Base `Action` (transaksi, retry 40P01/40001 maks. 2, afterCommit events)
- Trait/kolom `lock_version` dan pengecualian konflik → 409 + props terbaru
- Penanganan galat Inertia terlokalisasi; `reason_key` aman
- Request id di header, log konteks dan audit
- Hook frontend `useCommand` yang membuat/mengirim kunci idempotensi dan menangani 409 dengan ConflictDialog

## OUT OF SCOPE

- Aksi bisnis

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [COMMAND_AND_API_CONTRACTS §2–5](../06-api-performance/COMMAND_AND_API_CONTRACTS.md)
- [CONCURRENCY_AND_INTEGRITY §1, §6](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md)

## TRACEABILITY

- CAP: CAP-18, CAP-19
- AC/NFR: AC-21, AC-22
- IX: IX-002
- GAP: GAP-016

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Platform`
- `resources/js/lib/command.ts`
- `resources/js/components/app/ConflictDialog.tsx`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Satu perintah = satu Action
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Kunci idempotensi terikat pengguna; isi berbeda ditolak
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `platform_idempotency_keys`, `platform_settings`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Platform
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
- Expected affected areas: app/Modules/Platform, resources/js/lib/command.ts, resources/js/components/app/ConflictDialog.tsx

## CONTEXT7

- Topics to verify: Laravel exception handling for Inertia; DB::transaction attempts
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
- Halaman: —
- Primary action: —
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: ConflictDialog

## ROUTE IMPACT

- N/A

## INTERACTION CONTRACTS

- IX-002

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Pengiriman ganda dengan kunci sama menghasilkan satu efek dan redirect yang sama
- TAC-02 Kunci sama dengan isi berbeda → 422
- TAC-03 Konflik versi → 409 dengan data terbaru dan dialog
- TAC-04 Deadlock simulasi diulang otomatis lalu sukses atau 503 tanpa efek parsial
- TAC-05 Request id muncul di log dan audit

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Hook useCommand (Vitest)
- Feature: Idempotensi, konflik, retry
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Klik ganda pada aksi contoh
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Migration: migrate:fresh + rollback migrasi baru + hak peran DB

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [x] Lint
- [x] Typecheck
- [x] Relevant automated tests
- N/A Authorization: tanpa aksi bisnis atau kapabilitas baru; kunci idempotensi terikat pengguna diuji di `IdempotentCommandTest`
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

- Test outputs: run [41494819346](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41494819346) pada HEAD `def83d2`: `changes`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)` dan `ci-gate` sukses; `docs-check` dilewati (tanpa perubahan dokumen). Di `ci-fast`: Pest paralel dengan uji arsitektur dan rute, Vitest, migrasi `fresh` lalu `rollback`, dan pemeriksaan negatif lulus. TAC-01: `IdempotentCommandTest` (kirim ganda dengan kunci sama menjalankan perintah sekali dan redirect ke tempat yang sama; kunci terikat pengguna; duplikat yang masih berjalan mendapat 409 setelah menunggu; kunci yang gagal validasi, juga di error bag bernama, boleh dikirim ulang) dan E2E klik ganda pada simpan profil. TAC-02: kunci sama dengan isi berbeda ditolak 422 tanpa efek. TAC-03: `CommandContractTest` (simpan pada versi basi tidak menulis apa pun; 409 dengan data terbaru dan pesan konflik) dan Vitest `useCommand` (409 membuka ConflictDialog dan input tetap). TAC-04: 40P01/40001 diulang lalu sukses sekali; deadlock yang melampaui batas ulang menjawab 503 tanpa sisa; galat lain tidak diulang; deadlock setelah simpan berversi tetap menulis perubahan pada ulangan. TAC-05: setiap respons membawa request id yang sama dengan konteks log dan `audit_events`; halaman galat menampilkan request id-nya.
- Build result: `npm run build` lulus di `ci-fast` dan kedua shard E2E; Pint, Larastan, ESLint, Prettier dan TypeScript lulus.
- Route evidence: tanpa rute baru; `routes/settings.php` memberi `profile.update` middleware idempotensi; `RouteIntegrityTest` lulus di `ci-fast`.
- Interaction evidence: IX-002 — klik ganda menghasilkan satu simpan di bawah satu kunci, kirim ulang dengan kunci sama mendapat jawaban yang pertama, 409 membuka ConflictDialog, halaman galat menampilkan request id.
- Browser evidence: `tests/e2e/smoke/shell.spec.ts` lulus di Chromium dan WebKit (dua shard), termasuk klik ganda, kirim ulang, laci di ponsel dan shell kosong pada viewport proyek dengan axe tanpa pelanggaran.
- Localization evidence: kunci baru di `lang/{id,en}/command.php` dan `resources/js/locales/{id,en}/common.json`; `translations:check` lulus (kunci hilang 0).
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Codex dengan temuan: P1 dan P2 valid, diperbaiki di `a1ac7a2` dan `ea4f693` (thread dibiarkan terbuka untuk Owner). Codex ulang pada `5c7861c`: tanpa temuan utama; tidak diminta ulang untuk `6a7a638`, `036549e`, `def83d2`. Review independen HIGH: P2-1 (model di memori disinkronkan sebelum commit, sehingga ulangan deadlock hanya menulis versi) diperbaiki di `6a7a638`; P2-2 (galat validasi di error bag bernama dihitung sukses) diperbaiki di `036549e`, dengan perbaikan tipe Larastan di `def83d2`. Review delta eksekutor `5c7861c..def83d2`: kedua P2 tertutup, tanpa P0/P1/P2 baru. P3 yang ditunda: ulangan di dalam savepoint bersarang; halaman 503 karena ulangan habis menampilkan teks pemeliharaan; kunci yang macet di IN_PROGRESS; konteks `DomainRuleViolation` tidak ditampilkan; `syncOriginal` saat commit juga menandai atribut lain yang belum disimpan sebagai bersih; belum ada uji hak peran DB untuk tabel baru, balapan dua koneksi, render ConflictDialog dan respons HTTP 503.
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 31 berkas pada delta `b739bf0..def83d2` di [PR #34](https://github.com/yusufarst/PRANATA-UNY/pull/34), terutama `app/Modules/Platform/` (`Support/Action.php`, `Support/HasLockVersion.php`, `Support/RequestId.php`, `Http/Middleware/EnsureIdempotentCommand.php`, `Http/Middleware/AssignRequestId.php`, `Exceptions/`, `Console/PruneIdempotencyKeys.php`), migrasi `platform_settings` dan `platform_idempotency_keys`, `bootstrap/app.php`, `AuditLogger`, `resources/js/lib/command.ts`, `resources/js/components/app/ConflictDialog.tsx`, halaman profil dan galat, terjemahan, serta uji di `tests/Feature/Platform`, `resources/js/lib/command.test.ts` dan `tests/e2e/smoke/shell.spec.ts`.
- PR / CI run: [PR #34](https://github.com/yusufarst/PRANATA-UNY/pull/34) MERGED; run di atas. Run sebelumnya: 41487589092, 41488773800, 41489919224, 41491092514, 41493612941 (yang terakhir gagal hanya di Larastan, diperbaiki di `def83d2`).
- Commit SHA: `def83d27afd31879936acfb8f84fa12a77e76b8f`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode AUTONOMOUS.
- Notes: TAC-01–TAC-05 terbukti di CI. Hak peran DB untuk tabel baru tidak diuji tersendiri (P3 di atas); server E2E berjalan sebagai anggota `pranata_app` dan lulus. Pembersihan kunci kedaluwarsa dijadwalkan tiap jam. Uji izin positif/negatif N/A karena tidak ada aksi bisnis atau kapabilitas baru.

## SAFE NEXT TASK

- TASK-010
- TASK-011

## DO NOT DO

- Jangan menaruh pemanggilan jaringan di dalam transaksi
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
