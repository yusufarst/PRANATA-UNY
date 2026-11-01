# Konteks eksekusi — PRANATA UNY

Status: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P11

Konteks minimum yang stabil untuk eksekutor (AICWDF §34A). Dokumen ini **tidak menyalin** aturan; ia menunjuk pemilik kanoniknya. Bila ada konflik, pemilik kanonik yang berlaku dan konflik dicatat, bukan diselesaikan diam-diam.

```text
PROJECT: PRANATA UNY — sistem informasi pengadaan, gudang dan aset DPSA UNY (ALP; Gudang ALP dan PSRT)
CURRENT RELEASE: belum ada (pra-implementasi)
CURRENT PHASE: P0–P11 APPROVED (APPR-005, APPR-006); Planning Freeze FRZ-001
EXECUTION AUTHORITY: AUTH-013 — TASK-001–TASK-078, otonomi maksimum yang aman (bootstrap TASK-001–TASK-005, lalu AUTONOMOUS)
EXECUTOR STATE: siap memulai TASK-001 (EXECUTOR_BOOTSTRAP §5.0)
TASK BASELINE: 78 Task (TASK-001–TASK-078); status per TASK_PLAN; PCR-001 PUBLISHED (2026-11-01); hanya `origin/main` yang berlaku
```

## 1. Otoritas yang harus ada sebelum Task apa pun

