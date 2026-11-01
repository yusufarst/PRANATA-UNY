# ADR-0004 — Ledger append-only untuk stok dan aset dengan proyeksi yang dikunci

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

INV-009, INV-020 dan BR-030 menuntut posisi stok per gudang diturunkan dari ledger yang diterima dan tidak negatif; BR-026/INV-017 menuntut riwayat tidak ditimpa. Serah terima, pengembalian dan transfer dapat terjadi bersamaan.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Ledger append-only + saldo dikunci baris (dipilih) | Kebenaran dapat diaudit, saldo cepat dibaca, constraint `quantity >= 0` di DB |
| Hanya saldo yang diedit | Ditolak: riwayat hilang, rawan race |
| Saldo dihitung on-the-fly dari ledger | Benar tetapi lambat pada skala; tetap dipakai untuk verifikasi berkala |

## Keputusan dan alasan

`inv_stock_movements` dan `asset_transactions` append-only (trigger + hak peran DB). `inv_stock_balances` dan kolom proyeksi `asset_assets` diperbarui dalam transaksi yang sama dengan penguncian `SELECT … FOR UPDATE` menurut urutan kunci P6. Koreksi memakai record pembalik — paling banyak satu pembalikan penuh per entri, diperiksa constraint dan trigger; entri yang berasal dari peristiwa fisik membawa identitas sumber yang unik sehingga peristiwa yang sama tidak dapat diposting dua kali ([DATA_MODEL §1](../04-architecture/DATA_MODEL.md#1-konvensi)). Job berkala membandingkan saldo dengan jumlah ledger dan melaporkan selisih sebagai insiden.

## Risiko

- Deadlock bila urutan kunci dilanggar → aturan urutan kunci dan uji konkurensi.
- Proyeksi menyimpang karena bug → job verifikasi dan perbaikan melalui transaksi koreksi.

## Biaya

Tidak ada.

## Dampak kompatibilitas dan data

Tabel ledger dan saldo pada Inventory/Asset.

## Migrasi dan rollback

Struktur sejak awal; tidak ada rollback setelah data riil selain migrasi terkontrol.

## Yang terdampak

DATA_MODEL §15–16; CONCURRENCY_AND_INTEGRITY; WF-023/030/031, WF-017–021.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
