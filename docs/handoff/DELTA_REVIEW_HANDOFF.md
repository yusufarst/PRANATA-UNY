# Handoff pemeriksaan delta — koreksi SR-D4-P1-01 atas `78bf722`

Status: P0–P11 planning candidate — VERIFYING (AUTH-012) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Sesuai GOV-11, koreksi diverifikasi dengan **pemeriksaan delta sempit**: apakah SR-D4-P1-01 tertutup secara substansi dan apakah koreksinya menimbulkan masalah baru. Review delta `78bf722` telah menutup SR-D3-P1-01 (sisipan terlambat) di tingkat spesifikasi; bagian itu tidak perlu diperiksa ulang kecuali tersentuh koreksi ini. Handoff lengkap tetap [SECOND_REVIEW_HANDOFF](SECOND_REVIEW_HANDOFF.md).

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Repositori | `https://github.com/yusufarst/PRANATA-UNY`, cabang `docs/p0-p11-planning`, PR draft #1 (tidak untuk di-merge sebelum keputusan Owner) |
| Identitas yang direview sebelumnya | `78bf722178c1ba69663f05bbf98023a34d273f9b` — verdict **CORRECTIONS_REQUIRED**: SR-D4-P1-01 (P1); footer deskripsi PR sebagai tindakan Owner |
| Identitas koreksi | SHA HEAD cabang yang dilaporkan bersama permintaan ini — dua commit di atas `78bf722`: `1452ccd` (koreksi) dan commit tindak lanjut pemeriksaan internal (dokumen tidak dapat memuat hash commit yang memuatnya); diff `git diff 78bf722..<SHA HEAD>` |
| Status | Semua kandidat tetap VERIFYING / belum disetujui; 0 Task READY; GAP-001–GAP-029 OPEN; tidak ada kode aplikasi |

## 2. Disposisi dan peta koreksi

