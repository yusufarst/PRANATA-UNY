# Infrastruktur, observabilitas, cadangan dan pemulihan — PRANATA UNY

Status: P9 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P9

Dokumen ini memiliki **lingkungan, topologi, kontainer pengembangan yang terisolasi, CI, staging/produksi, batas sumber daya, rahasia, logging/pemantauan, cadangan/pemulihan, keamanan server, inventaris biaya dan respons insiden**. Prosedur langkah demi langkah ada di [OPERATIONS_RUNBOOK](OPERATIONS_RUNBOOK.md). Pilihan yang memerlukan keputusan Owner ditandai **[KEPUTUSAN OWNER]**. Tidak ada infrastruktur yang dibuat oleh dokumen ini.

## 1. Lingkungan

| Lingkungan | Tujuan | Data | Siapa mengakses |
|---|---|---|---|
| Lokal (mesin Owner/eksekutor) | Pengembangan dan uji Task | Sintetis | Eksekutor (Claude Code) dan Owner |
| CI (GitHub Actions, runner GitHub) | Uji otomatis per PR | Sintetis, dibuat per job | GitHub |
| Staging | Integrasi, E2E penuh, uji performa, UAT | Sintetis atau teranonimkan yang disetujui | Tim UAT, operator |
| Produksi | Operasi resmi | Riil | Pengguna; operator produksi yang ditunjuk Owner **[KEPUTUSAN OWNER]** |

Agen eksekusi tidak memiliki kredensial staging/produksi; deploy dilakukan oleh operator atau workflow yang memerlukan persetujuan Owner. Repositori bersifat **publik**: tidak ada rahasia, konfigurasi produksi, data riil atau artefak sensitif di repositori, issue, PR atau log CI.

## 2. Pengembangan lokal terisolasi

Mesin Owner juga menjalankan MULTIPLECORP, BantuKasir dan ELLIGBLE. PRANATA wajib terisolasi penuh:

| Aspek | Aturan |
|---|---|
| Nama proyek Compose | `name: pranata` di `compose.yaml`; semua kontainer `pranata-*` |
| Layanan | `pranata-app` (PHP-FPM + Composer + ekstensi pgsql/intl/gd/zip/bcmath), `pranata-web` (Nginx), `pranata-postgres` (major sama dengan produksi), `pranata-gotenberg`, `pranata-mailpit` (opsional, uji email), Node/Vite di host atau kontainer `pranata-node` |
| Port host (khusus PRANATA) | Web `8180`, Vite `5183`, PostgreSQL `55432`, Mailpit UI `8125`; Gotenberg tidak diekspos ke host. Port dicek bebas sebelum start; bila bentrok, ubah di `.env` PRANATA saja |
| Volume | `pranata_pgdata`, `pranata_storage`, `pranata_composer_cache`, `pranata_node_modules` |
| Jaringan | `pranata_net` |
| Batas sumber daya dev | postgres 1 GB, app 1 GB, gotenberg 1 GB, CPU dibatasi per layanan |
| Larangan | Tidak menjalankan `docker system prune`, `docker volume prune`, `docker network prune`, restart Docker Desktop global, atau perintah yang menyentuh kontainer/volume/basis data/berkas `.env` proyek lain |
| Basis data | `pranata_dev`, `pranata_test*`, `pranata_e2e_*` hanya di `pranata-postgres` |

Kinerja bind mount dari sistem berkas Windows diverifikasi saat menyiapkan lingkungan (TASK-003); bila terlalu lambat, rekomendasi dicatat untuk keputusan Owner (mis. kloning di filesystem WSL2) tanpa mengubah lokasi repositori secara sepihak.

## 3. CI

GitHub Actions dengan runner GitHub (repositori publik — tanpa biaya menit). Desain workflow di [QUALITY_GATES](../08-testing/QUALITY_GATES.md). Rahasia CI hanya untuk uji. Cabang terlindungi: `main` (integrasi Task berbasis trunk) dengan status check wajib `ci-gate`, PR wajib dan larangan force push/penghapusan; tag rilis `v*` dilindungi dan produksi hanya dari tag ([RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang)). Pembuatan proteksi dilakukan Owner atau melalui Task berizin.

## 4. Topologi staging dan produksi

```mermaid
flowchart TB
  U[Pengguna] -->|HTTPS| N[Nginx<br/>TLS Let's Encrypt]
  N --> F[PHP-FPM Laravel]
  F --> P[(PostgreSQL)]
  F --> S[(Disk berkas privat)]
  W[Worker antrean<br/>systemd/supervisor] --> P
  W --> S
  W --> G[Gotenberg kontainer<br/>tanpa akses keluar]
  C[Scheduler<br/>systemd timer] --> F
  P -->|WAL + backup| B[pgBackRest repo lokal]
  B -->|terenkripsi| O[(Lokasi offsite)]
  S -->|restic terenkripsi| O
```

| Keputusan | Usulan | Status |
|---|---|---|
| Penyedia server | VPS yang dibiayai klien atau server UNY | **[KEPUTUSAN OWNER]** |
| Spesifikasi produksi | 4 vCPU / 8 GB RAM / SSD 160 GB (16 GB RAM bila ClamAV) | Usulan, divalidasi uji beban staging |
| Staging | Server terpisah yang lebih kecil (2 vCPU / 4 GB) atau VM terpisah | **[KEPUTUSAN OWNER]** |
| Domain | Subdomain UNY atau domain klien; TLS otomatis | **[KEPUTUSAN OWNER]** |
| Cara deploy | Skrip deploy idempoten (git pull tag → composer/npm build → migrate dengan peran pemilik → optimize → restart worker) dijalankan operator atau workflow GitHub manual dengan persetujuan lingkungan | Usulan |
| Kontainer di produksi | Gotenberg (dan ClamAV bila diaktifkan) sebagai kontainer; aplikasi native atau kontainer — dipilih saat TASK infrastruktur | Usulan |

