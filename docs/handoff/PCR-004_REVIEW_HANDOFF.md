# Handoff Second Review terfokus — PCR-004 (jalur remediasi pasca-DONE, M1)

Status: PCR-004 — lihat [APPROVAL_RECORDS PCR-004](../00-governance/APPROVAL_RECORDS.md#pcr-004) | Updated: 2026-11-07 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT) dan Owner

Review **terfokus dan proporsional** (FRZ-001, GOV-11): hanya delta PCR-004. PR #52–#55 sudah direview atau diperiksa fakta oleh eksekutor dan tidak direview ulang di sini; C1–C5 dan S1–S4 di luar ruang lingkup.

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Base (`origin/main`, terverifikasi) | `351ba2f17c1764283e66215f6069ed64872fb97a` (16/78 DONE; TASK-017 READY, belum dimulai; hold Owner aktif) |
| Head | SHA HEAD cabang `docs/pcr-004-post-done-remediation` yang dilaporkan bersama permintaan ini; satu commit di atas base |
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
| M1-AC-07 | Sebelum merge entri 0, `merge-verified.ps1 -DryRun` versi `main` atas PR entri 0 menolak **hanya** dengan alasan status DONE; alasan lain = berhenti |

**Uji regresi keamanan wajib (offline, `scripts/executor/tests/scripts.tests.ps1`):** positif satu entri cocok; negatif tanpa REM, REM PROPOSED, REM CLOSED, dua REM ACTIVE yang cocok, blok rusak, Task tidak terdaftar, cabang lain, penerus dengan akhiran bukan `-rN`, berkas di luar jalur, TASK_PLAN berubah, berkas Task berubah di luar COMPLETION EVIDENCE, `STATUS` berubah, mode AUTONOMOUS, `-Bootstrap`, BLOCKED dengan entri; serta setiap pemeriksaan §8.1 lain gagal sendiri pada jalur remediasi. Uji guard (`guard.tests.ps1`) tetap lulus tanpa perubahan.

## 5. Urutan publikasi

1. **Planning:** review terfokus → persetujuan Owner → PCR-004 terbit lewat PR dengan `ci-gate` dan fast-forward terverifikasi → commit transisi (REM-001 ACTIVE), terbit dengan cara yang sama.
2. **Eksekutor, entri 0:** cabang `fix/task-001-post-done-remediation-gate` dari `main` terbaru; implementasi M1-AC-01–07 dan uji; CI; review independen (TASK-001 HIGH) dan Codex atau pengganti; DryRun versi `main` (M1-AC-07); merge dengan skrip dari HEAD PR yang direview; verifikasi `main`.
3. **Entri 1 (#53) lalu entri 2 (#52):** cabang penerus `-r2` dari `main` terbaru dengan cherry-pick; bukti patch identik (`git diff 351ba2f <head-lama>` = `git diff <base-baru> <head-baru>`); CI baru; disposisi review asal dicatat sebagai review pengganti untuk SHA baru; merge; PR asal ditutup dengan rujukan.
4. **Entri 3 (#54) lalu entri 4 (#55):** penerus `docs/reconcile-task-015-r3` dan `docs/reconcile-task-016-r3`. Tambahan hanya: penanda REM-001 dan SHA merge entri 1–3 pada butir remediasi, serta keadaan akhir di CURRENT_HANDOFF (remediasi terbit, hold tetap, TASK-017 belum dimulai, C1–C5 dan S1–S4 serta PI-001–PI-003 masih terbuka). Verifikasi delta independen hanya atas baris tambahan; CI baru; merge.
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
