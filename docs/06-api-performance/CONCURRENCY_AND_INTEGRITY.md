# Konkurensi, transaksi dan integritas data — PRANATA UNY

Status: P6 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P6 | ADR: [ADR-0004](../adr/ADR-0004-append-only-ledgers.md)

Dokumen ini memiliki **batas transaksi, urutan kunci, invarian yang ditegakkan basis data, perlakuan race condition, retry, konkurensi job/impor dan isolasi basis data uji**. Kontrak permintaan/idempotensi ada di [COMMAND_AND_API_CONTRACTS](COMMAND_AND_API_CONTRACTS.md).

## 1. Batas transaksi

| Aturan | Isi |
|---|---|
| Satu Action = satu transaksi | Semua tulisan sebuah perintah (record, transisi, audit, item pekerjaan, nomor) commit atau rollback bersama. |
| Efek samping setelah commit | Event, notifikasi dan job dikirim `afterCommit`; tidak ada pemanggilan jaringan (Gotenberg, SMTP) di dalam transaksi. |
| Isolasi | `READ COMMITTED` default PostgreSQL + penguncian eksplisit untuk invarian kritis; `SERIALIZABLE` tidak dipakai secara umum. |
| Durasi | Transaksi dijaga singkat; operasi massal dipotong per bagian (chunk) dengan transaksi per bagian dan status kemajuan yang dapat dilanjutkan. |
| Timeout | `statement_timeout` 30 detik untuk peran aplikasi web, 15 menit untuk koneksi worker job berat; `lock_timeout` 5 detik. |

## 2. Urutan kunci (lock order)

Untuk mencegah deadlock, Action mengambil kunci baris dalam urutan global berikut dan, di dalam satu tingkat, menurut kunci terurut naik:

1. Kontainer bisnis: `proc_packages` → `proc_contracts` → `inv_requests`/`inv_returns`/`inv_transfers`/`inv_counts` → `asset_assets` (menurut `id` naik).
2. Penomoran: `numbering_counters` (menurut `scheme_id`, `scope_key`).
3. Saldo stok: `inv_stock_balances` menurut `(warehouse_id, item_id)` naik.
4. Proyeksi lain (`asset_assets` kolom proyeksi bila belum dikunci di tingkat 1).

Contoh serah terima (TR-295): kunci `inv_requests` baris permintaan → kunci saldo untuk semua `(warehouse_id, item_id)` baris serah terima secara terurut → validasi `quantity >= jumlah` → insert movement ISSUE (dengan `balance_after`) → update saldo → update `inv_request_lines.handed_over_qty` → transisi + audit + item pekerjaan.

## 3. Invarian yang ditegakkan basis data

