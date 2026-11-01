# Handoff review terfokus — PCR-001

Status: Perubahan pasca-freeze PCR-001 (FRZ-001) — menunggu Second Review terfokus | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Baseline P0–P11 telah disetujui Owner pada `851013f` (APPR-005, APPR-006) dan dibekukan sebagai FRZ-001. Owner kemudian menyerahkan bukti fungsional baru: halaman legacy `index (1).html` dan laporan BMU triwulan III 2026. Owner juga meminta verifikasi regulasi nasional dan menambah satu persyaratan usulan pengadaan. Review ini **sempit**: apakah PCR-001 benar, minimal dan konsisten. Bagian baseline yang tidak tersentuh tidak perlu diperiksa ulang.

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Repositori | `https://github.com/yusufarst/PRANATA-UNY` |
| Baseline | Cabang `docs/planning-baseline` (catatan persetujuan, freeze dan otorisasi di atas `851013f`) |
| Perubahan | Cabang `docs/pcr-001-legacy-reporting-and-intake`; diff `git diff docs/planning-baseline..docs/pcr-001-legacy-reporting-and-intake` atau halaman perbandingan GitHub `compare/docs/planning-baseline...docs/pcr-001-legacy-reporting-and-intake`. Tidak ada PR yang dibuka, agar deskripsi PR tidak mendapat footer atribusi otomatis. |
| Status | Selama PCR-001 belum masuk `main`, TASK-025, TASK-066, TASK-069 dan TASK-071 BLOCKED. Task lain berjalan menurut AUTH-013. GAP-001–GAP-029 tetap OPEN. |

## 2. Isi perubahan

