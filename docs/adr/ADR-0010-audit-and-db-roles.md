# ADR-0010 — Jejak audit append-only berpartisi dan peran basis data terpisah

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

CAP-19 dan IX-024 menuntut jejak audit yang dapat dipercaya; OD-28 mensyaratkan pembacaan teraudit oleh Super Admin; INV-017 melarang penimpaan riwayat.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Tabel audit sendiri + peran DB tanpa UPDATE/DELETE (dipilih) | Kendali penuh atas isi, partisi dan hak |
| Paket activity log generik | Kurang kendali partisi/hak; tetap butuh peran DB |
| Log berkas saja | Sulit dicari dan diaudit |

## Keputusan dan alasan

`audit_events` berpartisi bulanan, ditulis oleh Action dan listener keamanan; peran `pranata_app` hanya `SELECT, INSERT` pada tabel append-only; migrasi memakai peran pemilik terpisah; trigger menolak UPDATE/DELETE. Pembacaan rahasia mewajibkan alasan dan gagal tertutup bila audit tidak tercatat.

## Risiko

- Pertumbuhan volume → partisi dan retensi (GAP-023).
- Konfigurasi peran salah → uji hak di CI dan pada checklist rilis.

## Biaya

Ruang disk (dicatat P9).

## Dampak kompatibilitas dan data

Tabel `audit_events`, `workflow_transitions` dan tabel append-only lain.

## Migrasi dan rollback

Peran dapat disesuaikan dengan migrasi; data audit tidak pernah dihapus tanpa kebijakan retensi.

## Yang terdampak

DATA_MODEL §2/§12, SECURITY_MODEL, INFRASTRUCTURE.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
