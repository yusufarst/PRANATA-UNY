# Strategi pengujian — PRANATA UNY

Status: P8 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P8

Dokumen ini memiliki **lapisan uji, alat, data uji, isolasi basis data uji, uji otorisasi/rute/interaksi/E2E/aksesibilitas/lokalisasi/keamanan/performa/migrasi dan kebijakan uji tidak stabil**. Gerbang kualitas, Definition of Done dan pipeline CI dimiliki [QUALITY_GATES](QUALITY_GATES.md). Mengikuti AICWDF §19, §25–§28.

## 1. Lapisan dan alat

| Lapisan | Alat (open-source) | Cakupan | Kapan |
|---|---|---|---|
| Format/lint PHP | Laravel Pint | Gaya kode | Setiap commit/CI cepat |
| Analisis statis PHP | Larastan (PHPStan), level dinaikkan bertahap sampai ≥ 6 | Tipe, panggilan tidak valid | CI cepat |
| Lint/format TS | ESLint (flat config, termasuk aturan teks literal JSX dan react-hooks), Prettier | Gaya, larangan teks hardcode | CI cepat |
| Typecheck | `tsc --noEmit` | Tipe frontend + tipe hasil generate | CI cepat |
| Unit PHP | Pest | Aturan domain, evaluator kebijakan, format/terbilang, kalkulasi desimal | CI cepat |
| Feature PHP | Pest + basis data PostgreSQL | Action, transisi TR, invarian, idempotensi, otorisasi, Query/scope | CI cepat (paralel) |
| Arsitektur | Pest `arch()` | Batas modul, larangan dependensi, kontroler tipis | CI cepat |
| Unit/komponen React | Vitest + Testing Library | Komponen komposit, format, hook | CI cepat |
| Integritas rute | Pest + uji crawler | Semua rute bernama punya middleware auth, policy dan respons yang diharapkan; tautan menu/aksi tidak mati | CI cepat |
| E2E browser | Playwright (Chromium **dan** WebKit) | Perjalanan J-01–J-18, konsol/jaringan bersih, kembali kontekstual, dua bahasa, dua viewport | CI penuh |
| Aksesibilitas | `@axe-core/playwright` + checklist manual | Tanpa pelanggaran serius/kritis | CI penuh; manual sebelum UAT |
| Lokalisasi | Skrip pemeriksa kunci + E2E dua bahasa | Kunci hilang = 0 | CI cepat + penuh |
| Keamanan | Uji SEC-*/AUTHZ-*, `composer audit`, `npm audit`, pemindai rahasia (gitleaks) | Regresi keamanan dan dependensi | CI cepat (audit) + penuh |
| Migrasi | `migrate:fresh`, rollback migrasi baru, uji hak peran DB, uji data migrasi | Keamanan skema | CI cepat (fresh) + penuh |
| Performa | Pest + data sintetis + EXPLAIN; k6 | Anggaran PB-* | Malam/rilis |
| Build produksi | `vite build`, `php artisan optimize` | Build berhasil tanpa peringatan kritis | CI cepat |

## 2. Prioritas berbasis risiko

| Area | Kedalaman uji wajib |
|---|---|
| Otorisasi, isolasi, pemisahan tugas | Matriks kapabilitas × profil × cakupan dibangkitkan otomatis + skenario AUTHZ-* + uji IDOR per rute objek |
| Ledger stok/aset, nilai uang, penomoran | Uji invarian, batas, konkurensi dua proses, pembalikan |
| Policy Registry | Validasi skema, golden cases tiap paket, maker-checker, label simulasi |
| Transisi workflow | Setiap TR yang diimplementasikan: guard sukses, guard gagal, otorisasi, riwayat, item pekerjaan |
| Dokumen | Template emas, banner, nomor unik, revisi |
| Impor | File valid/tidak valid/besar, duplikasi, kredensial tidak terbawa, lanjutan setelah gagal |
| UI | Keadaan wajib, kembali kontekstual, zero dead interactions, responsif, aksesibilitas |

## 3. Data uji

- Factory per model dengan state bernama (`draft()`, `submitted()`, `approved()`…) yang mengikuti transisi sah, bukan menulis status sembarangan.
- Seeder skenario deterministik (`ScenarioSeeder`) untuk E2E: organisasi sintetis, 2 gudang (ALP, PSRT), pengguna per profil, paket per metode × jenis, versi kebijakan DRAFT dengan nilai dari REGULATORY_SOURCE_REVIEW, template DRAFT.
- Generator skala `pranata:synthetic --assets=1000000` untuk uji performa (deterministik dari seed).
- Tidak ada data pribadi atau data transaksi riil di fixture, repositori atau artefak CI (PRODUCTION_DATA_SAFETY, EVIDENCE_POLICY).
- Waktu dikendalikan (`travelTo`) untuk tenggat/periode; zona waktu uji eksplisit.

