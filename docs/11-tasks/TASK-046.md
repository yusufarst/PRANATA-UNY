# TASK-046 — Pemeriksaan, serah terima dan BAST (WF-014)

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: STANDARD
MILESTONE: M4 — Pengadaan lanjutan dan penyedia
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan pemeriksaan hasil (mutu oleh Pengendali Kualitas dengan BA pemeriksaan, administratif, PHO/FHO), serah terima dan BAST sebagai klaim terpisah dengan baris yang dapat diklasifikasikan untuk hilir, masa pemeliharaan, dokumen terkait, dan penilaian kinerja penyedia sesudah kontrak selesai — terpisah dari evaluasi penawaran.

## WHY THIS EXISTS

CAP-09/10, BR-012/013/032: BAST tidak otomatis menjadi pengakuan aset atau stok.

## DEPENDS ON

- TASK-044
- TASK-042

## BLOCKS

- TASK-047
- TASK-048
- TASK-061

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-017, GAP-018) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `proc_inspections`, `proc_handovers`, `proc_handover_lines` (FK komposit ke serah terima dan baris kontrak pada kontrak yang sama; beku setelah SIGNED kecuali klasifikasi dan rujukan hilir)
- State WF-014 (TR-131–136)
- PG-117
- Penanda status hilir PENDING
- Pemeriksa dari penugasan kontrak (misalnya `QUALITY_CONTROLLER`, PR 34/2023 Pasal 14) dan tautan pemeriksaan ke serah terima
- Penilaian kinerja CONTRACT_COMPLETION oleh penugasan PPK pada kontrak COMPLETED, memakai `vendor_performance_reviews` dari TASK-042 dan kriteria `vendor.performance` (DI-29); PG-124

## OUT OF SCOPE

- Klasifikasi dan draf hilir (TASK-061)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-014](../03-workflows/WORKFLOW_CONTRACTS.md#wf-014)
- [STATE_TRANSITIONS WF-014](../03-workflows/STATE_TRANSITIONS.md#wf-014)
- [RT-014](../03-workflows/ROUTE_CONTRACTS.md#rt-014)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-09, CAP-10
- AC/NFR: AC-12, AC-13, AC-36
- WF/TR: WF-014 (TR-131–TR-136, 6 TR; BLOCKED_BY_GAP: TR-132, TR-133, TR-135) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-014
- IX: IX-003, IX-006, IX-007, IX-010, IX-011
- GAP: GAP-003, GAP-017, GAP-018
- Keputusan Owner: OD-30, OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Handover)`
- `resources/js/pages/procurement/handovers`

## BUSINESS RULES

- [BR-012](../02-domain/BUSINESS_RULES.md#br-012)
- [BR-013](../02-domain/BUSINESS_RULES.md#br-013)
- [BR-032](../02-domain/BUSINESS_RULES.md#br-032)
- [BR-039](../02-domain/BUSINESS_RULES.md#br-039)
- [INV-011](../02-domain/BUSINESS_RULES.md#inv-011)
- [INV-012](../02-domain/BUSINESS_RULES.md#inv-012)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Penugasan pemeriksa/penerima
- AUTHZ-51
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_inspections`, `proc_handovers`, `proc_handover_lines`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Handover)
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

- Context7: OPTIONAL (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: REQUIRED
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Procurement (Handover), resources/js/pages/procurement/handovers

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
- Halaman: PG-117, PG-124
- Primary action: Catat pemeriksaan / BAST
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Penilaian kinerja tersedia setelah kontrak selesai

## ROUTE IMPACT

- RT-014

## INTERACTION CONTRACTS

- IX-003
- IX-006
- IX-007
- IX-010
- IX-011

## NAVIGATION CONTRACT

- Parent: Detail kontrak
- Entry: Tab Serah Terima
- Back action: Detail kontrak
- Cancel: Detail kontrak
- Success destination: BAST
- Error behavior: Galat ditampilkan di tempat dengan request id; input dan konteks dipertahankan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Tabel menjadi kartu; aksi utama tetap terlihat; target sentuh ≥ 44 px

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Pemeriksaan, penerimaan, serah terima dan BAST tercatat sebagai klaim berbeda
- TAC-02 BAST tidak membuat aset/stok otomatis
- TAC-03 Baris BAST siap diklasifikasikan
- TAC-04 Baris BAST yang menunjuk baris kontrak dari kontrak lain ditolak aplikasi dan DB; kolom `contract_id` serah terima dan barisnya tidak dapat diubah (DI-17, DI-19)
- TAC-05 Tambah/ubah/hapus baris BAST setelah SIGNED ditolak kecuali pengisian kolom klasifikasi dan rujukan hilir (DI-15)
- TAC-06 Penilaian kinerja ditolak sebelum kontrak COMPLETED, untuk perusahaan selain penyedia kontrak, atau oleh pihak tanpa penugasan; penilaian FINAL tidak dapat diubah (DI-29, AUTHZ-51)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-014; DI-15, DI-19, DI-29 (uji negatif langsung DB)
- Authorization: AUTHZ-51
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Bagian J-08, J-20
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

- TASK-047
- TASK-061

## DO NOT DO

- Jangan mengakui aset dari BAST secara otomatis
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
