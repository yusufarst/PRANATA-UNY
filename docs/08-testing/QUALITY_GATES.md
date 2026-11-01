# Gerbang kualitas, Definition of Done dan pipeline CI — PRANATA UNY

Status: P8 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P8

Dokumen ini memiliki **status Task, Definition of Done per kelas risiko, gerbang review, desain pipeline CI dan aturan bukti**. Strategi uji dimiliki [TEST_STRATEGY](TEST_STRATEGY.md); operasi eksekutor dimiliki [EXECUTOR_OPERATING_MODEL](../11-tasks/EXECUTOR_OPERATING_MODEL.md); gerbang rilis dimiliki [RELEASE_PLAN](../10-release/RELEASE_PLAN.md).

## 1. Status Task dan tahap eksekusi

Status resmi mengikuti AICWDF §19: `TODO`, `READY`, `IN_PROGRESS`, `BLOCKED`, `VERIFYING`, `DONE`, `SUPERSEDED`. Tahap rinci GOV-12 dicatat pada field `EXECUTION STAGE`:

| Status | Tahap GOV-12 di dalamnya |
|---|---|
| READY | READY |
| IN_PROGRESS | IN_PROGRESS → IMPLEMENTED |
| VERIFYING | VERIFIED → REVIEWED → PR → CI → MERGED |
| DONE | DONE (setelah merge dan bukti lengkap) |
| BLOCKED | Tahap terakhir + alasan blokir |

`VERIFYING` bukan `DONE`. Checklist `[x]` di TASK_PLAN hanya untuk DONE dengan semua uji wajib PASS dan bukti ada.

## 2. Definition of Done

### 2.1 Semua Task

- [ ] Ruang lingkup sesuai kontrak Task; tidak ada perubahan di luar ruang lingkup tanpa TASK PLAN CHANGE.
- [ ] Pint, Larastan, ESLint, Prettier, `tsc` lulus tanpa penekanan baru yang tidak dijelaskan.
- [ ] Uji unit/feature/arsitektur relevan ditambah atau diperbarui dan lulus; uji yang ada tidak dilemahkan.
- [ ] Build produksi frontend lulus.
- [ ] `composer audit` dan `npm audit` tanpa temuan tinggi/kritis yang belum dikecualikan secara tercatat.
- [ ] Tidak ada rahasia, data pribadi atau data riil di diff/fixture.
- [ ] Dokumentasi yang dirujuk Task diperbarui bila Task menuntutnya (TOOLCHAIN, catatan modul).
- [ ] Penilaian dampak basis data dan biaya sesuai kontrak.
- [ ] Bukti tercatat di Task (bagian COMPLETION EVIDENCE).
- [ ] Bukti uji yang memerlukan PHP, PostgreSQL, Gotenberg atau browser berasal dari run GitHub-hosted pada SHA yang diklaim (OD-37); uji yang tidak dijalankan dicatat NOT RUN, bukan lulus.
- [ ] Commit memakai identitas Git Owner tanpa atribusi AI; metadata mentah diperiksa sebelum push (GOV-08).

### 2.2 Tambahan Task UI

- [ ] Semua teks dari katalog terjemahan; kunci hilang = 0; dua bahasa diperiksa.
- [ ] Keadaan memuat/kosong/galat/terlarang ada.
- [ ] Kembali/batal kontekstual sesuai RT; filter/daftar terjaga.
- [ ] Viewport 390×844 dan 1440×900 tanpa tumpang tindih/terpotong.
- [ ] E2E Chromium + WebKit untuk perjalanan yang disentuh; konsol/jaringan bersih.
- [ ] axe tanpa pelanggaran serius/kritis; fokus dan keyboard diperiksa.
- [ ] Gerbang konsistensi UI AICWDF §27 dicatat (PASS/FAIL per butir).

### 2.3 Tambahan Task HIGH

- [ ] Analisis dampak tertulis (modul, tabel, rute, izin yang terdampak).
- [ ] Uji otorisasi negatif untuk setiap aksi baru (pengguna lain unit, gudang lain, penyedia lain, Super Admin bila relevan).
- [ ] Uji konkurensi untuk invarian yang disentuh.
- [ ] Migrasi diuji: fresh, rollback bila reversibel, hak peran DB.
- [ ] Review independen sesuai bagian 3 selesai tanpa temuan P0/P1 terbuka.

