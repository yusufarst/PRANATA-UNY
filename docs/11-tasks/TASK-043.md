# TASK-043 — Portal Penyedia

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M4 — Pengadaan lanjutan dan penyedia
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan Portal Penyedia: aktivasi akun penyedia, profil perusahaan dan PIC, unggah dokumen dan status DPT, daftar undangan/partisipasi, kewajiban dan hasil yang diumumkan; kiriman penawaran hanya bila kebijakan kanal mengizinkan.

## WHY THIS EXISTS

CAP-07/AC-09/10 dengan isolasi penyedia (OD-28).

## DEPENDS ON

- TASK-042
- TASK-033

## BLOCKS

- TASK-078

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: SEC-D7 kanal kode aktivasi penyedia
- GAP terkait (GAP-014, GAP-015, GAP-029) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Kelompok peran VENDOR + `vendor_user_links`
- PG-401–405
- Pembaruan profil sebagai permintaan perubahan yang diverifikasi
- Gerbang `portal.submission.create` dari kebijakan kanal (default OFF)
- Isolasi: uji IDOR antar-penyedia
- Penyedia melihat tanda terima penawarannya, hasil yang diumumkan per lingkup dan — menurut kebijakan — alasan gugur penawarannya sendiri; tidak pernah skor atau penawaran penyedia lain (AUTHZ-48)

## OUT OF SCOPE

- Pendaftaran mandiri publik tanpa undangan (bukan V1)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-007](../03-workflows/WORKFLOW_CONTRACTS.md#wf-007)
- [WF-008](../03-workflows/WORKFLOW_CONTRACTS.md#wf-008)
- [ACCESS_CONTROL §7](../05-security/ACCESS_CONTROL.md#7-visibilitas-data-per-modul)
- [INFORMATION_ARCHITECTURE §3.5](../07-ux-design/INFORMATION_ARCHITECTURE.md#35-portal-penyedia)

## TRACEABILITY

- CAP: CAP-07
- AC/NFR: AC-09, AC-10, AC-31
- WF/TR: WF-007 (TR-061–TR-066, 6 TR; BLOCKED_BY_GAP: TR-064); WF-008 (TR-071–TR-077, 7 TR; BLOCKED_BY_GAP: TR-076)
- RT: RT-007, RT-008
- IX: IX-001, IX-002, IX-003, IX-005, IX-011, IX-019
- GAP: GAP-014, GAP-015, GAP-029
- Keputusan Owner: OD-28

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Vendor/Portal`
- `resources/js/pages/vendor`

## BUSINESS RULES

- [BR-007](../02-domain/BUSINESS_RULES.md#br-007)
- [BR-008](../02-domain/BUSINESS_RULES.md#br-008)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-003](../02-domain/BUSINESS_RULES.md#inv-003)
- [INV-004](../02-domain/BUSINESS_RULES.md#inv-004)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-30, AUTHZ-33, AUTHZ-48
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `vendor_user_links`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Vendor/Portal
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
- Expected affected areas: app/Modules/Vendor/Portal, resources/js/pages/vendor

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
- Halaman: PG-401, PG-402, PG-403, PG-404, PG-405
- Primary action: Lengkapi dokumen
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Mobile-first

## ROUTE IMPACT

- RT-007
- RT-008

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-003
- IX-005
- IX-011
- IX-019

## NAVIGATION CONTRACT

- Parent: Portal Penyedia
- Entry: Beranda penyedia
- Back action: Beranda/daftar
- Cancel: Kembali ke halaman asal tanpa menyimpan; konfirmasi bila ada perubahan belum tersimpan
- Success destination: Detail
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Filter, urutan, tab dan cursor di URL; dipulihkan saat kembali
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Penyedia hanya melihat perusahaan dan partisipasinya
- TAC-02 Tebakan ID objek penyedia lain → 404
- TAC-03 Formulir kiriman tidak tersedia bila kebijakan kanal OFF, dengan penjelasan kanal resmi
- TAC-04 Profil yang diperbarui menunggu verifikasi tanpa mengganggu partisipasi berjalan

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Portal
- Authorization: AUTHZ-30, AUTHZ-33, AUTHZ-48
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-15
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe portal
- Production build: npm run build
- Security: IDOR
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

- TASK-044

## DO NOT DO

- Jangan membuka registrasi publik
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