| Invarian | Penegakan |
|---|---|
| Stok fisik tidak negatif (INV-020) | `CHECK (quantity >= 0)` pada `inv_stock_balances` + validasi Action sebelum tulis |
| Pencatatan terlambat tidak membuat saldo per tanggal negatif | Di bawah kunci saldo yang sama, Action memeriksa saldo menurut `occurred_at` pada titik gerakan dan setiap titik sesudahnya sebelum menulis; OPENING hanya sebelum gerakan lain |
| Ledger dan riwayat tidak dapat diubah (INV-017) | Hak peran tanpa UPDATE/DELETE + trigger penolak |
| Nomor dokumen unik (OD-29) | `UNIQUE(document_type, number_text)` (lintas skema) dan `UNIQUE(scheme_id, scope_key, sequence_value)`; satu skema ACTIVE per jenis dokumen |
| Satu penetapan metode berlaku per paket | Unique parsial `WHERE status = 'DETERMINED'` |
| Satu HPS ditetapkan per paket | Unique parsial `WHERE status = 'SET'` |
| Satu hasil ditetapkan per paket | Unique parsial `WHERE status IN ('DETERMINED','ANNOUNCED')` |
| Satu versi berlaku per kunci dan tanggal | `EXCLUDE USING gist` atas jendela versi ACTIVE/SUPERSEDED untuk kebijakan, template dokumen dan klasifikasi aset; aktivasi menutup jendela versi sebelumnya secara atomik di bawah kunci baris induk; tanpa tanggal mundur (`effective_from` ≥ tanggal aktivasi) |
| Konsistensi induk lintas record | FK komposit `(rujukan, lingkup)`: peringkat/evaluasi/negosiasi ↔ partisipasi paket yang sama, hasil ↔ paket, SPPBJ ↔ hasil paket yang sama, kontrak ↔ SPPBJ paket yang sama, baris BAST ↔ baris kontrak yang sama, serah terima gudang ↔ permintaan dan gudangnya, baris serah terima ↔ baris permintaan dan barangnya; kolom lingkup tidak dapat diubah (`forbid_scope_change()`); perusahaan kontrak = pemenang dan penugasan kontribusi SPPBJ = paket yang sama diperiksa constraint trigger |
| Satu peristiwa fisik satu posting | Unique identitas sumber `(source_type, source_id, source_line_id, movement_type)` pada `inv_stock_movements` dan pola yang sama pada `asset_transactions`; berlaku walaupun kunci idempotensi berbeda; kecocokan gerakan dengan baris sumbernya diperiksa constraint trigger per jenis sumber |
| Pembalikan tunggal dan cocok | `uq(reversal_of_id)` + trigger `check_stock_reversal()`/`check_asset_reversal()` (entitas, jumlah, arah; bukan pembalikan atas pembalikan; stok: membawa sumber koreksi dan alasan; aset: hanya transaksi terakhir aset yang belum dibalik) |
| Baris final beku | `forbid_change_when_parent_final()` pada baris HPS setelah SET, baris penerimaan setelah POSTED, baris opname setelah CLOSED, baris transfer setelah DISPATCHED, baris serah terima gudang sejak diposting dan baris BAST setelah SIGNED (kecuali klasifikasi/rujukan hilir); `proc_contract_lines` append-only per versi kontrak dengan FK `(contract_id, version_no)` dan `uq(contract_id, version_no, line_no)` |
| Satu run penyusutan resmi per periode | Unique parsial untuk status QUEUED/RUNNING/FAILED/COMPLETED; unique baris resmi `(asset_id, period)`; penjaga proyeksi `last_depreciation_period < period` (bagian 6.1) |
| Maker-checker (tidak lolos bila kosong) | Kebijakan: `CHECK (status <> 'ACTIVE' OR activated_by IS NOT NULL)` + `CHECK (activated_by IS NULL OR (activated_by <> created_by AND activated_by IS DISTINCT FROM submitted_by))`; template: pola yang sama; grant istimewa: `CHECK (status <> 'ACTIVE' OR NOT requires_approval OR approved_by IS NOT NULL)` + `CHECK (approved_by IS NULL OR approved_by <> granted_by)` ([DATA_MODEL](../04-architecture/DATA_MODEL.md)) |
| Kuantitas serah terima ≤ disetujui | `CHECK` pada `inv_request_lines` |
| Partisipasi unik per penyedia per paket | `UNIQUE(package_id, company_id)` |
| Transfer bukan ke gudang sama | `CHECK (to_warehouse_id <> from_warehouse_id)` |

Invarian yang memerlukan konteks (pemisahan tugas, kebijakan ACTIVE, guard TR) ditegakkan Action dan diuji; bila memungkinkan didukung constraint tambahan.

## 4. Race condition yang ditangani

| Skenario | Penanganan |
|---|---|
| Dua petugas menyerahkan barang terakhir bersamaan | Kunci baris saldo; yang kedua melihat saldo kurang → 422 "stok tidak cukup" |
| Dua pengguna menyetujui/menolak permintaan yang sama | `lock_version` + guard state; yang kedua mendapat 409/422 dengan state terbaru |
| Klik ganda pada finalisasi dokumen | Idempotency key + guard state |
| Dua alokasi nomor bersamaan | `FOR UPDATE` pada counter; unique constraint sebagai jaring pengaman |
| Override nomor manual bertabrakan | Unique constraint → 422 dengan saran nomor berikut |
| Kebijakan diaktifkan saat pengguna menetapkan metode | Saran menyimpan `policy_version_id`; Action memeriksa versi masih berlaku → bila berubah, minta tinjau ulang (IX-023) |
| Grant dicabut saat pengguna sedang bekerja | Otorisasi dievaluasi setiap permintaan; tidak ada cache izin melewati satu permintaan |
| Konfirmasi penerima ganda | Idempotensi + guard state `PENDING` |
| Job dokumen dijalankan ulang setelah timeout | Kunci idempotensi job + status revisi |
| Run penyusutan periode yang sama dua kali | Unique parsial per periode non-simulasi untuk status QUEUED/RUNNING/FAILED/COMPLETED (run kedua ditolak saat dibuat, termasuk ketika run sebelumnya gagal) + `ShouldBeUnique` dengan kunci run; protokol bagian 6.1 |
| Dua permintaan melanjutkan run FAILED yang sama | Kunci baris run + transisi bersyarat `FAILED → RUNNING` (compare-and-set dengan `lock_version`); permintaan kedua melihat RUNNING → 409; job unik per run |
| Worker run penyusutan dari percobaan lama masih berjalan setelah run dilanjutkan | Setiap transaksi chunk mengunci baris run dan memeriksa `status = RUNNING` serta `attempt_no` milik worker; worker usang berhenti tanpa menulis |
| Dua posting untuk baris sumber yang sama (serah terima, penerimaan, pengembalian, transfer, opname) | Unique identitas sumber; posting kedua → 409 "sudah diposting" tanpa efek parsial, apa pun kunci idempotensinya |
| Dua pembalikan atas gerakan/transaksi yang sama | `uq(reversal_of_id)`; yang kedua → 409 |
| Dua aktivasi versi template atau kebijakan bersamaan | Kunci baris induk (template/paket kebijakan) + `EXCLUDE`; yang kedua → 409 dengan versi yang kini berlaku |

