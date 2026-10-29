# ADR-0005 — Generasi dokumen dari template DOCX dan konversi PDF melalui Gotenberg

Status: PROPOSED (P4 candidate, AUTH-012) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: belum ada — memerlukan persetujuan Owner atas kandidat P4

## Konteks dan bukti

OD-08/OD-29 meminta dokumen terkait dihasilkan dari data sekali-input; contoh historis berupa dokumen Word/PDF dengan format Indonesia; petugas perlu dapat menyesuaikan template resmi tanpa pengembang.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Template DOCX (PhpWord TemplateProcessor) + Gotenberg (dipilih) | Template dapat disunting di Word oleh petugas; PDF akurat dari LibreOffice; self-hosted gratis |
| HTML → PDF (browser headless) | Tata letak surat resmi sulit dikendalikan petugas; template butuh pengembang |
| Layanan dokumen berbayar | Ditolak: biaya berulang dan data keluar |

## Keputusan dan alasan

Template DOCX berversi dengan validasi placeholder, snapshot data bertipe, generasi di antrean, konversi PDF oleh kontainer Gotenberg lokal, banner DRAF/SIMULASI wajib, nomor dari layanan Numbering, revisi tanpa penimpaan, bukti tanda tangan berupa unggahan ([DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md)).

## Risiko

- Gotenberg tidak tersedia → DOCX tetap tersedia, konversi dapat diulang.
- Template rusak/placeholder salah → validasi saat unggah.

## Biaya

Nol lisensi; konsumsi CPU/RAM server untuk kontainer Gotenberg (dicatat P9).

## Dampak kompatibilitas dan data

Tabel `document_*`, `file_*`; berkas DOCX/PDF di disk privat.

## Migrasi dan rollback

Komponen dapat diganti (mis. konverter lain) di balik layanan Documents tanpa mengubah modul domain.

## Yang terdampak

DOCUMENT_ENGINE, WF-028, Task dokumen.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
