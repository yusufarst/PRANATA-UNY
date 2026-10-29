# Performa dan skala — PRANATA UNY

Status: P6 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P6

Dokumen ini memiliki **model beban, anggaran performa, strategi indeks/kueri, skenario uji performa dan perilaku kegagalan/pemulihan pada skala target**. Target P1 (NFR-P01–P04, AC-21) tetap berlaku; dokumen ini menurunkannya menjadi anggaran yang dapat diuji. Kapasitas pasti dan beban riil tetap GAP-016 sampai diukur pada staging dengan data sintetis.

## 1. Model beban (asumsi perencanaan)

| Parameter | Nilai perencanaan | Dasar |
|---|---|---|
| Pengguna terdaftar | ±200 internal + penyedia (ratusan, jarang aktif) | OD-16/32; SRC-052 |
| Pengguna aktif bersamaan puncak | 50 | Asumsi konservatif ±25% |
| Permintaan halaman puncak | 20 permintaan/detik selama 5 menit | Asumsi; diverifikasi uji beban |
| Aset | 1.000.000 baris aktif + riwayat | OD-16/22 |
| Transaksi aset | ±2–5 juta pada tahun pertama (termasuk impor) | Asumsi |
| Paket pengadaan | ±2.000–5.000/tahun | Asumsi; diverifikasi data riil |
| Movement stok | ±50.000/tahun per dua gudang | Asumsi |
| Dokumen dihasilkan | ±20.000/tahun | Asumsi |
| Audit | ±10–30 juta peristiwa/tahun | Turunan |

## 2. Anggaran performa

| ID | Skenario | Anggaran (p95, server staging setara produksi, data sintetis penuh) |
|---|---|---|
| PB-01 | Halaman daftar aset terfilter (unit + status + kata kunci) dengan 1 juta aset, halaman pertama 50 baris | TTFB ≤ 800 ms; hasil siap dipakai ≤ 3 detik (NFR-P01) |
| PB-02 | Halaman detail objek apa pun | TTFB ≤ 500 ms |
| PB-03 | Pencarian global 3+ karakter | ≤ 1,5 detik |
| PB-04 | Perintah biasa (simpan/transisi) | ≤ 700 ms |
| PB-05 | Serah terima 20 baris barang | ≤ 1 detik |
| PB-06 | Penerimaan operasi panjang (ekspor/impor/run) beserta ID status | ≤ 2 detik (NFR-P02) |
| PB-07 | Ekspor 100.000 baris aset XLSX | Selesai ≤ 3 menit, memori worker ≤ 256 MB (streaming) |
| PB-08 | Validasi intake 100.000 baris | ≤ 10 menit; kemajuan diperbarui ≤ setiap 10 detik |
| PB-09 | Run penyusutan 1 juta aset satu periode | ≤ 30 menit, chunk 5.000, tanpa mengunci pengguna lain |
| PB-10 | Generasi dokumen 1–10 halaman | ≤ 10 detik termasuk PDF |
| PB-11 | Dashboard per peran | ≤ 1,5 detik (agregat dari indeks/tabel ringkasan) |

Anggaran ini adalah target uji. Bila pengukuran gagal, eksekutor mencatat temuan dan mengajukan perbaikan atau penyesuaian anggaran melalui TASK PLAN CHANGE; anggaran tidak dilonggarkan diam-diam.

## 3. Strategi kueri dan indeks

| Pola | Strategi |
|---|---|
| Daftar besar | Keyset pagination; indeks komposit `(filter_utama…, id)`; tanpa `OFFSET` besar; tanpa `COUNT(*)` penuh pada tabel jutaan (perkiraan dari statistik atau batas "lebih dari 10.000") |
| Scope akses | Subtree unit memakai `ltree` (`path <@ ?`) dengan indeks GIST; grant diterjemahkan ke kondisi SQL, bukan filter PHP |
| Pencarian teks | GIN trigram, minimal 3 karakter, `LIMIT` per modul |
| Agregat dashboard | Kueri terindeks berbatas cakupan; bila > anggaran, tabel ringkasan yang diperbarui job/event (bukan cache yang dapat basi tanpa batas) |
| N+1 | `Model::preventLazyLoading()` di non-produksi; uji fitur menghitung jumlah kueri untuk halaman kunci |
| Ekspor | Cursor/`lazyById`, streaming OpenSpout, tanpa memuat semua baris ke memori |
| Impor | Pembaca streaming, insert batch ke staging, validasi berbasis set (SQL) bila memungkinkan |
| Rencana kueri | Uji performa menjalankan `EXPLAIN (ANALYZE, BUFFERS)` pada kueri kunci dengan data sintetis dan menyimpan hasilnya sebagai bukti Task |

