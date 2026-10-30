# TASK-035 — Hasil per lingkup, beberapa pemenang dan reviu hasil (WF-010)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan hasil per lingkup penghargaan: peringkat, pemenang dan cadangan dari putaran yang selesai; kegagalan beralasan per lingkup atau pemilihan; penetapan oleh fungsi berwenang menurut nilai (`procurement.authority`) untuk satu atau beberapa lingkup sekaligus; pengumuman (referensi kanal); reviu PPK sebelum kontrak bila berlaku; dan aktivasi cadangan sebagai versi hasil baru untuk satu lingkup — mendukung beberapa pemenang dalam satu paket (OD-35).

## WHY THIS EXISTS

OD-35, BR-037/BR-038, INV-024; PR 34/2023 Lampiran III B.3.b.8, Pasal 9 ayat (2) huruf a angka 13 dan huruf c angka 3, Pasal 38 ayat (4)–(5).

## DEPENDS ON

- TASK-034

## BLOCKS

- TASK-036

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-013, GAP-017, GAP-027) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `proc_results` (per pemilihan dan lingkup, berversi), `proc_result_rankings`, `proc_result_reviews` dengan FK komposit ke pemilihan, lingkup, mode penghargaan dan partisipasi; trigger kelayakan peringkat (lulus tahap gugur lingkup itu; E-Purchasing: opsi katalog)
- Penetapan beberapa lingkup dalam satu tindakan dan satu dokumen penetapan; kegagalan per lingkup dengan alasan; pemilihan gagal bila semua lingkupnya gagal
- Reviu hasil oleh penugasan `PPK` menurut kebijakan; tindak lanjut NOT_AGREED tetap terblokir (GAP-003)
- Aktivasi cadangan: versi hasil baru untuk satu lingkup setelah lingkup SPPBJ/kontrak pemenang lama nonaktif (DI-26)
- State WF-010 (TR-091–094)
- Evaluator `WinnerAuthority` dari TASK-024
- PG-112; dokumen surat penetapan/laporan sebagai draf
- Kolom `package_id`/`selection_id`/`scope_id` hasil tidak dapat diubah setelah insert (DI-17)

## OUT OF SCOPE

- SPPBJ (TASK-036)
- Kontrak (TASK-037)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-010](../03-workflows/WORKFLOW_CONTRACTS.md#wf-010)
- [STATE_TRANSITIONS WF-010](../03-workflows/STATE_TRANSITIONS.md#wf-010)
- [RT-010](../03-workflows/ROUTE_CONTRACTS.md#rt-010)
- [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)
- [BR-037](../02-domain/BUSINESS_RULES.md#br-037)
- [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-05
- AC/NFR: AC-07, AC-34
- WF/TR: WF-010 (TR-091–TR-094, 4 TR; BLOCKED_BY_GAP: TR-092) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-010
- IX: IX-003, IX-006, IX-007, IX-008, IX-009
- GAP: GAP-003, GAP-013, GAP-017, GAP-027
- Keputusan Owner: OD-27, OD-35

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (Result)`
- `resources/js/pages/procurement/result`

## BUSINESS RULES

- [BR-004](../02-domain/BUSINESS_RULES.md#br-004)
- [BR-037](../02-domain/BUSINESS_RULES.md#br-037)
- [BR-038](../02-domain/BUSINESS_RULES.md#br-038)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)
- [INV-024](../02-domain/BUSINESS_RULES.md#inv-024)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-41
- AUTHZ-49
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_results`, `proc_result_rankings`, `proc_result_reviews`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (Result)
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
- Expected affected areas: app/Modules/Procurement (Result), resources/js/pages/procurement/result

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
- Halaman: PG-112
- Primary action: Tetapkan hasil per lingkup
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: AwardScopeCard per lingkup; status reviu PPK

## ROUTE IMPACT

- RT-010

## INTERACTION CONTRACTS

- IX-003
- IX-006
- IX-007
- IX-008
- IX-009

## NAVIGATION CONTRACT

- Parent: Detail paket
- Entry: Tab Hasil
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Tab Hasil
- Error behavior: Gerbang kewenangan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Kartu per lingkup

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Paket dua lot menghasilkan dua hasil dengan pemenang berbeda; satu penyedia dapat memenangkan beberapa lot; hasil kedua yang berlaku untuk lingkup dan pemilihan yang sama ditolak (DI-26)
- TAC-02 Peringkat atau pemenang dari partisipasi pemilihan lain, WINNER kedua pada lingkup SINGLE, peringkat ganda, atau peserta tanpa penawaran OPENED yang mencakup lingkup hasil, tanpa hasil tahap lengkap untuk lingkup itu, atau tanpa PASS pada setiap tahap gugur yang berlaku baginya ditolak aplikasi dan DB, termasuk lewat sisipan langsung (DI-05)
- TAC-03 Penetap hasil sesuai ambang nilai kebijakan (uji batas); kegagalan per lingkup tercatat dengan alasan dan langkah berikut
- TAC-04 Penunjukan Langsung dengan negosiasi akhir NOT_AGREED tidak dapat menghasilkan WINNER; MULTIPLE_FRAMEWORK tanpa izin kebijakan ditolak (DI-26)
- TAC-05 Reviu hasil hanya oleh penugasan PPK pada paket itu; NOT_AGREED memerlukan alasan (AUTHZ-49)
- TAC-06 Aktivasi cadangan ditolak selama SPPBJ/kontrak pemenang lama aktif dan hanya mengubah lingkup itu (DI-26)
- TAC-07 Kolom `package_id`/`selection_id`/`scope_id` hasil tidak dapat diubah (DI-17)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-010; DI-05, DI-17, DI-26 (uji negatif langsung DB)
- Authorization: AUTHZ-41, AUTHZ-49
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-03, J-05, J-19
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

- TASK-036

## DO NOT DO

- Jangan menetapkan pemenang otomatis dari peringkat
- Jangan kembali ke model satu pemenang per paket
- Jangan mengizinkan beberapa penyedia pada satu lingkup tanpa kebijakan
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
