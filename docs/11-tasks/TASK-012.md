# TASK-012 — TOTP 2FA untuk akun istimewa

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: DONE
EXECUTION STAGE: DONE
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: YES
```

## OBJECTIVE

Menambahkan TOTP (RFC 6238) dengan kode pemulihan, pendaftaran dan pencabutan, serta kebijakan penegakan yang dapat dikonfigurasi (usulan wajib bagi SUPER_ADMIN dan profil istimewa).

## WHY THIS EXISTS

Mengurangi risiko pengambilalihan akun berhak tinggi (SECURITY_MODEL T-01).

## DEPENDS ON

- TASK-011

## BLOCKS

- TASK-078

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: SEC-D1 penegakan TOTP
- GAP terkait (GAP-015) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Pendaftaran TOTP (QR + kunci manual), konfirmasi, kode pemulihan sekali pakai
- Tantangan TOTP saat masuk
- Penegakan konfigurasi: wajib untuk peran/profil terpilih sebelum mengakses Administrasi
- Reset 2FA oleh admin dengan maker-checker
- Audit

## OUT OF SCOPE

- WebAuthn/passkey (masa depan)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §3](../05-security/SECURITY_MODEL.md#3-autentikasi)
- [ADR-0007](../adr/ADR-0007-local-authentication.md)

## TRACEABILITY

- CAP: CAP-01
- AC/NFR: AC-01
- WF/TR: WF-001 (TR-001–TR-003, 3 TR; BLOCKED_BY_GAP: TR-003)
- RT: RT-001
- IX: IX-019
- GAP: GAP-015
- Keputusan Owner: OD-11

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Identity`
- `resources/js/pages/account`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- SEC-03
- Rahasia TOTP terenkripsi dengan APP_KEY
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: users (kolom 2FA)
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
- Expected affected areas: app/Modules/Identity, resources/js/pages/account

## CONTEXT7

- Topics to verify: Laravel Fortify two-factor atau paket TOTP yang dipakai starter kit
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
- Halaman: PG-007
- Primary action: Aktifkan 2FA
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Alur pendaftaran mobile-friendly

## ROUTE IMPACT

- RT-001

## INTERACTION CONTRACTS

- IX-019

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Pengguna mendaftar dan masuk dengan TOTP; kode pemulihan sekali pakai
- TAC-02 SUPER_ADMIN tanpa 2FA terkonfirmasi diarahkan ke pendaftaran sebelum Administrasi (bila kebijakan wajib aktif)
- TAC-03 Reset 2FA oleh admin memerlukan admin kedua

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Pendaftaran/tantangan/pemulihan/penegakan
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Alur pendaftaran
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: SEC-03
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