## 4. Cache

- Konfigurasi/rute/view dicache saat deploy (`optimize`).
- Data referensi kecil (satuan, kategori, gudang, versi kebijakan ACTIVE) dicache singkat (≤ 10 menit) dan diinvalidasi saat berubah.
- **Izin dan scope akses tidak dicache lintas permintaan.**
- Tidak ada cache halaman penuh untuk konten terautentikasi.

## 5. Data sintetis skala

Generator data sintetis (factory + command `pranata:synthetic`) membuat: 1.000.000 aset terdistribusi ke ±300 unit dan ±200 klasifikasi, 3 juta transaksi aset, 5.000 paket dengan kontrak/dokumen, 2 gudang dengan 2.000 barang dan 100.000 movement, 200 pengguna dengan grant bervariasi. Data tidak memakai nama/nilai riil; diproduksi deterministik dari seed agar hasil uji dapat diulang.

## 6. Skenario uji performa

| ID | Alat | Isi | Lingkungan |
|---|---|---|---|
| PERF-01 | k6 (open-source) | Campuran baca 70% daftar/detail/pencarian, 30% perintah; 50 pengguna virtual; 15 menit | Staging dengan data sintetis penuh |
| PERF-02 | k6 | Lonjakan 20 permintaan/detik 5 menit | Staging |
| PERF-03 | Pest + data sintetis | Kueri kunci dengan EXPLAIN dan anggaran waktu | CI malam/manual |
| PERF-04 | Command terukur | Ekspor 100 ribu, impor 100 ribu, penyusutan 1 juta | Staging |
| PERF-05 | Playwright | Waktu muat halaman kunci di browser (Chromium) | Staging |

Uji performa penuh berjalan sebelum rilis yang mengubah kueri/tabel besar dan sebelum go-live (P10); uji ringan (PERF-03 subset) berjalan malam hari.

## 7. Kapasitas server awal (rekomendasi)

| Komponen | Rekomendasi minimum produksi |
|---|---|
| CPU/RAM | 4 vCPU, 8 GB RAM (tanpa ClamAV); 16 GB bila ClamAV diaktifkan |
| Disk | SSD 160 GB (basis data ±20–40 GB tahun pertama + berkas + WAL); pemantauan pertumbuhan |
| PostgreSQL | `shared_buffers` ±25% RAM, `work_mem` disetel per beban, `effective_cache_size` ±60–70% RAM, autovacuum disetel untuk tabel besar |
| PHP-FPM | `pm=dynamic`, jumlah child disesuaikan RAM; OPcache aktif |
| Worker | 2 worker `default`, 2 `documents`, 1 `exports`, 1 `imports`, 1 `maintenance` |

Nilai final ditetapkan setelah uji PERF-01/02 di staging dan dicatat di [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md); ukuran yang dibiayai klien menjadi keputusan Owner.

## 8. Kegagalan dan pemulihan pada skala

| Kegagalan | Perilaku yang diharapkan |
|---|---|
| Worker mati | Job tetap di tabel `jobs`; supervisor/systemd memulai ulang; operasi menampilkan status tertunda, bukan gagal palsu |
| Basis data penuh/tidak tersedia | Aplikasi menampilkan halaman pemeliharaan/galat; tidak ada tulisan parsial; peringatan operasi |
| Job besar gagal di tengah | Kemajuan per chunk tersimpan; dapat dilanjutkan dari chunk terakhir |
| Gotenberg lambat | Antrean `documents` menumpuk tanpa memengaruhi halaman lain |
| Pemulihan dari cadangan | Mengikuti runbook P9 dengan target desain RTO 4 jam / RPO 1 jam |
