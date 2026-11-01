# ADR-0006 — Antrean, cache, sesi dan lock memakai driver basis data; tanpa Redis/Valkey pada V1

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

AICWDF dan TOOLCHAIN: Redis/Valkey hanya bila dibenarkan. Beban ~200 pengguna; job berlatar terutama dokumen, ekspor, impor dan penyusutan.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Driver database (dipilih) | Satu komponen stateful lebih sedikit; cukup untuk beban; cadangan ikut basis data |
| Redis/Valkey | Lebih cepat, tetapi menambah layanan, memori dan operasi tanpa kebutuhan terbukti |

## Keputusan dan alasan

Laravel queue `database`, cache `database`, session `database`, lock `cache_locks`. Antrean terpisah: `default`, `documents`, `exports`, `imports`, `maintenance`. Kriteria peninjauan ulang: p95 latensi antrean > 30 detik pada beban normal selama 7 hari, atau kontensi lock/sesi yang terukur; perubahan memerlukan ADR baru.

## Risiko

- Kontensi tabel `jobs` pada lonjakan → antrean terpisah, batas concurrency, pemantauan Pulse.

## Biaya

Tidak ada.

## Dampak kompatibilitas dan data

Tabel kerangka Laravel.

## Migrasi dan rollback

Beralih ke Redis cukup dengan konfigurasi driver dan layanan baru.

## Yang terdampak

INFRASTRUCTURE, PERFORMANCE_AND_SCALE.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