| Bagian | Masalah pada baseline | Perubahan | Tempat memeriksa |
|---|---|---|---|
| A. Laporan BMU, penyusutan dan Neraca BMU | TASK-071 hanya menyediakan entri BMU "menunggu definisi resmi" tanpa angka. Padahal Owner menyatakan rumusnya sudah ada di HTML legacy dan meminta agar tidak ditanyakan lagi. | Bukti rumus legacy dicatat lengkap tanpa data pribadi: dialek CSV, periode Y/S1/S2/Q1/Q3, cutoff, penyusutan, BMU, pengelompokan, Neraca BMU, riwayat lima tahun dan ekspor. Rumus itu menjadi nilai bawaan DRAFT, dengan koreksi tercatat atas cacat legacy: mutasi hanya tambah, kunci agregasi kode barang saja, unit saat ini, tanggal kosong, catch-up S2/Q3 tidak konsisten, dan heuristik kondisi/hapus. TASK-071 kini membangun keluarga laporan itu dengan label simulasi dan bergantung pada TASK-066. | [LEGACY_REPORTING_EVIDENCE](../00-governance/evidence/LEGACY_REPORTING_EVIDENCE.md); [POLICY_REGISTRY §2, §5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi); [TASK-071](../11-tasks/TASK-071.md), TASK_PLAN |
| B. Evaluator penyusutan | Contoh kebijakan hanya memuat `MONTH_AFTER_RECOGNITION` dengan pembulatan per baris. | Jangkar tanggal perolehan, mulai bulan sesudahnya, masa manfaat dari record lalu klasifikasi, residu nol, pembulatan kumulatif, catch-up pada periode pembukuan untuk semua jenis periode, dan cakupan flag. Semua dapat dikonfigurasi; GAP-007 tetap terbuka. | [TASK-066](../11-tasks/TASK-066.md) TAC-08/09; POLICY_REGISTRY §5 |
| C. Adapter intake aset | Bentuk ekspor legacy belum dipetakan. | Profil adapter titik koma dengan deteksi header. Tanggal kosong dan `flag_sak` tak dikenal menjadi galat validasi. Masa manfaat dan nilai per record dipertahankan. | [TASK-069](../11-tasks/TASK-069.md) TAC-04/05 |
| D. Dua kanal usulan | Persyaratan Owner yang baru: usulan dapat diajukan langsung oleh unit maupun melalui kanal pengadaan pusat. | `proc_needs.submission_channel`, `received_ref`, `received_evidence_file_id`. Penerima intake pusat (`procurement.need.review`, TR-021) mencatat atas nama unit asal yang tetap. Tidak ada kapabilitas atau Task baru. | [DATA_MODEL §14.1](../04-architecture/DATA_MODEL.md#141-anggaran-dan-kebutuhan) (`proc_needs`); [TASK-025](../11-tasks/TASK-025.md) TAC-05 |
| E. Regulasi nasional | Peraturan LKPP pelaksana belum ditinjau. | Peraturan LKPP 3/2026 (ditetapkan 21-09-2026) mencabut Peraturan LKPP 12/2021 jo. 4/2024. Isinya hanya berdampak pada rezim APBN/APBD, tanpa nilai yang diaktifkan. Tidak ditemukan perubahan Perpres sesudah 46/2025, dan tidak ditemukan perubahan publik atas PR 34/2023. GAP-026 tetap OPEN. | [REGULATORY_SOURCE_REVIEW RG-08](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-08-national-sources-checked-on-2026-11-01) |

## 3. Batas dan pilihan yang perlu dinilai

1. HTML legacy adalah rujukan perilaku, bukan kewenangan akuntansi. Rumusnya hanya dipakai sebagai nilai bawaan DRAFT. Aktivasi resmi tetap memerlukan GAP-007/009/024.
2. Setiap koreksi atas perilaku legacy beralasan integritas data: periode historis tetap benar sesudah penghapusan, kode campuran tidak salah atribusi, tidak ada nilai bawaan diam-diam, dan kondisi fisik tidak diturunkan dari umur penyusutan. Tidak ada aturan akuntansi baru yang dikarang.
3. PDF BMU 84 halaman tidak tersedia bagi sesi perencanaan. Paritas terhadap keluaran nyata dilakukan lokal oleh eksekutor dengan berkas milik Owner. Yang dicatat hanya lulus/gagal dan kategori selisih, tanpa angka atau data.
4. Nama dan nomor identitas penanda tangan di HTML legacy tidak disalin. Penanda tangan berasal dari peran dan penugasan.

## 4. Pertanyaan pemeriksaan

1. Apakah rumus pada LEGACY_REPORTING_EVIDENCE §2–§8 setia pada HTML legacy, dan apakah setiap koreksi §9 perlu serta tidak mengarang aturan?
2. Apakah TASK-066, TASK-069 dan TASK-071 cukup diuji untuk paritas, pembulatan, catch-up, penghapusan dan kunci agregasi, dan apakah dependensi baru TASK-071 → TASK-066 benar?
3. Apakah dua kanal usulan konsisten dengan WF-003/TR-021 dan model akses tanpa memperluas kewenangan?
4. Apakah RG-08 mencatat sumber nasional dengan benar tanpa menutup GAP-026?

## 5. Yang tidak berubah

Kata-kata P0–P3 yang disetujui tetap. Jumlah Task tetap 78, tanpa Task baru, GAP baru atau GAP yang diselesaikan. Keputusan Owner D-01–D-29 tidak berubah.

## 6. Format hasil

```text
VERDICT: APPROVED | APPROVED WITH CONDITIONS | REVISE | BLOCKED
IDENTITAS YANG DIREVIEW: <SHA HEAD cabang PCR-001>
TEMUAN (bila ada):
| ID | Tingkat | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <bila ada>
```

Laporan kepada Owner ditulis dalam Bahasa Indonesia. Dengan verdict yang dapat diterima, PCR-001 diterbitkan ke `main` melalui fast-forward terverifikasi (oleh Owner bila sesi perencanaan tidak diizinkan mendorong `main`), dan verdict tersebut dicatat di APPROVAL_RECORDS.
