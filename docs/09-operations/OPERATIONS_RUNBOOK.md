# Runbook operasi — PRANATA UNY

Status: P9 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P9

Runbook ini ditujukan bagi **operator produksi yang ditunjuk Owner**, bukan agen AI. Setiap prosedur yang mengubah produksi mengikuti [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md): cadangan terverifikasi sebelum perubahan, perintah yang sudah direview, dan pencatatan. Perintah rinci (nama layanan, path) difinalkan pada Task infrastruktur sesuai server yang dipilih.

## RB-01 Deploy rilis

1. Pastikan rilis disetujui (P10) dan tag rilis tersedia; baca deklarasi `DB CHANGE` dan `ADDITIONAL RECURRING COST CHANGE`.
2. Verifikasi cadangan terakhir sukses dan WAL mengalir (RB-06).
3. Bila ada migrasi non-trivial: `php artisan down --render="errors::maintenance"` dengan pesan terjadwal.
4. Ambil kode tag, `composer install --no-dev --optimize-autoloader`, build aset frontend (atau unduh artefak build rilis).
5. Jalankan migrasi dengan koneksi peran pemilik: `php artisan migrate --database=migrator --force`.
6. `php artisan optimize`, restart PHP-FPM dan worker (`php artisan queue:restart`), cek scheduler.
7. `php artisan up`; jalankan smoke test (RB-02); pantau log 30 menit.

## RB-02 Smoke test pasca-deploy

Masuk sebagai akun uji non-produktif yang dibuat khusus (tanpa data riil), buka beranda tiap ruang kerja, buka satu daftar besar, generate satu dokumen uji pada objek uji, cek `/health/ready`, cek antrean kosong/berjalan, cek tidak ada lonjakan galat di log/Pulse.

## RB-03 Rollback

- Tanpa migrasi destruktif: deploy ulang tag sebelumnya (RB-01 tanpa migrasi), `migrate:rollback` hanya bila migrasi rilis tercatat reversibel dan aman.
- Dengan perubahan data yang tidak dapat dibalik: pemulihan PITR ke titik sebelum deploy (RB-07) setelah keputusan Owner; data setelah titik itu dicatat dan dimasukkan ulang terkendali.

## RB-04 Antrean dan scheduler

Cek `php artisan queue:monitor` (ambang per antrean), `queue:failed`; ulangi job gagal setelah penyebab diperbaiki (`queue:retry <id>`); restart worker setelah deploy; pastikan timer scheduler berjalan setiap menit.

## RB-05 Partisi audit dan pemeliharaan basis data

Scheduler membuat partisi `audit_events` tiga bulan ke depan dan `asset_depreciation_lines` tahun berikut; operator memeriksa laporan job bulanan. Autovacuum dipantau untuk tabel besar; `VACUUM (ANALYZE)` manual hanya pada jendela pemeliharaan.

## RB-06 Verifikasi cadangan harian

Periksa status pgBackRest (`info`), umur WAL terakhir < 10 menit, snapshot restic terakhir < 2 jam, sinkronisasi offsite sukses. Kegagalan = insiden S2.

## RB-07 Pemulihan (PITR) dan latihan restore

1. Tentukan titik waktu target dan alasan (insiden/latihan).
2. Siapkan server/instance tujuan (staging untuk latihan).
3. Restore basis data dengan pgBackRest ke titik waktu; restore berkas dengan restic.
4. Pasang `.env` dan `APP_KEY` dari cadangan konfigurasi terenkripsi.
5. Jalankan pemeriksaan integritas (jumlah baris kunci, verifikasi saldo ledger, checksum berkas sampel) serta job verifikasi integritas [CONCURRENCY_AND_INTEGRITY §7](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#7-verifikasi-integritas-berkala) untuk sidik jari tangkapan dan seluruh berkas bersegel.
6. Catat waktu mulai–selesai terhadap RTO dan selisih data terhadap RPO; simpan catatan latihan.

## RB-08 Reset password dibantu admin

Admin memverifikasi identitas sesuai prosedur (tatap muka/kartu pegawai/surat resmi penyedia), memilih metode verifikasi di form, menghasilkan password sementara, menyerahkannya langsung kepada pemilik akun, dan memastikan pengguna menggantinya saat masuk. Reset akun Super Admin atau akun istimewa memerlukan admin kedua.

## RB-09 Aktivasi kebijakan

Pengelola kebijakan menyusun versi sesuai keputusan resmi yang sudah tercatat di repositori (GAP RESOLVED), melampirkan bukti, menjalankan kasus uji; pengesah yang berbeda memeriksa diff, bukti dan hasil uji lalu mengaktifkan dengan tanggal berlaku. Setelah aktivasi: bila tanggal berlakunya sudah tercapai, periksa label simulasi hilang pada fitur terkait; bila tanggalnya di masa depan, periksa halaman kebijakan menampilkan "Terjadwal — berlaku mulai <tanggal>", versi pendahulu tetap dipakai sampai tanggal itu, dan ulangi pemeriksaan pada tanggal berlaku ([POLICY_REGISTRY §3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi)). Catat di log operasi.

## RB-10 Disk penuh atau mendekati penuh

Identifikasi pertumbuhan (log, WAL, berkas, staging intake); bersihkan log rotasi lama, pastikan arsip WAL terkirim, perluas disk bila perlu; jangan menghapus berkas aplikasi atau data. Disk > 90% = S1.

## RB-11 Gotenberg bermasalah

Cek health kontainer, restart kontainer Gotenberg saja, ulangi job `documents` yang gagal; DOCX tetap dapat diunduh selama gangguan.

## RB-12 Sertifikat TLS

Perpanjangan otomatis; peringatan 14 hari sebelum kedaluwarsa; uji perpanjangan setelah perubahan konfigurasi Nginx.

## RB-13 Offboarding pengguna

Nonaktifkan akun (DEACTIVATED), cabut sesi, akhiri penugasan aktif dengan pengganti yang ditunjuk, tinjau grant istimewa; riwayat tetap.

## RB-14 Permintaan koreksi data

Koreksi data bisnis selalu melalui fitur aplikasi (transaksi koreksi/pembalik dengan alasan) atau command koreksi yang dikembangkan melalui Task, direview, diuji pada salinan, dan dijalankan operator dengan cadangan terverifikasi. Tidak ada UPDATE/DELETE manual di basis data produksi.

## RB-15 Rotasi rahasia

Ganti kredensial DB/SMTP/kunci cadangan secara terjadwal atau saat personel berganti; `APP_KEY` hanya diganti dengan prosedur re-enkripsi rahasia 2FA yang direncanakan (atau reset 2FA semua pengguna setelah pemberitahuan).
