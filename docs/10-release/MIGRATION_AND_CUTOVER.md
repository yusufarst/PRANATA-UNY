# Migrasi data historis dan cutover — PRANATA UNY

Status: P10 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P10

Dokumen ini memiliki **strategi intake historis, validasi, rekonsiliasi, cutover, go/no-go dan rollback**. Mekanisme intake di aplikasi adalah WF-026 (staging → validasi → keputusan → penerimaan). Kepercayaan sumber, identitas historis, pemetaan kode dan kepemilikan tulis setelah cutover tetap GAP-011/012/005/006/022 dan menjadi gerbang. Tidak ada impor ke produksi oleh agen; semua pemuatan dijalankan operator melalui fitur intake.

## 1. Sumber kandidat

| Sumber | Isi | GAP |
|---|---|---|
| Ekspor SIMASET / register aset | Aset, klasifikasi, unit, nilai, kondisi | GAP-011/012/022/008 |
| Data persediaan per gudang | Barang, saldo awal, movement historis dengan kode mentah P01/P02 | GAP-005/006/025 |
| Rekap pengadaan (XLSX) | Paket/kontrak historis untuk referensi | GAP-011/019 |
| Data penyedia | Perusahaan, dokumen, status DPT | GAP-026 |
| Struktur organisasi | Unit dan hierarki | GAP-021 |

Berkas sumber riil tidak masuk repositori; hanya profil pemetaan (nama kolom, tipe, aturan) tanpa nilai.

## 2. Alur intake

1. **Profil sumber**: kustodian memberikan versi/tanggal sumber; operator membuat profil pemetaan (kolom → field target, normalisasi kode, satuan) yang direview.
2. **Unggah dan parse** ke `intake_rows` (nilai mentah disimpan apa adanya).
3. **Validasi struktur**: wajib, tipe, format tanggal/angka, kode dikenal, referensi (unit, klasifikasi).
4. **Validasi makna**: duplikasi kandidat (aturan identitas GAP-011), konflik dengan data yang ada, kode ambigu (P01/P02), nilai di luar kebiasaan.
5. **Isu dan keputusan**: setiap isu diputuskan (perbaiki di sumber, terima dengan catatan, tolak, lewati) oleh pemutus berwenang; keputusan tercatat.
6. **Penerimaan per bagian** melalui kontrak modul tujuan; record hasil membawa provenance batch/baris.
7. **Rekonsiliasi**: total jumlah dan nilai per unit/klasifikasi/gudang dibandingkan dengan laporan sumber; selisih dibuka sebagai kasus WF-025.
8. **Sign-off** oleh kustodian dan pemilik data yang ditunjuk.

Kredensial atau pola password dalam sumber tidak pernah dibawa ke akun (INV-016).

## 3. Uji migrasi

- Latihan penuh di staging dengan salinan sumber teranonimkan yang disetujui atau data sintetis berbentuk sama.
- Metrik: baris diterima/ditolak/dilewati, isu per jenis, waktu proses (PB-08), hasil rekonsiliasi.
- Minimal dua latihan berhasil berturut-turut sebelum cutover produksi.

## 4. Cutover

| Langkah | Isi |
|---|---|
| T-14 hari | Latihan cutover kedua lulus; jadwal dan komunikasi disetujui Owner |
| T-3 hari | Pembekuan perubahan struktur sumber; daftar pengguna produksi siap |
| T-0 | Ekspor final dari sumber (titik potong), intake ke produksi oleh operator, rekonsiliasi, sign-off |
| T+0 | Go/no-go; bila go: sistem lama menjadi baca-saja untuk data yang dipindahkan (keputusan GAP-012), pengguna mulai di PRANATA |
| T+30 hari | Periode stabilisasi; rekonsiliasi bulanan pertama |

Kepemilikan tulis setelah cutover (sistem mana yang menjadi sumber kebenaran per jenis data, terutama SIMASET dan aplikasi persediaan institusi) adalah keputusan GAP-012; sampai diputuskan, PRANATA dapat beroperasi paralel dengan rekonsiliasi berkala tanpa mengklaim menggantikan sistem resmi.

## 5. Go/no-go

Go bila: rekonsiliasi dalam toleransi yang disetujui kustodian; tidak ada isu kritis terbuka; cadangan pra-cutover terverifikasi; dukungan pengguna siap; Owner menyetujui. No-go bila salah satu tidak terpenuhi.

## 6. Rollback cutover

Pulihkan basis data ke titik pra-cutover (PITR), sistem lama tetap menjadi sumber kebenaran, catat penyebab, jadwalkan ulang setelah perbaikan. Karena sistem lama tidak dimatikan sebelum stabilisasi, rollback tidak kehilangan operasi institusi.
