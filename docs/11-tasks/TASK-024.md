# TASK-024 — Paket kebijakan pengadaan DRAFT dan kasus uji regulasi

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M3 — Pengadaan inti (R1 / UAT-1)
PARALLEL SAFE: NO
```

## OBJECTIVE

Menyusun skema dan isi DRAFT untuk keluarga `procurement.regime`, `procurement.methods`, `procurement.authority`, `procurement.documents`, `procurement.contract`, `procurement.hps`, `procurement.budget`, `procurement.evaluation`, `vendor.dpt`, `vendor.performance` dan `numbering` untuk rezim PR 34/2023 dan Perpres 16/2018 jo. 46/2025, lengkap dengan rujukan pasal dan kasus uji emas — semuanya berstatus DRAFT.

## WHY THIS EXISTS

Fitur pengadaan harus dapat diuji end-to-end tanpa mengarang aturan; nilai regulasi disalin dengan rujukan dan tetap menunggu konfirmasi kustodian (GAP-026/027).

## DEPENDS ON

- TASK-018
- TASK-017

## BLOCKS

- TASK-027
- TASK-031

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Resolusi GAP-026/027 sebelum ACTIVE di produksi
- GAP terkait (GAP-003, GAP-004, GAP-017, GAP-019, GAP-026, GAP-027, GAP-028, GAP-029) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Seeder idempoten paket DRAFT dengan `source_refs` ke SRC-048/049/050 dan `gap_refs`
- Evaluator `MethodSuggestion` (pita inklusif/eksklusif, kriteria kualitatif, tumpang tindih, syarat DPT, kanal)
- Evaluator `WinnerAuthority`, `ContractForm`, `GuaranteeRequirement`, `AmendmentLimit`, `LatePenalty`, `HpsRequirement`
- Evaluator `EvaluationRules`: metode evaluasi yang diizinkan per metode/varian/jenis, minimum undangan atau kualifikasi yang bersumber aturan, praktik setempat informatif (tiga penawaran Pengadaan Langsung, `PENDING_VALIDATION`), mode penghargaan lingkup, urutan negosiasi E-Purchasing, kegagalan Penunjukan Langsung, penerapan reviu hasil; kriteria `vendor.performance` (kualitas, kuantitas, waktu)
- Matriks penerapan langkah dan dokumen `procurement.documents` per metode × varian × jenis × pita nilai dengan `PENDING_VALIDATION` bila belum bersumber
- Kasus uji emas dari REGULATORY_SOURCE_REVIEW untuk kedua rezim (termasuk batas 700 juta, 2 miliar, konsultansi 300–400 juta, e-purchasing ≤ 15 miliar, PL Perpres 200/400/100 juta)
- Penanda GAP-027 (a)–(e) sebagai wajib-pilih manusia

## OUT OF SCOPE

- Aktivasi ACTIVE (memerlukan bukti resolusi GAP oleh Owner)
- UI pengadaan

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [POLICY_REGISTRY §1, §4](../04-architecture/POLICY_REGISTRY.md)
- [REGULATORY_SOURCE_REVIEW](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md)
- [GAP_REGISTER GAP-026–029](../00-governance/GAP_REGISTER.md)
- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)
- [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)

## TRACEABILITY

- CAP: CAP-05, CAP-23
- AC/NFR: AC-07, AC-30, AC-32, AC-33, AC-35
- WF/TR: WF-005 (TR-041–TR-045, 5 TR; BLOCKED_BY_GAP: TR-044); WF-032 (TR-311–TR-316, 6 TR; BLOCKED_BY_GAP: TR-313, TR-315)
- RT: RT-032
- GAP: GAP-003, GAP-004, GAP-017, GAP-019, GAP-026, GAP-027, GAP-028, GAP-029
- Keputusan Owner: OD-27, OD-35, OD-36

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Policy/Schemas`
- `app/Modules/Procurement/Policy`
- `database/seeders/Policy`

## BUSINESS RULES

- [BR-031](../02-domain/BUSINESS_RULES.md#br-031)
- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [BR-036](../02-domain/BUSINESS_RULES.md#br-036)
- [BR-037](../02-domain/BUSINESS_RULES.md#br-037)
- [BR-038](../02-domain/BUSINESS_RULES.md#br-038)
- [INV-022](../02-domain/BUSINESS_RULES.md#inv-022)

## ARCHITECTURE RULES

- Evaluator murni (tanpa efek samping), menerima PolicyHandle
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Tidak ada data pribadi dalam isi kebijakan
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: data seed policy_*
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Policy/Schemas, app/Modules/Procurement/Policy
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
- Browser E2E: N/A
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: app/Modules/Policy/Schemas, app/Modules/Procurement/Policy, database/seeders/Policy

## CONTEXT7

- Topics to verify: —
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- N/A (tanpa antarmuka pengguna)

## ROUTE IMPACT

- RT-032

## INTERACTION CONTRACTS

- N/A

## NAVIGATION CONTRACT

- N/A

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Semua paket tervalidasi skema dan berstatus DRAFT dengan rujukan sumber dan GAP
- TAC-02 Seluruh kasus uji emas lulus untuk kedua rezim
- TAC-03 Tumpang tindih konsultansi PR 34/2023 menghasilkan penanda wajib-pilih
- TAC-04 Tidak ada nilai rezim yang dipakai untuk rezim lain (uji)
- TAC-05 Nilai yang meragukan dari OCR ditandai untuk konfirmasi di `notes` versi
- TAC-06 Kasus emas evaluasi: Quotation dengan < 3 DPT diundang → peringatan; tender terbatas dengan 2 peserta lulus kualifikasi → gagal; pembukaan tidak pernah menggugurkan; Pengadaan Langsung dengan 2 penawaran tidak diblokir (praktik tiga penawaran informatif); E-Purchasing dengan dua penyedia → negosiasi termurah lebih dahulu; MULTIPLE_FRAMEWORK hanya untuk kontrak payung
- TAC-07 Kombinasi metode × jenis × nilai tanpa aturan dokumen menghasilkan `PENDING_VALIDATION`, bukan wajib atau tidak berlaku

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: Evaluator dan golden cases
- Feature: Seeder idempoten
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
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

HIGH — swa-review terstruktur + review PR otomatis (Codex) + review independen kedua; lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.3 HIGH
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

- TASK-025
- TASK-027

## DO NOT DO

- Jangan menandai paket ACTIVE
- Jangan mengisi pejabat penetap dari jawaban provisional sebagai nilai ACTIVE
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
