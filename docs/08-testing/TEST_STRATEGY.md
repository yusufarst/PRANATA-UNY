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

Pengaman wajib: skrip uji menolak berjalan bila nama basis data tidak cocok `^pranata_(test|e2e|perf)` atau `APP_ENV=production`. Uji tidak pernah memakai basis data proyek lain di mesin yang sama (port, nama kontainer dan volume khusus `pranata`, [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md)).

## 5. E2E browser

- Playwright dengan proyek `chromium` dan `webkit`; viewport 390×844 dan 1440×900; lokal `id` dan `en` untuk perjalanan yang menandai pemeriksaan bahasa.
- Setiap uji mengumpulkan galat konsol dan permintaan gagal; galat kritis = gagal.
- Login memakai state penyimpanan per peran yang dibuat sekali per run (cepat), dengan satu uji login penuh terpisah.
- Selektor berbasis peran/label aksesibel (`getByRole`, `getByLabel`), bukan kelas CSS.
- Artefak saat gagal: trace, screenshot, video pendek; disimpan sebagai artefak CI 7 hari.
- Perjalanan wajib: J-01–J-18 ([USER_JOURNEYS](../07-ux-design/USER_JOURNEYS.md)); Task UI menambah atau memperbarui perjalanan yang disentuhnya.

## 6. Uji tidak stabil (flaky)

Uji yang gagal tidak konsisten dicatat sebagai temuan; boleh di-*quarantine* paling lama 3 hari kerja dengan issue terbuka dan pemilik, tidak boleh disembunyikan dengan retry tanpa batas. Retry CI maksimal 1 kali untuk E2E dan hasil retry dilaporkan. Penyebab umum (waktu, urutan, data bersama) diperbaiki pada akar.

## 7. Evidence per Task

Setiap Task mencatat: perintah uji yang dijalankan dan ringkasan hasil, jumlah uji, cakupan area yang diubah, tautan run CI, artefak E2E untuk Task UI, bukti EXPLAIN untuk Task kueri besar, dan daftar pemeriksaan keamanan/basis data. Format di [QUALITY_GATES](QUALITY_GATES.md) dan [EXECUTOR_OPERATING_MODEL](../11-tasks/EXECUTOR_OPERATING_MODEL.md).
