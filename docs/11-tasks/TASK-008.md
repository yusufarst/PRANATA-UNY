# TASK-008 — Peran basis data, tabel append-only dan jejak audit

Status dokumen: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: NO
```

## OBJECTIVE

Menerapkan peran PostgreSQL `pranata_owner`/`pranata_app`, koneksi `migrator`, utilitas trigger penolak UPDATE/DELETE, tabel `audit_events` berpartisi bulanan dan layanan `AuditLogger`, beserta job pembuat partisi.

## WHY THIS EXISTS

Kepercayaan riwayat (INV-017, CAP-19) dan hak minimum DB (ADR-0010) menjadi dasar semua modul.

## DEPENDS ON

- TASK-004
- TASK-007

## BLOCKS

- TASK-009
- TASK-010

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- GAP terkait (GAP-023) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Migrasi pembuatan peran (dev/test) dan dokumentasi pembuatan peran di server
- Koneksi `migrator` di konfigurasi; aplikasi memakai `pranata_app`
- Fungsi trigger generik `forbid_update_delete()`, `forbid_scope_change()`, `forbid_change_when_parent_final()` (INSERT/UPDATE/DELETE dengan kunci baris induk `FOR SHARE`) dan kerangka constraint trigger tertunda `verify_capture_manifest()` untuk segel tangkapan, beserta helper migrasi untuk memasangnya; pemasangan pada tabel domain oleh Task pemilik tabel (DATA_MODEL §19 aturan 6)
- `audit_events` berpartisi + partisi default + command pembuat partisi 3 bulan ke depan
- `AuditLogger` (actor, action, subject, changes tanpa rahasia, request_id, ip, reason) dan uji
- Uji hak: `pranata_app` tidak dapat UPDATE/DELETE audit

## OUT OF SCOPE

- Halaman penelusuran audit PG-511 (TASK-072)
- Retensi (GAP-023)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [DATA_MODEL §2, §12](../04-architecture/DATA_MODEL.md#2-peran-basis-data-dan-hak)
- [ADR-0010](../adr/ADR-0010-audit-and-db-roles.md)
- [SECURITY_MODEL §9](../05-security/SECURITY_MODEL.md#9-pemantauan-keamanan)
- [OPERATIONS_RUNBOOK RB-05](../09-operations/OPERATIONS_RUNBOOK.md#rb-05-partisi-audit-dan-pemeliharaan-basis-data)
- [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)

## TRACEABILITY

- CAP: CAP-19
- AC/NFR: AC-22
- IX: IX-012
- GAP: GAP-023

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Audit`
- `app/Modules/Platform`
- `database/migrations`
- `config/database.php`

## BUSINESS RULES

- [BR-026](../02-domain/BUSINESS_RULES.md#br-026)
- [INV-017](../02-domain/BUSINESS_RULES.md#inv-017)

## ARCHITECTURE RULES

- Audit ditulis dalam transaksi Action yang sama
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- Tidak menulis password/token/rahasia ke audit
- Audit gagal → aksi gagal (fail closed) untuk aksi rahasia
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: audit_events (partisi), peran DB
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Audit, app/Modules/Platform
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
- Expected affected areas: app/Modules/Audit, app/Modules/Platform, database/migrations, config/database.php

## CONTEXT7

- Topics to verify: PostgreSQL declarative partitioning; Laravel multiple database connections for migrations
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- N/A (tanpa antarmuka pengguna)

## ROUTE IMPACT

- N/A

## INTERACTION CONTRACTS

- IX-012

## NAVIGATION CONTRACT

- N/A

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Migrasi berjalan dengan peran pemilik; aplikasi berjalan dengan peran app
- TAC-02 UPDATE/DELETE pada audit_events ditolak untuk peran app dan oleh trigger
- TAC-03 Partisi bulan berjalan + 3 bulan ke depan ada; sisipan di luar rentang masuk partisi default
- TAC-04 AuditLogger mencatat field yang diwajibkan dan menolak memuat field rahasia
- TAC-05 Pada tabel uji, `forbid_scope_change()` menolak perubahan kolom lingkup dan `forbid_change_when_parent_final()` menolak ubah/hapus baris anak dari induk final, termasuk perintah langsung memakai peran app (DI-17)
- TAC-06 Pada tabel uji, `forbid_change_when_parent_final()` juga menolak sisipan baris anak ke induk final, termasuk sisipan dari koneksi kedua yang bersamaan dengan finalisasi, dan `verify_capture_manifest()` menolak saat commit sisipan anak sesudah transaksi pencatatan induk serta sidik jari yang tidak cocok

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Unit: AuditLogger
- Feature: Pest uji hak dan trigger; uji negatif langsung DB untuk ketiga fungsi trigger generik (DI-17)
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Production build: npm run build
- Migration: fresh + rollback

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

- TASK-009

## DO NOT DO

- Jangan memberi peran app hak DELETE pada tabel append-only
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
