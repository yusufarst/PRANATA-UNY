# ADR-0009 — Penyimpanan berkas pada disk privat lokal dengan unduhan terotorisasi

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

Bukti, dokumen bertanda tangan, dokumen penyedia dan penawaran bersifat internal/rahasia (OD-28); penyimpanan eksternal bukan dependency V1 (BR-027).

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Disk privat lokal + cadangan offsite (dipilih) | Sederhana, gratis, terkendali |
| Object storage awan | Biaya dan keputusan lokasi data; tetap tersedia sebagai opsi kelak via Flysystem |

## Keputusan dan alasan

Berkas di luar webroot (`storage/app/private`), nama acak, ditulis sekali tanpa timpa, SHA-256 dan ukuran dihitung server, metadata di `file_objects` dengan identitas isi yang tidak dapat diubah (tautan ke pemilik bersegel juga tidak dapat diubah atau dilepas); unggahan divalidasi MIME/ekstensi/ukuran; unduhan hanya melalui controller yang memeriksa izin subjek, memverifikasi SHA-256 sebelum mengirim dan mencatat audit untuk berkas rahasia; dicadangkan bersama basis data ke lokasi offsite pilihan Owner (P9).

## Risiko

- Disk penuh → pemantauan kapasitas dan ambang peringatan.
- Kehilangan server → cadangan offsite + uji restore.

## Biaya

Kapasitas disk server/offsite yang dibiayai klien.

## Dampak kompatibilitas dan data

Tabel `file_objects`, `file_links`, `file_sealed_owner_types`.

## Migrasi dan rollback

Migrasi ke object storage dengan menyalin berkas dan mengganti disk.

## Yang terdampak

SECURITY_MODEL, INFRASTRUCTURE, Task berkas.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
