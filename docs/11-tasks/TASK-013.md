# TASK-013 — Organisasi berversi, jabatan dan penunjukan

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyediakan unit organisasi hierarkis generik (ltree) dengan riwayat berlaku, jabatan/fungsi resmi dan penunjukan berjangka, serta alur permintaan perubahan WF-029 F1/F2 yang keputusan berlakunya tetap tergerbang kebijakan.

## WHY THIS EXISTS

Cakupan akses, asal usulan dan penanda tangan bergantung pada organisasi (OD-04/05, CAP-02).

## DEPENDS ON

- TASK-009
- TASK-011

## BLOCKS

- TASK-014
- TASK-069

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-001, GAP-014, GAP-021) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- `org_units` + `org_unit_history` + `org_positions` + `org_appointments` (kolom `evidence_file_id` ditambahkan TASK-016)
- Perhitungan path ltree saat simpan/pindah induk
- Permintaan perubahan F1/F2 dengan state WF-029 (TR-281–288); TR-285 hanya bila versi kebijakan `organization.maintenance` berlaku resmi pada tanggal acuan (POLICY_REGISTRY §3.1)
- PG-505 Administrasi → Organisasi (pohon, detail, riwayat, penunjukan)
- Seeder organisasi sintetis untuk dev/test

## OUT OF SCOPE

- Grant akses (TASK-014)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DATA_MODEL §6](../04-architecture/DATA_MODEL.md#6-organization)
- [WF-029](../03-workflows/WORKFLOW_CONTRACTS.md#wf-029)
- [STATE_TRANSITIONS WF-029](../03-workflows/STATE_TRANSITIONS.md#wf-029)

## TRACEABILITY

- CAP: CAP-02
- AC/NFR: AC-03, AC-04
- WF/TR: WF-029 (TR-281–TR-288, 8 TR; BLOCKED_BY_GAP: TR-285) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-032
- IX: IX-001, IX-002, IX-003, IX-012
- GAP: GAP-001, GAP-014, GAP-021
- Keputusan Owner: OD-04, OD-05, OD-10

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Organization`
- `resources/js/pages/admin/organization`

## BUSINESS RULES

- [BR-001](../02-domain/BUSINESS_RULES.md#br-001)
- [BR-025](../02-domain/BUSINESS_RULES.md#br-025)
- [BR-026](../02-domain/BUSINESS_RULES.md#br-026)
- [INV-017](../02-domain/BUSINESS_RULES.md#inv-017)
- [INV-018](../02-domain/BUSINESS_RULES.md#inv-018)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Hanya admin.org.manage
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `org_units`, `org_unit_history`, `org_positions`, `org_appointments`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Organization
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
- Expected affected areas: app/Modules/Organization, resources/js/pages/admin/organization

## CONTEXT7

- Topics to verify: PostgreSQL ltree operators and GiST index
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
- Halaman: PG-505
- Primary action: Ajukan perubahan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Pohon unit dapat dicari

## ROUTE IMPACT

- RT-032

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-003
- IX-012

## NAVIGATION CONTRACT

- Parent: Administrasi
- Entry: Menu Organisasi
- Back action: Pohon/daftar unit
- Cancel: Detail unit
- Success destination: Detail unit + riwayat
- Error behavior: Galat validasi per field + ringkasan di atas form; input dipertahankan
- State preservation: Unit terpilih di URL
- Mobile behavior: Pohon → daftar bertingkat

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Pohon unit multi-level dengan pencarian; perpindahan induk memperbarui subtree secara konsisten
- TAC-02 Riwayat perubahan tidak menimpa versi lama
- TAC-03 Penunjukan berjangka dengan rujukan SK; penunjukan tidak memberi izin
- TAC-04 Keputusan berlaku (TR-285) tidak tersedia di produksi tanpa versi kebijakan `organization.maintenance` yang berlaku resmi pada tanggal acuan

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: ltree subtree, riwayat, penunjukan, gerbang kebijakan
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Kelola unit
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
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

- TASK-014

## DO NOT DO

- Jangan mengasumsikan struktur hanya fakultas
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
