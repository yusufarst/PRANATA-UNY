# TASK-016 — Berkas privat: unggah, unduh terotorisasi dan audit

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M1 — Identitas, akses dan audit
PARALLEL SAFE: YES
```

## OBJECTIVE

Mewujudkan `file_objects`/`file_links`, unggah tervalidasi (ekstensi+MIME+ukuran), penyimpanan privat tulis-sekali dengan SHA-256 dan identitas isi yang tidak dapat diubah, tautan dengan pelepasan sekali serta tautan bersegel yang tidak dapat diubah, unduhan/pratinjau terotorisasi melalui subjek yang memverifikasi SHA-256, audit unduhan rahasia, dan komponen FileDropzone/EvidenceList.

## WHY THIS EXISTS

Bukti dan dokumen adalah inti CAP-19 dan sumber risiko kebocoran (T-09).

## DEPENDS ON

- TASK-014

## BLOCKS

- TASK-018
- TASK-021
- TASK-023
- TASK-026
- TASK-031
- TASK-068

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: SEC-D4 ClamAV opsional
- GAP terkait (GAP-023) **tidak menghalangi implementasi** menurut model gerbang usulan yang menunggu konfirmasi Owner (D-28); aksi resmi yang bergantung padanya hanya aktif di produksi bila versi kebijakan terkait disahkan dengan bukti resolusi dan berlaku resmi pada tanggal acuan aksi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register), [§3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi))

## IN SCOPE

- Tabel `file_objects`, `file_links`, `file_sealed_owner_types` dan service penyimpanan privat tulis-sekali: path acak baru dengan pembuatan eksklusif, SHA-256 dan ukuran dihitung server dari byte yang disimpan, tanpa operasi timpa, pindah atau hapus berkas tersimpan (DI-33)
- Hak kolom `pranata_app`: `file_objects` hanya `UPDATE (classification, scan_status)`, `file_links` hanya `UPDATE (label, unlinked_by, unlinked_at)`, `file_sealed_owner_types` hanya `SELECT`; trigger `guard_file_object_change()` (identitas isi tetap, klasifikasi hanya naik, status pemindaian tidak kembali ke NOT_SCANNED) dan `guard_file_link_change()` (tautan dibuat aktif; `owner_type` berupa nama tabel sesuai morph map yang dipaksakan; berkas, pemilik dan tujuan tetap; pelepasan sekali dengan jam basis data; sisipan ke pemilik terdaftar bersegel mengunci baris pemilik `FOR KEY SHARE` dan ditolak bila belum terlihat; setiap UPDATE/DELETE tautan ke jenis pemilik terdaftar bersegel ditolak) (DI-33)
- Pemasangan `verify_capture_manifest()` sekali pada `file_links` dengan dispatch ke `file_sealed_owner_types.seal_function`, diuji dengan jenis pemilik dan fungsi sidik jari uji (DI-33)
- Validasi allowlist dan ukuran per tujuan; penolakan ekstensi ganda
- Rute unduh `/files/{public_id}` dengan pemeriksaan subjek dan verifikasi ukuran serta SHA-256 atas salinan yang akan dikirim sebelum byte pertama; ketidakcocokan atau berkas hilang gagal tertutup dengan peristiwa audit `file.integrity_failed` dan peringatan operasi (DI-33)
- Layanan integritas berkas sebagai satu-satunya jalur baca byte tersimpan (unduhan, pratinjau, perakitan paket dokumen, konversi) dan job harian verifikasi ulang byte berkas bertaut ke pemilik bersegel (CONCURRENCY_AND_INTEGRITY §7 (e)) (DI-33)
- Pratinjau inline hanya PDF/PNG/JPG
- Audit unduhan CONFIDENTIAL/RESTRICTED
- Status pemindaian `NOT_SCANNED` (ClamAV opsional kelak)
- Komponen UI unggah/daftar bukti; aksi lepas hanya untuk tautan tidak bersegel yang diizinkan modul pemilik
- Migrasi menambah `evidence_file_id` (FK `file_objects`) pada `org_appointments` dan `process_assignments`, serta lampiran bukti SK pada panel penunjukan dan penugasan

## OUT OF SCOPE

- Antivirus (keputusan Owner SEC-D4)

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [SECURITY_MODEL §6](../05-security/SECURITY_MODEL.md#6-berkas-unduhan-dan-ekspor)
- [ADR-0009](../adr/ADR-0009-private-file-storage.md)
- [COMMAND_AND_API_CONTRACTS §7](../06-api-performance/COMMAND_AND_API_CONTRACTS.md#7-unggah-dan-unduh)

## TRACEABILITY

- CAP: CAP-19
- AC/NFR: AC-22
- WF/TR: WF-028 (TR-271–TR-276, 6 TR; BLOCKED_BY_GAP: TR-274)
- RT: RT-031
- IX: IX-011
- GAP: GAP-023

## EXPECTED MODULES / FILE AREAS

- `app/Modules/Files`
- `resources/js/components/app/FileDropzone.tsx`
- `resources/js/components/app/EvidenceList.tsx`

## BUSINESS RULES

- [BR-026](../02-domain/BUSINESS_RULES.md#br-026)

## ARCHITECTURE RULES

- Unggah selalu dalam konteks subjek modul pemilik
- Isi berkas berbeda selalu menjadi `file_objects` baru
- Ikuti aturan dependensi modul ([ARCHITECTURE §5](../04-architecture/ARCHITECTURE.md#5-aturan-dependensi-antar-modul)) dan pola lapisan §6
- Satu perintah = satu Action bertransaksi; efek samping setelah commit

## SECURITY / PERMISSION REQUIREMENTS

- SEC-10–13
- SEC-22
- AUTHZ-44
- Penegakan di server/kueri/unduhan; frontend hanya mencerminkan izin ([ACCESS_CONTROL §8](../05-security/ACCESS_CONTROL.md#8-kesesuaian-frontendbackend))

## DATABASE IMPACT ASSESSMENT

- Schema change: PROPOSED
- Migration required: YES
- Tables: `file_objects`, `file_links`, `file_sealed_owner_types`
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: app/Modules/Files
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: YES
- Replaceable adapter/contract required: YES — Penyimpanan berkas di balik disk Flysystem (lokal V1, object storage kelak)
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
- Expected affected areas: app/Modules/Files, resources/js/components/app/FileDropzone.tsx, resources/js/components/app/EvidenceList.tsx

## CONTEXT7

- Topics to verify: Laravel filesystem private disk, streamed downloads
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
- Halaman: PG-012
- Primary action: Unggah bukti
- Secondary actions: dalam menu "Lainnya" atau outline; tanpa duplikasi
- Destructive actions: ConfirmActionDialog dengan alasan bila wajib
- Redundancy constraints: satu aksi utama per halaman/panel
- Loading state: skeleton
- Empty state: penjelasan + aksi pertama
- Error state: pesan + request id + langkah aman
- Catatan: Progres unggah dan galat jelas

## ROUTE IMPACT

- RT-031

## INTERACTION CONTRACTS

- IX-011

## NAVIGATION CONTRACT

- Mengikuti kontrak RT terkait dan INTERACTION_PATTERNS §6

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Berkas tidak dapat diakses lewat URL statis
- TAC-02 Unduhan tanpa izin subjek ditolak; unduhan rahasia tercatat
- TAC-03 Validasi menolak tipe/ukuran tidak sah dengan pesan terlokalisasi
- TAC-04 SHA-256 dan ukuran dihitung server dari byte yang disimpan dan diverifikasi pada setiap unduhan dan pratinjau sebelum byte pertama dikirim; berkas yang byte-nya diubah atau hilang di disk menghasilkan galat terlokalisasi tanpa isi berkas dan tercatat sebagai insiden integritas (DI-33, SEC-22)
- TAC-05 Dengan koneksi `pranata_app` langsung, perubahan identitas isi `file_objects`, penurunan klasifikasi dan transisi status pemindaian yang tidak sah ditolak, sedangkan transisi pemindaian yang sah berhasil; trigger tetap menolak dengan peran berhak tabel penuh di basis data uji (DI-33)
- TAC-06 Berkas, pemilik dan tujuan tautan tidak dapat dialihkan; pelepasan tautan tidak bersegel berhasil sekali tanpa menautkan ulang; pada jenis pemilik uji yang terdaftar bersegel, pelepasan, perubahan label, pengalihan dan penghapusan tautan ditolak, termasuk dari koneksi kedua selama transaksi penautan belum commit dan sesudahnya, sisipan ke baris pemilik yang tidak ada ditolak, dan `owner_type` yang bukan nama tabel ditolak (DI-33)
- TAC-07 Unggahan tidak pernah menimpa berkas yang ada dan mengabaikan sidik jari atau ukuran dari klien; pembacaan sisi server (perakitan paket uji) atas berkas rusak gagal tertutup; job harian melaporkan berkas bersegel uji yang byte-nya berubah sebagai insiden integritas tanpa memperbaikinya (DI-33)

## TEST REQUIREMENTS

- Lint: Pint + ESLint pada berkas yang diubah
- Typecheck: Larastan + tsc
- Feature: Unggah/unduh/otorisasi; DI-33 (uji negatif langsung DB dengan dua koneksi)
- Authorization: Uji izin positif dan negatif untuk setiap aksi baru
- Route integrity: Rute baru terdaftar di uji integritas rute
- Browser E2E: Unggah bukti
- Localization/language switch: Dua bahasa; kunci hilang = 0
- Responsive: 390×844 dan 1440×900
- Accessibility: axe tanpa pelanggaran serius/kritis
- Production build: npm run build
- Security: SEC-10–13, SEC-22, AUTHZ-44
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

- TASK-018
- TASK-021

## DO NOT DO

- Jangan menyimpan berkas di public/
- Jangan menimpa, memindah atau mengganti isi berkas tersimpan; isi berbeda selalu menjadi berkas baru
- Jangan memperluas ruang lingkup; perubahan rencana diajukan ke Planning Agent sebagai usulan TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