## 5. Konkurensi job dan impor

- Job yang memodifikasi agregat yang sama memakai `WithoutOverlapping`/`ShouldBeUnique` dengan kunci bisnis.
- Satu batch intake aktif per target (`intake_batches` status non-final) — ditegakkan dengan unique parsial dan pemeriksaan Action.
- Penerimaan intake berjalan per bagian 500–1.000 baris dalam transaksi masing-masing, menulis kemajuan; kegagalan bagian tidak membatalkan bagian yang sudah diterima dan dapat dilanjutkan (EP-010, WF-026).
- Ekspor membaca dengan cursor/stream dan tidak mengunci tabel bisnis.
- Worker memakai `--max-time` dan `--memory` untuk restart berkala; retry dengan backoff (3 kali untuk job jaringan, 1 kali untuk job domain) lalu masuk `failed_jobs` dengan peringatan.

## 6. Retry dan kegagalan

| Galat | Perlakuan |
|---|---|
| Deadlock/serialization failure (`40P01`, `40001`) | Action diulang otomatis maksimal 2 kali dengan jeda acak singkat; masih gagal → 503 dengan pesan aman, tanpa efek parsial |
| Lock timeout | 409 "sedang diproses pengguna lain, coba lagi" |
| Gangguan Gotenberg | Retry job dengan backoff; status konversi gagal dapat diulang manual |
| Disk penuh | Gagal tertutup; peringatan operasi |
| Hasil tidak diketahui di klien (jaringan putus) | Klien mengirim ulang dengan idempotency key yang sama → hasil asli |

### 6.1 Protokol run penyusutan resmi

Mekanisme ini mengisi ruang yang diserahkan WF-022 TR-214 kepada P6: pengulangan tidak otomatis dan mekanismenya ditentukan P6. Nilai penyusutan sendiri tetap berasal dari kebijakan `asset.depreciation` (BR-017, GAP-007).

