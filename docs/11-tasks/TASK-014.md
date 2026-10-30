# TASK-014 — Model akses: kapabilitas, profil, grant bercakupan dan scope kueri

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
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

- TASK-015
- TASK-016

## DO NOT DO

- Jangan menegakkan izin hanya di frontend
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
