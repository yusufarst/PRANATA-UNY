# TASK-052 — Permintaan barang dan keputusan (WF-030 bagian 1)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M5 — Gudang
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan permintaan barang per gudang: draf, ajukan bernomor, rantai keputusan dari kebijakan gudang (dengan SOD-06), setujui sebagian/tolak, kembalikan untuk perbaikan, tarik; tanpa reservasi atau perubahan stok.

## WHY THIS EXISTS

OD-31, CAP-22, AC-29.

## DEPENDS ON

- TASK-050
- TASK-019
- TASK-015

## BLOCKS

- TASK-053

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Resolusi GAP-025 sebelum aktif resmi di produksi
- GAP terkait (GAP-023, GAP-025) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `inv_requests` (`uq(id, warehouse_id)`; `warehouse_id` tidak dapat diubah), `inv_request_lines` (`uq(id, request_id, item_id)`; `request_id` tidak dapat diubah) sebagai induk FK komposit serah terima
- TR-291–294, TR-298
- Rantai keputusan multi-langkah dari `warehouse.operations`: satu `inv_request_decisions` per langkah per siklus (penugasan REQUEST_APPROVER per langkah); TR-293 berulang per langkah; jumlah disetujui tidak boleh naik antarlangkah
- PG-005, PG-202 (daftar, antrean keputusan, detail, form)
- Ketersediaan stok sebagai informasi

## OUT OF SCOPE

- Serah terima (TASK-053)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-030](../03-workflows/WORKFLOW_CONTRACTS.md#wf-030)
- [STATE_TRANSITIONS WF-030](../03-workflows/STATE_TRANSITIONS.md#wf-030)
- [RT-033](../03-workflows/ROUTE_CONTRACTS.md#rt-033)
- [BR-033](../02-domain/BUSINESS_RULES.md#br-033)

## TRACEABILITY

- CAP: CAP-22
- AC/NFR: AC-29
- WF/TR: WF-030 (TR-291–TR-298, 8 TR; BLOCKED_BY_GAP: TR-293) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-033
- IX: IX-001, IX-002, IX-003, IX-004, IX-005, IX-007, IX-008, IX-009
- GAP: GAP-023, GAP-025
- Keputusan Owner: OD-31

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Inventory/Requests`
- `resources/js/pages/warehouse/requests`

## BUSINESS RULES

- [BR-030](../02-domain/BUSINESS_RULES.md#br-030)
- [BR-033](../02-domain/BUSINESS_RULES.md#br-033)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-11
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `inv_requests`, `inv_request_lines`, `inv_request_decisions`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Inventory/Requests
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
- Expected affected areas: app/Modules/Inventory/Requests, resources/js/pages/warehouse/requests

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
- Halaman: PG-005, PG-202
- Primary action: Ajukan / Putuskan
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Form mobile-first

## ROUTE IMPACT

- RT-033

## INTERACTION CONTRACTS

- IX-001
- IX-002
- IX-003
- IX-004
- IX-005
- IX-007
- IX-008
- IX-009

## NAVIGATION CONTRACT

- Parent: Beranda → Permintaan saya / Gudang → Permintaan
- Entry: Menu / inbox
- Back action: Daftar asal
- Cancel: Kembali ke halaman asal tanpa menyimpan; konfirmasi bila ada perubahan belum tersimpan
- Success destination: Detail permintaan
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Filter, urutan, tab dan cursor di URL; dipulihkan saat kembali
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Persetujuan tidak mengubah stok
- TAC-02 Pemohon tidak dapat menyetujui permintaannya sendiri
- TAC-03 Jumlah disetujui sebagian wajib beralasan
- TAC-04 Rantai dua langkah: setiap langkah tercatat terpisah; permintaan APPROVED setelah langkah terakhir; pengembalian memulai siklus baru tanpa menghapus keputusan lama
- TAC-05 Di produksi tanpa kebijakan gudang ACTIVE, keputusan resmi tidak tersedia dengan penjelasan
- TAC-06 Gudang permintaan dan induk baris tidak dapat diubah setelah insert (DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: TR-291–294/298; DI-17
- Authorization: AUTHZ-11
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-09 bagian 1
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

- TASK-053

## DO NOT DO

- Jangan memesan/mengunci stok saat persetujuan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
