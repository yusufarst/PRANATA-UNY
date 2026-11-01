# Bootstrap eksekutor — PRANATA UNY

Status: P11 — APPROVED (APPR-006); baseline beku FRZ-001; **aktif menurut AUTH-013** | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P11

Dokumen ini memiliki **urutan memulai Claude Code sebagai Execution Agent**: bootstrap baca-saja yang boleh dipakai sekarang, urutan aktivasi setelah otorisasi, templat prompt, dan konvensi cabang/commit/PR. Aturan operasi dimiliki [EXECUTOR_OPERATING_MODEL](EXECUTOR_OPERATING_MODEL.md); konteks minimum dimiliki [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md). Owner tidak perlu menulis ulang instruksi operasi: setelah otorisasi, templat di bagian 5 cukup.

## 1. Cakupan bootstrap

| Unsur (U-007 §24) | Pemilik |
|---|---|
| Dokumen masuk repositori, konteks minimum | [AGENTS.md](../../AGENTS.md), [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md) |
| Otoritas aktif | [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md), [PHASE_STATUS](../PHASE_STATUS.md), [EXECUTOR_OPERATING_MODEL §1](EXECUTOR_OPERATING_MODEL.md#1-prasyarat-aktivasi) |
| Pemilihan Task | [EXECUTOR_OPERATING_MODEL §3](EXECUTOR_OPERATING_MODEL.md#3-pemilihan-task-blokir-dan-paralelisme), [TASK_PLAN](TASK_PLAN.md) |
| Konvensi cabang dan PR | Bagian 6 dokumen ini; [RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang) |
| Persyaratan CI dan review | [QUALITY_GATES](../08-testing/QUALITY_GATES.md), [EXECUTOR_OPERATING_MODEL §6–§7](EXECUTOR_OPERATING_MODEL.md#6-review-berbasis-risiko-dan-review-pr-codex) |
| Siklus watcher, pemulihan idle, kompaksi | [EXECUTOR_OPERATING_MODEL §4, §9](EXECUTOR_OPERATING_MODEL.md#9-runner-watcher-checkpoint-dan-pemulihan) |
| Catatan penyelesaian dan resume | [EXECUTOR_OPERATING_MODEL §8, §10](EXECUTOR_OPERATING_MODEL.md#8-merge-dan-rekonsiliasi-done) |
| Isolasi multi-proyek | [EXECUTOR_OPERATING_MODEL §11](EXECUTOR_OPERATING_MODEL.md#11-isolasi-multi-proyek-dan-keamanan-sumber-daya), [INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi) |
| Eskalasi Owner | [EXECUTOR_OPERATING_MODEL §12](EXECUTOR_OPERATING_MODEL.md#12-pelaporan-kemajuan-dan-eskalasi-owner) |
| Kebijakan biaya | [COST_POLICY](../00-governance/COST_POLICY.md) |
| Batasan produksi | [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md), [OPERATIONS_RUNBOOK](../09-operations/OPERATIONS_RUNBOOK.md) |

## 2. Bootstrap baca-saja (WAITING)

Boleh dijalankan kapan saja; **tidak mengubah apa pun**.

1. Verifikasi repositori, cabang, HEAD, working tree dan remote (`git status`, `git log -1`, `git ls-remote origin refs/heads/main`).
2. Baca `git config user.name` dan `user.email` (tanpa mengubah) dan bandingkan dengan identitas Owner rujukan di [GIT_WORKFLOW](../00-governance/GIT_WORKFLOW.md).
3. Baca AGENTS.md, EXECUTION_CONTEXT, CURRENT_HANDOFF, PHASE_STATUS dan catatan otorisasi terbaru.
4. Laporkan dengan format AICWDF §38: peran `EXECUTION AGENT — WAITING`, Task aktif `—`, otorisasi yang ada/tidak ada, dan `SAFE TO IMPLEMENT: NO` selama prasyarat mode SUPERVISED di [EXECUTOR_OPERATING_MODEL §1](EXECUTOR_OPERATING_MODEL.md#1-prasyarat-aktivasi) belum lengkap; `YES` hanya untuk Task dalam ruang lingkup otorisasi dan pada mode yang prasyaratnya terpenuhi (mode otonom memerlukan prasyarat tambahan).
5. Berhenti. Tidak memasang paket, membuat cabang, menjalankan Docker, membuat skema/migrasi/UI/Task, menutup GAP atau mengubah kebenaran perencanaan.

## 3. Prasyarat aktivasi

- [ ] Persetujuan Owner atas kandidat P0–P11 (SHA yang direview) tercatat.
- [ ] Kandidat perencanaan masuk ke `main` dengan metode yang diputuskan Owner; `main` remote diverifikasi.
- [ ] Planning Freeze dan baseline Task tercatat; TASK_PLAN menyatakan TASK-001 READY.
- [ ] Otorisasi eksekusi berdiri tercatat dengan ruang lingkupnya.
- [ ] Keputusan metode merge D-08 ([EXECUTOR_OPERATING_MODEL §8.2](EXECUTOR_OPERATING_MODEL.md#82-metode-merge-keputusan-owner)), cakupan token eksekutor D-27 dan model gerbang GAP D-28.
- [ ] Mesin Owner: Claude Code terbaru, Git, GitHub CLI terautentikasi akun Owner, Docker Desktop, Node, PowerShell; proyek lain tetap berjalan tanpa gangguan.

## 4. Urutan aktivasi

1. **Verifikasi otoritas** — bootstrap bagian 2 dengan hasil `SAFE TO IMPLEMENT: YES` hanya untuk TASK-001 dalam mode SUPERVISED.
2. **TASK-001 (SUPERVISED, mode bootstrap)** — Owner hadir; eksekutor membangun pengaman, runner/watcher dan penjaga atribusi, menguji kemampuan C-01–C-15, dan menulis laporan kemampuan. Setiap izin baru ditanyakan kepada Owner.
3. **TASK-002–TASK-005 (SUPERVISED, mode bootstrap)** — tanpa `ci-gate` sampai TASK-005 membuat `ci.yml`; bukti verifikasi lokal dan metadata commit disaksikan Owner ([EXECUTOR_OPERATING_MODEL §8.4](EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada)).
4. **Proteksi repositori** — setelah `ci-gate` terbukti pada PR TASK-005, Owner menerapkan ruleset `main` dan perlindungan tag sesuai D-08; eksekutor hanya memverifikasinya dengan `gh api` baca.
5. **Token runner** — Owner memasang token sesuai D-27 khusus untuk proses runner (C-15).
6. **Mode otonom** — sudah diotorisasi untuk TASK-006–TASK-078 oleh AUTH-013; berlaku begitu laporan kemampuan menunjukkan kemampuan pengaman VERIFIED, ruleset dan token terpasang. Bila kemampuan pengaman gagal, eksekutor tetap SUPERVISED dan melapor.
7. **Mulai** — satu perintah: `scripts/executor/runner.ps1 -Start` (dibuat TASK-001); watcher terjadwal diaktifkan. Laporan pertama memuat footer EXECUTION PROGRESS.
8. **Titik sentuh Owner** — hal yang dicadangkan ([EXECUTOR_OPERATING_MODEL §12](EXECUTOR_OPERATING_MODEL.md#12-pelaporan-kemajuan-dan-eskalasi-owner)), merge yang menyentuh jalur pengaman, UAT, dan keputusan rilis.

## 5. Templat prompt

Templat ditulis untuk ditempel apa adanya; teks dalam `<…>` diisi runner atau Owner.

### 5.0 Mulai eksekusi (AUTH-013)

Dipakai Owner sekali di sesi interaktif Claude Code pada klon PRANATA di PC Windows, setelah `main` memuat FRZ-001:

```text
Peran: EXECUTION AGENT. Otorisasi: AUTH-013 (TASK-001–TASK-078, otonomi maksimum yang aman).
Verifikasi dulu: `git fetch origin`, cabang main = origin/main, working tree bersih, git config
user.name/user.email = identitas Owner di GIT_WORKFLOW, origin/main memuat `851013f`
(`git merge-base --is-ancestor 851013ff2dc33bf75a1973089f09e38c7512c058 origin/main`), dan APPROVAL_RECORDS
di origin/main memuat FRZ-001 serta AUTH-013. Hanya origin/main yang berlaku; cabang yang belum terbit tidak mengikat.
Bila salah satu tidak cocok: berhenti dan laporkan.
Lalu ikuti AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → TASK-001 dan EXECUTOR_OPERATING_MODEL.
Kerjakan TASK-001–TASK-005 berurutan dalam mode bootstrap tanpa meminta persetujuan per Task; tanyakan hanya
izin alat yang belum ada di allowlist dan langkah GitHub yang hanya dapat dilakukan Owner (ruleset, token D-27).
Sesudah prasyarat mode otonom terpenuhi, aktifkan runner dan watcher (scripts/executor/runner.ps1 -Start) dan
lanjutkan TASK-006–TASK-078 menurut dependensi. Jangan memulai Task BLOCKED (termasuk yang ditahan PCR-001).
Eskalasi hanya hal yang dicadangkan EXECUTOR_OPERATING_MODEL §12. Tanpa atribusi AI di commit/PR/dokumen.
Laporan dalam Bahasa Indonesia, ringkas, diakhiri footer EXECUTION PROGRESS.
```

### 5.1 Bootstrap WAITING (dapat dipakai sekarang)

```text
Peran: EXECUTION AGENT — WAITING untuk repositori PRANATA UNY.
Jalankan hanya bootstrap baca-saja di docs/11-tasks/EXECUTOR_BOOTSTRAP.md bagian 2.
Jangan mengubah berkas, memasang paket, membuat cabang, menjalankan Docker atau commit.
Laporkan dalam Bahasa Indonesia dengan format AICWDF §38, lalu berhenti.
```

### 5.2 Mulai TASK-001 (SUPERVISED)

```text
Peran: EXECUTION AGENT — SUPERVISED. Otorisasi: <AUTH-ID>, ruang lingkup: TASK-001.
Ikuti AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → TASK-001 dan
EXECUTOR_OPERATING_MODEL. Jawaban pertama memakai format AICWDF §38.
Tanyakan setiap izin yang belum ada di allowlist. Jangan mengaktifkan mode otonom.
Akhiri setiap laporan dengan footer EXECUTION PROGRESS (Bahasa Indonesia).
```

### 5.3 Langkah runner (AUTONOMOUS)

```text
Peran: EXECUTION AGENT — AUTONOMOUS. Otorisasi: <AUTH-ID> (<ruang lingkup>).
Baca .pranata/executor/context.md, lalu verifikasi keadaan nyata (cabang, HEAD, PR, CI,
status merge) sebelum bertindak. Task: <TASK-ID>, tahap: <STAGE>.
Tindakan berikutnya: <NEXT_ACTION>.
Kerjakan sampai tahap berikutnya atau sampai menunggu hasil eksternal; perbarui checkpoint
pada setiap transisi; patuhi hold. Jangan mengulang perintah yang tidak idempoten tanpa
memverifikasi hasil sebelumnya. Keluarkan baris status JSON akhir:
{"task":"<TASK-ID>","stage":"<STAGE>","result":"progressed|waiting|blocked|done","next":"<...>"}
```

### 5.4 Resume setelah hold atau idle

```text
Peran: EXECUTION AGENT — <SUPERVISED|AUTONOMOUS>. Owner melepas hold: <ya/tidak>.
Jalankan prosedur resume EXECUTOR_OPERATING_MODEL §10: verifikasi repositori, integritas
checkpoint (SHA cabang = head_sha), sinkronisasi lokal/remote, kunci basi; pastikan hanya satu
watcher; lanjutkan <TASK-ID> dari tahap <STAGE>. Laporkan dengan footer EXECUTION PROGRESS.
```

### 5.5 Peninjau independen (subagen segar)

```text
Anda peninjau independen untuk <TASK-ID> (kelas <HIGH|pengganti Codex>). Anda tidak menulis
kode. Masukan: kontrak docs/11-tasks/<TASK-ID>.md, diff `git diff main...<HEAD>`, dan bagian
dokumen yang dirujuk kontrak. Cari cacat nyata: kebenaran, otorisasi/isolasi, integritas data
dan konkurensi, keamanan, migrasi, aksesibilitas/lokalisasi bila UI, uji yang hilang atau lemah.
Untuk setiap temuan: tingkat P0–P3, berkas:baris, skenario kegagalan konkret, saran perbaikan.
Jangan melaporkan preferensi gaya sebagai cacat. Bila tidak ada temuan, katakan demikian.
```

### 5.6 Perintah Owner

| Maksud | Perintah |
|---|---|
| Hentikan segera dengan aman | `scripts/executor/hold.ps1 -Mode IMMEDIATE_SAFE_SHUTDOWN -Reason "<alasan>"` atau tulis di sesi `HOLD: IMMEDIATE_SAFE_SHUTDOWN — <alasan>` |
| Berhenti setelah Task aktif | `scripts/executor/hold.ps1 -Mode SHUTDOWN_AFTER_CURRENT_TASK -Reason "<alasan>"` |
| Lepas hold dan lanjutkan | `scripts/executor/release-hold.ps1` lalu templat 5.4 |
| Lihat status | `scripts/executor/status.ps1` (membaca checkpoint, TASK_PLAN, PR/CI) |

## 6. Konvensi cabang, commit dan PR

| Unsur | Konvensi |
|---|---|
| Cabang Task | `feature/task-0xx-<slug>`, `chore/task-0xx-<slug>`, `fix/task-0xx-<slug>`; rekonsiliasi `docs/reconcile-task-0xx`; cabang pengganti `…-r2` |
| Commit | `<type>(task-0xx): <ringkasan>` dengan `type` ∈ `feat`, `fix`, `chore`, `test`, `docs`, `refactor`, `perf`; WIP lokal `wip(task-0xx): checkpoint <tahap>`; tanpa trailer atribusi |
| Judul PR | `TASK-0xx — <judul Task>`; rekonsiliasi `TASK-0xx — rekonsiliasi DONE` |
| Badan PR | Ringkasan; tautan Task; perubahan utama; bukti verifikasi (perintah + hasil); dampak basis data/keamanan/UI/biaya; status review (swa-review, independen, Codex); daftar periksa DoD. Tanpa footer atau tanda atribusi AI/alat |
| Target | `main` (berbasis trunk); PR draft boleh dibuka lebih awal, review Codex diminta saat siap review |
| Identitas | Author dan committer Owner saja (GOV-08); diverifikasi sebelum dan sesudah setiap commit |