## 3. Gerbang review berbasis risiko

| Kelas | Review wajib | Lulus bila |
|---|---|---|
| HIGH (auth, otorisasi, uang, ledger stok/aset, skema, isolasi, konkurensi kritis, keamanan, infrastruktur produksi, arsitektur besar) | Swa-review terstruktur + review PR otomatis (Codex) + review independen kedua (subagen reviewer segar atau manusia yang ditunjuk) | Tidak ada temuan P0/P1 terbuka; P2 diperbaiki atau dicatat dengan alasan |
| STANDARD | Swa-review terstruktur + review PR otomatis (Codex) | Tidak ada temuan P0/P1 terbuka |
| LOW (dokumentasi Task, teks, gaya tanpa logika) | Swa-review terstruktur + CI | CI hijau |

**Jalur pengaman.** Perubahan pada `.github/workflows/**`, `.claude/**`, `.githooks/**` dan `scripts/executor/**` — berapa pun kelas risiko Task-nya — hanya di-merge dalam mode SUPERVISED dengan Owner hadir dan meninjau diff; skrip merge menolaknya pada mode otonom ([EXECUTOR_OPERATING_MODEL §11](../11-tasks/EXECUTOR_OPERATING_MODEL.md#11-isolasi-multi-proyek-dan-keamanan-sumber-daya)).

Tingkat temuan: **P0** kerusakan/kehilangan data, celah keamanan atau pelanggaran aturan Owner; **P1** perilaku salah pada kasus utama atau uji/otorisasi yang hilang; **P2** kasus tepi, pemeliharaan, performa; **P3** saran. Status review Codex: `NOT_REQUESTED`, `PENDING`, `STARTED`, `COMPLETED_NO_FINDINGS`, `COMPLETED_WITH_FINDINGS`, `FAILED`, `TIMEOUT`; `TIMEOUT` dan `FAILED` **bukan** PASS (prosedur di EXECUTOR_OPERATING_MODEL).

## 4. Pipeline CI (GitHub Actions)

Dua workflow: `ci.yml` (PR dan `main`) dan `ci-nightly.yml` (terjadwal). Nama job di bawah dipakai sebagai nama check. Sebelum TASK-005 hanya ada workflow bootstrap sementara (bagian 4.1). Semua job berjalan di runner GitHub-hosted; runner self-hosted tidak dipakai (OD-37).

| Job | Workflow dan pemicu | Isi | Target durasi |
|---|---|---|---|
| `changes` | `ci.yml`: setiap PR ke `main` dan push ke `main` | Menentukan kelompok berkas yang berubah (kode aplikasi, E2E, dokumen, workflow) | < 1 menit |
| `ci-fast` | `ci.yml`, bila kode/konfigurasi/workflow berubah | Pint, Larastan, ESLint, Prettier, tsc, Vitest, Pest (paralel, PostgreSQL service), arch tests, route integrity, pemeriksa kunci terjemahan, `migrate:fresh`, build produksi, `composer audit`, `npm audit`, gitleaks | ≤ 10 menit (cache hangat), ≤ 15 menit run pertama |
| `ci-e2e` | `ci.yml`, bila `app/`, `resources/`, `routes/`, `database/`, `lang/`, `tests/e2e/`, `config/`, `bootstrap/`, `public/`, `composer.json`/`composer.lock`, `package.json`/`package-lock.json`, `vite.config.*`, `playwright.config.*`, `tsconfig*.json` atau `.github/workflows/` berubah | Build + server uji + asap HTTP + Gotenberg service (smoke dan Pest grup `gotenberg`) + Playwright Chromium & WebKit (sharded) + axe | ≤ 20 menit |
| `docs-check` | `ci.yml`, bila `docs/` atau berkas Markdown akar berubah | Pemeriksa tautan/anchor/ID dokumen dan konsistensi TASK_PLAN | ≤ 2 menit |
| `ci-gate` | `ci.yml`, selalu | Agregat: lulus hanya bila setiap job yang diperlukan menurut `changes` lulus | < 1 menit |
| `ci-nightly` | `ci-nightly.yml`: terjadwal pada `main` + manual | E2E penuh, uji performa ringan PERF-03, uji migrasi penuh, integrasi Gotenberg | Bebas |

Aturan kecepatan dan keandalan:

- `ci.yml` tidak berjalan pada setiap push cabang kerja (menghindari run ganda push+PR); pemeriksaan ringan sebelum PR dijalankan lokal dan pemeriksaan berat pada run PR GitHub-hosted menurut piramida verifikasi [TEST_STRATEGY §8](TEST_STRATEGY.md#8-piramida-verifikasi-dan-pemeriksaan-sebelum-pr). PR draft boleh dibuka lebih awal; CI berjalan pada setiap pembaruan PR.
- `concurrency` per PR dengan `cancel-in-progress: true` (run usang dibatalkan); run push ke `main` tidak dibatalkan agar setiap SHA `main` memiliki hasil.
- Filter path di dalam `ci.yml` (job `changes`) agar perubahan dokumentasi tidak menjalankan suite aplikasi.
- Cache Composer (`composer.lock`), npm (`package-lock.json`), browser Playwright (versi), dan build Vite antarjob dalam satu run (artefak).
- Pest paralel (`--parallel`) dengan basis data per proses; Playwright sharding 2–4.
- Service container `postgres` dengan major yang dipatok di TOOLCHAIN (sama dengan target staging/produksi); Gotenberg (image dipatok) berjalan di `ci-e2e` (smoke dan Pest grup `gotenberg`; wajib bila terpicu), di `ci-nightly` (integrasi mendalam; bukan gerbang PR) dan di job bootstrap TASK-003 (bagian 4.1). Pengujian ini tidak menetapkan cara deploy produksi ([INFRASTRUCTURE §4](../09-operations/INFRASTRUCTURE.md#4-topologi-staging-dan-produksi)).
- Tidak ada rahasia produksi di CI; variabel hanya untuk uji.
- Keamanan workflow: pemicu `pull_request` (bukan `pull_request_target`); `permissions` minimum (`contents: read` kecuali alasan tercatat); action dipatok SHA commit; image service dipatok; tanpa secret repositori untuk uji; data sintetis saja; workflow tidak commit, push atau membuat rilis (GOV-08). Artefak diunduh eksekutor dan di-commit dengan identitas Owner. Artefak repositori publik dapat diunduh pengguna GitHub lain, jadi tidak memuat rahasia, `APP_KEY` atau data riil.
- Status check wajib sebelum merge ke `main` adalah satu job agregat **`ci-gate`** yang selalu berjalan pada setiap PR dan lulus hanya bila setiap job yang dipicu filter path (di dalam workflow, bukan filter tingkat workflow) lulus. Dengan begitu PR dokumentasi tidak menunggu check yang tidak dipicu, dan PR kode tidak dapat di-merge bila `ci-fast`/`ci-e2e` yang relevan gagal atau dilewati secara keliru.
- Proteksi `main` mengikuti metode merge yang diputuskan Owner (D-08, [EXECUTOR_OPERATING_MODEL §8.2](../11-tasks/EXECUTOR_OPERATING_MODEL.md#82-metode-merge-keputusan-owner)): **opsi A** — ruleset dengan status check `ci-gate` wajib (commit harus lulus di ref lain lebih dahulu), riwayat linear, tanpa force push/penghapusan dan **tanpa** aturan PR wajib (PR-dahulu ditegakkan kebijakan, hook dan skrip merge eksekutor); **opsi B** — PR wajib + `ci-gate` wajib + cabang mutakhir. Tag `v*` dilindungi. Ruleset diterapkan setelah `ci-gate` terbukti (TASK-005); sebelum itu berlaku mode bootstrap ([EXECUTOR_OPERATING_MODEL §8.4](../11-tasks/EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada)). Produksi hanya dari tag rilis ([RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang)).
- Hasil run dapat dibedakan menurut pemicu (PR, push ke `main`, terjadwal, manual) melalui nama workflow/job dan ringkasan run; artefak kegagalan (log, laporan Playwright, trace) disimpan menurut [TEST_STRATEGY §5](TEST_STRATEGY.md#5-e2e-browser).
- Penanganan kegagalan CI mengikuti prosedur akar masalah di [EXECUTOR_OPERATING_MODEL §7](../11-tasks/EXECUTOR_OPERATING_MODEL.md#7-kegagalan-ci); job wajib tidak pernah dinonaktifkan dan asersi valid tidak pernah dilemahkan untuk memperoleh status hijau.

### 4.1 Workflow bootstrap sementara

Berlaku untuk TASK-002–TASK-004 sebelum `ci.yml` ada (OD-37, PCR-002). Berkas: `.github/workflows/bootstrap.yml` (workflow `bootstrap`). TASK-002 membuatnya, TASK-003 dan TASK-004 memperluasnya, lalu TASK-005 menggabungkannya ke `ci.yml` dan menghapusnya.

| Job | Ditambah | Isi minimum |
|---|---|---|
| `scaffold` | TASK-002, sementara | Membuat aplikasi dari starter kit di runner dan mengunggah artefak sumber + lockfile (tanpa `vendor/`, `node_modules/`, `.env` atau kunci); dihapus sebelum HEAD akhir PR TASK-002 |
| `app-boot` | TASK-002 | PostgreSQL service; `composer install`, `npm ci`, `npm run build`; `migrate:fresh` + rollback; server uji; `GET /login` 200 dan registrasi 404; Pest; Pint, ESLint, tsc; screenshot halaman masuk 390×844 dan 1440×900 sebagai artefak |
| `db-integration` | TASK-003 | Versi major PostgreSQL; ekstensi `pg_trgm`, `ltree`, `btree_gist`; `numeric`/`timestamptz`; Pest `--parallel` pada `pranata_test_<token>`; uji DatabaseGuard; Gotenberg service: health + konversi berkas sintetis |
| `quality` | TASK-004 | `composer qa`, `npm run qa`, uji negatif arch/rute, `composer audit`, `npm audit`, gitleaks |
| `bootstrap-gate` | TASK-002 | Selalu berjalan (`if: always()`, `needs` semua job lain); gagal bila ada job yang tidak `success`; mengunggah artefak kecil `bootstrap-evidence` (JSON): nomor PR, `github.event.pull_request.head.sha`, `github.event.pull_request.base.sha`, `github.sha` (commit uji-merge sintetis `refs/pull/<n>/merge`), SHA tree commit yang diuji, ID run dan attempt |

- Pemicu `pull_request` ke `main` saja: run dimulai dari PR draft dan tidak memerlukan workflow di `main` lebih dahulu. `concurrency` per PR dengan `cancel-in-progress`; `timeout-minutes` per job; `runs-on` berlabel runner GitHub-hosted (misalnya `ubuntu-24.04`), tidak pernah `self-hosted`.
- Aturan keamanan workflow bagian 4 berlaku penuh.
- **Gerbang merge bootstrap (fail-closed).** Run `pull_request` menguji commit uji-merge sintetis (`github.sha`), bukan SHA HEAD PR; karena itu bukti dikaitkan lewat metadata run dan artefak, bukan dugaan. Skrip `merge-verified.ps1 -Bootstrap` (diperluas TASK-002) menolak merge kecuali semua langkah berikut lulus:
  1. PR terbuka, base `main`, `mergeable` = `MERGEABLE`; SHA yang akan di-fast-forward = `headRefOid` saat ini; `main` remote adalah leluhurnya ([EXECUTOR_OPERATING_MODEL §8.1](../11-tasks/EXECUTOR_OPERATING_MODEL.md#81-pemeriksaan-sebelum-merge)).
  2. Run dicari lewat API Actions (`gh api repos/<owner>/<repo>/actions/workflows/bootstrap.yml/runs?event=pull_request&head_sha=<HEAD>`); dipakai run terbaru (ID dan `run_attempt` tertinggi) yang `pull_requests`-nya memuat nomor PR ini. Tidak ada run = tolak; PR yang konflik merge tidak menghasilkan run, jadi juga ditolak.
  3. Run `status = completed` dan `conclusion = success`. Daftar job attempt itu (`…/actions/runs/<id>/attempts/<n>/jobs`) memuat himpunan job wajib minimum yang dipatok di skrip per Task (TASK-002: `app-boot`, `bootstrap-gate`; TASK-003: + `db-integration`; TASK-004: + `quality`), dan setiap job dalam run berkesimpulan `success`. Job yang hilang, `skipped`, `cancelled`, `failure`, `timed_out`, `action_required` atau `neutral` = tolak.
  4. Artefak `bootstrap-evidence` run itu: nomor PR dan `head.sha` = HEAD saat ini; `base.sha` = `main` remote saat ini; tree yang diuji = `git rev-parse <HEAD>^{tree}`. Karena `main` leluhur HEAD, commit uji-merge dan target fast-forward berpohon sama. Selisih apa pun = tolak. Push baru atau `main` yang bergerak membuat bukti basi dan memerlukan run baru.
  5. ID run, attempt, SHA uji-merge, SHA HEAD dan SHA base dicatat di COMPLETION EVIDENCE.

  Ditambah review sesuai kelas risiko dan kesaksian Owner (mode SUPERVISED, [EXECUTOR_OPERATING_MODEL §8.4](../11-tasks/EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada)). Mekanisme ini **UNVERIFIED** sampai TASK-002 membuktikannya sebagai C-16 pada PR uji sekali pakai sebelum merge TASK-002: gerbang wajib menolak job gagal, dibatalkan atau dilewati, run yang hilang, bukti basi setelah push baru, base yang bergerak dan tree yang tidak cocok, serta menerima run hijau pada HEAD terbaru. PR uji ditutup tanpa merge. Pemicu `pull_request` dan permission baca-saja tetap; tidak ada izin bypass.
- Bukti di COMPLETION EVIDENCE: URL dan ID run, SHA HEAD, kesimpulan dan durasi per job, tautan artefak. Run lama tidak berlaku untuk SHA baru. Pemeriksaan yang tidak dijalankan dicatat NOT RUN.
- **Klasifikasi setelah konsolidasi (TASK-005).** `ci-gate` hanya mencakup job `ci.yml`; tidak ada pemeriksaan wajib yang dipindahkan hanya ke `ci-nightly`.

| Pemeriksaan bootstrap | Tujuan | Kelas gerbang |
|---|---|---|
| `app-boot`: `composer install`, `npm ci`, `npm run build`, `migrate:fresh` + rollback, Pest (termasuk rute masuk 200 dan registrasi 404), Pint, ESLint, tsc | `ci-fast` | PR-wajib lewat `ci-gate` bila kode, konfigurasi atau workflow berubah |
| `app-boot`: boot server uji dan `GET /login` 200 | Asap HTTP di `ci-e2e`; sejak TASK-007 perjalanan Playwright | PR-wajib lewat `ci-gate` bila `ci-e2e` terpicu |
| `app-boot`: screenshot halaman masuk | Tidak dipindahkan; bukti sekali TASK-002 (diganti E2E responsif TASK-007) | Bukan gerbang |
| `db-integration`: versi major, ekstensi, `numeric`/`timestamptz`, Pest `--parallel`, DatabaseGuard | `ci-fast` | PR-wajib |
| `db-integration`: Gotenberg health dan konversi sintetis | `ci-e2e` (smoke dan Pest grup `gotenberg`) | PR-wajib bila `ci-e2e` terpicu: kode aplikasi, konfigurasi, lockfile atau workflow, termasuk setiap perubahan Gotenberg |
| `quality`: `composer qa`, `npm run qa`, uji negatif arch/rute, `composer audit`, `npm audit`, gitleaks | `ci-fast` | PR-wajib |
| Integrasi Gotenberg mendalam (semua route, gagal-konversi, merge besar) | `ci-nightly` | Regresi terjadwal pada `main`; **tidak** dicakup `ci-gate`; kegagalan ditangani lebih dahulu menurut [EXECUTOR_OPERATING_MODEL §7](../11-tasks/EXECUTOR_OPERATING_MODEL.md#7-kegagalan-ci) |

- `bootstrap` bukan status check wajib ruleset; ruleset hanya memakai `ci-gate` (TASK-005).

## 5. Bukti di Task

Bagian COMPLETION EVIDENCE setiap Task berisi: perintah dan ringkasan hasil uji (jumlah lulus/gagal), tautan run CI dan SHA, daftar berkas berubah, hasil gerbang UI (bila UI), artefak E2E (tautan), bukti basis data (migrasi/rollback/hak peran), bukti performa (bila relevan), status review (Codex dan independen) dengan tingkat temuan, dan catatan keputusan. Bukti tidak memuat data pribadi atau rahasia.
