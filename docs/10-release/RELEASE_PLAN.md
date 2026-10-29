# Rencana rilis — PRANATA UNY

Status: P10 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P10

Dokumen ini memiliki **urutan increment, gerbang rilis, deklarasi rilis, versi, rollback dan hubungan dengan penerimaan V1**. UAT dimiliki [UAT_PLAN](UAT_PLAN.md); migrasi data dan cutover dimiliki [MIGRATION_AND_CUTOVER](MIGRATION_AND_CUTOVER.md); prosedur operasi dimiliki [OPERATIONS_RUNBOOK](../09-operations/OPERATIONS_RUNBOOK.md).

## 1. Increment

| Increment | Isi (milestone Task) | Lingkungan | Penerimaan |
|---|---|---|---|
| R0 Fondasi | M0–M2: kerangka, CI, identitas, akses, organisasi, referensi, kebijakan, berkas, audit, dokumen, penomoran, shell UI | Staging | Uji teknis + review |
| R1 Pengadaan inti | M3: usulan, RUP, paket, HPS, penetapan metode, kegiatan, partisipasi tercatat, evaluasi, hasil, SPPBJ, kontrak, dokumen | Staging | **UAT-1 Pengadaan** (OD-32) |
| R2 Gudang | M5: gudang ALP/PSRT, katalog, ledger, permintaan, serah terima, konfirmasi, pengembalian, opname, transfer | Staging | UAT-2 Gudang |
| R3 Aset | M6–M7 + prasyarat dari M4 (TASK-044 pelaksanaan kontrak, TASK-046 pemeriksaan/BAST): register, handoff, mutasi, inventarisasi, perubahan, penghapusan, penyusutan, KDP, intake | Staging | UAT-3 Aset |
| R4 Lengkap V1 | Sisa M4 + M8: adendum, SPJ, pembayaran, portal penyedia/DPT, dokumen pengadaan tahap 2, rekonsiliasi, laporan, hardening (pelaksanaan kontrak dan BAST diuji ulang dalam regresi) | Staging → Produksi | UAT-4 + penerimaan V1 |

Isi increment ditentukan oleh Task beserta dependensinya di [TASK_PLAN](../11-tasks/TASK_PLAN.md), bukan oleh nomor milestone semata; Task yang menjadi prasyarat increment lain diselesaikan lebih dahulu menurut urutan eksekusi [EXECUTOR_OPERATING_MODEL](../11-tasks/EXECUTOR_OPERATING_MODEL.md).

Menurut V1_SCOPE yang disetujui, V1 diterima hanya bila seluruh hasil minimum CAP terbukti. Karena itu default rencana adalah **satu go-live produksi setelah R4**. Rilis produksi bertahap (misalnya R1 Pengadaan lebih dahulu) adalah perubahan batas rilis P1 yang memerlukan **keputusan Owner** ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)); bila disetujui, increment produksi tetap memenuhi gerbang bagian 2 dan V1 tetap baru dinyatakan diterima setelah seluruh CAP terbukti.

