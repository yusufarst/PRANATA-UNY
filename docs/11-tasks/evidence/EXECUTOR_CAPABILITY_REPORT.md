# Laporan kemampuan eksekutor — TASK-001

Status: bukti TASK-001 | Updated: 2026-11-01 (Asia/Jakarta) | Pemilik: Execution Agent | Rujukan: [EXECUTOR_OPERATING_MODEL §13](../EXECUTOR_OPERATING_MODEL.md#13-kemampuan-platform-yang-wajib-diverifikasi)

Setiap status berasal dari uji nyata pada mesin Owner, tanpa rahasia. Lingkungan: Windows 11 Home, Windows PowerShell 5.1.26100, Git 2.55.0.windows.3, GitHub CLI 2.96.0, Claude Code 2.1.310, Node 24.18.0. RAM fisik 7,2 GB. PHP, Composer dan psql tidak terpasang. Docker lokal **tidak digunakan** (keputusan Owner 2026-11-01); semua uji larangan Docker adalah uji keputusan guard tanpa menjalankan Docker.

Ulangi dengan: `scripts/executor/tests/guard.tests.ps1`, `scripts.tests.ps1` (keduanya luring), `capability.probes.ps1` (repositori sekali pakai + panggilan model ringan), `c10.probe.ps1` (repositori sandbox publik sintetis), `c15.probe.ps1` (setelah token terpasang), `c16.probe.ps1` (PR uji sekali pakai dan sandbox).

## Ringkasan

| # | Kemampuan | Status | Bukti | Batas yang diketahui |
|---|---|---|---|---|
| C-01 | Hook PreToolUse menolak Bash dan PowerShell, termasuk headless | VERIFIED | Headless: hook menolak `git push origin HEAD:main` (PowerShell) dan `git push origin +chore/probe` (Bash); origin sekali pakai tetap tanpa ref. Pembacaan berkas sintetis di folder bernama proyek terlindungi ditolak aturan deny sebelum hook. Sesi interaktif: hook menolak perintah nyata saya sendiri. Kegagalan skrip → `onFailure: block` plus penolakan internal | Skrip yang ditulis lalu dijalankan tidak dipindai teksnya; lapisan lain: allowlist `dontAsk` dan ruleset |
| C-02 | Headless: allowlist, `dontAsk`, JSON, batas giliran, resume | VERIFIED | `session_id`/`is_error`/`num_turns` terbaca; Write di luar allowlist tidak membuat berkas; resume mengingat jawaban; `--max-turns 1` → `error_max_turns`. Satu langkah nyata lewat `runner.ps1` mode AUTONOMOUS: sesi tercatat, berkas jalur pengaman tidak tertulis, status `blocked` tercatat, kunci dilepas | Workspace belum dipercaya mengabaikan `permissions.allow` proyek; runner selalu mengirim `--allowedTools`. CLI harus login terpisah dari aplikasi desktop |
| C-03 | Hook Stop | VERIFIED | Checkpoint tertinggal: blok menyala, `num_turns=2` dari 12, `success`. Penanda sesi+HEAD di `.pranata/executor/stop-guard-marker` menjamin satu blok saja tanpa bergantung pada `stop_hook_active` | Dokumentasi tidak memastikan `stop_hook_active` pada Stop; desain tidak bergantung padanya |
| C-04 | Kompaksi | VERIFIED (terbatas) | `/compact` lewat `--resume` exit 0; giliran berikut `completed`, kata kode tetap diingat | Pemuatan ulang instruksi akar dan proses latar tidak diuji; mode otonom memakai langkah headless pendek |
| C-05 | Atribusi | VERIFIED | Commit headless: metadata mentah hanya identitas Owner, tanpa trailer. PR headless di sandbox: badan tanpa footer. Hook `commit-msg` menolak 7 bentuk atribusi, juga salinan CRLF. Gerbang merge memeriksa commit dan badan PR | — |
| C-06 | Penjadwal Tugas Windows | VERIFIED (terbatas) | Task berisolasi nama `PRANATA-UNY-*` dibuat, dijalankan, menulis log watcher, hasil 0, lalu dihapus | Tidur/bangun dan jadwal terlewat **tidak diuji** agar tidak mengganggu proyek lain; `StartWhenAvailable` aktif. Watcher belum didaftarkan |
| C-07 | Tugas terjadwal aplikasi desktop | NOT AVAILABLE | Tidak diuji dan tidak diandalkan | — |
| C-08 | Membangunkan giliran desktop | NOT AVAILABLE | Tidak ada mekanisme resmi yang ditemukan; tidak diandalkan | Resume manual atau headless |
| C-09 | Review Codex | VERIFIED | Diuji pada PR #2: lihat bagian C-09 | Durasi baru dari satu PR; kalibrasi §14 berlanjut |
| C-10 | Fast-forward dan ruleset | VERIFIED | Sandbox `yusufarst/pranata-uny-sandbox-c10`: PR tercatat MERGED setelah fast-forward; commit tanpa hasil `ci-gate` ditolak; `ci-gate` lulus di cabang kerja lalu fast-forward diterima dan PR MERGED; `ci-gate` gagal ditolak; force push dan penghapusan `main` ditolak | Ruleset PRANATA sendiri diverifikasi 2026-11-02: lihat TAC-05 di [TASK-005](../TASK-005.md#completion-evidence) |
| C-11 | Subagen dalam headless | VERIFIED | Subagen membaca berkas dan melaporkan isinya, exit 0 | — |
| C-12 | Deteksi batas pemakaian | VERIFIED (simulasi) | Stub: batas terdeteksi hanya pada langkah gagal, `wait_until` diisi, tanpa panggilan model selama menunggu. Galat autentikasi nyata (`is_error=true`, `subtype=success`) kini menghentikan loop | Pesan batas pemakaian yang asli belum teramati; pola dikalibrasi saat terjadi |
| C-13 | Worktree Git di Windows | VERIFIED | Worktree dibuat dan dihapus pada repositori sekali pakai | — |
| C-14 | Bind mount Docker Desktop | SUPERSEDED / NOT APPLICABLE | Keputusan Owner OD-37 (2026-11-01, tanpa Docker lokal); diklasifikasi ulang oleh PCR-002; tidak diuji | — |
| C-15 | Token runner terpisah | VERIFIED (2026-11-02) | Owner memasang token lewat `install-runner-token.ps1`, tersimpan DPAPI di luar repositori. `c15.probe.ps1`: baca repositori berhasil; percobaan ruleset dan rahasia ditolak 403; login Owner tidak berubah. Hak yang dibutuhkan runner terbukti tanpa menulis apa pun: push dry-run dengan wiring proses anak runner, PR dan komentar (422 atas body tidak valid), check run dan Actions terbaca | `current_user_can_bypass` token pada `release-tags` bernilai `always`, karena bypass mengikuti peran admin pemilik token; penahan push tag bagi eksekutor adalah guard, bukan ruleset |
| C-16 | Gerbang bukti bootstrap | VERIFIED (TASK-002) | PR uji sekali pakai dan sandbox dengan run GitHub-hosted nyata: lihat bagian C-16 | `pull_request.base.sha` tidak diperbarui setelah base bergerak |

## C-16

Diverifikasi TASK-002 pada 2026-11-01 dengan `scripts/executor/tests/c16.probe.ps1`. Bagian 1 menjalankan `merge-verified.ps1 -Bootstrap -DryRun` pada [PR uji #6](https://github.com/yusufarst/PRANATA-UNY/pull/6), yang ditutup tanpa merge; `main` tetap `bbf2e59` sebelum dan sesudahnya. Job `app-boot` pada PR uji adalah stub cepat, sedangkan header workflow dan job `bootstrap-gate` diambil dari `bootstrap.yml` yang sebenarnya. Berkas review di PR uji adalah fixture lokal per SHA, agar hanya gerbang bukti yang diuji.

| Skenario | Run | SHA HEAD | Hasil gerbang |
|---|---|---|---|
| Job gagal | [41327250082](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41327250082) | `87700ea` | Ditolak: job `app-boot` `failure` |
| Run belum selesai | [41327322461](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41327322461) | `0a641ab` | Ditolak: status `in_progress` |
| Run dibatalkan | 41327322461 | `0a641ab` | Ditolak: kesimpulan `cancelled` |
| Job wajib dilewati | [41327463823](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41327463823) | `cde61af` | Ditolak: job `app-boot` `skipped` |
| Job tambahan dilewati, run `success` | [41327518373](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41327518373) | `8b42d15` | Ditolak: job `extra` `skipped` |
| Run hilang (berkas workflow kosong pada HEAD ini) | Tidak ada | `44056dc` | Ditolak: tidak ada run bootstrap |
| Run hijau pada HEAD terbaru | [41327683341](https://github.com/yusufarst/PRANATA-UNY/actions/runs/41327683341), attempt 1 | `542e683` | **Diterima**; base `bbf2e59`, uji-merge `4ec7c0e` |
| Push baru, SHA lama | 41327683341 | `542e683` → `3b227f3` | Ditolak: 8.1.1 |
| Bukti lama untuk HEAD baru | 41327683341 | `3b227f3` | Ditolak: `head_sha` dan `tree_sha` |

**Filter `head_sha` pada API nyata.** SHA HEAD PR menemukan satu run dan semua hasilnya bersesuaian; SHA uji-merge `4ec7c0e` dan SHA `main` tidak menemukan run. Filter sesuai dengan prosedur QUALITY_GATES §4.1, jadi jalur cadangan enumerasi run tidak diperlukan.

**Base bergerak dan tree tidak cocok.** `main` repositori ini tidak boleh digerakkan untuk uji, jadi kedua skenario memakai fungsi gerbang yang sama atas run nyata di sandbox `yusufarst/pranata-uny-sandbox-c10` ([PR #7](https://github.com/yusufarst/pranata-uny-sandbox-c10/pull/7)). Base digerakkan lewat ruleset sandbox itu sendiri.

| Skenario | Run | SHA HEAD | Hasil |
|---|---|---|---|
| Garis dasar hijau | [41328110703](https://github.com/yusufarst/pranata-uny-sandbox-c10/actions/runs/41328110703) | `719fc94` | Tanpa kegagalan; base `50b523d` |
| Base bergerak ke `eda177c` | 41328110703 | `719fc94` | Ditolak: `base_sha` |
| Run baru setelah base bergerak | [41328205032](https://github.com/yusufarst/pranata-uny-sandbox-c10/actions/runs/41328205032) | `6709649` | Ditolak: `tree_sha` dan `base_sha` |

**Temuan platform.** Setelah base bergerak, run baru menguji uji-merge terhadap base baru, tetapi `github.event.pull_request.base.sha` tetap berisi base lama (run 41327925221 dan 41328205032). Akibatnya PR yang melewati perpindahan base selalu ditolak, dan pemulihannya adalah cabang `-r2` baru menurut [§8.2](../EXECUTOR_OPERATING_MODEL.md#82-metode-merge-keputusan-owner). Perilaku ini fail-closed dan sesuai prosedur. Satu run probe pertama ditandai FAILED karena asersinya mengharapkan hanya `tree_sha` yang gagal; gerbang sudah menolak dengan benar, asersi diperbaiki, gerbang tidak diubah, dan bagian sandbox dijalankan ulang.

**Batas.** Penolakan `tree_sha` yang berdiri sendiri hanya dibuktikan oleh uji luring `scripts.tests.ps1`, karena pada run nyata selalu disertai `base_sha` atau `head_sha`.

## C-09

Terverifikasi pada [PR #2](https://github.com/yusufarst/PRANATA-UNY/pull/2) (2026-11-01), dibaca dengan `gh api`:

- Akun bot: `chatgpt-codex-connector[bot]`.
- Permintaan: review otomatis repositori berjalan saat PR dibuka. Pada PR #2 komentar `@codex review` juga terkirim, jadi keduanya terjadi; §6.3 menghendaki satu saja, dan mulai sekarang komentar hanya dipakai bila review otomatis tidak muncul dalam jendela konfirmasi.
- Sinyal mulai: reaksi `eyes` pada PR atau komentar permintaan, serta komentar ringkasan dengan penanda `codex-review-state:v1` berisi JSON `status`, `commit_sha`, `has_any_findings`, `has_unresolved_findings`.
- Sinyal selesai: `status: completed` pada penanda itu; temuan berupa komentar review berpenanda `codex-code-review-finding:v1`; tanpa temuan ditandai reaksi `+1`.
- Durasi teramati: sekitar 5 menit (13:49:02 → 13:54:13 UTC), dalam batas §14.
- Hasil pada `bafb717`: dua P1 dan satu P2, ketiganya valid dan diperbaiki.

## Sisa risiko yang diterima

- Guard berbasis teks tidak dapat melihat isi skrip yang ditulis lalu dijalankan. Lapisan lain: allowlist runner tanpa tool PowerShell, `disallowedTools` jalur pengaman, gerbang merge, ruleset `main` setelah TASK-005.
- Langkah headless memakai Bash untuk semua perintah (`allowlist.probe.ps1`, 2026-11-02). Pada `dontAsk`, tool PowerShell menolak `powershell -File scripts/executor/*.ps1` dan pemanggilan skrip langsung dalam 7 dari 7 bentuk aturan, termasuk `PowerShell(powershell *)`; `PowerShell(npm *)` dan `PowerShell(git *)` juga sempat ditolak sesekali tanpa penyebab terdokumentasi. Bentuk Bash lulus 5 dari 5, dan `hold.ps1` serta `runner.ps1` tetap ditolak.
- Tugas terjadwal watcher memakai prioritas 4. Dengan prioritas bawaan 7, 2 dari 2 run gagal dengan `0xC000013A`; prioritas 4 lulus 3 dari 3 dalam 11–17 detik. Mekanisme pastinya belum terbukti.
- Penolakan kata `docker` di dalam teks perintah dapat salah sasaran (misalnya `git commit -m`); pakai `git commit -F`. Ini sengaja: pembungkus seperti `powershell -c` tetap tertangkap.
- `PSScriptAnalyzer` tidak terpasang dan tidak dipasang (tanpa instalasi global); pengganti: parser AST PowerShell dan pemeriksaan ASCII pada semua skrip.

## Sumber daya mesin

RAM bebas selama TASK-001 berkisar 0,2–1,1 GB tanpa beban kontainer PRANATA; pemakai terbesar bukan proses PRANATA. Tidak ada proses PRANATA tertinggal setelah uji. Ini mendukung keputusan Owner memindahkan uji berat ke runner GitHub-hosted.
