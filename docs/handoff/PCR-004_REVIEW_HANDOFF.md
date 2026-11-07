# Handoff Second Review terfokus — PCR-004 (jalur remediasi pasca-DONE, M1)

Status: PCR-004 — lihat [APPROVAL_RECORDS PCR-004](../00-governance/APPROVAL_RECORDS.md#pcr-004) | Updated: 2026-11-07 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT) dan Owner

> Selesai: review delta atas `807ddc4` memberi verdict APPROVED; Owner menyetujui PCR-004 dan mengaktifkan REM-001 (U-015); PCR-004 terbit di `main` pada 2026-11-07. Handoff ini kini historis.

Review **terfokus dan proporsional** (FRZ-001, GOV-11): hanya delta PCR-004. PR #52–#55 sudah direview atau diperiksa fakta oleh eksekutor dan tidak direview ulang di sini; C1–C5 dan S1–S4 di luar ruang lingkup.

## 0. Koreksi atas review `f51b7a6` dan review delta `80b78b0` (REVISE)

| Temuan | Koreksi | Tempat memeriksa |
|---|---|---|
| PCR004-SR-P1-01 Entri tidak terikat pada sumber yang direview | Entri 1–4 memuat `SOURCE` (base..HEAD PR asal, SHA penuh) dan `EQUIVALENCE`; nomor PR = provenans, PR penerus bernomor dan ber-SHA baru; skrip membuktikan kesetaraan per blob (IDENTICAL) atau dengan SHA-256 diff yang diverifikasi independen (DELTA); disposisi review baru untuk SHA HEAD baru dengan `source_sha`; selain itu tolak | EXECUTOR_OPERATING_MODEL §8.5 butir 5; APPROVAL_RECORDS REM-001; M1-AC-08 |
| PCR004-SR-P2-01 Checkpoint tidak diselaraskan | Sebelum setiap DryRun/merge (termasuk DryRun skrip lama entri 0) checkpoint ditulis lewat pembantu state: `task_id` Task entri, SUPERVISED, `remediation` = `REM-nnn/<entri>`; pemeriksaan kecocokan tetap; `hold.json` tidak disentuh; checkpoint dikembalikan sesudah entri terakhir | §8.5 butir 3; M1-AC-07, M1-AC-09 |
| PCR004-SR-P2-02 Urutan entri tidak ditegakkan | Entri N hanya bila entri 0..N-1 punya PR merged dari keluarganya dengan HEAD leluhur `main`, dan entri N belum terbit; sekali pakai; perbaikan jalur memerlukan REM baru | §8.5 butir 7–8; REM-001 `ORDER`; M1-AC-10 dan uji negatif |
| PCR004-DELTA-P1-01 (review delta `80b78b0`) PR historis dapat terhitung sebagai bukti publikasi | Bukti publikasi kini terikat pada REM: commit aktivasi ditentukan dari riwayat `main`; PR pendahulu dan kandidat wajib turunan commit aktivasi, memuat `Remediation: REM-001 entry k`, merged dengan HEAD leluhur `main`, dan hanya mengubah jalur entrinya; PR sebelum aktivasi (#49, #51) tidak pernah dihitung; urutan dan sekali pakai ditegakkan dari bukti itu saja | §8.5 butir 7; M1-AC-10 dan uji negatif historis |

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Base (`origin/main`, terverifikasi) | `351ba2f17c1764283e66215f6069ed64872fb97a` (16/78 DONE; TASK-017 READY, belum dimulai; hold Owner aktif) |
| Head | SHA HEAD cabang `docs/pcr-004-post-done-remediation` yang dilaporkan bersama permintaan ini: `f51b7a6` ditambah dua commit koreksi; review delta terakhir memeriksa `git diff 80b78b0..<SHA HEAD>` |
| Masukan | Direktif Owner M1 (2026-11-07); usulan audit eksekutor di PR #55 (butir M1); `scripts/executor/lib/merge-gate.ps1` dan `merge-verified.ps1` di `main`; PR #52–#55 |
| Masalah | `Get-PranataMergeFailures` menolak setiap PR yang Task-nya berstatus DONE di `main` (`task: contract status on main is 'DONE'`), sehingga koreksi yang diizinkan Owner tidak dapat terbit tanpa salah atribusi ke TASK-017 |

## 2. Berkas berubah (4)

| Berkas | Perubahan |
|---|---|
| `docs/11-tasks/EXECUTOR_OPERATING_MODEL.md` | §8.1 no. 7 (pemeriksaan status yang selama ini hanya ada di skrip, kini tertulis) dan §8.5 baru: jalur remediasi pasca-DONE |
| `docs/00-governance/APPROVAL_RECORDS.md` | PCR-004 (status menunggu, isu PI-001–PI-003) dan REM-001 (PROPOSED, blok mesin lima entri) |
| `docs/11-tasks/TASK-028.md` | Prasyarat READY: PI-001 dan PI-002 diputuskan lebih dahulu |
| `docs/handoff/PCR-004_REVIEW_HANDOFF.md` | Berkas ini |

Tidak berubah: skrip eksekutor (diubah eksekutor lewat entri 0 setelah persetujuan), workflow, ruleset, izin PAT, kewenangan jalankan ulang CI, TASK_PLAN, status Task, CURRENT_HANDOFF (milik PR #55), kontrak TASK-015/TASK-016/TASK-017.

## 3. Desain singkat

- Pengecualian **hanya** pemeriksaan status DONE (§8.1 no. 7). Semua pemeriksaan lain tetap: `ci-gate` pada SHA HEAD, review sesuai kelas risiko kontrak, disposisi review untuk SHA yang sama, Codex atau pengganti, leluhur `main`, `main` tidak bergerak, GOV-08, jalur pengaman SUPERVISED, fast-forward terverifikasi di bawah ruleset (riwayat linear, tanpa force push).
- Otorisasi berbentuk data di `main`: REM-001 ACTIVE dengan entri per Task, keluarga cabang, PR, jalur berkas persis, dan tujuan. Rekaman PROPOSED, CLOSED, rusak atau ganda = tolak. Menutup REM-001 langsung mematikan jalur ini.
- Kecocokan fail-closed: berkas di luar entri, perubahan TASK_PLAN, perubahan berkas Task di luar `## COMPLETION EVIDENCE`, `STATUS` selain DONE, mode otonom atau bootstrap = tolak.
- Atribusi benar: koreksi tetap di bawah TASK-015/TASK-016 (dan TASK-001 untuk perubahan skrip), tidak di bawah TASK-017.

## 4. Kriteria penerimaan (untuk implementasi entri 0 oleh eksekutor)

| ID | Kriteria |
|---|---|
| M1-AC-01 | `merge-verified.ps1` membaca rekaman `REM-nnn` dari APPROVAL_RECORDS pada SHA `main` yang diperiksa (bukan dari cabang PR) dan hanya menerima tepat satu rekaman ACTIVE dengan entri yang cocok |
| M1-AC-02 | Task DONE lulus pemeriksaan status hanya bila entri cocok: Task, keluarga cabang (`<cabang>` atau `<cabang>-rN`, N ≥ 2), jalur persis, TASK_PLAN tidak berubah, berkas Task hanya berubah di `## COMPLETION EVIDENCE` dengan `STATUS: DONE` tetap |
| M1-AC-03 | Status selain TODO/READY/IN_PROGRESS/VERIFYING/DONE (mis. BLOCKED, SUPERSEDED) tetap ditolak walaupun ada entri; Task non-DONE tetap memakai jalur biasa tanpa perubahan perilaku |
| M1-AC-04 | Jalur remediasi ditolak pada mode AUTONOMOUS dan bersama `-Bootstrap` |
| M1-AC-05 | Semua pemeriksaan lain §8.1 tetap gagal sendiri-sendiri pada jalur remediasi (uji negatif per pemeriksaan) |
| M1-AC-06 | Keluaran skrip mencatat `REM-001 entry N` dan alasan penolakan yang persis |
| M1-AC-07 | Sebelum merge entri 0, `merge-verified.ps1 -DryRun` versi `main` atas PR entri 0, dengan checkpoint `task_id` TASK-001, menolak **hanya** dengan alasan status DONE; alasan lain = berhenti |
| M1-AC-08 | Kesetaraan sumber (§8.5 butir 5): blob `main` = blob base `SOURCE` untuk setiap jalur entri; IDENTICAL = blob HEAD PR sama dengan HEAD `SOURCE`; DELTA = SHA-256 diff `HEAD SOURCE..HEAD PR` pada jalur entri sama dengan nilai di disposisi review untuk SHA HEAD PR; disposisi wajib memuat `source_sha` = HEAD `SOURCE` dan `equivalence`; selain itu tolak |
| M1-AC-09 | Checkpoint (§8.5 butir 3): pemeriksaan kecocokan `task_id` 8.1 tetap; checkpoint `remediation` wajib sama dengan `REM-nnn/<entri>` yang dicocokkan; skrip tidak menulis checkpoint maupun `hold.json` |
| M1-AC-10 | Urutan dan bukti publikasi (§8.5 butir 7): commit aktivasi ditentukan skrip dari riwayat APPROVAL_RECORDS di `main`; bukti entri k = PR merged dari keluarga entri k dengan baris `Remediation: REM-001 entry k`, SHA HEAD turunan commit aktivasi dan leluhur `main`, dan perubahan hanya pada jalur entri k; entri N diterima hanya bila entri 0..N-1 masing-masing punya tepat satu bukti, entri N belum punya, dan PR kandidat memuat barisnya sendiri dengan HEAD turunan commit aktivasi; PR sebelum aktivasi tidak pernah dihitung |

**Uji regresi keamanan wajib (offline, `scripts/executor/tests/scripts.tests.ps1`):** positif satu entri cocok; negatif tanpa REM, REM PROPOSED, REM CLOSED, dua REM ACTIVE yang cocok, blok rusak, Task tidak terdaftar, cabang lain, penerus dengan akhiran bukan `-rN`, berkas di luar jalur, TASK_PLAN berubah, berkas Task berubah di luar COMPLETION EVIDENCE, `STATUS` berubah, mode AUTONOMOUS, `-Bootstrap`, BLOCKED dengan entri; serta setiap pemeriksaan §8.1 lain gagal sendiri pada jalur remediasi. Ditambah (koreksi review): blob `main` berbeda dari base `SOURCE`; IDENTICAL dengan blob HEAD berbeda; DELTA dengan SHA-256 diff berbeda atau tanpa verifikasi delta; disposisi untuk SHA atau `source_sha` lain; checkpoint `task_id` lain; `remediation` kosong atau entri lain; entri 1 sebelum entri 0 terbit; entri 3 sebelum entri 2; entri yang sudah terbit dipakai lagi; PR pendahulu merged tetapi HEAD-nya bukan leluhur `main`. Skenario historis nyata: #49 (`docs/reconcile-task-015`, HEAD `06d83a7`, merged) tidak dihitung sebagai entri 3 dan #51 (`docs/reconcile-task-016`, HEAD `351ba2f`, merged) tidak dihitung sebagai entri 4, karena keduanya bukan turunan commit aktivasi; entri 4 ditolak bila entri 3 yang asli belum terbit walaupun #49 ada; PR pendahulu tanpa baris `Remediation`, dengan nomor entri lain, atau dengan berkas di luar jalur entrinya tidak dihitung; PR kandidat tanpa barisnya sendiri ditolak; dua bukti untuk satu entri ditolak. Uji guard (`guard.tests.ps1`) tetap lulus tanpa perubahan.

## 5. Urutan publikasi

1. **Planning:** review terfokus → persetujuan Owner → PCR-004 terbit lewat PR dengan `ci-gate` dan fast-forward terverifikasi → commit transisi (REM-001 ACTIVE), terbit dengan cara yang sama.
2. **Eksekutor, entri 0:** cabang `fix/task-001-post-done-remediation-gate` dari `main` terbaru; implementasi M1-AC-01–07 dan uji; CI; review independen (TASK-001 HIGH) dan Codex atau pengganti; DryRun versi `main` (M1-AC-07); merge dengan skrip dari HEAD PR yang direview; verifikasi `main`.
3. **Entri 1 (#53) lalu entri 2 (#52):** checkpoint diselaraskan (§8.5 butir 3); cabang penerus `-r2` dari `main` terbaru dengan cherry-pick; kesetaraan IDENTICAL terhadap `SOURCE` dibuktikan skrip; CI baru; disposisi review baru untuk SHA HEAD baru dengan `source_sha` dan rujukan ke review sumber; merge; PR asal ditutup dengan rujukan.
4. **Entri 3 (#54) lalu entri 4 (#55):** penerus `docs/reconcile-task-015-r3` dan `docs/reconcile-task-016-r3`. Tambahan hanya: penanda REM-001 dan SHA merge entri 1–3 pada butir remediasi, serta keadaan akhir di CURRENT_HANDOFF (remediasi terbit, hold tetap, TASK-017 belum dimulai, C1–C5 dan S1–S4 serta PI-001–PI-003 masih terbuka). Kesetaraan DELTA: verifikasi independen hanya atas diff `HEAD SOURCE..HEAD PR`, SHA-256-nya dicatat di disposisi review; checkpoint diselaraskan; CI baru; merge.
5. **Planning:** REM-001 CLOSED dan catatan publikasi PCR-004 diperbarui. Status Task dan 16/78 tidak berubah.

## 6. Henti dan pemulihan

Henti dan lapor bila: DryRun versi `main` memberi alasan selain DONE; patch penerus tidak identik dan delta belum diverifikasi; temuan P0–P2 baru; berkas di luar entri; CI gagal (tanpa jalankan ulang di luar §7, tanpa tutup-buka PR, tanpa commit kosong); `main` bergerak di luar urutan; identitas tidak cocok; hold berubah. Pemulihan: Owner atau Planning menutup REM-001 (skrip langsung menolak); cacat pada perubahan skrip diperbaiki lewat penerus entri 0, tidak dengan force push atau revert langsung ke `main`.

## 7. Pertanyaan pemeriksaan

1. Apakah pengecualian benar-benar terbatas pada pemeriksaan status DONE, dan apakah kecocokan entri fail-closed?
2. Apakah jalur ini menghindari salah atribusi tanpa membuka ulang TASK-015/TASK-016 atau memulai TASK-017?
3. Apakah urutan publikasi dan aturan penerus (patch identik vs verifikasi delta) cukup tanpa review berulang?
4. Apakah PI-001–PI-003 tetap terbuka dengan tingkat aslinya, dan apakah prasyarat TASK-028 tepat sebagai pelacakan, bukan keputusan?
5. Apakah C1–C5, S1–S4, izin PAT dan kewenangan jalankan ulang CI benar-benar tidak tersentuh?

## 8. Format hasil

```text
VERDICT: APPROVED | APPROVED WITH CONDITIONS | REVISE | BLOCKED
IDENTITAS YANG DIREVIEW: <SHA HEAD cabang PCR-004>
TEMUAN:
| ID | Tingkat P0–P3 | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <bila ada>
```
