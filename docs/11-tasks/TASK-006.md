# TASK-006 — Token desain, AppShell dan lokalisasi id/en

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan token DESIGN_SYSTEM di Tailwind 4/shadcn, font Plus Jakarta Sans dan JetBrains Mono self-hosted, AppShell (sidebar, header, pemilih ruang kerja/bahasa, menu akun), komponen komposit dasar dan infrastruktur i18n id/en.

## WHY THIS EXISTS

Semua halaman mewarisi shell, token dan lokalisasi yang sama (CAP-20).

## DEPENDS ON

- TASK-004

## BLOCKS

- TASK-007
- TASK-010

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN

## IN SCOPE

- Variabel CSS token warna/teks/semantik/radius/bayangan/gerak dan pemetaan shadcn
- Font self-hosted, verifikasi angka tabular
- AppShell responsif (drawer mobile, collapse tablet), breadcrumb, PageHeader, StatusBadge, EmptyState, ErrorState, ForbiddenState, NotFoundState, Skeleton
- i18next + react-i18next dengan namespace; `lang/{id,en}` server; pemilih bahasa yang menyimpan `users.locale` (setelah TASK-010) atau sesi
- Halaman galat 403/404/419/429/500/503 terlokalisasi

## OUT OF SCOPE

- Halaman bisnis
- DataTable (TASK-022)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DESIGN_SYSTEM](../07-ux-design/DESIGN_SYSTEM.md)
- [INTERACTION_PATTERNS §4](../07-ux-design/INTERACTION_PATTERNS.md#4-keadaan-wajib)
- [LOCALIZATION](../07-ux-design/LOCALIZATION.md)
- [PRODUCT_EXPERIENCE_DIRECTION](../01-product/PRODUCT_EXPERIENCE_DIRECTION.md)

## TRACEABILITY

- CAP: CAP-20
- AC/NFR: AC-23, AC-24, AC-25
- WF/TR: WF-002 (TR-011–TR-014, 4 TR)
- RT: RT-002
- IX: IX-016
- Keputusan Owner: OD-17, OD-21, OD-33, OD-34

## EXPECTED MODULES / FILE AREAS

- `resources/css/app.css`
- `resources/js/components/ui`
- `resources/js/components/app`
- `resources/js/locales`
- `lang/`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Tidak ada warna hex lepas di komponen
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada aset dari CDN pihak ketiga
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: —
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: N/A
- Replaceable adapter/contract required: NO
- Provider lock-in risk: NONE
- Future extension impact: LOW
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
- Expected affected areas: resources/css/app.css, resources/js/components/ui, resources/js/components/app, resources/js/locales, lang/

## CONTEXT7

- Topics to verify: Tailwind CSS 4 @theme; shadcn/ui theming untuk Tailwind 4; react-i18next
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
- Halaman: PG-003, PG-013
- Primary action: —
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Shell dan keadaan wajib

## ROUTE IMPACT

- RT-002

## INTERACTION CONTRACTS

- IX-016

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Kontras token sesuai DESIGN_SYSTEM §2 terverifikasi otomatis
- TAC-02 Shell responsif pada 390×844 dan 1440×900 tanpa tumpang tindih
- TAC-03 Pemilih bahasa mengganti seluruh teks tanpa kehilangan halaman
- TAC-04 Kunci terjemahan hilang = 0
- TAC-05 Halaman galat tampil dalam kedua bahasa

## TEST REQUIREMENTS

- Lint: ESLint (larangan teks literal)
- Typecheck: tsc
- Unit: Vitest komponen komposit
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: N/A — harness dibuat TASK-007 (uji asap shell)
- Localization/language switch: Pemeriksa kunci; dua bahasa
- Responsive: Tangkapan layar dua viewport (manual terdokumentasi); otomatis sejak TASK-007
- Accessibility: axe-core pada komponen melalui Vitest; axe di browser sejak TASK-007
- Production build: npm run build

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

STANDARD — swa-review terstruktur + review PR otomatis (Codex); lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.2 UI
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

- TASK-007
- TASK-010

## DO NOT DO

- Jangan menyalin warna atau aset dari video referensi
- Jangan menambahkan mode gelap pada V1
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