## 4. Isolasi basis data uji

| Konteks | Basis data | Aturan |
|---|---|---|
| Pest lokal | `pranata_test` (+ `_N` per proses paralel) pada kontainer `pranata-postgres` proyek | Konfigurasi `.env.testing`; `RefreshDatabase`/`LazilyRefreshDatabase` |
| Pest CI | Service container PostgreSQL per job | Basis data dibuat ulang per job |
| E2E lokal/CI | `pranata_e2e_<run_id>` | Migrate + `ScenarioSeeder` sebelum suite; dihapus setelahnya; worker Playwright berbagi data hanya-baca dan membuat objek uji sendiri dengan nama unik untuk perubahan |
| Performa | `pranata_perf` di staging/mesin uji | Tidak pernah produksi |

Pengaman wajib: skrip uji menolak berjalan bila nama basis data tidak cocok `^pranata_(test|e2e|perf)(_[a-z0-9_]+)?$` atau `APP_ENV=production`; perintah destruktif di luar uji (misalnya reset `pranata_dev`) memakai pengaman [CONCURRENCY_AND_INTEGRITY §8](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#8-isolasi-basis-data-uji). Uji tidak pernah memakai basis data proyek lain di mesin yang sama (port, nama kontainer dan volume khusus `pranata`, [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md)).

## 5. E2E browser

- Playwright dengan proyek `chromium` dan `webkit`; viewport 390×844 dan 1440×900; lokal `id` dan `en` untuk perjalanan yang menandai pemeriksaan bahasa.
- Setiap uji mengumpulkan galat konsol dan permintaan gagal; galat kritis = gagal.
- Login memakai state penyimpanan per peran yang dibuat sekali per run (cepat), dengan satu uji login penuh terpisah.
- Selektor berbasis peran/label aksesibel (`getByRole`, `getByLabel`), bukan kelas CSS.
- Artefak saat gagal: trace, screenshot, video pendek; disimpan sebagai artefak CI 7 hari.
- Perjalanan wajib: J-01–J-18 ([USER_JOURNEYS](../07-ux-design/USER_JOURNEYS.md)); Task UI menambah atau memperbarui perjalanan yang disentuhnya.

## 6. Uji tidak stabil (flaky)

Uji yang gagal tidak konsisten dicatat sebagai temuan dengan bukti ketidakstabilan (hasil berbeda pada SHA yang sama). *Quarantine* paling lama 3 hari kerja dengan issue terbuka dan pemilik hanya boleh bila: (a) uji tidak mencakup kriteria `TAC` Task aktif atau area yang diubah Task itu; (b) uji bukan uji invarian BR/INV, otorisasi (AUTHZ/SOD) atau keamanan (SEC); (c) peninjau independen (subagen segar) mengonfirmasi bukti ketidakstabilan. Quarantine uji kategori (b) memerlukan persetujuan Owner. Uji tidak boleh disembunyikan dengan retry tanpa batas. Retry CI maksimal 1 kali untuk E2E dan hasil retry dilaporkan. Penyebab umum (waktu, urutan, data bersama) diperbaiki pada akar.

## 7. Evidence per Task

Setiap Task mencatat: perintah uji yang dijalankan dan ringkasan hasil, jumlah uji, cakupan area yang diubah, tautan run CI, artefak E2E untuk Task UI, bukti EXPLAIN untuk Task kueri besar, dan daftar pemeriksaan keamanan/basis data. Format di [QUALITY_GATES](QUALITY_GATES.md) dan [EXECUTOR_OPERATING_MODEL](../11-tasks/EXECUTOR_OPERATING_MODEL.md).

## 8. Piramida verifikasi dan pemeriksaan sebelum PR

Urutan verifikasi per Task (U-007 §10.3): **berkas berubah → pemeriksaan statis cepat → uji fungsional terarah → integrasi/otorisasi/keamanan yang relevan → CI final wajib**.

| Langkah | Kapan | Isi minimum |
|---|---|---|
| Statis cepat | Setiap siklus edit | `pint --dirty`, ESLint/Prettier pada berkas berubah, `tsc --noEmit` (inkremental), Larastan dengan cache hasil |
| Uji terarah | Setelah perilaku berubah | Pest untuk berkas/grup yang menyentuh perilaku (`--filter`, `--group`), Vitest berkas terkait |
| Relevan | Sebelum membuka/memperbarui PR | Uji otorisasi dan integritas rute modul yang disentuh; `migrate:fresh` + rollback bila migrasi berubah; build produksi bila frontend berubah; E2E lokal hanya perjalanan yang disentuh pada Chromium |
| CI final | Setiap pembaruan PR | `ci-gate` pada HEAD PR terakhir ([QUALITY_GATES §4](QUALITY_GATES.md#4-pipeline-ci-github-actions)); WebKit, E2E penuh yang terpicu dan audit dependensi dijalankan di CI |

Aturan bukti:

- Bukti lokal hanya berlaku untuk SHA tempat bukti itu dihasilkan. Setelah perbaikan, uji terarah untuk area yang berubah dijalankan ulang; suite yang tidak terdampak tidak perlu diulang secara lokal karena CI final menjalankannya pada HEAD terakhir.
- Bukti CI yang dipakai untuk merge selalu dari HEAD PR terakhir; hasil run lama tidak berlaku untuk kode yang berubah.
- Suite lengkap tidak dijalankan setelah setiap edit kecil; suite yang berat (E2E penuh, performa) tidak dijalankan lokal pada stasiun kerja yang terbatas kecuali Task menuntutnya.
- Sumber daya lokal diukur (durasi, CPU/RAM) pada TASK-003/TASK-007; paralelisme lokal hanya dipakai bila terbukti lebih cepat tanpa mengganggu proyek lain.

## 9. Keandalan fixture dan kegagalan setup

- `globalSetup` Playwright memverifikasi basis data E2E, `ScenarioSeeder`, dan state login per peran **sebelum** suite berjalan. Bila gagal, run dihentikan dengan pesan **SETUP FAILURE** yang menyebut langkah dan penyebabnya, sehingga kegagalan setup tidak terbaca sebagai puluhan cacat UI.
- Fixture idempoten dan aman dijalankan berulang: identitas unik dibangkitkan per run/worker (sufiks run id), tidak ada ketergantungan urutan uji, tidak ada state lintas uji yang di-hardcode.
- Fixture menghormati otorisasi dan scope organisasi: setiap peran masuk sebagai pengguna yang benar; uji yang membutuhkan data unit/gudang lain membuatnya melalui factory dengan scope eksplisit.
- Uji asap fixture bersama (`tests/e2e/smoke/fixtures.spec.ts`) berjalan pertama di CI dan lokal; kegagalannya menghentikan job dengan status setup.
- Selektor stabil berbasis peran/label; asersi aksesibilitas (axe) dipertahankan pada perjalanan yang disentuh; proyek Chromium dan WebKit didukung.

## 10. Skenario integritas data wajib

Skenario berikut adalah uji regresi wajib bagi Task pemiliknya. Setiap skenario yang ditandai **DB** diuji juga dengan insert/update langsung ke basis data memakai peran `pranata_app`, sehingga constraint atau trigger terbukti menolak walaupun lapisan aplikasi dilewati. Skenario **K** memakai dua koneksi/proses paralel ([CONCURRENCY_AND_INTEGRITY §9](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#9-pengujian-konkurensi)).

| ID | Skenario dan hasil yang diharapkan | Jenis | Task |
|---|---|---|---|
| DI-01 | Run penyusutan resmi gagal setelah sebagian baris tertulis → dilanjutkan pada run yang sama dari checkpoint, baris yang ada dilewati, tidak ada baris ganda, proyeksi diterapkan sekali; aset yang tidak dapat dihitung terdeteksi pada fase VALIDATION sebelum baris pertama ditulis | Feature, DB | TASK-066 |
| DI-02 | Gagal di tengah fase proyeksi → lanjutan memulai dari kursor; aset yang sudah diterapkan tidak berubah lagi (penjaga `last_depreciation_period`); transaksi nilai aset lain di antara fase tidak tertimpa | Feature, DB | TASK-066 |
| DI-03 | Dua permintaan melanjutkan run FAILED yang sama → satu berjalan, satu ditolak 409; worker dari percobaan lama tidak dapat menulis setelah run dilanjutkan; tidak ada eksekusi ganda | K | TASK-066 |
| DI-04 | Run resmi kedua untuk periode yang memiliki run QUEUED/RUNNING/FAILED/COMPLETED ditolak; run resmi di luar urutan periode ditolak; permintaan run pengganti/koreksi ditolak dengan penjelasan GAP-007/008; pembatalan hanya bila belum ada baris; setiap run FAILED memiliki jalan lanjut (lanjutkan, atau batalkan bila tanpa baris) | Feature, DB | TASK-066 |
| DI-05 | Peringkat/pemenang dari partisipasi paket lain ditolak; hanya satu WINNER dan peringkat unik per hasil | Feature, DB | TASK-035 |
| DI-06 | SPPBJ yang menunjuk hasil paket lain, kontrak yang menunjuk SPPBJ paket lain, atau kontrak dengan perusahaan selain pemenang ditolak; penugasan kontribusi SPPBJ dari paket lain ditolak | Feature, DB | TASK-036, TASK-037 |
| DI-07 | Evaluasi atau negosiasi untuk partisipasi paket lain ditolak | Feature, DB | TASK-034 |
| DI-08 | Serah terima gudang dengan permintaan dari gudang lain, baris dari permintaan lain atau barang berbeda ditolak; pengembalian yang merujuk serah terima gudang lain ditolak; gerakan stok yang tidak cocok dengan baris sumbernya ditolak trigger | Feature, DB | TASK-053, TASK-054 |
| DI-09 | Dua posting bersamaan untuk baris sumber yang sama (serah terima, penerimaan, pengembalian, transfer, opname) → satu sukses, satu ditolak identitas sumber, termasuk dengan kunci idempotensi berbeda | K, DB | TASK-050, TASK-051, TASK-053, TASK-054, TASK-055, TASK-056 |
| DI-10 | Dua pembalikan bersamaan atas gerakan stok yang sama → satu sukses; pembalikan dengan gudang/barang/jumlah/arah berbeda, tanpa sumber koreksi atau alasan, pembalikan atas pembalikan, atau pembalikan yang membuat saldo negatif ditolak | K, DB | TASK-050 |
| DI-11 | Transaksi aset: pembalikan ganda atau tidak cocok ditolak; sumber yang sama tidak membuat transaksi ganda; pembalikan atas transaksi yang bukan transaksi terakhir aset yang belum dibalik ditolak (gunakan koreksi WF-020) | K, DB | TASK-059 |
| DI-12 | Template: dua versi berlaku dengan jendela tumpang tindih ditolak; dua aktivasi bersamaan → satu gagal; aktivasi bertanggal mundur ditolak; pemilihan template paling spesifik dan versi menurut tanggal generasi deterministik; revisi dokumen lama memakai versi template yang tersimpan | Feature, K, DB | TASK-021 |
| DI-13 | Penomoran: dua skema ACTIVE untuk jenis dokumen yang sama ditolak; teks nomor yang sama pada skema berbeda untuk jenis dokumen yang sama ditolak | DB | TASK-020 |
| DI-14 | Kontrak: UPDATE/DELETE baris versi kontrak ditolak (hak + trigger); baris dengan `(contract_id, version_no)` yang tidak ada ditolak; adendum berlaku membuat versi baru dengan set baris lengkap tanpa mengubah versi lama; satu versi per adendum | Feature, DB | TASK-037, TASK-045 |
| DI-15 | Baris final beku: ubah/hapus baris HPS setelah SET, baris penerimaan setelah POSTED, baris opname setelah CLOSED, baris transfer setelah DISPATCHED (kecuali `qty_received` saat penerimaan), baris serah terima gudang, dan baris BAST setelah SIGNED (kecuali klasifikasi/rujukan hilir) ditolak; revisi HPS membuat versi baru | Feature, DB | TASK-029, TASK-046, TASK-051, TASK-053, TASK-055, TASK-056 |
| DI-16 | Rencana langkah dan penetapan metode mencatat versi setiap keluarga kebijakan penyumbang; versi baru salah satu keluarga yang berlaku pada tanggal acuan paket menghasilkan rencana versi baru, rencana lama mempertahankan rujukannya | Feature | TASK-018, TASK-028, TASK-030 |
| DI-17 | Kolom lingkup induk (`package_id`, `contract_id`, `request_id`, `warehouse_id`) tidak dapat diubah setelah insert | DB | TASK-008 (utilitas), TASK-033, TASK-035, TASK-036, TASK-037, TASK-046, TASK-052, TASK-053 |
| DI-18 | Dua versi klasifikasi aset berlaku dengan jendela tumpang tindih ditolak; aktivasi menutup jendela versi sebelumnya secara atomik; aktivasi bertanggal mundur ditolak | Feature, DB | TASK-058 |
| DI-19 | Baris BAST yang menunjuk baris kontrak dari kontrak lain ditolak | Feature, DB | TASK-046 |
| DI-20 | Kebijakan: jendela versi ACTIVE/SUPERSEDED yang tumpang tindih ditolak; dua aktivasi bersamaan untuk paket yang sama → satu gagal; aktivasi bertanggal mundur ditolak; resolver memilih versi SUPERSEDED untuk tanggal acuan di dalam jendelanya dan tidak pernah memilih RETIRED; keadaan WF-029 S4/S5/S6 diturunkan benar dan TR-286/TR-287 tercatat saat tanggal efektif | Feature, K, DB | TASK-018 |
