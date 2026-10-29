# Gerbang kualitas, Definition of Done dan pipeline CI — PRANATA UNY

Status: P8 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P8

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

Tingkat temuan: **P0** kerusakan/kehilangan data, celah keamanan atau pelanggaran aturan Owner; **P1** perilaku salah pada kasus utama atau uji/otorisasi yang hilang; **P2** kasus tepi, pemeliharaan, performa; **P3** saran. Status review Codex: `NOT_REQUESTED`, `PENDING`, `STARTED`, `COMPLETED_NO_FINDINGS`, `COMPLETED_WITH_FINDINGS`, `FAILED`, `TIMEOUT`; `TIMEOUT` dan `FAILED` **bukan** PASS (prosedur di EXECUTOR_OPERATING_MODEL).

## 4. Pipeline CI (GitHub Actions)

Dua workflow: `ci.yml` (PR dan `main`) dan `ci-nightly.yml` (terjadwal). Nama job di bawah dipakai sebagai nama check.

| Job | Workflow dan pemicu | Isi | Target durasi |
|---|---|---|---|
| `changes` | `ci.yml`: setiap PR ke `main` dan push ke `main` | Menentukan kelompok berkas yang berubah (kode aplikasi, E2E, dokumen, workflow) | < 1 menit |
| `ci-fast` | `ci.yml`, bila kode/konfigurasi/workflow berubah | Pint, Larastan, ESLint, Prettier, tsc, Vitest, Pest (paralel, PostgreSQL service), arch tests, route integrity, pemeriksa kunci terjemahan, `migrate:fresh`, build produksi, `composer audit`, `npm audit`, gitleaks | ≤ 10 menit (cache hangat), ≤ 15 menit run pertama |
| `ci-e2e` | `ci.yml`, bila `app/`, `resources/`, `routes/`, `database/`, `lang/`, `tests/e2e/` berubah | Build + server uji + Playwright Chromium & WebKit (sharded) + axe | ≤ 20 menit |
| `docs-check` | `ci.yml`, bila `docs/` atau berkas Markdown akar berubah | Pemeriksa tautan/anchor/ID dokumen dan konsistensi TASK_PLAN | ≤ 2 menit |
| `ci-gate` | `ci.yml`, selalu | Agregat: lulus hanya bila setiap job yang diperlukan menurut `changes` lulus | < 1 menit |
| `ci-nightly` | `ci-nightly.yml`: terjadwal pada `main` + manual | E2E penuh, uji performa ringan PERF-03, uji migrasi penuh, integrasi Gotenberg | Bebas |

Aturan kecepatan dan keandalan:

- `ci.yml` tidak berjalan pada setiap push cabang kerja (menghindari run ganda push+PR); pemeriksaan sebelum PR dijalankan lokal menurut piramida verifikasi [TEST_STRATEGY](TEST_STRATEGY.md). PR draft boleh dibuka lebih awal; CI berjalan pada setiap pembaruan PR.
- `concurrency` per PR dengan `cancel-in-progress: true` (run usang dibatalkan); run push ke `main` tidak dibatalkan agar setiap SHA `main` memiliki hasil.
- Filter path di dalam `ci.yml` (job `changes`) agar perubahan dokumentasi tidak menjalankan suite aplikasi.
- Cache Composer (`composer.lock`), npm (`package-lock.json`), browser Playwright (versi), dan build Vite antarjob dalam satu run (artefak).
- Pest paralel (`--parallel`) dengan basis data per proses; Playwright sharding 2–4.
- Service container `postgres` versi yang sama dengan produksi; Gotenberg hanya di `ci-nightly` dan Task dokumen.
- Tidak ada rahasia produksi di CI; variabel hanya untuk uji.
- Status check wajib sebelum merge ke `main` adalah satu job agregat **`ci-gate`** yang selalu berjalan pada setiap PR dan lulus hanya bila setiap job yang dipicu filter path (di dalam workflow, bukan filter tingkat workflow) lulus. Dengan begitu PR dokumentasi tidak menunggu check yang tidak dipicu, dan PR kode tidak dapat di-merge bila `ci-fast`/`ci-e2e` yang relevan gagal atau dilewati secara keliru.
- `main` dilindungi: hanya melalui PR, cabang harus mutakhir terhadap `main` sebelum merge, tanpa force push atau penghapusan; tag `v*` dilindungi. Produksi hanya dari tag rilis ([RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang)).
- Hasil run dapat dibedakan menurut pemicu (push cabang kerja, PR, `main` setelah merge, terjadwal) melalui nama workflow/job dan ringkasan run; artefak kegagalan (log, laporan Playwright, trace) disimpan menurut [TEST_STRATEGY §5](TEST_STRATEGY.md#5-e2e-browser).
- Penanganan kegagalan CI mengikuti prosedur akar masalah di [EXECUTOR_OPERATING_MODEL §7](../11-tasks/EXECUTOR_OPERATING_MODEL.md#7-kegagalan-ci); job wajib tidak pernah dinonaktifkan dan asersi valid tidak pernah dilemahkan untuk memperoleh status hijau.

## 5. Bukti di Task

Bagian COMPLETION EVIDENCE setiap Task berisi: perintah dan ringkasan hasil uji (jumlah lulus/gagal), tautan run CI dan SHA, daftar berkas berubah, hasil gerbang UI (bila UI), artefak E2E (tautan), bukti basis data (migrasi/rollback/hak peran), bukti performa (bila relevan), status review (Codex dan independen) dengan tingkat temuan, dan catatan keputusan. Bukti tidak memuat data pribadi atau rahasia.