| Fase/status | Perilaku |
|---|---|
| Pembuatan | Run resmi dibuat QUEUED hanya bila tidak ada run resmi lain untuk periode itu dengan status QUEUED/RUNNING/FAILED/COMPLETED (unique parsial), tidak ada run resmi berstatus QUEUED/RUNNING/FAILED untuk periode sebelumnya, dan tidak ada run resmi selain CANCELLED untuk periode sesudahnya — sehingga run diproses berurutan menurut periode dan penjaga proyeksi tetap sahih; apakah suatu periode boleh dilewati mengikuti kebijakan (GAP-007). Versi kebijakan, kriteria cakupan, `scope_max_asset_id` dan `period` dibekukan pada run. |
| RUNNING, fase VALIDATION | Sebelum baris pertama ditulis, kueri agregat memastikan setiap aset dalam cakupan dapat dihitung dengan versi kebijakan yang dibekukan (klasifikasi terpetakan, parameter lengkap). Bila tidak, run FAILED tanpa baris beserta daftar aset dan alasannya; setelah penyebab diperbaiki, run dilanjutkan atau dibatalkan. |
| RUNNING, fase LINES | Aset dalam cakupan diproses per chunk menurut `id` naik. Setiap chunk satu transaksi: kunci baris run dan pastikan `status = RUNNING` serta `attempt_no` milik worker, insert baris penyusutan (`ON CONFLICT DO NOTHING` pada kunci primer), lalu majukan `checkpoint_asset_id` dan `lines_written`. Kegagalan di tengah chunk membatalkan chunk itu saja. |
| RUNNING, fase PROJECTION | Setelah semua baris tertulis, proyeksi diterapkan per chunk dengan pemeriksaan run yang sama: `UPDATE asset_assets SET accumulated_depreciation = accumulated_depreciation + l.amount, book_value = book_value - l.amount, last_depreciation_period = period, last_depreciation_run_id = run … WHERE id IN chunk AND (last_depreciation_period IS NULL OR last_depreciation_period < period)`, lalu majukan kursor dan `projections_applied` dalam transaksi yang sama. Penjaga periode mencegah penerapan ganda walaupun chunk diulang; penambahan selisih tidak menimpa transaksi aset lain yang terjadi di antara fase. |
| COMPLETED | Fase DONE; jumlah baris dan proyeksi dicocokkan dengan cakupan sebelum status berubah. |
| FAILED | Galat dicatat (`last_error`, `attempt_no`). Run tetap memegang periodenya. Lanjutan hanya atas permintaan pengguna berwenang (bukan retry otomatis): transisi bersyarat `FAILED → RUNNING` pada run **yang sama**, melanjutkan dari `phase` dan `checkpoint_asset_id`. |
| CANCELLED | Hanya dari QUEUED atau FAILED dan hanya bila `lines_written = 0`; setelah itu run resmi baru untuk periode itu dapat dibuat. Run yang sudah menulis baris tidak dapat dibatalkan, sehingga periode tidak pernah memiliki baris resmi tanpa run pemiliknya. Karena kegagalan deterministik tertangkap pada fase VALIDATION, kegagalan setelah baris tertulis bersifat sementara dan selalu dapat dilanjutkan; kegagalan berulang yang tidak terduga dicatat sebagai insiden integritas (bagian 7) dan dieskalasi, tidak pernah diselesaikan dengan menghapus atau menimpa baris. |
| Koreksi | Tidak ada run pengganti untuk periode yang memiliki run COMPLETED/FAILED berbaris. Koreksi nilai resmi menunggu kebijakan (BR-017, GAP-007/008) dan dirancang melalui perubahan rencana; sampai saat itu permintaan koreksi ditolak dengan penjelasan. |

Uji wajib: DI-01–DI-04 ([TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib)). Keadaan WF-022: QUEUED/RUNNING = S3, COMPLETED = S4, FAILED = S5; lanjutan dan pembatalan adalah mekanisme pemulihan yang diserahkan TR-214 kepada P6 dan tidak pernah menimpa hasil final.

## 7. Verifikasi integritas berkala

Job `maintenance` harian: (a) membandingkan `inv_stock_balances` dengan jumlah ledger per `(warehouse_id, item_id)`; (b) membandingkan proyeksi nilai aset dengan transaksi/penyusutan; (c) memeriksa kesinambungan partisi audit. Selisih dicatat sebagai peristiwa insiden integritas dan peringatan operasi — tidak pernah diperbaiki diam-diam.

## 8. Isolasi basis data uji

- Basis data pengujian tidak pernah sama dengan basis data pengembangan atau produksi; nama selalu berprefiks `pranata_` (`pranata_dev`, `pranata_test`, `pranata_test_<token>` untuk worker paralel, `pranata_e2e_<run>`).
- Uji paralel Pest memakai satu basis data per proses; E2E memakai basis data khusus run yang dimigrasi dan di-seed ulang, lalu dihapus.
- Tidak ada uji yang berjalan terhadap basis data proyek lain di mesin pengembang (MULTIPLECORP, BantuKasir, ELLIGBLE); konfigurasi mengikat host/port/kontainer proyek `pranata` ([INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md)).
- Sebelum menjalankan perintah destruktif (`migrate:fresh`, `db:wipe`), skrip memastikan nama basis data cocok `^pranata_(dev|test|e2e|perf)(_[a-z0-9_]+)?$` dan `APP_ENV` bukan `production`; selain itu berhenti. Skrip uji otomatis memakai pola yang lebih sempit tanpa `dev` ([TEST_STRATEGY §4](../08-testing/TEST_STRATEGY.md#4-isolasi-basis-data-uji)).

## 9. Pengujian konkurensi

Uji fitur memakai dua koneksi/proses paralel untuk: serah terima barang terakhir, alokasi nomor bersamaan, persetujuan ganda, aktivasi kebijakan dan template bersamaan, penerimaan intake paralel (ditolak), run penyusutan ganda dan lanjutan run ganda, posting ganda baris sumber yang sama, dan pembalikan ganda. Uji memastikan tidak ada saldo negatif, nomor ganda, transisi ganda, penyusutan ganda atau record setengah jadi. Skenario integritas data yang wajib, termasuk uji negatif langsung ke basis data, dikatalogkan sebagai DI-01–DI-20 di [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib).