| Temuan | Hasil verifikasi pada `78bf722` | Inti koreksi | Tempat memeriksa | Uji regresi |
|---|---|---|---|---|
| SR-D4-P1-01 Tautan berkas penawaran dan identitas berkas masih dapat diubah | Terbukti. `file_links` dan `file_objects` adalah tabel biasa dengan hak `UPDATE` penuh bagi `pranata_app`; `verify_capture_manifest()` hanya berjalan pada INSERT, dan "tautan OFFER tidak dapat dilepas" hanya pernyataan tanpa mekanisme. Tautan OFFER yang sudah tersegel dapat dilepas atau dialihkan (`file_id`, `owner_id`, `owner_type`, `purpose`) lewat SQL langsung, dan `path`, `disk`, `sha256` atau `size_bytes` berkasnya dapat diubah sehingga `file_id` yang sama menyajikan byte lain — paling cepat terdeteksi pada perhitungan ulang harian. Sekelas: sidik jari hanya mencakup tautan bertujuan OFFER, sehingga tautan bertujuan lain ke kiriman lolos segel; bukti terima dan bukti penarikan memiliki identitas berkas yang sama-sama dapat diubah; `withdrawal_evidence_file_id` tetap dapat diubah sesudah penarikan; verifikasi sidik jari saat unduh hanya opsional dan kontrak penyimpanan tidak melarang penimpaan byte | **Identitas isi tetap untuk setiap berkas:** `sha256`/`size_bytes` dihitung server dari byte yang disimpan; penyimpanan tulis-sekali (path acak baru, pembuatan eksklusif, tanpa operasi timpa/pindah/hapus); hak kolom `file_objects` hanya `UPDATE (classification, scan_status)`; `guard_file_object_change()` menolak perubahan identitas, penurunan klasifikasi dan transisi pemindaian yang tidak sah. **Tautan:** hak kolom hanya `UPDATE (label, unlinked_by, unlinked_at)`; `guard_file_link_change()` menjaga berkas, pemilik dan tujuan setiap tautan tetap, mengizinkan pelepasan sekali untuk tautan biasa (diputuskan modul pemilik), dan menolak **setiap** UPDATE — termasuk pelepasan dan label — serta DELETE tautan yang jenis pemiliknya terdaftar di tabel baru `file_sealed_owner_types` (hanya migrasi; V1: `proc_submissions`, didaftarkan TASK-033). `owner_type` tautan dibatasi pada nama tabel sesuai morph map; sisipan tautan ke pemilik bersegel mengunci baris pemilik dan ditolak bila belum terlihat; `verify_capture_manifest()` dipasang sekali pada `file_links` dengan dispatch melalui `seal_function`. Sidik jari kiriman kini mencakup semua tautannya (termasuk status lepas) beserta `sha256` dan `size_bytes` berkas serta bukti terima; `content_sha256` didefinisikan dan dicocokkan saat commit; bukti terima dan penarikan dirujuk lewat kolom FK, bukan tautan, dan bukti penarikan tetap sesudah transisi penarikan. **Unduhan, pratinjau dan setiap pembacaan byte sisi server** (perakitan paket dokumen, konversi) selalu memverifikasi ukuran dan SHA-256 lebih dahulu dan gagal tertutup (`files.integrity_failed`, audit, peringatan operasi); job harian menghitung ulang byte berkas bersegel; latihan restore menjalankan job yang sama | [DATA_MODEL §1](../04-architecture/DATA_MODEL.md#1-konvensi) (segel tangkapan), [§2](../04-architecture/DATA_MODEL.md#2-peran-basis-data-dan-hak), [§12](../04-architecture/DATA_MODEL.md#12-files-documents-dan-audit) (`file_objects`, `file_links`, `file_sealed_owner_types`), [§14.3](../04-architecture/DATA_MODEL.md#143-pemilihan) (`proc_submissions`), §19 aturan 4 dan 6, §20; [COMMAND_AND_API_CONTRACTS §7](../06-api-performance/COMMAND_AND_API_CONTRACTS.md#7-unggah-dan-unduh); DOCUMENT_ENGINE (paket SPJ); [SECURITY_MODEL §6](../05-security/SECURITY_MODEL.md#6-berkas-unduhan-dan-ekspor) dan SEC-22; [CONCURRENCY_AND_INTEGRITY §3, §4, §7, §9](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#3-invarian-yang-ditegakkan-basis-data); ARCHITECTURE (baris Berkas); ADR-0009; DESIGN_SYSTEM (`EvidenceList`); OPERATIONS_RUNBOOK RB-07 dan INFRASTRUCTURE §7; [TASK-016](../11-tasks/TASK-016.md), [TASK-033](../11-tasks/TASK-033.md) | DI-23 (SQL langsung, peran berhak tabel penuh, koneksi kedua, `owner_type` varian, pemilik belum terlihat, `content_sha256`, unduhan dengan byte yang diubah), DI-33 baru (TASK-016; termasuk pembacaan sisi server dan job harian), SEC-22 |
| Footer deskripsi PR #1 | Masih ada | Tidak diubah dari sesi perencanaan karena gateway GitHub sesi ini menambahkannya kembali pada setiap penulisan deskripsi. Owner menghapus dua baris terakhir (`---` dan baris generated-by) di antarmuka web GitHub; deskripsi diperiksa ulang sebelum persetujuan akhir atau merge. Tidak ada commit yang ditulis ulang, tanpa force push, identitas Git tidak diubah | [P0_P11_PLANNING_QUALITY_GATE](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md#independent-delta-second-review-of-78bf722) | — |

## 3. Pilihan rancangan yang perlu dinilai

1. **Larangan langsung, bukan deteksi saat commit.** Perubahan baris yang sudah tertangkap ditolak seketika oleh hak kolom dan trigger BEFORE; segel sidik jari tetap menolak sisipan, dan job harian menjadi lapisan deteksi ketiga, bukan pengaman utama.
2. **Identitas isi tetap untuk semua berkas, bukan hanya berkas penawaran.** Tidak ada alur yang sah untuk mengganti isi `file_objects`; isi berbeda selalu menjadi berkas baru. Karena itu aturan ini tidak membekukan alur lain, dan bukti terima, bukti penarikan serta dokumen hasil generasi ikut terlindungi.
3. **Segel tautan per jenis pemilik.** Daftar `file_sealed_owner_types` yang hanya diisi migrasi menjadi satu sumber bagi trigger, job harian dan antarmuka. Tautan ke pemilik lain tetap dapat dilepas sekali menurut aturan modulnya; pengalihan selalu berupa tautan baru.
4. **Kolom operasional berbatas.** Hanya `classification` (naik) dan `scan_status` (transisi tertentu) yang dapat diperbarui; keduanya tidak masuk sidik jari, dan hasil pemindaian tidak pernah menghapus, mengganti atau melepas berkas.
5. **Sidik jari wajib di kedua ujung.** Saat diterima, SHA-256 dan ukuran dihitung server dan masuk segel kiriman; saat diunduh, verifikasi dilakukan atas salinan yang akan dikirim sebelum byte pertama, sehingga tidak ada celah antara verifikasi dan pengiriman. Pembacaan sisi server lain memakai layanan yang sama, sehingga byte rusak tidak dapat "dicuci" ke berkas baru bersidik jari sah.
6. **Bukti lewat kolom FK, bukan tautan.** Bukti terima dan bukti penarikan tetap dirujuk kolom kiriman; dengan begitu bukti penarikan yang diunggah kemudian tidak bertabrakan dengan segel tautan, dan semua tautan ke kiriman tetap terbatas pada transaksi pencatatan.
7. **Risiko sisa dinyatakan.** Peran istimewa (`migrator`) yang menulis ulang data beserta sidik jarinya sekaligus tidak terdeteksi job harian; risiko ini dibatasi pembatasan koneksi `migrator`, audit dan cadangan, bukan oleh mekanisme aplikasi.

## 4. Pertanyaan pemeriksaan

1. Apakah pelepasan, perubahan label, pengalihan (`file_id`, `owner_id`, `owner_type`, `purpose`) dan penghapusan tautan berkas kiriman, serta perubahan identitas isi berkas yang dirujuk kiriman, benar-benar ditolak lewat SQL langsung sebagai `pranata_app` — dan oleh trigger walaupun hak tabel diberikan — termasuk dari koneksi kedua selama pencatatan?
2. Apakah batas kolom operasional (`classification`, `scan_status`) cukup aman, dan apakah verifikasi unduhan yang gagal tertutup serta job harian menjawab pertanyaan sidik jari wajib saat penerimaan dan unduhan?
3. Apakah pelepasan sekali untuk tautan tidak bersegel, daftar jenis pemilik bersegel dan pembaruan TASK-016/TASK-033, DI-23/DI-33 dan SEC-22 konsisten tanpa regresi pada urutan pembuatan skema, kepemilikan DI atau kata-kata P0–P3 yang disetujui?

## 5. Yang tidak berubah

- Tidak ada berkas P0–P3 yang disentuh oleh koreksi ini; kata-kata P0–P3 yang disetujui tetap.
- Jumlah Task tetap 78; tidak ada Task baru, GAP baru atau GAP yang diselesaikan; keputusan Owner D-01–D-29 tidak berubah.

## 6. Bukti pemeriksaan mandiri

[P0_P11_PLANNING_QUALITY_GATE](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md#independent-delta-second-review-of-78bf722): render 78 Task, tautan/anchor, ID, cakupan (termasuk SEC-22) dan kepemilikan DI (33/33), urutan pembuatan skema, serta 82 FK komposit dengan unique pasangan — semuanya tanpa masalah. Pemeriksaan internal oleh agen terpisah atas `1452ccd` tidak menemukan P1; enam P2 dan tujuh P3 diperbaiki pada commit tindak lanjut. Hasil identitas dan atribusi commit koreksi dilaporkan bersama permintaan ini.

## 7. Format hasil yang diminta

```text
VERDICT: READY_FOR_OWNER_APPROVAL | CORRECTIONS_REQUIRED
IDENTITAS YANG DIREVIEW: <SHA HEAD>
DISPOSISI: SR-D4-P1-01 = CLOSED | OPEN (alasan)
TEMUAN BARU (bila ada):
| ID | Tingkat | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <hal yang memerlukan keputusan Owner>
```

Laporan dan pertanyaan kepada Owner ditulis dalam Bahasa Indonesia. Verdict bukan persetujuan Owner.