Fitur yang bergantung pada kebijakan hanya aktif resmi di produksi bila versi kebijakannya ACTIVE (GAP terselesaikan); bila belum, fitur tampil dengan penjelasan "menunggu pengesahan kebijakan" dan tidak menghasilkan record resmi. **GAP mana yang wajib selesai sebelum go-live dan sebelum penerimaan V1 adalah keputusan Owner (D-28)**; usulan perencanaan: semua GAP yang menggerbangi aksi resmi kapabilitas V1 MUST yang akan dipakai sejak go-live, ditambah gerbang rilis/cutover dan GAP-024 untuk AC-16 ([TRACEABILITY_MATRIX — Perlakuan GAP](../11-tasks/TRACEABILITY_MATRIX.md#perlakuan-gap)). Go-live dengan GAP lain yang masih terbuka hanya terjadi bila Owner menerimanya secara eksplisit beserta daftar keterbatasan, dan V1 tidak dinyatakan diterima selama CAP terkait belum terbukti.

## 2. Gerbang rilis produksi

- [ ] Semua Task dalam cakupan rilis DONE dengan bukti; tidak ada Task HIGH dengan temuan P0/P1 terbuka.
- [ ] `ci-fast`, `ci-e2e` dan `ci-nightly` hijau pada SHA rilis.
- [ ] Staging memakai SHA yang sama; E2E kritis J-01–J-18 yang relevan lulus di Chromium dan WebKit; verifikasi dua bahasa.
- [ ] Regresi penuh (AICWDF §28 lapisan 3) lulus; uji performa PB-* lulus pada data sintetis skala.
- [ ] UAT yang dipersyaratkan disetujui penandatangan.
- [ ] Latihan restore terakhir sukses dan tercatat; cadangan produksi siap.
- [ ] Rencana rollback dan target rollback (tag sebelumnya / titik PITR) tertulis.
- [ ] Health check, smoke test dan pemantauan siap.
- [ ] Deklarasi rilis lengkap (bagian 3) dan persetujuan rilis Owner tercatat.

## 3. Deklarasi rilis

```text
RELEASE: vYYYY.MM.N (tag Git)
SCOPE: increment + daftar TASK
DB CHANGE: NONE / APPROVED CONTROLLED MIGRATION (daftar migrasi, reversibilitas, cadangan)
ADDITIONAL RECURRING COST CHANGE: NONE / APPROVED EXCEPTION (rujukan)
POLICY STATE: daftar paket kebijakan ACTIVE / DRAFT yang memengaruhi fitur rilis
ROLLBACK TARGET: tag sebelumnya / titik PITR
UAT: rujukan berita acara
APPROVAL: rujukan persetujuan Owner
```

## 4. Versi dan cabang

Versi kalender `vYYYY.MM.N`; kandidat rilis untuk UAT `vYYYY.MM.N-rc.K`. Alur cabang (berbasis trunk):

1. Cabang kerja per Task dari `main` terbaru: `feature/task-0xx-…`, `fix/…`, `chore/…`, `docs/…` — tanpa nama alat AI (GOV-09).
2. PR ke `main` dengan status check wajib `ci-gate` dan review sesuai kelas risiko ([QUALITY_GATES](../08-testing/QUALITY_GATES.md)); merge hanya bila semua gerbang lulus, dengan metode yang diputuskan Owner (D-08), lalu `main` remote diverifikasi memuat merge tersebut (GOV-12). Sebelum `ci-gate` ada (TASK-001–TASK-004) berlaku mode bootstrap yang disaksikan Owner.
3. Staging memakai SHA `main` tertentu; UAT dijalankan pada tag kandidat `-rc.K` di staging.
4. Produksi **hanya** dari tag rilis yang lulus gerbang bagian 2 dan disetujui Owner; tidak pernah dari HEAD cabang.
5. Hotfix: bila `main` belum memuat perubahan yang belum dirilis, cabang `fix/…` dari `main` → PR → tag patch. Bila sudah memuat, cabang `release/vYYYY.MM` dari tag produksi → PR hotfix ke cabang itu → tag patch → perubahan yang sama di-PR ke `main`.

`main` adalah cabang integrasi terlindungi (status check `ci-gate` wajib, tanpa force push/penghapusan; aturan PR wajib hanya pada opsi merge B — [QUALITY_GATES §4](../08-testing/QUALITY_GATES.md#4-pipeline-ci-github-actions)) yang memuat baseline perencanaan yang disetujui dan kode yang telah lulus gerbang Task; `main` **bukan** cermin produksi — produksi selalu menjalankan tag rilis yang tercatat. Model ini mengadaptasi default AICWDF §32 (`develop` → `main`) sesuai siklus GOV-12 dari U-007 §9/§19 yang menuntut merge dan verifikasi pada `main` remote; gerbang staging/UAT sebelum produksi dipertahankan melalui tag kandidat dan tag rilis, sehingga cabang `develop` tidak diperlukan. Adaptasi ini adalah bagian kandidat P10 dan memerlukan persetujuan Owner ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)).

## 5. Pasca-rilis

Pemantauan intensif 48 jam, laporan insiden bila ada, catatan rilis bahasa Indonesia untuk pengguna (perubahan yang terlihat), pembaruan CURRENT_HANDOFF dan TASK_PLAN.
