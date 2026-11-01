# Konkurensi, transaksi dan integritas data — PRANATA UNY

Status: P6 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P6 | ADR: [ADR-0004](../adr/ADR-0004-append-only-ledgers.md)

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

1. Kontainer bisnis: `proc_packages` → `proc_selections` → `proc_contracts` → `inv_requests`/`inv_returns`/`inv_transfers`/`inv_counts` → `asset_assets` (menurut `id` naik).
2. Penomoran: `numbering_counters` (menurut `document_type`, `sequence_key`, `scope_key`).
3. Saldo stok: `inv_stock_balances` menurut `(warehouse_id, item_id)` naik.
4. Proyeksi lain (`asset_assets` kolom proyeksi bila belum dikunci di tingkat 1).

Contoh serah terima (TR-295): kunci `inv_requests` baris permintaan → kunci saldo untuk semua `(warehouse_id, item_id)` baris serah terima secara terurut → validasi `quantity >= jumlah` → insert movement ISSUE (dengan `balance_after`) → update saldo → update `inv_request_lines.handed_over_qty` → transisi + audit + item pekerjaan.

## 3. Invarian yang ditegakkan basis data

| Invarian | Penegakan |
|---|---|
| Stok fisik tidak negatif (INV-020) | `CHECK (quantity >= 0)` pada `inv_stock_balances` + validasi Action sebelum tulis |
| Pencatatan terlambat tidak membuat saldo per tanggal negatif | Di bawah kunci saldo yang sama, Action memeriksa saldo menurut `occurred_at` pada titik gerakan dan setiap titik sesudahnya sebelum menulis; OPENING hanya sebelum gerakan lain |
| Ledger dan riwayat tidak dapat diubah (INV-017) | Hak peran tanpa UPDATE/DELETE + trigger penolak |
| Nomor dokumen unik (OD-29) | `UNIQUE(document_type, number_text)` (lintas skema) dan `UNIQUE(document_type, sequence_key, scope_key, sequence_value)`; paling banyak satu skema per jenis dokumen untuk setiap versi kebijakan `numbering`, dipilih menurut versi yang berlaku resmi pada tanggal finalisasi; penghitung berkunci `sequence_key` sehingga pergantian versi tidak mereset nomor urut |
| Satu penetapan metode berlaku per paket | Unique parsial `WHERE status = 'DETERMINED'` |
| Satu HPS ditetapkan per paket | Unique parsial `WHERE status = 'SET'` |
| Satu hasil berlaku per lingkup penghargaan pada satu pemilihan | Unique parsial `(selection_id, scope_id) WHERE status IN ('DETERMINED','ANNOUNCED')`; paling banyak satu pemilihan ACTIVE per paket; satu lingkup pada paling banyak satu pemilihan ACTIVE (constraint trigger) |
| Satu skema evaluasi beku per pemilihan dan tidak berubah | Unique parsial `WHERE status = 'FROZEN'`; trigger menolak perubahan skema FROZEN beserta tahap/kriteria dan penggantian sesudah pembukaan pertama |
| Penawaran asli imutabel; pembukaan tidak menggugurkan (INV-023) | Hak kolom `UPDATE (status, opened_at, withdrawn_at, withdrawal_evidence_file_id)` + trigger `forbid_offer_change()`; lingkup, rincian harga, versi batas akhir dan entri pembukaan append-only; entri pembukaan tidak memiliki kolom hasil; **segel tangkapan**: sidik jari isi yang tersimpan saat pencatatan diverifikasi constraint trigger tertunda `verify_capture_manifest()` pada setiap sisipan anak, sehingga lingkup, rincian harga, tautan berkas (apa pun tujuannya) dan entri pembukaan tidak dapat ditambah sesudah tercatat; kesalahan pencatatan hanya diperbaiki dengan kiriman pengganti atau catatan pembukaan pengganti ([DATA_MODEL §1, §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)) |
| Bukti berkas yang tertangkap tidak dapat dialihkan | `file_objects`: hak kolom hanya `UPDATE (classification, scan_status)` + `guard_file_object_change()` (identitas isi tetap, klasifikasi hanya naik, transisi status pemindaian terbatas); `file_links`: hak kolom hanya `UPDATE (label, unlinked_by, unlinked_at)` + `guard_file_link_change()` (berkas, pemilik dan tujuan tetap; pelepasan sekali; setiap UPDATE/DELETE tautan ke pemilik terdaftar di `file_sealed_owner_types` ditolak); penyimpanan tulis-sekali dan verifikasi SHA-256 pada setiap pembacaan byte tersimpan ([DATA_MODEL §12](../04-architecture/DATA_MODEL.md#12-files-documents-dan-audit)) |
| Batas akhir dan keterlambatan penawaran tidak dapat dilewati | Batas akhir per pemilihan dan sampul append-only berversi (`check_deadline_version()`, tanpa versi baru sesudah catatan pembukaan); catatan pembukaan/penutupan merujuk versi tertinggi dan menyalin `cutoff_at`/`cutoff_inclusive` lewat FK; `CHECK` tepat waktu pada entri pembukaan atas salinan `received_at`; constraint trigger tertunda `check_opening_consistency()` menyelaraskan status kiriman dengan entri dan batas; hasil evaluasi ber-FK ke entri `opened = true`; jam basis data untuk kanal PRANATA dan untuk menggolongkan perubahan batas; batas beku oleh pembukaan pertama yang mencakupnya (termasuk sampul harga pada dua sampul); perubahan sesudah batas lewat hanya memperpanjang dan selalu keputusan tercatat; pemajuan tidak boleh membuat kiriman tercatat terlambat; paling banyak satu kiriman tepat waktu per peserta dan sampul; `opened = false` hanya untuk sampul harga peserta yang gugur lebih dahulu; tanpa kolom penanda terlambat ([DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan)) |
| Kriteria hanya dinilai pada lingkup penerapannya | `proc_evaluation_criterion_scopes`; salinan `per_scope` lewat FK dengan `CHECK (per_scope = (scope_id IS NOT NULL))`; FK `(criterion_id, scope_id)` ke penerapan dan `(submission_id, scope_id)` ke lingkup yang ditawar; pembekuan menolak kriteria per lingkup tanpa penerapan; baris anak skema dan lingkup pemilihan tidak dapat ditambahkan sesudah FROZEN; hasil tahap per lingkup hanya bila ada kriteria yang berlaku (constraint trigger); peringkat hanya untuk peserta dengan penawaran OPENED yang mencakup lingkup hasil (FK `(submission_id, scope_id)`) dan hasil tahap lengkap |
| Lingkup penghargaan eksklusif (INV-024) | Item → tepat satu lingkup (`award_scope_id`); `uq(result_id) WHERE role='WINNER' AND award_mode='SINGLE'`; `uq(scope_id) WHERE is_active AND award_mode='SINGLE'` pada lingkup SPPBJ dan kontrak; FK `(package_item_id, scope_id)` pada baris penawaran dan baris kontrak; FK ke peringkat dengan `role = 'WINNER'`; perusahaan kontrak = perusahaan partisipasi pemenang melalui FK `(participation_id, company_id)` |
| Ketidaklulusan dan kegagalan beralasan | `CHECK` alasan pada hasil kriteria/tahap FAIL, pemilihan FAILED, hasil FAILED/SUPERSEDED dan reviu NOT_AGREED |
| Satu versi berlaku per kunci dan tanggal | `EXCLUDE USING gist` atas jendela versi ACTIVE/SUPERSEDED untuk kebijakan, template dokumen dan klasifikasi aset; aktivasi menutup jendela versi sebelumnya secara atomik di bawah kunci baris induk; tanpa tanggal mundur (`effective_from` ≥ tanggal aktivasi); gerbang aksi resmi memakai keberlakuan resmi (disahkan dan jendela memuat tanggal acuan), bukan `status` ([POLICY_REGISTRY §3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi)) |
| Konsistensi induk lintas record | FK komposit `(rujukan, lingkup)`: pemilihan/lingkup ↔ paket, skema/putaran/kriteria ↔ pemilihan dan skema yang sama, hasil kriteria/tahap/klarifikasi/negosiasi ↔ partisipasi dan lingkup pemilihan yang sama, hasil ↔ lingkup pemilihan, peringkat ↔ hasil dan partisipasi pemilihan yang sama, SPPBJ/kontrak ↔ partisipasi pemenang dan lingkupnya, kontrak ↔ SPPBJ pemenang yang sama, baris BAST ↔ baris kontrak yang sama, serah terima gudang ↔ permintaan dan gudangnya, baris serah terima ↔ baris permintaan dan barangnya; kolom lingkup tidak dapat diubah (`forbid_scope_change()`); perusahaan kontrak = pemenang dan penugasan kontribusi SPPBJ = paket yang sama diperiksa constraint trigger |
| Satu peristiwa fisik satu posting | Unique identitas sumber `(source_type, source_id, source_line_id, movement_type)` pada `inv_stock_movements` dan pola yang sama pada `asset_transactions`; berlaku walaupun kunci idempotensi berbeda; kecocokan gerakan dengan baris sumbernya diperiksa constraint trigger per jenis sumber |
| Pembalikan tunggal dan cocok | `uq(reversal_of_id)` + trigger `check_stock_reversal()`/`check_asset_reversal()` (entitas, jumlah, arah; bukan pembalikan atas pembalikan; stok: membawa sumber koreksi dan alasan; aset: hanya transaksi terakhir aset yang belum dibalik) |
| Baris final beku | `forbid_change_when_parent_final()` untuk INSERT, UPDATE dan DELETE dengan kunci baris induk `FOR SHARE` pada baris HPS setelah SET, baris penerimaan setelah POSTED, baris opname setelah CLOSED, baris transfer setelah DISPATCHED, baris serah terima gudang sejak diposting dan baris BAST setelah SIGNED (kecuali klasifikasi/rujukan hilir); `proc_contract_lines` append-only per versi kontrak dengan FK `(contract_id, version_no)`, `uq(contract_id, version_no, line_no)` dan segel tangkapan per versi; tahap dan kriteria skema evaluasi setelah FROZEN, hasil putaran setelah COMPLETED dan peringkat setelah hasil DETERMINED |
| Satu run penyusutan resmi per periode | Unique parsial untuk status QUEUED/RUNNING/FAILED/COMPLETED; unique baris resmi `(asset_id, period)`; penjaga proyeksi `last_depreciation_period < period` (bagian 6.1) |
| Maker-checker (tidak lolos bila kosong) | Kebijakan: `CHECK (status NOT IN ('ACTIVE', 'SUPERSEDED') OR (activated_by IS NOT NULL AND activated_at IS NOT NULL))` dan trigger `guard_version_transition()` (juga untuk versi template dan klasifikasi: DRAFT tidak dapat langsung menjadi ACTIVE/SUPERSEDED) + `CHECK (activated_by IS NULL OR (activated_by <> created_by AND activated_by IS DISTINCT FROM submitted_by))`; template: pola yang sama; grant istimewa: `CHECK (status <> 'ACTIVE' OR NOT requires_approval OR approved_by IS NOT NULL)` + `CHECK (approved_by IS NULL OR approved_by <> granted_by)` ([DATA_MODEL](../04-architecture/DATA_MODEL.md)) |
| Kuantitas serah terima ≤ disetujui | `CHECK` pada `inv_request_lines` |
| Partisipasi unik per penyedia per pemilihan | `UNIQUE(selection_id, company_id)`; undangan unik per pemilihan |
| Transfer bukan ke gudang sama | `CHECK (to_warehouse_id <> from_warehouse_id)` |

Invarian yang memerlukan konteks (pemisahan tugas, keberlakuan resmi kebijakan pada tanggal acuan, guard TR) ditegakkan Action dan diuji; bila memungkinkan didukung constraint tambahan.

## 4. Race condition yang ditangani

| Skenario | Penanganan |
|---|---|
| Dua petugas menyerahkan barang terakhir bersamaan | Kunci baris saldo; yang kedua melihat saldo kurang → 422 "stok tidak cukup" |
| Dua pengguna menyetujui/menolak permintaan yang sama | `lock_version` + guard state; yang kedua mendapat 409/422 dengan state terbaru |
| Klik ganda pada finalisasi dokumen | Idempotency key + guard state |
| Dua alokasi nomor bersamaan | `FOR UPDATE` pada counter; unique constraint sebagai jaring pengaman |
| Override nomor manual bertabrakan | Unique constraint → 422 dengan saran nomor berikut |
| Versi yang berlaku resmi berubah antara saran/pratinjau dan commit (penerus mulai berlaku, tanggal commit berganti tengah malam Asia/Jakarta, versi dipensiunkan, atau aktivasi bersamaan) | Saran/pratinjau menyimpan `policy_version_id` dan tanggal acuan; Action menyelesaikan ulang di dalam transaksinya → bila versi berbeda, minta tinjau ulang (IX-023) tanpa menyimpan hasil lama; versi terjadwal tidak pernah dipakai sebelum tanggal berlakunya ([POLICY_REGISTRY §3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi)) |
| Grant dicabut saat pengguna sedang bekerja | Otorisasi dievaluasi setiap permintaan; tidak ada cache izin melewati satu permintaan |
| Konfirmasi penerima ganda | Idempotensi + guard state `PENDING` |
| Job dokumen dijalankan ulang setelah timeout | Kunci idempotensi job + status revisi |
| Run penyusutan periode yang sama dua kali | Unique parsial per periode non-simulasi untuk status QUEUED/RUNNING/FAILED/COMPLETED (run kedua ditolak saat dibuat, termasuk ketika run sebelumnya gagal) + `ShouldBeUnique` dengan kunci run; protokol bagian 6.1 |
| Dua permintaan melanjutkan run FAILED yang sama | Kunci baris run + transisi bersyarat `FAILED → RUNNING` (compare-and-set dengan `lock_version`); permintaan kedua melihat RUNNING → 409; job unik per run |
| Worker run penyusutan dari percobaan lama masih berjalan setelah run dilanjutkan | Setiap transaksi chunk mengunci baris run dan memeriksa `status = RUNNING` serta `attempt_no` milik worker; worker usang berhenti tanpa menulis |
| Dua posting untuk baris sumber yang sama (serah terima, penerimaan, pengembalian, transfer, opname) | Unique identitas sumber; posting kedua → 409 "sudah diposting" tanpa efek parsial, apa pun kunci idempotensinya |
| Dua pembalikan atas gerakan/transaksi yang sama | `uq(reversal_of_id)`; yang kedua → 409 |
| Dua aktivasi versi template atau kebijakan bersamaan | Kunci baris induk (template/paket kebijakan) + `EXCLUDE`; yang kedua → 409 dengan versi yang kini berlaku |
| Batas akhir diubah bersamaan dengan pencatatan pembukaan/penutupan | Trigger keduanya mengunci baris `proc_selections`; versi batas akhir baru ditolak bila catatan pembukaan sudah ada, dan pembukaan menolak batas yang bukan versi tertinggi |
| Penawaran eksternal dicatat bersamaan dengan pencatatan pembukaan | Insert kiriman berbatas mengunci baris `proc_selections` (trigger); sesudah pembukaan, kiriman tepat waktu ditolak dan kiriman terlambat hanya REJECTED_LATE; kelengkapan pembukaan diperiksa constraint trigger tertunda saat commit |
| Sisipan lingkup, rincian harga, tautan berkas atau entri pembukaan dari koneksi lain selama atau sesudah pencatatan | Sidik jari tersimpan diverifikasi saat commit (`verify_capture_manifest()`); sisipan anak ber-FK dari koneksi lain menunggu induknya commit lalu ditolak saat commit karena sidik jari berubah; tautan berkas (tanpa FK) ke kiriman yang belum terlihat ditolak seketika oleh `guard_file_link_change()`; sisipan sesudah commit ditolak dengan cara yang sama |
| Pelepasan, pengalihan atau perubahan tautan berkas kiriman, atau identitas berkasnya, dari koneksi lain selama atau sesudah pencatatan | Tautan yang belum commit tidak terlihat koneksi lain (UPDATE mengenai 0 baris); sesudah commit `guard_file_link_change()` menolak setiap UPDATE/DELETE tautan bersegel seketika, tanpa bergantung pada verifikasi saat commit, dan hak kolom menolak perubahan identitas berkas; pembaruan `scan_status` bersamaan tetap sah karena tidak masuk sidik jari |
| Sisipan baris anak bersamaan dengan finalisasi induk (HPS, penerimaan, opname, transfer, serah terima, BAST, peringkat) | Trigger mengunci baris induk `FOR SHARE`; finalisasi menunggu sisipan yang lebih dahulu, atau sisipan melihat status final sesudah menunggu dan ditolak |
| Pembekuan skema bersamaan dengan pencatatan pembukaan | Kunci baris `proc_selections`; pembukaan mensyaratkan skema FROZEN dan penggantian skema mensyaratkan belum ada pembukaan, keduanya diperiksa di bawah kunci yang sama |
| Dua evaluator menyimpan hasil kriteria yang sama | `uq(round_id, participation_id, criterion_id, scope) WHERE status = 'VALID'` + `lock_version`; yang kedua → 409 dengan nilai terbaru, tanpa penimpaan |
| Penyelesaian putaran saat evaluator masih menulis | Kunci baris putaran `FOR UPDATE`; penulisan hasil memeriksa putaran OPEN di bawah kunci yang sama; penyelesaian menolak bila masih ada hasil tahap PENDING |
| Penetapan hasil bersamaan dengan evaluasi ulang | Kunci baris pemilihan; hasil merujuk putaran COMPLETED, evaluasi ulang menandai putaran SUPERSEDED dan hasil yang belum DETERMINED dibatalkan dalam transaksi yang sama |
| Dua SPPBJ atau kontrak untuk lingkup yang sama dibuat bersamaan | Unique parsial lingkup aktif; yang kedua → 409 "lingkup sudah dihargakan" |
| Aktivasi cadangan saat kontrak pemenang lama sedang disiapkan | Kunci pemilihan lalu kontrak; versi hasil baru hanya setelah lingkup SPPBJ/kontrak lama nonaktif; penyiapan kontrak memeriksa hasil masih berlaku |

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

Job `maintenance` harian: (a) membandingkan `inv_stock_balances` dengan jumlah ledger per `(warehouse_id, item_id)`; (b) membandingkan proyeksi nilai aset dengan transaksi/penyusutan; (c) memeriksa kesinambungan partisi audit; (d) menghitung ulang sidik jari tangkapan kiriman, catatan pembukaan dan versi kontrak lalu membandingkannya dengan nilai tersimpan; (e) menghitung ulang SHA-256 dan ukuran byte tersimpan setiap berkas yang bertaut ke pemilik bersegel atau menjadi bukti terima/penarikan kiriman, lalu membandingkannya dengan `file_objects`. Selisih dicatat sebagai peristiwa insiden integritas dan peringatan operasi — tidak pernah diperbaiki diam-diam.

## 8. Isolasi basis data uji

- Basis data pengujian tidak pernah sama dengan basis data pengembangan atau produksi; nama selalu berprefiks `pranata_` (`pranata_dev`, `pranata_test`, `pranata_test_<token>` untuk worker paralel, `pranata_e2e_<run>`).
- Uji paralel Pest memakai satu basis data per proses; E2E memakai basis data khusus run yang dimigrasi dan di-seed ulang, lalu dihapus.
- Tidak ada basis data PRANATA di mesin pengembang (OD-37); uji berbasis data hanya berjalan pada service container PostgreSQL runner GitHub-hosted, dan basis data proyek lain (MULTIPLECORP, BantuKasir, ELLIGBLE) tidak pernah dijangkau ([INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi)).
- Sebelum menjalankan perintah destruktif (`migrate:fresh`, `db:wipe`), skrip memastikan nama basis data cocok `^pranata_(dev|test|e2e|perf)(_[a-z0-9_]+)?$` dan `APP_ENV` bukan `production`; selain itu berhenti. Skrip uji otomatis memakai pola yang lebih sempit tanpa `dev` ([TEST_STRATEGY §4](../08-testing/TEST_STRATEGY.md#4-isolasi-basis-data-uji)).

## 9. Pengujian konkurensi

Uji fitur memakai dua koneksi/proses paralel untuk: serah terima barang terakhir, alokasi nomor bersamaan, persetujuan ganda, aktivasi kebijakan dan template bersamaan, penerimaan intake paralel (ditolak), run penyusutan ganda dan lanjutan run ganda, posting ganda baris sumber yang sama, pembalikan ganda, pembekuan skema bersamaan pembukaan, perubahan batas akhir atau pencatatan penawaran bersamaan pembukaan, sisipan anak penawaran atau entri pembukaan dari koneksi kedua selama pencatatan, pelepasan atau pengalihan tautan berkas kiriman dari koneksi kedua selama pencatatan, sisipan baris anak bersamaan finalisasi induk, dan dua evaluator pada hasil yang sama. Uji memastikan tidak ada saldo negatif, nomor ganda, transisi ganda, penyusutan ganda atau record setengah jadi. Skenario integritas data yang wajib, termasuk uji negatif langsung ke basis data, dikatalogkan sebagai DI-01–DI-33 di [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib).