## 5. Konfigurasi dan rahasia

`.env` produksi berada hanya di server (pemilik user layanan, izin 0600), tidak di repositori. Nilai wajib: `APP_KEY`, kredensial DB per peran (`DB_USERNAME` aplikasi, `DB_MIGRATOR_*`), `PRANATA_ALLOW_DRAFT_POLICIES=false`, konfigurasi mail (opsional), path penyimpanan, URL Gotenberg internal. `APP_KEY` dicadangkan terpisah secara terenkripsi. Rotasi rahasia mengikuti runbook.

## 6. Logging dan pemantauan

| Kebutuhan | Solusi (gratis/self-hosted) |
|---|---|
| Log aplikasi | Laravel log harian format JSON, logrotate, retensi 30 hari (aplikasi) / 90 hari (keamanan) — sampai kebijakan retensi GAP-023 |
| Metrik aplikasi | Laravel Pulse (first-party) di Administrasi, hanya Super Admin |
| Kesehatan | `/up` + `/health/ready` (DB, antrean, disk, Gotenberg) untuk pemantau |
| Uptime eksternal | Pemeriksa uptime gratis (mis. Uptime Kuma pada host terpisah milik klien, atau layanan free tier dengan risiko tercatat) **[KEPUTUSAN OWNER]** |
| Galat | Kartu exception Pulse + log; pelacak galat self-hosted (GlitchTip) hanya bila diputuskan kelak |
| Peringatan | Email (bila SMTP) atau kanal yang dipilih Owner untuk: situs mati, disk > 80%, antrean tertahan > 15 menit, job gagal, cadangan gagal, peristiwa keamanan (SECURITY_MODEL §9) **[KEPUTUSAN OWNER: penerima]** |

## 7. Cadangan dan pemulihan

| Objek | Metode | Frekuensi | Retensi usulan |
|---|---|---|---|
| PostgreSQL | pgBackRest: full mingguan, differential harian, arsip WAL kontinu (`archive_timeout` 300 detik) | Kontinu | 4 minggu full + WAL; 12 snapshot bulanan |
| Berkas privat | restic snapshot | Per jam (inkremental) + harian | 30 harian, 12 bulanan |
| Konfigurasi server dan `APP_KEY` | Salinan terenkripsi terpisah | Saat berubah | Semua versi |
| Lokasi offsite | Server UNY kedua, penyimpanan objek, atau lokasi lain yang dibiayai klien; terenkripsi di sisi klien | — | **[KEPUTUSAN OWNER]** |

Target desain **RTO ≤ 4 jam, RPO ≤ 1 jam** (OD-32, NFR-O01/O02). Dengan WAL setiap ≤ 5 menit ke repositori lokal dan sinkronisasi offsite ≤ 15 menit, RPO teoretis jauh di bawah 1 jam; klaim hanya sah setelah uji restore nyata:

- **Latihan restore bulanan** ke staging (basis data PITR ke waktu tertentu + berkas), mengukur durasi dan memverifikasi integritas (jumlah baris kunci, verifikasi saldo ledger, checksum berkas sampel).
- **Latihan pemulihan bencana triwulanan** dari offsite ke server bersih mengikuti runbook, mencatat waktu aktual terhadap RTO.
- Hasil dicatat sebagai bukti operasi; kegagalan memicu tindakan korektif sebelum rilis berikutnya.

## 8. Keamanan server

SSH dengan kunci saja, tanpa login root, port SSH dibatasi; firewall hanya 80/443 publik; fail2ban; pembaruan keamanan otomatis untuk OS; jendela pemeliharaan bulanan untuk PHP/Nginx/PostgreSQL minor; PostgreSQL hanya mendengar localhost/jaringan privat; Gotenberg tanpa akses jaringan keluar; peran DB terpisah (DATA_MODEL §2); `statement_timeout` per peran; audit akses server oleh operator.

## 9. Inventaris biaya berulang

| Item | Biaya tambahan | Catatan |
|---|---|---|
| Server produksi + staging | Dibiayai klien | **[KEPUTUSAN OWNER]** |
| Domain/TLS | Domain dibiayai klien atau subdomain UNY; TLS gratis | |
| Penyimpanan offsite | 0 bila server UNY kedua; berbayar bila penyimpanan objek (pengecualian berbayar perlu persetujuan) | **[KEPUTUSAN OWNER]** |
| SMTP | 0 bila tidak dipakai; berbayar bila layanan pihak ketiga | Opsional |
| Semua perangkat lunak | 0 (open-source/first-party) | Laravel, PostgreSQL, Nginx, Gotenberg, pgBackRest, restic, Pulse, Playwright, k6 |
| CI | 0 (repositori publik) | Bila repositori menjadi privat, kuota menit perlu ditinjau |

Tidak ada pengecualian berbayar yang disetujui saat ini.

## 10. Respons insiden

| Tingkat | Contoh | Respons |
|---|---|---|
| S1 | Layanan mati, kebocoran data, data rusak | Segera; operator + Owner; pertimbangkan mode pemeliharaan; pemulihan dari cadangan bila perlu |
| S2 | Fitur inti tidak dapat dipakai (dokumen, serah terima) | Hari kerja yang sama |
| S3 | Fitur sekunder terganggu | Rilis perbaikan terjadwal |
| S4 | Kosmetik | Backlog |

Setiap S1/S2 menghasilkan catatan insiden (kronologi, dampak, akar masalah, tindakan) tanpa data pribadi. Agen eksekusi tidak bertindak pada produksi; perbaikan melalui Task dan rilis.
