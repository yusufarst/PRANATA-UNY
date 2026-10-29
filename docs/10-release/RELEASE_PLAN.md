# Rencana rilis — PRANATA UNY

Status: P10 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P10

Dokumen ini memiliki **urutan increment, gerbang rilis, deklarasi rilis, versi, rollback dan hubungan dengan penerimaan V1**. UAT dimiliki [UAT_PLAN](UAT_PLAN.md); migrasi data dan cutover dimiliki [MIGRATION_AND_CUTOVER](MIGRATION_AND_CUTOVER.md); prosedur operasi dimiliki [OPERATIONS_RUNBOOK](../09-operations/OPERATIONS_RUNBOOK.md).

## 1. Increment

| Increment | Isi (milestone Task) | Lingkungan | Penerimaan |
|---|---|---|---|
| R0 Fondasi | M0–M2: kerangka, CI, identitas, akses, organisasi, referensi, kebijakan, berkas, audit, dokumen, penomoran, shell UI | Staging | Uji teknis + review |
| R1 Pengadaan inti | M3: usulan, RUP, paket, HPS, penetapan metode, kegiatan, partisipasi tercatat, evaluasi, hasil, SPPBJ, kontrak, dokumen | Staging | **UAT-1 Pengadaan** (OD-32) |
| R2 Gudang | M5: gudang ALP/PSRT, katalog, ledger, permintaan, serah terima, konfirmasi, pengembalian, opname, transfer | Staging | UAT-2 Gudang |
| R3 Aset | M6–M7: register, handoff, mutasi, inventarisasi, perubahan, penghapusan, penyusutan, KDP, intake | Staging | UAT-3 Aset |
| R4 Lengkap V1 | M4 + M8: pelaksanaan kontrak, adendum, BAST, SPJ, pembayaran, portal penyedia/DPT, rekonsiliasi, laporan, hardening | Staging → Produksi | UAT-4 + penerimaan V1 |

Menurut V1_SCOPE yang disetujui, V1 diterima hanya bila seluruh hasil minimum CAP terbukti. Karena itu default rencana adalah **satu go-live produksi setelah R4**. Rilis produksi bertahap (misalnya R1 Pengadaan lebih dahulu) adalah perubahan batas rilis P1 yang memerlukan **keputusan Owner** ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)); bila disetujui, increment produksi tetap memenuhi gerbang bagian 2 dan V1 tetap baru dinyatakan diterima setelah seluruh CAP terbukti.

Fitur yang bergantung pada kebijakan hanya aktif resmi di produksi bila versi kebijakannya ACTIVE (GAP terselesaikan). Go-live tidak menunggu semua GAP; fitur yang kebijakannya belum ACTIVE tampil dengan penjelasan "menunggu pengesahan kebijakan" dan tidak menghasilkan record resmi.

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

Versi kalender `vYYYY.MM.N`. Alur cabang: cabang kerja Task (`feature/…`, `fix/…`, `chore/…`, `docs/…` — tanpa nama alat AI, GOV-09) → PR ke `develop` (CI wajib) → staging dari `develop` → PR rilis `develop` → `main` dengan persetujuan → tag → produksi. `main` selalu mencerminkan rilis produksi atau baseline dokumentasi yang disetujui.

## 5. Pasca-rilis

Pemantauan intensif 48 jam, laporan insiden bila ada, catatan rilis bahasa Indonesia untuk pengguna (perubahan yang terlihat), pembaruan CURRENT_HANDOFF dan TASK_PLAN.
