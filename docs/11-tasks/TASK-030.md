# TASK-030 — Penetapan metode (WF-032)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Mewujudkan WF-032 lengkap: data dasar penetapan, resolusi rezim, saran metode dengan rujukan pasal, penetapan oleh penugasan `METHOD_DETERMINER` dengan alasan wajib pada penyimpangan/tumpang tindih/kriteria kualitatif, perubahan penetapan dan dampaknya ke rencana langkah, serta IX-023.

## WHY THIS EXISTS

OD-27/BR-031: lima metode per rezim dengan keputusan manusia yang terlacak.

## DEPENDS ON

- TASK-029
- TASK-028

## BLOCKS

- TASK-031

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-003, GAP-026, GAP-027, GAP-029) **tidak menghalangi implementasi**; aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Tabel `proc_method_determinations`; satu DETERMINED per paket
- State WF-032 (TR-311–316); TR-313/315 hanya dengan kebijakan ACTIVE di produksi
- Kartu saran IX-023 (memenuhi/tidak memenuhi beserta alasan, tumpang tindih)
- Nota penetapan metode (dokumen draf) via provider
- PG-108; kapabilitas `procurement.method.*`

## OUT OF SCOPE

- Aktivasi kebijakan (Owner/GAP)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [WF-032](../03-workflows/WORKFLOW_CONTRACTS.md#wf-032)
- [STATE_TRANSITIONS WF-032](../03-workflows/STATE_TRANSITIONS.md#wf-032)
- [RT-035](../03-workflows/ROUTE_CONTRACTS.md#rt-035)
- [IX-023](../03-workflows/INTERACTION_CONTRACTS.md#ix-023)
- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)

## TRACEABILITY

- CAP: CAP-05, CAP-23
- AC/NFR: AC-07, AC-30, AC-32
- WF/TR: WF-032 (TR-311–TR-316, 6 TR; BLOCKED_BY_GAP: TR-313, TR-315) — rentang adalah seluruh WF; transisi yang dikerjakan Task ini tercantum di IN SCOPE
- RT: RT-035
- IX: IX-001, IX-003, IX-004, IX-023
- GAP: GAP-003, GAP-026, GAP-027, GAP-029
- Keputusan Owner: OD-27

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Procurement (MethodDetermination)`
- `resources/js/pages/procurement/method`

## BUSINESS RULES

- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- AUTHZ-40
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `proc_method_determinations`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Procurement (MethodDetermination)
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
- Expected affected areas: app/Modules/Procurement (MethodDetermination), resources/js/pages/procurement/method

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
- Halaman: PG-108
- Primary action: Tetapkan metode
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Saran dengan rujukan pasal; banner simulasi

## ROUTE IMPACT

- RT-035

## INTERACTION CONTRACTS

- IX-001
- IX-003
- IX-004
- IX-023

## NAVIGATION CONTRACT

- Parent: Detail paket tab Metode
- Entry: Tab Metode / inbox penetap
- Back action: Detail paket
- Cancel: Detail paket
- Success destination: Detail paket dengan metode
- Error behavior: Alasan wajib/gerbang kebijakan
- State preservation: Tidak ada state daftar khusus
- Mobile behavior: Kartu saran

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Saran untuk barang Rp700.000.000 menyertakan PL; Rp700.000.001 tidak (rezim PR 34 DRAFT)
- TAC-02 Konsultansi Rp350.000.000 menampilkan tumpang tindih dan mewajibkan alasan
- TAC-03 Penetapan hanya oleh penugasan penetap; Super Admin tidak dapat
- TAC-04 Perubahan kebijakan sejak saran memaksa tinjau ulang
- TAC-05 Penetapan baru menggantikan lama dan menandai langkah terdampak
- TAC-06 Di konfigurasi produksi tanpa kebijakan ACTIVE, tombol tetapkan nonaktif dengan alasan

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: WF-032 transisi dan guard
- Authorization: AUTHZ-40
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: J-03, J-04
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe PG-108
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

- TASK-031
- TASK-032

## DO NOT DO

- Jangan menjadikan saran sebagai penetapan otomatis
- Jangan memperluas ruang lingkup tanpa TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