- Test outputs: run [41640350425](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41640350425) pada HEAD `140277f`: `changes`, `ci-fast`, `ci-e2e (1)`, `ci-e2e (2)` dan `ci-gate` sukses; `docs-check` dilewati (tanpa perubahan dokumen); log Pest per uji tidak dibaca, keberhasilan job adalah buktinya. TAC-01: `tests/Feature/Identity/TwoFactorTest.php` (pendaftaran dengan QR dan kunci manual, konfirmasi kode, tantangan saat masuk, kode pemulihan sekali pakai, kode TOTP tidak dapat dipakai ulang; rahasia dan kode pemulihan terenkripsi APP_KEY, SEC-03). TAC-02: `config('auth.two_factor_required')` (bawaan aktif, SEC-D1 menurut D-12) dan middleware `two-factor` pada rute Administrasi mengarahkan akun istimewa tanpa TOTP terkonfirmasi ke halaman Keamanan; akun itu tidak dapat menonaktifkan TOTP atau memaksa rahasia baru menimpa yang terkonfirmasi. TAC-03: `identity_two_factor_resets` dengan Action minta, setujui dan cabut; penyetuju adalah admin kedua yang bukan peminta dan bukan target (Action dan CHECK basis data); persetujuan menghapus TOTP, mengakhiri sesi akun dan memberi tahu pengguna; ditolak bila TOTP sudah tidak ada; uji izin negatif. Kode TOTP atau pemulihan yang salah dihitung ke penguncian masuk; tantangan ditolak untuk akun terkunci, SUSPENDED atau DEACTIVATED (`TwoFactorChallengeGuard`). Migrasi `identity_two_factor_resets` lulus `migrate:fresh` dan rollback di `ci-fast`.
- Build result: `npm run build` lulus di `ci-fast`; Pint, Larastan, ESLint, Prettier, TypeScript, `translations:check` dan pemindaian rahasia lulus.
- Route evidence: `RouteIntegrityTest` lulus dengan rute reset 2FA PG-502 di bawah `auth` + `can:MANAGE_USERS` + `two-factor`; tantangan dan pengaturan TOTP lewat rute Fortify.
- Interaction evidence: PG-007 Keamanan dengan satu aksi utama Aktifkan 2FA, modal pendaftaran yang ramah layar sempit (QR, kunci manual berlabel, input kode berlabel), daftar kode pemulihan dan pembuatan ulang di balik konfirmasi password; PG-502 detail akun dengan minta, setujui dan cabut reset 2FA.
- Browser evidence: `tests/e2e/journeys/two-factor.spec.ts` (pendaftaran dari kunci manual, konfirmasi, masuk dengan kode baru dan sekali dengan kode pemulihan yang lalu habis, axe) lulus di semua proyek browser, desktop dan mobile, pada kedua shard. Penegakan (TAC-02) dan reset dua admin (TAC-03) dibuktikan uji fitur.
- Localization evidence: teks baru di `lang/{id,en}/identity.php` dan `resources/js/locales/{id,en}/{account,admin}.json`; `translations:check` lulus.
- Review status (Codex / independen, temuan P0–P3): swa-review selesai. Review independen HIGH pada `38e02fb...9608a39`: P2 kode TOTP salah tidak pernah mengunci akun (password benar mengosongkan penghitung gagal); P3 tantangan tidak memeriksa ulang status atau kunci akun; P3 persetujuan tidak memeriksa ulang bahwa TOTP masih ada; P3 `force` Fortify dapat menukar rahasia terkonfirmasi akun yang wajib TOTP; P3 uji pemakaian ulang kode TOTP belum ada; semuanya diperbaiki di `ff72c92` dan `7099fb3` dengan uji, `140277f` membuang `instanceof` yang selalu benar (Larastan). Codex review pada `140277f` (issuecomment-6604608224): tanpa temuan, 0 komentar inline. P3 yang ditunda: status EXPIRED reset 2FA tidak ditulis (permintaan kedaluwarsa tidak dapat disetujui maupun ditampilkan); setujui reset 2FA dan buat ulang kode pemulihan tanpa dialog konfirmasi (sama dengan pola persetujuan TASK-011; buat ulang di balik konfirmasi password); uji cabang 403 JSON; dugaan masalah axe pada daftar kode pemulihan tidak dapat direproduksi.
- Cost evidence/exception if applicable: tanpa biaya; runner standar repositori publik.
- Files changed: 43 berkas di [PR #42](https://github.com/yusufarst/PRANATA-UNY/pull/42), terutama `app/Modules/Identity/` (Action reset 2FA, event, model `TwoFactorReset`, middleware `EnsureTwoFactorIsEnabled`, `TwoFactorPolicy`, `TwoFactorChallengeGuard`, notifikasi, migrasi `identity_two_factor_resets`, rute), `app/Actions/Fortify/`, `app/Providers/FortifyServiceProvider.php`, listener `app/Modules/Audit/Listeners/`, `config/auth.php`, `bootstrap/app.php`, `resources/js/pages/`, `resources/js/components/`, `resources/js/locales/`, `lang/{id,en}/identity.php`, serta uji di `tests/Feature/Identity` dan `tests/e2e`. Tanpa perubahan `.github/workflows` atau `.gitleaks.toml`.
- PR / CI run: [PR #42](https://github.com/yusufarst/PRANATA-UNY/pull/42) MERGED; run di atas. Run gagal yang tercatat: 41632512864 pada `d11ab1f` (label input dan pemeriksaan selalu-benar, diperbaiki di `0b81f78`), 41633488042 pada `0b81f78` dan 41635473560 pada `9b84a74` (axe dan stabilitas E2E, diperbaiki di `9b84a74` dan `9608a39`), 41639525738 pada `7099fb3` (`ci-fast`: Larastan `instanceof` selalu benar, diperbaiki di `140277f`); 41639351284 pada `ff72c92` dibatalkan karena digantikan. Run hijau antara: 41637236240 (`9608a39`).
- Commit SHA: `140277f823d3a2f87f8f691941f1c1cf30a78069`, `main` diverifikasi dengan `git ls-remote`; fast-forward D-08 lewat `merge-verified.ps1` dengan `ci-gate`, mode AUTONOMOUS (opsi A).
- Notes: TAC-01–TAC-03 terbukti di CI. SEC-D1 diterapkan menurut D-12 (TOTP wajib bagi akun istimewa, dapat dikonfigurasi). Tanpa SMS/email sebagai faktor kedua. TOTP memakai Fortify dari starter kit; tanpa kolom `users` baru karena kolom 2FA Fortify sudah ada.

## SAFE NEXT TASK

- TASK-013

## DO NOT DO

- Jangan memakai SMS/email sebagai faktor kedua
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
