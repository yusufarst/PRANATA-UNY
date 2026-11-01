# Laporan kemampuan eksekutor — TASK-001

Status: bukti TASK-001 | Updated: 2026-11-01 (Asia/Jakarta) | Pemilik: Execution Agent | Rujukan: [EXECUTOR_OPERATING_MODEL §13](../EXECUTOR_OPERATING_MODEL.md#13-kemampuan-platform-yang-wajib-diverifikasi)

Setiap status berasal dari uji nyata pada mesin Owner, tanpa rahasia. Lingkungan: Windows 11 Home, Windows PowerShell 5.1.26100, Git 2.55.0.windows.3, GitHub CLI 2.96.0, Claude Code 2.1.310, Node 24.18.0. RAM fisik 7,2 GB. PHP, Composer dan psql tidak terpasang. Docker lokal **tidak digunakan** (keputusan Owner 2026-11-01); semua uji larangan Docker adalah uji keputusan guard tanpa menjalankan Docker.

Ulangi dengan: `scripts/executor/tests/guard.tests.ps1`, `scripts.tests.ps1` (keduanya luring), `capability.probes.ps1` (repositori sekali pakai + panggilan model ringan), `c10.probe.ps1` (repositori sandbox publik sintetis), `c15.probe.ps1` (setelah token terpasang).

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
| C-10 | Fast-forward dan ruleset | VERIFIED | Sandbox `yusufarst/pranata-uny-sandbox-c10`: PR tercatat MERGED setelah fast-forward; commit tanpa hasil `ci-gate` ditolak; `ci-gate` lulus di cabang kerja lalu fast-forward diterima dan PR MERGED; `ci-gate` gagal ditolak; force push dan penghapusan `main` ditolak | Ruleset PRANATA sendiri tetap diverifikasi setelah Owner memasangnya |
| C-11 | Subagen dalam headless | VERIFIED | Subagen membaca berkas dan melaporkan isinya, exit 0 | — |
| C-12 | Deteksi batas pemakaian | VERIFIED (simulasi) | Stub: batas terdeteksi hanya pada langkah gagal, `wait_until` diisi, tanpa panggilan model selama menunggu. Galat autentikasi nyata (`is_error=true`, `subtype=success`) kini menghentikan loop | Pesan batas pemakaian yang asli belum teramati; pola dikalibrasi saat terjadi |
| C-13 | Worktree Git di Windows | VERIFIED | Worktree dibuat dan dihapus pada repositori sekali pakai | — |
| C-14 | Bind mount Docker Desktop | SUPERSEDED / NOT APPLICABLE | Keputusan Owner OD-37 (2026-11-01, tanpa Docker lokal); diklasifikasi ulang oleh PCR-002; tidak diuji | — |
| C-15 | Token runner terpisah | NOT AVAILABLE — menunggu Owner | Kode dan `c15.probe.ps1` siap; token DPAPI hanya masuk proses anak (diuji dengan nilai palsu, tidak muncul di argumen atau log). Uji nyata setelah Owner memasang token | Wajib VERIFIED sebelum mode otonom |

## C-09

Terverifikasi pada [PR #2](https://github.com/yusufarst/PRANATA-UNY/pull/2) (2026-11-01), dibaca dengan `gh api`:

- Akun bot: `chatgpt-codex-connector[bot]`.
- Permintaan: review otomatis repositori berjalan saat PR dibuka. Pada PR #2 komentar `@codex review` juga terkirim, jadi keduanya terjadi; §6.3 menghendaki satu saja, dan mulai sekarang komentar hanya dipakai bila review otomatis tidak muncul dalam jendela konfirmasi.
- Sinyal mulai: reaksi `eyes` pada PR atau komentar permintaan, serta komentar ringkasan dengan penanda `codex-review-state:v1` berisi JSON `status`, `commit_sha`, `has_any_findings`, `has_unresolved_findings`.
- Sinyal selesai: `status: completed` pada penanda itu; temuan berupa komentar review berpenanda `codex-code-review-finding:v1`; tanpa temuan ditandai reaksi `+1`.
- Durasi teramati: sekitar 5 menit (13:49:02 → 13:54:13 UTC), dalam batas §14.
- Hasil pada `bafb717`: dua P1 dan satu P2, ketiganya valid dan diperbaiki.

## Sisa risiko yang diterima

- Guard berbasis teks tidak dapat melihat isi skrip yang ditulis lalu dijalankan. Lapisan lain: allowlist runner tanpa PowerShell bebas, `disallowedTools` jalur pengaman, gerbang merge, ruleset `main` setelah TASK-005.
- Penolakan kata `docker` di dalam teks perintah dapat salah sasaran (misalnya `git commit -m`); pakai `git commit -F`. Ini sengaja: pembungkus seperti `powershell -c` tetap tertangkap.
- `PSScriptAnalyzer` tidak terpasang dan tidak dipasang (tanpa instalasi global); pengganti: parser AST PowerShell dan pemeriksaan ASCII pada semua skrip.

## Sumber daya mesin

RAM bebas selama TASK-001 berkisar 0,2–1,1 GB tanpa beban kontainer PRANATA; pemakai terbesar bukan proses PRANATA. Tidak ada proses PRANATA tertinggal setelah uji. Ini mendukung keputusan Owner memindahkan uji berat ke runner GitHub-hosted.
