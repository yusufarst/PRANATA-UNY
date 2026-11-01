# ADR-0002 — Konvensi data: tabel berprefiks modul, ID ganda, desimal dan tanpa hard delete

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

Kebutuhan jejak historis (BR-026, INV-017), pencegahan enumerasi ID di URL, kebenaran nilai uang, skala 1 juta aset dan pemisahan kepemilikan modul.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Prefiks modul di skema `public` (dipilih) | Kepemilikan jelas, nol risiko kompatibilitas tooling Laravel |
| Skema PostgreSQL per modul | Rapi, tetapi menambah konfigurasi search_path dan risiko tooling (migrate:fresh, paket pihak ketiga) |
| UUID sebagai PK | Indeks lebih besar dan lambat untuk tabel jutaan baris; ULID publik terpisah lebih efisien |
| Float untuk uang | Ditolak: galat pembulatan |

## Keputusan dan alasan

Tabel berprefiks modul pada skema `public`; PK `bigint identity` dan `public_id` ULID untuk akar agregat yang tampil di URL; `timestamptz` UTC dan `date` untuk tanggal bisnis; `numeric` untuk uang/kuantitas dengan aritmetika desimal presisi; status `varchar` + CHECK; `lock_version` untuk konkurensi optimistis; tidak ada hard delete atau soft delete pada record bisnis — pembatalan memakai status dan riwayat ([DATA_MODEL](../04-architecture/DATA_MODEL.md)).

## Risiko

- Volume ruang tambahan kolom ganda ID kecil.
- Tanpa delete, data draf menumpuk → status DISCARDED dan arsip menurut kebijakan retensi kelak (GAP-023).

## Biaya

Tidak ada.

## Dampak kompatibilitas dan data

Menjadi dasar semua migrasi; perubahan setelah ada data memerlukan migrasi expand–contract.

## Migrasi dan rollback

Diterapkan sejak migrasi pertama; pembalikan sebelum produksi murah, sesudahnya mahal.

## Yang terdampak

DATA_MODEL; semua Task berdampak skema.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