1. Persetujuan Owner atas kandidat P0–P3 (amandemen) dan P4–P11 pada SHA yang direview, tercatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
2. Planning Freeze tercatat dan baseline Task dinyatakan siap.
3. Otorisasi eksekusi berdiri (standing) yang menyebut ruang lingkupnya: milestone/Task yang boleh dikerjakan, mode (terawasi atau otonom), izin merge ke `main`, dan batasnya.
4. Keputusan Owner D-08 (metode merge), D-27 (token eksekutor) dan D-28 (model gerbang GAP). Untuk mode otonom ada prasyarat tambahan: TASK-001–TASK-005 DONE dalam mode bootstrap, ruleset `main` aktif, token runner terpasang dan otorisasi mode otonom ([EXECUTOR_OPERATING_MODEL §1](EXECUTOR_OPERATING_MODEL.md#1-prasyarat-aktivasi)).

Status 2026-11-01: butir 1–4 terpenuhi oleh APPR-005, APPR-006, FRZ-001 dan AUTH-013 setelah `main` memuat FRZ-001; jawaban Owner D-01–D-29 (infrastruktur, staging tanpa VPS kedua, UAT, SMTP, biaya) ada di [OWNER_DECISION_PACKAGE §0](../handoff/OWNER_DECISION_PACKAGE.md#0-jawaban-owner-2026-11-01) dan mengikat setiap Task yang menyebut keputusan itu.

Tanpa butir 1–4: baca saja, laporkan, berhenti.

## 2. Stack default

Laravel (versi stabil terkini, diverifikasi pada TASK-002) + Inertia 2 + React + TypeScript + Tailwind CSS 4 + shadcn/ui; PostgreSQL ≥ 16 (`pg_trgm`, `ltree`, `btree_gist`); Nginx; antrean/cache/sesi basis data (tanpa Redis pada V1); Gotenberg (PDF), PhpWord TemplateProcessor (DOCX), OpenSpout (XLSX/CSV); `brick/math` untuk desimal; i18next/react-i18next. Modular monolith `app/Modules/<Module>`. Pemilik: [ARCHITECTURE](../04-architecture/ARCHITECTURE.md), [DATA_MODEL](../04-architecture/DATA_MODEL.md), ADR di [docs/adr](../adr/).

## 3. Profil autentikasi

```text
AUTH PROFILE:
- implementation: LARAVEL_NATIVE (starter kit resmi Laravel React; registrasi publik mati)
- password profile: AICWDF-COMPAT-8 (kecuali Owner memilih profil lebih kuat, SEC-D3)
- Google sign-in: OFF (OD-11/OD-12)
- Google registration behavior: N/A
- identitas masuk: username atau email + password; TOTP sesuai SECURITY_MODEL
- akun internal dibuat admin berwenang; akun penyedia dibuat petugas pengadaan berwenang
```

Pemilik: [SECURITY_MODEL](../05-security/SECURITY_MODEL.md), [ACCESS_CONTROL](../05-security/ACCESS_CONTROL.md).

## 4. Aturan global

| Area | Ringkas | Pemilik kanonik |
|---|---|---|
| Bahasa UI | Indonesia sederhana default; Inggris didukung; pengalih bahasa mempertahankan konteks | [LOCALIZATION](../07-ux-design/LOCALIZATION.md) |
| Desain | Navy `#172554`, kanvas `#EFF6FF`, permukaan putih dominan; shadcn/ui; tanpa mode gelap V1 | [DESIGN_SYSTEM](../07-ux-design/DESIGN_SYSTEM.md) (OD-34) |
| Interaksi | Satu aksi utama; tanpa tombol mati; kembali/batal kontekstual; keadaan memuat/kosong/galat/terlarang | [INTERACTION_PATTERNS](../07-ux-design/INTERACTION_PATTERNS.md), RT/IX P3 |
| Responsif | Mobile-first; 390×844 dan 1440×900 wajib diperiksa | [INTERACTION_PATTERNS §7](../07-ux-design/INTERACTION_PATTERNS.md#7-responsif) |
| Akses | Grup peran SUPER_ADMIN/INTERNAL/VENDOR + grant berscope + penugasan proses; penegakan server/kueri/unduhan | [ACCESS_CONTROL](../05-security/ACCESS_CONTROL.md) |
| Aturan resmi | Nilai resmi hanya dari versi kebijakan yang berlaku resmi pada tanggal acuan (disahkan dan jendela memuat tanggal itu, bukan kolom status); lainnya simulasi berlabel; GAP tidak diselesaikan agen | [POLICY_REGISTRY](../04-architecture/POLICY_REGISTRY.md), [GAP_REGISTER](../00-governance/GAP_REGISTER.md) |
| Data | Tanpa hapus record bisnis; tabel append-only; uang `numeric`; waktu `timestamptz` UTC | [DATA_MODEL §1](../04-architecture/DATA_MODEL.md#1-konvensi) |
| Konkurensi | Idempotency key, `lock_version`, urutan kunci tetap | [CONCURRENCY_AND_INTEGRITY](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md) |
| Biaya | Tambahan biaya produksi berulang ≈ 0 di luar infrastruktur yang dibiayai klien; layanan berbayar perlu persetujuan Owner | [COST_POLICY](../00-governance/COST_POLICY.md) |
| Produksi | Basis data produksi zero-touch; eksekutor tidak memegang kredensial staging/produksi | [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md), [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md) |
| Git | Identitas Owner saja; tanpa atribusi AI; tanpa force push; cabang netral | [GIT_WORKFLOW](../00-governance/GIT_WORKFLOW.md) (GOV-08/GOV-09), [RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang) |
| Isolasi | Hanya sumber daya `pranata`; MULTIPLECORP, BantuKasir, ELLIGBLE tidak disentuh | [INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi), [EXECUTOR_OPERATING_MODEL §11](EXECUTOR_OPERATING_MODEL.md#11-isolasi-multi-proyek-dan-keamanan-sumber-daya) |

## 5. Lingkungan pengembangan dan uji

```text
Topologi (OD-37): tanpa Docker/Compose, basis data atau layanan PRANATA di PC Owner
Lokal: penyuntingan, Git, gh, Node/npm, PowerShell, Claude Code + pengaman; alat ringan lingkup proyek saja
Uji berat: runner GitHub-hosted; PHP, PostgreSQL dan Gotenberg sebagai service container
Sebelum TASK-005: workflow bootstrap.yml (QUALITY_GATES §4.1); sesudahnya ci.yml + ci-gate
Basis data: pranata_test(_N), pranata_e2e_<run>, pranata_perf; hanya di service container CI
Perintah dev/uji: ditetapkan TASK-002–TASK-004 dan dicatat di TOOLCHAIN
```

Rujukan generik dalam Task pada kontainer dev atau pemeriksaan lokal dibaca menurut OD-37: pemeriksaan yang memerlukan PHP, PostgreSQL, Gotenberg atau browser dijalankan di runner GitHub-hosted ([INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi)); uji yang tidak dijalankan dicatat NOT RUN, bukan lulus.

## 6. Masuk eksekusi

1. Baca [AGENTS.md](../../AGENTS.md), dokumen ini, [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md), tabel status [TASK_PLAN](TASK_PLAN.md), lalu Task aktif.
2. Baca **hanya** bagian yang dirujuk Task (REFERENCES, TRACEABILITY) dan berkas sumber yang relevan; jangan membaca ulang seluruh P0–P11.
3. Ikuti siklus dan aturan [EXECUTOR_OPERATING_MODEL](EXECUTOR_OPERATING_MODEL.md); jawaban pertama memakai format AICWDF §38.
4. Indeks ID: BR/INV → [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md); WF/TR/RT/IX → [docs/03-workflows](../03-workflows/); PG → [INFORMATION_ARCHITECTURE](../07-ux-design/INFORMATION_ARCHITECTURE.md); AUTHZ/SOD → [ACCESS_CONTROL](../05-security/ACCESS_CONTROL.md); SEC → [SECURITY_MODEL](../05-security/SECURITY_MODEL.md); PB/PERF → [PERFORMANCE_AND_SCALE](../06-api-performance/PERFORMANCE_AND_SCALE.md); J/E2E → [USER_JOURNEYS](../07-ux-design/USER_JOURNEYS.md); RB → [OPERATIONS_RUNBOOK](../09-operations/OPERATIONS_RUNBOOK.md); UAT → [UAT_PLAN](../10-release/UAT_PLAN.md).

## 7. Jangan dibuka ulang

- Keputusan Owner yang tercatat (OD, GOV) dan baseline P0–P3 yang disetujui, kecuali ada konflik nyata — konflik dicatat dan dilaporkan.
- Pemilihan stack, modular monolith, model akses tiga grup peran, Policy Registry sebagai gerbang aturan resmi, dan model ledger append-only — kecuali ADR baru disetujui Owner.
- Aturan resmi pengadaan/akuntansi/organisasi: tidak pernah disimpulkan sendiri; GAP tetap OPEN sampai Owner/pemegang kewenangan menjawab.
