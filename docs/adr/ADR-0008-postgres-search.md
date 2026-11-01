# ADR-0008 — Pencarian memakai PostgreSQL (pg_trgm dan full-text) tanpa mesin pencari eksternal

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

CAP-18 meminta pencarian dan pelaporan berskala 1 juta aset dengan scope akses; COST_POLICY mengutamakan kemampuan bawaan.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| PostgreSQL pg_trgm + tsvector (dipilih) | Satu sumber kebenaran, scope akses mudah diterapkan, gratis |
| Meilisearch/Elasticsearch | Layanan tambahan, sinkronisasi indeks dan risiko kebocoran scope |

## Keputusan dan alasan

Indeks GIN trigram untuk nama/kode/nomor seri; pencarian awalan/infix dengan batas hasil; pencarian global menggabungkan penyedia pencarian per modul yang menerapkan scope akses. Target performa di PERFORMANCE_AND_SCALE.

## Risiko

- Kueri trigram lambat untuk istilah pendek → minimum 3 karakter, batas hasil, filter wajib cakupan.

## Biaya

Tidak ada.

## Dampak kompatibilitas dan data

Indeks GIN pada tabel besar (ruang disk dicatat P9).

## Migrasi dan rollback

Mesin eksternal dapat ditambahkan di balik `SearchProvider` bila kelak terbukti perlu.

## Yang terdampak

DATA_MODEL indeks; PERFORMANCE_AND_SCALE.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
