# TASK-074 — Regresi keamanan dan matriks otorisasi lengkap

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M8 — Pelaporan, hardening dan rilis
PARALLEL SAFE: YES
```

## OBJECTIVE

Menyelesaikan suite regresi SEC-01–20 dan AUTHZ-* (matriks dibangkitkan dari katalog kapabilitas × profil × cakupan), header keamanan/CSP, pemeriksaan IDOR semua rute objek, audit dependensi, pemindaian rahasia, dan tinjauan model ancaman terhadap implementasi.

## WHY THIS EXISTS

SECURITY_MODEL dan ACCESS_CONTROL; AC-31.

## DEPENDS ON

- TASK-072

## BLOCKS

- TASK-078

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Opsional: uji penetrasi independen
- GAP terkait (GAP-014) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila kebijakan terkait ACTIVE ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register))

## IN SCOPE

- Uji otomatis seluruh skenario
- Skenario offboarding RB-13: pengguna yang dinonaktifkan kehilangan seluruh akses seketika, penugasan berakhir dan pekerjaan dialihkan
- CSP final dengan nonce Vite
- Tinjauan independen (subagen/manusia) atas hasil; uji penetrasi eksternal hanya bila Owner memutuskannya (D-29)
- Laporan keamanan di `docs/05-security/evidence/`

## OUT OF SCOPE

- Uji penetrasi pihak ketiga (opsional, keputusan Owner)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §10](../05-security/SECURITY_MODEL.md#10-skenario-regresi-keamanan-otomatis)
- [ACCESS_CONTROL §10](../05-security/ACCESS_CONTROL.md#10-skenario-regresi-otorisasi)

## TRACEABILITY

- CAP: CAP-02, CAP-19
- AC/NFR: AC-04, AC-22, AC-31
- RT: Semua RT
- IX: IX-024
- GAP: GAP-014
- Keputusan Owner: OD-28

## EXPECTED MODULES / FILE AREAS

- `tests/Feature/Security`
- `tests/Feature/Authorization`
- `config/`

## BUSINESS RULES

- [BR-034](../02-domain/BUSINESS_RULES.md#br-034)
- [INV-001](../02-domain/BUSINESS_RULES.md#inv-001)
- [INV-004](../02-domain/BUSINESS_RULES.md#inv-004)
- [INV-021](../02-domain/BUSINESS_RULES.md#inv-021)

## ARCHITECTURE RULES

- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Semua SEC-* dan AUTHZ-*
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
- Browser E2E: N/A
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: tests/Feature/Security, tests/Feature/Authorization, config/

## CONTEXT7

- Topics to verify: Laravel security headers/CSP with Vite nonce
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- N/A (tanpa antarmuka pengguna)

## ROUTE IMPACT

- Semua RT

## INTERACTION CONTRACTS

- IX-024

## NAVIGATION CONTRACT

- N/A

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Semua SEC-* dan AUTHZ-* lulus, termasuk AUTHZ-46–AUTHZ-51 untuk evaluasi dan penghargaan per lingkup
- TAC-02 Tidak ada temuan kritis/tinggi dependensi terbuka
- TAC-03 Tidak ada rute objek tanpa uji IDOR

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Authorization: Matriks
- Route integrity: Rute baru terdaftar di uji integritas rute
- Production build: npm run build
- Security: Suite lengkap

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

- TASK-078

## DO NOT DO

- Jangan menonaktifkan uji yang gagal
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
