# ADR-0007 — Autentikasi lokal Laravel-native dengan pemulihan dibantu admin dan TOTP untuk akun istimewa

Status: PROPOSED (P4 candidate, AUTH-012) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: belum ada — memerlukan persetujuan Owner atas kandidat P4

## Konteks dan bukti

OD-11 menetapkan autentikasi lokal username/email + password; OD-12 tanpa SSO/LDAP/identitas institusi pada V1; FUT-03 mensyaratkan pemulihan password yang dapat dipakai; GAP-015 (kanal pemulihan) OPEN; jawaban provisional Owner: admin dapat mereset password penyedia setelah pemeriksaan identitas (SRC-052).

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Laravel-native + reset dibantu admin + SMTP opsional (dipilih) | Tanpa ketergantungan email institusi; selalu ada jalur pemulihan |
| Wajib email reset | Bergantung SMTP/biaya dan alamat email valid semua pengguna |
| SSO kampus | Ditolak untuk V1 (OD-12) |

## Keputusan dan alasan

Login dengan username atau email + password (profil AICWDF-COMPAT-8 kecuali keputusan Owner lain), throttle per akun+IP, penguncian sementara, sesi database, tanpa registrasi publik; akun dibuat Super Admin atau alur undangan penyedia. Pemulihan: (1) dibantu admin dengan pemeriksaan identitas tercatat dan password sementara sekali-pakai yang wajib diganti; (2) tautan email bila SMTP dikonfigurasi. TOTP: diusulkan wajib untuk Super Admin dan opsional untuk lainnya — **keputusan Owner** ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)). Rincian di [SECURITY_MODEL](../05-security/SECURITY_MODEL.md).

## Risiko

- Rekayasa sosial pada reset dibantu admin → prosedur pemeriksaan identitas, audit, notifikasi ke pengguna, maker-checker untuk reset akun istimewa.

## Biaya

Nol bila tanpa SMTP; SMTP pihak ketiga hanya dengan persetujuan Owner.

## Dampak kompatibilitas dan data

Tabel `users`, `identity_password_resets`.

## Migrasi dan rollback

Guard tambahan (SSO) dapat ditambahkan kelak tanpa mengubah otorisasi.

## Yang terdampak

SECURITY_MODEL, ACCESS_CONTROL, Task identitas.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
