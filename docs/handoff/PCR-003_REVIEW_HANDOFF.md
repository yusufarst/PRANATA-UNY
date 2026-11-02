# Handoff Second Review terfokus — PCR-003 (penonaktifan akun)

Status: PCR-003 — lihat [APPROVAL_RECORDS PCR-003](../00-governance/APPROVAL_RECORDS.md#pcr-003) | Updated: 2026-11-02 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Review **terfokus dan proporsional** (FRZ-001, GOV-11): hanya delta PCR-003. Baseline P0–P11, PCR-001, PCR-002 dan Task yang sudah DONE tidak direview ulang.

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Base (`origin/main`, terverifikasi) | `84d00708d4c51a175efb0804d1b2e519a8491bb9` (TASK-009 sedang berjalan) |
| Head | SHA HEAD cabang `docs/pcr-003-account-deactivation` yang dilaporkan bersama permintaan ini; satu commit di atas base; diff `git diff 84d0070..<SHA HEAD>` |
| Keputusan Owner | OD-38 (U-014, 2026-11-02): ganti penghapusan akun bawaan starter kit dengan penonaktifan; tanpa DELETE pada `users`; penghapusan permanen tidak tersedia sampai kebijakan retensi institusi tervalidasi (GAP-023) |
| Masukan utama | Usulan TASK PLAN CHANGE eksekutor di CURRENT_HANDOFF ("account deletion", `5330ce7`) |

## 2. Berkas berubah (7)

| Berkas | Perubahan |
|---|---|
| `docs/11-tasks/TASK-010.md` | IN SCOPE: hapus `profile.destroy`, aksi kontroler dan UI hapus akun; OUT OF SCOPE: penonaktifan (TASK-011), penghapusan permanen (GAP-023); SECURITY: tanpa DELETE `users`; ROUTE IMPACT; TAC-07 baru; uji integritas rute; DO NOT DO; REFERENCES dan TRACEABILITY (GAP-023, OD-38) |
| `docs/11-tasks/TASK-011.md` | OBJECTIVE: makna "nonaktifkan"; IN SCOPE: penonaktifan (DEACTIVATED, `deactivated_at`, cabut semua sesi, `audit_events`, baris tetap, RB-13); OUT OF SCOPE: penghapusan permanen; SECURITY; DATABASE IMPACT; TAC-06 baru; uji feature; REFERENCES dan TRACEABILITY |
| `docs/00-governance/DECISION_LOG.md`, `DECISION_INDEX.md` | OD-38 (tambahan) |
| `docs/00-governance/SOURCE_INVENTORY.md` | U-014 |
| `docs/00-governance/APPROVAL_RECORDS.md` | PCR-003 (tambahan), status menunggu review dan gerbang publikasi |
| `docs/handoff/PCR-003_REVIEW_HANDOFF.md` | Berkas ini |

Tidak berubah: DATA_MODEL §2 dan §5, SECURITY_MODEL, OPERATIONS_RUNBOOK RB-13 (sudah sesuai: tanpa DELETE `users`, status DEACTIVATED, riwayat tetap); TASK-008 dan kontrol basis datanya; TASK-009 dan berkas status yang sedang dipakai eksekutor (TASK_PLAN, CURRENT_HANDOFF, PHASE_STATUS); 78 ID Task dan seluruh DEPENDS ON.

## 3. Pemetaan syarat Owner

| # | Syarat | Dipenuhi di |
|---|---|---|
| 1 | Ganti penghapusan akun bawaan dengan penonaktifan | OD-38; TASK-010 IN SCOPE, TAC-07; TASK-011 IN SCOPE, TAC-06 |
| 2 | Record pengguna dan riwayat audit tetap | TASK-011 IN SCOPE, TAC-06 |
| 3 | Sesi aktif dicabut saat dinonaktifkan | TASK-011 IN SCOPE, TAC-06 |
| 4 | Tanpa DELETE pada `users` | TASK-010 SECURITY, TAC-07, DO NOT DO; TASK-011 SECURITY; OD-38 |
| 5 | `profile.destroy` dihapus | TASK-010 IN SCOPE, ROUTE IMPACT, TAC-07 |
| 6 | Penghapusan permanen tidak tersedia sebelum kebijakan retensi tervalidasi | TASK-010/011 OUT OF SCOPE; OD-38; GAP-023 tetap OPEN |
| 7 | Hanya kontrak dan traceability terdampak | Bagian 2 |
| 8 | Baseline 78 Task dan keamanan tetap | APPROVAL_RECORDS PCR-003 |
| 9 | Cabang terisolasi, checkout TASK-009 tidak disentuh | Cabang `docs/pcr-003-account-deactivation` dari worktree terpisah; tanpa berkas status aktif |
| 10–11 | Terbit hanya setelah TASK-009 DONE dan review diterima; TASK-010 BLOCKED sampai publikasi terverifikasi | APPROVAL_RECORDS PCR-003, Gate |

## 4. Risiko ringkas

| Risiko | Mitigasi |
|---|---|
| Uji kit yang memanggil `profile.destroy` tertinggal | TAC-07 mewajibkan uji itu dihapus atau diganti |
| Pencabutan sesi butuh DELETE pada `sessions` | Sudah diizinkan untuk tabel kerangka (DATA_MODEL §2); tidak ada hak baru |
| Cabang tertinggal setelah TASK-009 DONE | Dibawa ke atas `main` baru tanpa menulis ulang riwayat yang terbit; konflik kecil karena berkas status tidak disentuh |
| Akhir penugasan aktif saat offboarding | Tidak diubah; RB-13 dan Task penugasan yang ada tetap berlaku |

## 5. Pertanyaan pemeriksaan

1. Apakah TASK-010 TAC-07 dan TASK-011 TAC-06 cukup membuktikan penonaktifan tanpa penghapusan dan tanpa uji berlebihan?
2. Apakah ada konflik dengan DATA_MODEL, SECURITY_MODEL, RB-13, TASK-008 atau GAP-023?
3. Apakah gerbang publikasi (setelah TASK-009 DONE) dan status BLOCKED TASK-010 jelas?

## 6. Format hasil

```text
VERDICT: APPROVED | APPROVED WITH CONDITIONS | REVISE | BLOCKED
IDENTITAS YANG DIREVIEW: <SHA HEAD cabang PCR-003>
TEMUAN:
| ID | Tingkat P0–P3 | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <bila ada>
```
