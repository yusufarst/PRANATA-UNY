# Ruleset `main` dan tag rilis

Status: instruksi TASK-005 untuk diterapkan Owner | Updated: 2026-11-02 (Asia/Jakarta) | Pemilik: Owner | Keputusan: D-08 opsi A ([EXECUTOR_OPERATING_MODEL §8.2](../11-tasks/EXECUTOR_OPERATING_MODEL.md#82-metode-merge-keputusan-owner))

Hanya Owner yang menerapkan ruleset. Terapkan setelah `ci-gate` terbukti pada PR ([QUALITY_GATES §4](../08-testing/QUALITY_GATES.md#4-pipeline-ci-github-actions)). Token eksekutor tidak memegang hak administrasi.

## Ruleset untuk `main`

Buka **Settings → Rules → Rulesets → New ruleset → New branch ruleset** pada `yusufarst/PRANATA-UNY`.

| Pengaturan | Nilai |
|---|---|
| Ruleset name | `main` |
| Enforcement status | Active |
| Bypass list | Kosong |
| Target branches | Include by pattern: `main` |
| Restrict deletions | Aktif |
| Require linear history | Aktif |
| Block force pushes | Aktif |
| Require status checks to pass | Aktif, dengan check `ci-gate` dari GitHub Actions |
| Require branches to be up to date | Nonaktif |
| Require a pull request before merging | **Nonaktif** |

Aturan PR tetap nonaktif karena D-08 opsi A memajukan `main` dengan fast-forward terverifikasi, agar author dan committer tetap identitas Owner. Keharusan PR ditegakkan oleh kebijakan, hook dan `scripts/executor/merge-verified.ps1`.

## Ruleset untuk tag rilis

Buat **New tag ruleset** bernama `release-tags`, Active, bypass list kosong, target tag pattern `v*`, dengan Restrict creations, Restrict updates, Restrict deletions dan Block force pushes aktif. Tag rilis dibuat Owner.

## Verifikasi

Setelah kedua ruleset aktif, beri tahu eksekutor. Eksekutor memeriksa hasilnya dengan pembacaan API saja, lalu membuktikan TAC-05 TASK-005: commit yang belum lulus `ci-gate` ditolak saat didorong ke `main`, dan `main` tidak bergerak. Mode AUTONOMOUS tetap memerlukan prasyarat lain di [EXECUTOR_OPERATING_MODEL §1](../11-tasks/EXECUTOR_OPERATING_MODEL.md#1-prasyarat-aktivasi).
