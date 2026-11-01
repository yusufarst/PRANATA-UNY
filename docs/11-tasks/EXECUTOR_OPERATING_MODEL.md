# Model operasi eksekutor — PRANATA UNY

Status: P11 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P11

Dokumen ini memiliki **siklus eksekusi Task GOV-12, pemilihan Task, aturan konteks, review berbasis risiko dan review PR Codex, penanganan kegagalan CI, merge dan rekonsiliasi DONE, runner/watcher/checkpoint, hold dan shutdown aman, isolasi multi-proyek, pelaporan dan kemampuan platform yang wajib diverifikasi**. Status Task, Definition of Done dan desain CI dimiliki [QUALITY_GATES](../08-testing/QUALITY_GATES.md); strategi uji dimiliki [TEST_STRATEGY](../08-testing/TEST_STRATEGY.md); urutan langkah memulai dimiliki [EXECUTOR_BOOTSTRAP](EXECUTOR_BOOTSTRAP.md).

Model ini **dirancang sekarang dan tidak aktif** (GOV-12). Tidak ada kemampuan platform yang diklaim sebelum diuji pada TASK-001; setiap asumsi kemampuan tercantum di bagian 13 dengan cara verifikasi dan jalan cadangannya. MULTIPLECORP hanya rujukan metodologis.

## 1. Prasyarat aktivasi

**Prasyarat mode SUPERVISED** (cukup untuk memulai TASK-001):

| Prasyarat | Bukti | Pemutus |
|---|---|---|
| Kandidat P0–P3 (amandemen) dan P4–P11 disetujui pada SHA yang direview | [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md) | Owner |
| Planning Freeze dan baseline Task dinyatakan siap; TASK-001 READY | APPROVAL_RECORDS, [PHASE_STATUS](../PHASE_STATUS.md), TASK_PLAN | Owner |
| Otorisasi eksekusi berdiri dengan ruang lingkup (milestone/Task, mode, izin merge, batas) | APPROVAL_RECORDS | Owner |
| Keputusan metode merge (D-08), cakupan token eksekutor (D-27) dan model gerbang GAP (D-28) | Jawaban paket keputusan Owner | Owner |

**Prasyarat tambahan mode AUTONOMOUS:**

| Prasyarat | Bukti | Pemutus |
|---|---|---|
| TASK-001 DONE dengan laporan kemampuan; kemampuan pengaman (C-01, C-02, C-05, C-15, serta C-10 bila D-08 memilih opsi A) VERIFIED | `docs/11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md` | Eksekutor, disaksikan Owner |
| TASK-002–TASK-005 DONE (TASK-001–TASK-004 dalam mode bootstrap, bagian 8.4); `ci-gate` terbukti pada PR | PR dan run CI | Eksekutor, disaksikan Owner |
| Ruleset `main` dan perlindungan tag diterapkan sesuai D-08 dan diverifikasi | Keluaran `gh api` baca aturan cabang | Owner |
| Token eksekutor sesuai D-27 terpasang khusus untuk runner | Laporan kemampuan C-15 | Owner |
| Otorisasi mode otonom dengan ruang lingkup | APPROVAL_RECORDS | Owner |

Mode eksekutor:

| Mode | Kapan | Perilaku |
|---|---|---|
| `WAITING` | Sekarang sampai otorisasi | Bootstrap baca-saja; laporan; tidak mengubah apa pun ([EXECUTOR_BOOTSTRAP §2](EXECUTOR_BOOTSTRAP.md#2-bootstrap-baca-saja-waiting)) |
| `SUPERVISED` | TASK-001–TASK-005 (wajib), setiap merge yang menyentuh jalur pengaman (bagian 11), dan Task lain bila Owner memilih | Sesi interaktif Claude Code dengan Owner hadir; setiap izin yang belum ada di allowlist ditanyakan |
| `AUTONOMOUS` | Setelah semua prasyarat tambahan di atas | Runner headless + satu watcher (bagian 9); tanpa interaksi rutin Owner |

**Otorisasi yang berlaku (AUTH-013, 2026-11-01).** Owner memberi otorisasi berdiri untuk TASK-001–TASK-078 dengan otonomi maksimum yang aman (D-04 diganti): TASK-001–TASK-005 sekali dalam mode bootstrap, kemudian mode AUTONOMOUS segera setelah prasyarat tambahan di atas terpenuhi, tanpa perluasan bertahap per milestone dan tanpa persetujuan per Task. Pengecualian dan kontrol yang dipertahankan tercantum di [APPROVAL_RECORDS AUTH-013](../00-governance/APPROVAL_RECORDS.md#auth-013).

Bila kemampuan pengaman (hook penolakan, pembatasan izin, penjaga atribusi) gagal pada saat mana pun, eksekutor **turun ke SUPERVISED**, menghentikan runner, mencatat alasannya dan melapor. Mode yang melewati semua izin (bypass permissions) **tidak pernah dipakai**; langkah headless memakai allowlist dan alat di luar allowlist ditolak.

## 2. Siklus hidup Task

Siklus GOV-12: **READY → IN_PROGRESS → IMPLEMENTED → VERIFIED → REVIEWED → PR → CI → MERGED → DONE → Task berikutnya READY**. Tahap dicatat di field `EXECUTION STAGE` Task dan di checkpoint; status AICWDF mengikuti pemetaan [QUALITY_GATES §1](../08-testing/QUALITY_GATES.md#1-status-task-dan-tahap-eksekusi).

| Tahap | Masuk bila | Aktivitas | Bukti keluar |
|---|---|---|---|
| READY | Gerbang AICWDF §22.8: semua DEPENDS ON DONE, prasyarat Owner terpenuhi, referensi ada, tidak ada ambiguitas yang belum diputus | Catat READY di TASK_PLAN (lewat commit rekonsiliasi Task sebelumnya atau commit pertama cabang Task) | Baris TASK_PLAN, checkpoint |
| IN_PROGRESS | Eksekutor mengklaim Task (kunci checkpoint) | Cabang dari `main` terbaru; analisis dampak terarah; implementasi terkecil yang koheren; uji ditulis bersama perilaku | Commit di cabang Task |
| IMPLEMENTED | Seluruh IN SCOPE dikerjakan | Periksa diri terhadap daftar `TAC-nn` dan OUT OF SCOPE | Daftar periksa |
| VERIFIED | Piramida verifikasi lokal lulus ([TEST_STRATEGY §8](../08-testing/TEST_STRATEGY.md#8-piramida-verifikasi-dan-pemeriksaan-sebelum-pr)) | Statis, uji terarah, uji relevan | Ringkasan perintah dan hasil di COMPLETION EVIDENCE |
| REVIEWED | Swa-review terstruktur selesai; review independen selesai untuk HIGH; temuan valid diperbaiki dan diverifikasi delta | Bagian 6 | Catatan review dengan tingkat temuan |
| PR | PR siap review dibuka ke `main` dengan bukti terisi; review Codex diminta | Templat PR [EXECUTOR_BOOTSTRAP §6](EXECUTOR_BOOTSTRAP.md#6-konvensi-cabang-commit-dan-pr) | URL PR, SHA HEAD |
| CI | `ci-gate` selesai pada HEAD terakhir; status Codex final atau jalan cadangan selesai | Bagian 6 dan 7 | URL run, SHA, status review |
| MERGED | Semua pemeriksaan bagian 8 lulus | Merge dengan metode yang diputuskan Owner; verifikasi `main` remote | SHA `main` |
| DONE | Rekonsiliasi tercatat di `main` | PR rekonsiliasi dokumen (bagian 8.3) | Status DONE, checkbox, penghitung, CURRENT_HANDOFF |

Langkah otomatis per Task (U-007 §9): baca Task dan konteks minimum → periksa prasyarat → buat/pakai cabang Task → implementasi kontrak → uji regresi → pemeriksaan relevan → review independen bila wajib → perbaiki temuan valid → commit dan push → buat PR → pantau CI → proses review Codex → perbaiki isu CI/review yang valid → merge bila semua gerbang wajib lulus → verifikasi `main` remote → rekonsiliasi catatan penyelesaian → pilih Task berikutnya yang memenuhi syarat → lanjut tanpa interaksi rutin Owner.

Pembedaan yang tidak boleh dikaburkan (U-007 §19): kode diimplementasi ≠ pemeriksaan lokal lulus ≠ review independen selesai ≠ CI lulus ≠ layak merge ≠ PR benar-benar ter-merge ≠ DONE terekonsiliasi di `main`. Rencana tindakan yang belum terjadi tidak pernah dilaporkan sebagai hasil.

## 3. Pemilihan Task, blokir dan paralelisme

**Kelayakan.** Task layak bila berstatus TODO, semua DEPENDS ON berstatus DONE, prasyarat Owner pada bagian PREREQUISITES terpenuhi, dan Task tercakup otorisasi eksekusi. Menurut model gerbang usulan, GAP OPEN **bukan** penghalang implementasi bila Task memakai kebijakan berversi berlabel simulasi ([POLICY_REGISTRY §2.1](../04-architecture/POLICY_REGISTRY.md#21-hubungan-dengan-gap_register)); ia penghalang aktivasi resmi di produksi. Model ini berlaku setelah Owner mengonfirmasinya (D-28); sebelum itu Task yang aksi resminya bergantung pada GAP gerbang aktivasi ([TRACEABILITY_MATRIX — Perlakuan GAP](TRACEABILITY_MATRIX.md#perlakuan-gap)) tidak dianggap layak kecuali Owner menyatakan lain; GAP gerbang rilis tidak menahan implementasi.

**Urutan.** Default: Task layak bernomor terkecil. Owner dapat menetapkan prioritas lain melalui TASK PLAN CHANGE. Task yang menunggu keputusan Owner (misalnya TASK-040 hosting staging) dilewati dengan catatan "menunggu keputusan Owner: …" tanpa menahan Task lain.

**Blokir.** Task menjadi BLOCKED hanya karena penghalang nyata: dependensi BLOCKED, keputusan Owner yang belum ada, kemampuan platform yang gagal, konflik dengan dokumen kanonik, atau kegagalan CI yang tidak terselesaikan menurut bagian 7. Blokir dicatat dengan alasan, bukti dan pemutus yang dibutuhkan; eksekutor melanjutkan Task lain yang layak dan tidak bergantung padanya — kecuali hold `SHUTDOWN_AFTER_CURRENT_TASK` aktif, yang selalu didahulukan: eksekutor berhenti dan tidak memilih Task lain. Dependensi yang benar-benar menghalangi tidak pernah dilewati.

**Paralelisme.** Satu eksekutor utama, satu Task aktif. Task kedua hanya boleh berjalan bersamaan bila keduanya `PARALLEL SAFE: YES` dan:

- area berkas (EXPECTED MODULES / FILE AREAS) tidak beririsan;
- paling banyak satu dari keduanya memiliki migrasi;
- worktree Git terpisah di folder khusus PRANATA (`<induk repo>/pranata-uny-worktrees/task-0xx`), tanpa menyentuh folder proyek lain;
- basis data uji terpisah (`pranata_test_t0xx`, `pranata_e2e_t0xx_<run>`);
- satu koordinator verifikasi: suite berat (E2E, migrasi penuh) diserialkan dengan kunci `.pranata/executor/verify.lock`;
- integrasi ke `main` berurutan; cabang yang tertinggal tidak di-rebase dengan force push (bagian 8.2).

Paralelisme tidak dipakai bila tidak terbukti mempercepat total waktu pada sumber daya mesin Owner.

## 4. Konteks minimum, kompaksi dan subagen

Urutan baca per Task: [AGENTS.md](../../AGENTS.md) → [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md) → [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md) → tabel status [TASK_PLAN](TASK_PLAN.md) → Task aktif → bagian dokumen yang dirujuk Task → berkas sumber yang relevan. Seluruh pustaka P0–P11 tidak dibaca ulang per Task.

**Paket konteks.** Runner membangkitkan `.pranata/executor/context.md` (diabaikan Git) dari status repositori pada setiap transisi tahap: otorisasi aktif dan ruang lingkupnya, Task aktif dan tahapnya, referensi persis, dependensi, cabang/HEAD, PR dan status CI/Codex, temuan terbuka, perintah uji yang relevan, dan **satu tindakan berikutnya yang persis**. Sesi baru harus dapat merekonstruksi keadaan dari repositori dan paket ini, bukan dari percakapan tersembunyi.

**Kompaksi.** Kompaksi bawaan dipakai bila perlu; sesi tidak dimulai ulang hanya karena konteks tinggi. Setelah kompaksi, eksekutor membaca ulang EXECUTION_CONTEXT, Task aktif dan checkpoint sebelum bertindak.

**Penulisan catatan.** CURRENT_HANDOFF diperbarui pada batas Task (DONE, BLOCKED, hold, resume), bukan pada setiap langkah kecil; checkpoint diperbarui pada setiap transisi tahap. Tidak membuat dokumen handoff tambahan untuk operasi rutin.

**Subagen.** Hanya untuk pekerjaan terbatas yang independen: review independen Task HIGH, review pengganti saat Codex tidak tersedia, riset terarah yang tidak mengubah berkas. Tidak menugaskan beberapa agen untuk analisis yang sama.

**Riset versi.** Temuan Context7/dokumentasi resmi dicatat di [TOOLCHAIN](../00-governance/TOOLCHAIN.md) dengan versi dan tanggal; tidak diulang selama versi tidak berubah.

## 5. Implementasi, verifikasi dan commit

- Cabang dari `main` terbaru: `feature/task-0xx-<slug>`, `chore/task-0xx-<slug>` (perkakas/infrastruktur), `fix/task-0xx-<slug>`; rekonsiliasi `docs/reconcile-task-0xx`. Tanpa nama alat AI (GOV-09).
- Solusi terkecil yang koheren; pakai ulang arsitektur dan komponen yang ada; tanpa refaktor spekulatif, desain ulang UI yang tidak terkait, atau membuka ulang keputusan arsitektur tanpa cacat nyata.
- Verifikasi mengikuti piramida [TEST_STRATEGY §8](../08-testing/TEST_STRATEGY.md#8-piramida-verifikasi-dan-pemeriksaan-sebelum-pr) dan isolasi basis data [TEST_STRATEGY §4](../08-testing/TEST_STRATEGY.md#4-isolasi-basis-data-uji). Suite yang mengubah basis data tidak dijalankan bersamaan pada basis data yang sama.
- Proses panjang (suite, migrasi, build) dijalankan dengan log di `.pranata/logs/` dan dipantau kemajuannya; proses hanya dihentikan bila terbukti macet (tanpa keluaran dan tanpa aktivitas CPU melebihi ambang bagian 14), dan hanya PID milik PRANATA.
- Dependensi baru hanya bila dalam ruang lingkup Task, sumber terbuka/gratis, lolos `composer audit`/`npm audit`, dan dicatat di TOOLCHAIN.
- **Commit (GOV-08).** Sebelum setiap commit: `git config user.name` dan `user.email` dibaca (tanpa diubah) dan dibandingkan dengan identitas Owner rujukan; ketidakcocokan = berhenti. Pesan: `<type>(task-0xx): <ringkasan>`; tanpa trailer atribusi apa pun. Setelah commit dan sebelum push: metadata mentah (`git cat-file -p HEAD`) dan pesan lengkap diperiksa — author dan committer Owner saja, tanpa co-author AI/alat, tanpa trailer atribusi. Penyimpangan = tidak push, tidak amend otomatis, laporkan ke Owner. Tanpa force push.
- **Jalur pengaman.** `.github/workflows/**`, `.claude/**`, `.githooks/**` dan `scripts/executor/**` hanya diubah oleh Task yang kontraknya mencakupnya (TASK-001, TASK-005) atau dengan persetujuan Owner; pada mode otonom penulisan ke jalur ini ditolak hook dan aturan izin, dan merge PR yang menyentuhnya hanya dalam mode SUPERVISED (bagian 8.1).
- **Penjaga atribusi berlapis.** (1) Pengaturan atribusi Claude Code proyek dinonaktifkan untuk commit dan PR; (2) hook Git `commit-msg` repositori yang menolak trailer atribusi AI/alat; (3) hook PreToolUse yang menolak perintah commit/PR yang memuat atribusi; (4) pemeriksaan metadata pasca-commit. Pengaturan alat perlu tetapi tidak pernah cukup.

## 6. Review berbasis risiko dan review PR Codex

### 6.1 Kelas risiko dan alur review

Kelas dan syarat lulus mengikuti [QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko). Alur yang dipilih: **satu review substantif awal → temuan valid diperbaiki → verifikasi delta terarah → gerbang wajib final**.

- **Swa-review terstruktur** (semua Task): ruang lingkup vs kontrak; setiap `TAC-nn` terbukti; uji ditambah dan tidak dilemahkan; uji otorisasi negatif; keamanan basis data; gerbang UI (bila UI); kunci terjemahan; rahasia/data pribadi; kebersihan diff.
- **Review independen** (HIGH): subagen peninjau segar tanpa konteks implementasi menerima kontrak Task, diff `main...HEAD` dan bagian dokumen yang dirujuk; tugasnya hanya menemukan cacat, mengklasifikasikan P0–P3 dengan lokasi dan skenario kegagalan, tanpa mengubah berkas.
- **Review ulang penuh** hanya bila implementasi berubah material secara arsitektur, perbaikan memperluas permukaan risiko, muncul temuan serius baru, atau kontrak Task menuntutnya. Tidak ada batas jumlah review buatan yang memungkinkan cacat kritis yang diketahui ter-merge.

### 6.2 Temuan

P0/P1 selalu menghalangi. P2 menghalangi bila melanggar kriteria penerimaan atau perilaku integritas/keamanan/aksesibilitas yang wajib. P3 tidak memicu siklus review penuh kecuali melanggar persyaratan wajib. Temuan yang tidak valid dijawab dengan alasan; temuan yang ditunda dicatat jujur di COMPLETION EVIDENCE (dan GAP/issue bila perlu). Cacat nyata tidak pernah diturunkan kelasnya demi mempercepat merge.

### 6.3 Review PR Codex

Status: `NOT_REQUESTED`, `PENDING`, `STARTED`, `COMPLETED_NO_FINDINGS`, `COMPLETED_WITH_FINDINGS`, `FAILED`, `TIMEOUT`.

| Langkah | Aturan |
|---|---|
| Permintaan | Saat PR siap review, review diminta satu kali dengan mekanisme yang terverifikasi pada TASK-001 (misalnya komentar permintaan review atau review otomatis repositori — tidak keduanya). Status `PENDING`; waktu permintaan dicatat |
| Konfirmasi mulai | Sinyal mulai yang terverifikasi pada TASK-001 (misalnya reaksi atau komentar dari akun bot Codex) dibaca dengan `gh api`. Bila tidak ada konfirmasi dalam jendela konfirmasi (bagian 14), diminta ulang **sekali**; bila tetap tidak ada → `FAILED` (tidak dimulai) |
| Menunggu hasil | Batas waktu review dihitung **sejak mulai yang terkonfirmasi**, bukan sejak push atau pembuatan PR. Melewati batas → `TIMEOUT` |
| Selesai | Review/komentar akhir bot → `COMPLETED_NO_FINDINGS` atau `COMPLETED_WITH_FINDINGS`; tautan sumber dicatat |
| Temuan | Setiap temuan dinilai validitasnya; yang valid diperbaiki dan diverifikasi delta; yang tidak valid dijawab dengan alasan di PR. Perbaikan kecil tidak menuntut review Codex penuh baru kecuali risiko berubah; bila Codex otomatis meninjau push baru, temuan barunya tetap diproses |
| Jalan cadangan | Bila `FAILED`, `TIMEOUT` atau Codex tidak tersedia: **review pengganti** oleh subagen peninjau segar atas diff final PR dengan cakupan setara (kebenaran, keamanan, regresi), dicatat sebagai pengganti. Untuk HIGH, review pengganti ini terpisah dari review independen bagian 6.1 |
| Kejujuran | `TIMEOUT` dan `FAILED` bukan PASS. Hasil review tidak pernah dikarang. Tidak ada check CI wajib yang dilewati karena Codex tidak tersedia |
| Hasil terlambat | Hasil Codex yang datang setelah merge tetap diproses. Temuan P0/P1 yang valid mengembalikan Task ke VERIFYING (checkbox dibuka, penghitung disesuaikan) sampai perbaikan `fix/task-0xx-…` ter-merge; perbaikan didahulukan sebelum Task baru |

Selama menunggu CI atau review, eksekutor boleh menyiapkan Task berikutnya secara baca-saja (membaca kontrak dan referensi), tetapi tidak membuat perubahan untuk Task lain.

## 7. Kegagalan CI

Prosedur akar masalah (U-007 §11.2):

1. Identifikasi SHA yang diuji dan pastikan itu HEAD PR terakhir; notifikasi kegagalan untuk SHA lama tidak dianggap masalah HEAD terbaru.
2. Baca job yang gagal secara persis (`gh run view --log-failed`).
3. Baca log dan artefak yang relevan (laporan Playwright, trace).
4. Tentukan akar masalah. Kegagalan `globalSetup`/fixture bersama diklasifikasikan sebagai **kegagalan setup**, bukan puluhan cacat UI ([TEST_STRATEGY §9](../08-testing/TEST_STRATEGY.md#9-keandalan-fixture-dan-kegagalan-setup)).
5. Terapkan perbaikan terarah.
6. Jalankan uji regresi terfokus secara lokal.
7. Push perbaikan.
8. Verifikasi run CI baru pada SHA baru.

Aturan:

- Kode yang tidak berubah tidak dijalankan ulang berulang-ulang. Satu kali jalankan ulang hanya bila log menunjukkan penyebab transien infrastruktur (misalnya galat jaringan runner) dan dicatat.
- Asersi yang valid tidak dilemahkan; job wajib tidak dinonaktifkan; uji tidak dikarantina di luar kebijakan [TEST_STRATEGY §6](../08-testing/TEST_STRATEGY.md#6-uji-tidak-stabil-flaky).
- Bila akar masalah yang sama gagal diperbaiki sebanyak batas percobaan (bagian 14), Task menjadi BLOCKED dengan diagnosis, dan eksekutor beralih ke Task lain yang layak.
- Kegagalan pada `main` setelah merge (run push ke `main` atau `ci-nightly`) didahulukan sebelum Task baru: perbaikan `fix/…` dengan prosedur yang sama.

## 8. Merge dan rekonsiliasi DONE

### 8.1 Pemeriksaan sebelum merge

1. SHA HEAD PR sama dengan SHA yang diverifikasi dan direview.
2. `ci-gate` sukses pada SHA tersebut (`gh pr checks`, `gh api …/commits/<sha>/check-runs`).
3. Disposisi review lengkap: swa-review, review independen (HIGH), status Codex final atau review pengganti, tanpa temuan penghalang terbuka.
4. Cabang mutakhir: `main` adalah leluhur HEAD PR dan `main` tidak bergerak sejak pemeriksaan.
5. PR dapat di-merge dan otorisasi eksekusi mencakup merge.
6. Diff tidak menyentuh jalur pengaman (bagian 5); bila menyentuh, merge hanya dalam mode SUPERVISED dengan Owner hadir — skrip merge menolaknya pada mode otonom.

### 8.2 Metode merge (keputusan Owner)

GOV-08 menuntut author **dan committer** hanya identitas Owner. Merge melalui tombol/API GitHub membuat commit baru dengan committer platform GitHub. Karena itu metode merge diputuskan Owner ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)):

| Opsi | Cara | Identitas | Proteksi `main` |
|---|---|---|---|
| **A — fast-forward terverifikasi (rekomendasi)** | Setelah 8.1 lulus, `main` dimajukan tepat ke SHA HEAD PR (`git push origin <sha>:main`) oleh skrip merge terjaga; PR tercatat merged oleh GitHub karena commit-nya masuk ke cabang dasar (diverifikasi TASK-001) | Seluruh commit tetap author+committer Owner | Ruleset: status check `ci-gate` wajib (commit harus lulus di ref lain lebih dahulu), riwayat linear, tanpa force push/penghapusan, **tanpa** aturan PR wajib dan tanpa daftar bypass; "PR dahulu" ditegakkan kebijakan, hook dan skrip merge eksekutor |
| B — merge GitHub (squash/merge commit) | `gh pr merge` | Author Owner; committer GitHub | Ruleset dengan PR wajib + `ci-gate` wajib; memerlukan penerimaan eksplisit Owner bahwa committer GitHub pada commit merge tidak melanggar GOV-08 |

Cabang yang tertinggal dari `main` tidak di-rebase dengan force push: eksekutor membuat cabang baru dari `main` terbaru (`…-r2`), memindahkan commit dengan cherry-pick, membuka PR baru dan menutup PR lama dengan rujukan. Cabang tidak dihapus otomatis.

### 8.3 Setelah merge

1. Verifikasi `main` remote (`git ls-remote origin refs/heads/main`): opsi A = SHA HEAD PR; opsi B = commit merge yang memuat HEAD PR.
2. Verifikasi PR berstatus merged (`gh pr view --json state,mergeCommit`).
3. **PR rekonsiliasi dokumen** `docs/reconcile-task-0xx` (kelas LOW, hanya Task file, TASK_PLAN, CURRENT_HANDOFF): status DONE + EXECUTION STAGE DONE, COMPLETION EVIDENCE final (SHA `main`, run CI, status review), checkbox `[x]`, penghitung dan persentase, CURRENT TASK/NEXT READY TASK, lalu Task berikutnya yang layak ditandai READY. `ci-gate` (docs-check) wajib; tanpa review Codex; merge dengan metode yang sama; `main` diverifikasi lagi.
4. Pilih Task berikutnya (bagian 3).

Task baru DONE setelah rekonsiliasi berada di `main`. Total di TASK_PLAN harus cocok dengan daftar Task.

### 8.4 Mode bootstrap (sebelum CI ada)

TASK-001–TASK-004 berjalan sebelum `ci.yml` ada, sehingga `ci-gate` tidak dapat dipersyaratkan. Untuk keempatnya: mode SUPERVISED; seluruh piramida verifikasi dijalankan lokal dan buktinya dicatat; PR tetap dibuka sebagai catatan; Owner menyaksikan bukti, diff dan metadata commit; merge memakai metode D-08 tanpa `ci-gate`. TASK-005 membuat `ci.yml`, sehingga PR-nya sendiri sudah menjalankan `ci-gate`; setelah TASK-005 ter-merge, Owner menerapkan ruleset `main`. Sejak TASK-006 setiap merge mensyaratkan `ci-gate`. Uji E2E browser tersedia sejak TASK-007; Task sebelumnya mencatatnya N/A dengan alasan.

## 9. Runner, watcher, checkpoint dan pemulihan

### 9.1 Komponen

| Komponen | Bentuk | Tugas |
|---|---|---|
| Sesi eksekutor | Claude Code: interaktif (SUPERVISED) atau headless per langkah (AUTONOMOUS) | Mengerjakan Task |
| Runner | `scripts/executor/runner.ps1`, satu instance (`.pranata/executor/runner.lock` berisi PID dan waktu mulai) | Membaca hold dan checkpoint, membangkitkan paket konteks, memanggil sesi headless dengan allowlist alat, batas giliran **dan batas waktu dinding per langkah** (bagian 14), mencatat log, menunggu hasil eksternal tanpa memanggil model |
| Watcher | `scripts/executor/watcher.ps1` dijalankan Penjadwal Tugas Windows berkala, kunci tunggal | Memeriksa sinyal murah (proses, umur heartbeat, mtime berkas, status PR/CI lewat `gh` dengan interval jarang), memulai ulang runner bila mati dan tidak ada hold, mencatat peringatan |
| Checkpoint | `.pranata/executor/checkpoint.json` (diabaikan Git) | Status tahan lama: `authorization_id`, `mode`, `task_id`, `stage`, `branch`, `head_sha`, `pr_number`, `ci_run_ids`, `codex_status`, `review_status`, `last_action` + apakah idempoten, `next_action`, `session_id`, `updated_at` |
| Heartbeat | `.pranata/executor/heartbeat` diperbarui hook PostToolUse | Bukti aktivitas yang murah |

Tidak ada polling AI berkelanjutan dan tidak ada watcher ganda. Pemantauan tidak menghalangi pekerjaan coding.

### 9.2 Deteksi idle

Ambang kandidat **45 menit** tanpa heartbeat. Idle hanya disimpulkan bila **semua** benar: tidak ada proses Claude Code berdirektori kerja PRANATA yang aktif; tidak ada proses uji/migrasi/build PRANATA yang aktif atau masih menghasilkan keluaran; checkpoint tidak berada pada tahap tunggu yang sah (menunggu CI/Codex dengan timer yang masih berjalan); tidak ada hold. Sesi yang **macet** diperlakukan sama: proses Claude Code PRANATA masih hidup tetapi heartbeat tidak berubah melebihi ambang macet, tanpa proses anak yang aktif, atau langkah headless melewati batas waktu dinding. Runner menghentikan hanya PID itu setelah checkpoint menunjukkan tidak sedang dalam urutan commit/push/merge, lalu memulihkan menurut bagian 9.3. Tidak ada perubahan berkas kode **bukan** bukti idle. Suite PHP yang panjang, uji migrasi, tunggu CI atau review independen adalah pekerjaan aktif yang sah. Ambang dikalibrasi dari durasi nyata (bagian 14).

### 9.3 Pemulihan

Saat benar-benar idle: verifikasi status sesi dan proses; pastikan tidak ada proses kritis integritas yang berjalan; baca checkpoint; cegah eksekusi ganda melalui kunci; lanjutkan **hanya** dengan mekanisme yang terverifikasi (memulai sesi headless baru atau melanjutkan sesi dengan identitasnya). Prompt lanjutan mewajibkan verifikasi keadaan nyata (cabang, HEAD, PR, CI, status merge) sebelum tindakan apa pun; aksi dirancang idempoten (push SHA yang sama, pembuatan PR memeriksa PR yang ada, merge memeriksa status PR). Perintah yang tidak idempoten tidak pernah diulang membabi buta.

### 9.4 Batas platform yang diakui

- Watcher tidak diklaim dapat "membangunkan" giliran Claude Code Desktop yang sudah selesai; kemampuan itu tidak terdokumentasi. Mode otonom memakai runner headless; dalam mode interaktif, Owner melanjutkan dengan prompt resume ([EXECUTOR_BOOTSTRAP §5](EXECUTOR_BOOTSTRAP.md#5-templat-prompt)).
- Kompaksi otomatis tidak menjamin giliran baru dimulai.
- Tugas terjadwal aplikasi desktop hanya berjalan saat aplikasi terbuka dan mesin terjaga; jadwal yang terlewat tidak dikejar. Runner memerlukan mesin menyala, tidak tidur dan pengguna masuk; Owner mengatur skema daya untuk jendela eksekusi. Saat mesin tidur, eksekusi berhenti dan dilanjutkan dari checkpoint setelah bangun.
- Batas pemakaian paket Claude yang ada: bila tercapai, runner mencatat waktu reset (bila tersedia), menunggu tanpa loop sibuk, lalu melanjutkan. Tidak ada peningkatan paket berbayar tanpa persetujuan Owner.

## 10. Hold dan shutdown aman

Dua mode, tidak aktif kecuali diminta Owner secara eksplisit: **IMMEDIATE_SAFE_SHUTDOWN** (simpan Task yang belum selesai dan hentikan aktivitas proyek pada batas aman) dan **SHUTDOWN_AFTER_CURRENT_TASK** (selesaikan Task aktif sampai DONE terekonsiliasi, lalu berhenti sebelum memilih Task berikutnya).

Permintaan: Owner menjalankan `scripts/executor/hold.ps1 -Mode <MODE> -Reason "<alasan>"` atau menulis di sesi `HOLD: <MODE> — <alasan>`; eksekutor menulis `.pranata/executor/hold.json` (`mode`, `reason`, `requested_at`, `requested_by: Owner`). Hook dan runner memeriksa hold pada setiap batas alat.

**Batas aman**: tidak di tengah urutan commit/push/merge, migrasi atau uji yang menulis basis data; perintah yang sedang berjalan diselesaikan dahulu.

Saat shutdown: perubahan yang belum di-commit disimpan sebagai commit lokal `wip(task-0xx): checkpoint <tahap>` di cabang Task (tidak di-push kecuali Owner meminta); checkpoint ditulis; runner, sesi eksekutor, proses uji PRANATA dan kontainer `pranata` dihentikan (`docker compose -p pranata stop`, tanpa menghapus volume); tugas terjadwal watcher PRANATA dinonaktifkan; proyek lain tidak disentuh; Task yang belum selesai tidak ditandai DONE; instruksi resume persis diberikan.

Saat resume (hanya dengan instruksi Owner yang melepas hold): verifikasi status repositori; integritas checkpoint (SHA cabang = `head_sha`); sinkronisasi lokal/remote; pulihkan kunci watcher/runner yang basi dengan aman (PID mati); lepas hold; aktifkan kembali **satu** watcher; lanjutkan Task yang belum selesai. Hold selalu mengalahkan aturan melanjutkan Task lain (bagian 3). Shutdown tidak pernah disimpulkan dari jam atau lamanya Task.

## 11. Isolasi multi-proyek dan keamanan sumber daya

| Area | Diizinkan | Ditolak (hook PreToolUse Bash dan PowerShell + allowlist izin) |
|---|---|---|
| Berkas | Repositori PRANATA, folder worktree PRANATA, `.pranata/` | Folder MULTIPLECORP, BantuKasir, ELLIGBLE dan folder pengguna lain di luar ruang lingkup |
| Git | Perintah normal di repositori PRANATA; push cabang kerja; merge menurut bagian 8 lewat skrip terjaga | `push --force`/`--force-with-lease`, push langsung ke `main` di luar skrip merge, ubah `user.name`/`user.email`, konfigurasi global, penghapusan cabang remote |
| Docker | `docker compose -p pranata …` dan kontainer `pranata-*` | `docker system/volume/network/builder prune`, restart Docker Desktop, `rm`/`stop` kontainer atau volume non-`pranata` |
| Basis data | `pranata_dev`, `pranata_test*`, `pranata_e2e_*`, `pranata_perf` di `pranata-postgres` | Host/port/basis data lain; staging dan produksi |
| Proses | Menghentikan PID yang dicatat milik PRANATA setelah memeriksa direktori kerja/baris perintahnya | Menghentikan proses berdasarkan nama saja |
| Jalur pengaman | Perubahan dalam Task yang kontraknya mencakupnya, dengan merge SUPERVISED | Penulisan ke `.github/workflows/**`, `.claude/**`, `.githooks/**`, `scripts/executor/**` pada mode otonom |
| GitHub | Baca status PR/CI/review; membuat cabang, PR dan komentar; merge lewat skrip terjaga | `gh api` non-GET ke ruleset, proteksi cabang, ref, secret atau pengaturan repositori; `gh pr merge --admin`; `gh repo edit`; `gh secret`; `gh auth`; mengubah ruleset |
| Token | Token runner sesuai D-27 (rekomendasi: token terbatas repositori ini tanpa hak Administration) | Memakai login admin Owner untuk langkah otonom bila D-27 memilih token terbatas |
| Mode izin | Allowlist + hook deny | Mode bypass permissions dalam kondisi apa pun |
| Rahasia | `.env` lokal PRANATA tanpa kredensial produksi | Membaca/menulis `.env` proyek lain; menyimpan token di repositori, log atau PR |
| Layanan | Paket sumber terbuka/gratis dalam ruang lingkup Task | Layanan berbayar atau pemantauan/otomasi berbayar tanpa persetujuan Owner |
| Produksi | — | Deploy produksi, akses basis data produksi, perubahan DNS/server (dilakukan operator atas keputusan Owner) |

Daftar uji perintah terlarang di TASK-001 membuktikan setiap aturan menolak. Infrastruktur otomasi tidak boleh lebih kompleks daripada alur kerja aplikasi yang didukungnya.

## 12. Pelaporan kemajuan dan eskalasi Owner

Setiap laporan bermakna kepada Owner, dalam Bahasa Indonesia, diakhiri:

```text
PRANATA UNY — EXECUTION PROGRESS
- Selesai: N/<total> Task (persentase)
- Task aktif: TASK-xxx (tahap)
- PR/CI: <status>
- Review: <status>
- Eksekusi: ACTIVE / BLOCKED / PAUSED
- Berikutnya: TASK-xxx
```

Laporan dibuat pada Task DONE, BLOCKED, hold/resume, kebutuhan keputusan Owner, dan akhir sesi interaktif. Pada mode otonom, ringkasan ditulis ke CURRENT_HANDOFF dan log runner; tidak ada giliran model yang dibuat hanya untuk mengumumkan kemajuan yang tidak berubah. Persentase dihitung dari TASK_PLAN, bukan dari fase.

Eskalasi ke Owner hanya untuk hal yang dicadangkan (U-007 §22): ambiguitas kebijakan pengadaan resmi, perubahan ruang lingkup produk yang material, ketidakpastian kebijakan akuntansi, penyimpangan keamanan/arsitektur yang material, layanan berbayar berulang, akses data institusi yang sensitif, persetujuan fase, Planning Freeze, otorisasi eksekusi, rilis produksi, ketidakcocokan identitas Git, dan kegagalan kemampuan pengaman. Pertanyaan dikelompokkan, dicatat di CURRENT_HANDOFF (DECISIONS PENDING), dan pekerjaan lain yang sah dilanjutkan. Pertanyaan yang sudah dijawab tidak diajukan ulang. Keputusan teknis biasa (komponen, paket yang sudah ada, perbaikan uji, cacat kode, refaktor aman, koreksi dokumen biasa, urutan Task sesuai graf) diambil eksekutor sendiri.

**Perubahan rencana (GOV-10).** Eksekutor tidak menyusun kebenaran perencanaan. Dokumentasi yang boleh diperbarui eksekutor terbatas pada: COMPLETION EVIDENCE dan status/tahap Task aktif, checkbox dan penghitung TASK_PLAN, CURRENT_HANDOFF, fakta versi di TOOLCHAIN, laporan kemampuan dan status C-xx di bagian 13 (bukti TASK-001), serta pembaruan dokumentasi yang secara eksplisit dituntut kontrak Task. Usulan penambahan, pemecahan, penggabungan, pengurutan ulang atau perubahan isi Task, serta perubahan aturan dan parameter dokumen ini, dicatat sebagai usulan TASK PLAN CHANGE di CURRENT_HANDOFF; Task terdampak menjadi BLOCKED; Planning Agent menyusun perubahannya, dengan Second Review proporsional dan persetujuan Owner bila material.

## 13. Kemampuan platform yang wajib diverifikasi

Semua berstatus **UNVERIFIED** sampai TASK-001 (C-14 pada TASK-003) mencatat VERIFIED, FAILED atau NOT AVAILABLE beserta bukti di laporan kemampuan.

| # | Kemampuan yang diasumsikan | Dipakai untuk | Cara verifikasi (TASK-001) | Jalan cadangan bila gagal |
|---|---|---|---|---|
| C-01 | Hook PreToolUse menolak perintah Bash dan PowerShell pada Windows, termasuk dalam mode headless | Isolasi, larangan Git/Docker | Daftar uji perintah terlarang; hook dengan shell PowerShell yang dipatok | Allowlist izin yang lebih sempit; mode otonom tidak diaktifkan sampai penolakan terbukti |
| C-02 | Mode headless (`-p`) dengan allowlist alat, mode izin terbatas, keluaran JSON terstruktur, batas giliran, dan melanjutkan sesi | Runner | Jalankan langkah contoh di repositori uji sekali pakai | Mode SUPERVISED saja |
| C-03 | Hook Stop dengan `stop_hook_active`; batas penimpaan setelah blok berturut-turut | Mencegah berhenti diam-diam saat checkpoint belum dicatat | Uji blok tunggal; desain tidak bergantung pada blok berulang | Runner memeriksa checkpoint setelah setiap pemanggilan |
| C-04 | Perilaku kompaksi (instruksi akar dimuat ulang; proses latar tetap berjalan) | Kontinuitas sesi panjang | Sesi panjang terkontrol | Langkah headless pendek per tahap |
| C-05 | Pengaturan atribusi proyek menonaktifkan trailer commit dan footer PR | GOV-08 | Commit dan PR uji pada repositori sekali pakai; periksa metadata mentah | Hook `commit-msg` + pemeriksaan pasca-commit + templat PR (tetap wajib) |
| C-06 | Penjadwal Tugas Windows menjalankan watcher saat pengguna masuk; perilaku setelah tidur/jadwal terlewat | Watcher | Uji tidur/bangun dan jadwal terlewat | Runner dimulai manual oleh Owner dengan satu perintah |
| C-07 | Tugas terjadwal aplikasi desktop | Tidak diandalkan | Catat perilaku | — |
| C-08 | Membangunkan giliran desktop yang selesai | Tidak diandalkan | Catat bila ada mekanisme resmi | Resume manual/headless |
| C-09 | Review Codex: mekanisme permintaan, akun bot, sinyal mulai dan selesai yang dapat dibaca `gh api` | Bagian 6.3 | PR uji | Review pengganti subagen |
| C-10 | Fast-forward `main` ke HEAD PR menandai PR merged; ruleset status check menolak commit yang belum lulus | Opsi merge A | Repositori uji dengan ruleset | Opsi B bila Owner menerimanya |
| C-11 | Subagen peninjau dapat dipakai dalam mode headless | Review independen | Uji subagen pada PR uji | Sesi headless terpisah sebagai peninjau |
| C-12 | Deteksi galat batas pemakaian dan waktu reset | Runner | Simulasi/log nyata | Hold otomatis dan laporan |
| C-13 | Worktree Git di Windows untuk Task paralel | Paralelisme | Uji worktree | Tanpa paralelisme |
| C-14 | Kinerja bind mount Docker Desktop dan batas sumber daya | Lingkungan dev | TASK-003 | Rekomendasi ke Owner (mis. kloning di WSL2) |
| C-15 | Runner memakai token GitHub terpisah (variabel lingkungan dan kredensial Git khusus proses runner) tanpa memengaruhi login Owner; token tanpa hak Administration ditolak saat mencoba mengubah ruleset | Pembatasan kewenangan eksekutor (D-27) | Uji pada repositori sekali pakai | Bila tidak dapat dipisahkan: mode otonom memakai login Owner hanya bila Owner menerimanya secara eksplisit |

**Status hasil TASK-001 (2026-11-01, bukti di [EXECUTOR_CAPABILITY_REPORT](evidence/EXECUTOR_CAPABILITY_REPORT.md)):** VERIFIED: C-01, C-02, C-03, C-05, C-10, C-11, C-13. VERIFIED dengan batas tercatat: C-04, C-06, C-12 (simulasi). NOT AVAILABLE: C-07, C-08, C-14 (Docker lokal dibatalkan keputusan Owner; lihat usulan di CURRENT_HANDOFF), C-15 (menunggu token Owner). C-09: lihat laporan.

Kemampuan yang FAILED atau NOT AVAILABLE memicu jalan cadangannya dan, bila memengaruhi Task lain, TASK PLAN CHANGE. Model ini diperbarui dari hasil uji, bukan dari dugaan.

## 14. Parameter default dan kalibrasi

| Parameter | Default | Kalibrasi |
|---|---|---|
| Ambang kandidat idle | 45 menit tanpa heartbeat + syarat bagian 9.2 | Dari durasi suite/CI nyata pada M0–M1 |
| Interval watcher | 10 menit | Diturunkan hanya bila bukti menunjukkan perlu |
| Jendela konfirmasi mulai Codex | 10 menit sejak permintaan; satu permintaan ulang | Dari 10 PR pertama |
| Batas review Codex | 10 menit sejak mulai terkonfirmasi | Dari 10 PR pertama; dicatat dengan bukti |
| Percobaan perbaikan CI per akar masalah | 3, lalu BLOCKED | — |
| Ambang proses macet | 20 menit tanpa keluaran dan tanpa aktivitas CPU | Per jenis proses |
| Task paralel maksimum | 2 (bagian 3) | Hanya bila terbukti lebih cepat |
| Batas giliran per pemanggilan headless | Ditetapkan TASK-001 | Dari uji runner |
| Batas waktu dinding per langkah headless | 90 menit (tidak termasuk menunggu hasil eksternal di luar sesi) | Dari durasi langkah nyata M0–M1 |
| Karantina uji tidak stabil | 3 hari kerja | TEST_STRATEGY §6 |

Eksekutor mencatat hasil kalibrasi sebagai bukti dan mengusulkan perubahan parameter (bagian 12); Planning Agent memperbarui dokumen ini. Perubahan yang melemahkan pengaman (isolasi, atribusi, gerbang CI/review, karantina uji) memerlukan persetujuan Owner.
