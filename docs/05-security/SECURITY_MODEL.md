# Model keamanan — PRANATA UNY

Status: P5 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P5 | ADR: [ADR-0007](../adr/ADR-0007-local-authentication.md), [ADR-0009](../adr/ADR-0009-private-file-storage.md), [ADR-0010](../adr/ADR-0010-audit-and-db-roles.md)

Dokumen ini memiliki **autentikasi, sesi, pemulihan, klasifikasi data, model ancaman, perlindungan berkas/ekspor, header, rahasia, pemantauan keamanan dan skenario regresi keamanan**. Otorisasi (kapabilitas, cakupan, penugasan, pemisahan tugas, isolasi) dimiliki [ACCESS_CONTROL](ACCESS_CONTROL.md). Keputusan yang masih memerlukan Owner ditandai **[KEPUTUSAN OWNER]** dan dikumpulkan di [OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md).

## 1. Prinsip

1. Penegakan selalu di server: rute, Action, Query, unduhan, ekspor dan pencarian. Menyembunyikan menu bukan kontrol keamanan (OD-28, OD-33).
2. Hak minimum: setiap kemampuan berasal dari grant bercakupan atau penugasan proses yang eksplisit dan tercatat.
3. Gagal tertutup: bila otorisasi, audit wajib atau kebijakan tidak dapat dievaluasi, aksi ditolak dengan alasan aman.
4. Jejak: setiap perubahan material, keputusan, perubahan akses dan pembacaan rahasia tercatat (ADR-0010).
5. Basis data produksi zero-touch bagi agen ([PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md)).

## 2. Klasifikasi data

| Kelas | Contoh | Perlakuan |
|---|---|---|
| PUBLIC | Tidak ada pada V1 (aplikasi sepenuhnya di balik login) | — |
| INTERNAL | Paket, status, dokumen non-rahasia, register aset, stok | Hanya pengguna dengan cakupan |
| CONFIDENTIAL | Penawaran penyedia sebelum pengumuman, evaluasi, negosiasi, rincian HPS bila kebijakan menyatakan rahasia, berkas bertanda tangan | Hanya penugasan proses terkait; Super Admin hanya lewat pembacaan teraudit (IX-024); unduhan tercatat |
| RESTRICTED | Nomor pajak/identitas usaha, nomor pegawai, kontak pribadi PIC, rahasia 2FA, hash password | Kolom minimum, tidak ditampilkan di daftar, disamarkan di log/ekspor kecuali kebutuhan resmi; rahasia dienkripsi aplikasi |

Retensi, arsip, anonimisasi dan dasar hukum data pribadi tetap GAP-023; sampai diputuskan tidak ada penghapusan/anonimisasi otomatis dan tidak ada pengumpulan data pribadi di luar kebutuhan minimum.

## 3. Autentikasi

| Aspek | Spesifikasi |
|---|---|
| Identitas masuk | Username **atau** email + password; tanpa registrasi publik; tanpa login sosial (OD-11/12) |
| Pembuatan akun | Internal: oleh Super Admin/admin akses berwenang. Penyedia: oleh petugas pengadaan berwenang saat pendaftaran DPT/undangan; kode aktivasi sekali-pakai diserahkan melalui kanal resmi (GAP-015) |
| Profil password | **AICWDF-COMPAT-8**: minimal 8 karakter, huruf besar+kecil, angka, simbol, boleh tempel/pengelola password, tanpa rotasi paksa berkala; ditolak bila sama dengan username/email. Bila Owner memilih profil lebih kuat (mis. minimal 12) atau ada kewajiban eksternal, profil itu dipakai **[KEPUTUSAN OWNER — opsional]** |
| Hash | Hasher bawaan framework (bcrypt/argon2id sesuai default versi terpasang) dengan rehash otomatis |
| Throttle | Maks. 5 percobaan/menit per kombinasi identitas+IP; setelah 10 kegagalan dalam 15 menit akun terkunci 15 menit; pesan kegagalan generik; semua kegagalan tercatat |
| Status akun | ACTIVE, SUSPENDED (admin), LOCKED (otomatis sementara), DEACTIVATED (tidak dapat masuk; riwayat tetap) |
| Ganti password wajib | Setelah reset dibantu admin atau aktivasi pertama; sesi lain dicabut setelah ganti password |
| TOTP 2FA | RFC 6238 + kode pemulihan sekali pakai. Usulan: **wajib untuk SUPER_ADMIN dan profil akses istimewa**, opsional untuk lainnya **[KEPUTUSAN OWNER]** |
| Pemeriksaan password bocor | Opsional, default OFF karena memerlukan panggilan ke layanan eksternal **[KEPUTUSAN OWNER — opsional]** |

### 3.1 Pemulihan akun

| Jalur | Alur | Kontrol |
|---|---|---|
| Dibantu admin (selalu tersedia) | Pengguna menghubungi admin → admin memeriksa identitas (metode dicatat: tatap muka/kartu pegawai/surat resmi penyedia) → sistem membuat password sementara sekali-pakai berumur pendek (default 24 jam) yang ditampilkan sekali → pengguna masuk dan wajib mengganti | `identity_password_resets` + audit; reset akun SUPER_ADMIN/istimewa memerlukan admin kedua (maker-checker); notifikasi in-app ke pengguna setelah reset |
| Email swalayan (bila SMTP dikonfigurasi) | Tautan reset bertoken berumur 60 menit ke email terverifikasi | Throttle; respons generik; dinonaktifkan bila SMTP tidak tersedia |

Kanal resmi penyerahan kode aktivasi/password sementara kepada penyedia tetap GAP-015; default aman: diserahkan langsung oleh petugas atau melalui kanal yang ditetapkan Owner.

## 4. Sesi

| Aspek | Spesifikasi |
|---|---|
| Driver | `database`; ID sesi diregenerasi saat login dan perubahan hak |
| Cookie | `Secure`, `HttpOnly`, `SameSite=Lax`, domain aplikasi saja |
| Timeout idle | Usulan 60 menit pengguna biasa, 30 menit Super Admin **[KEPUTUSAN OWNER]** |
| Timeout absolut | 12 jam |
| Kendali | Pengguna melihat sesi aktif dan dapat keluar dari semua perangkat; admin dapat mencabut sesi pengguna (teraudit) |
| Remember me | Tidak tersedia untuk SUPER_ADMIN; maksimal 14 hari untuk lainnya bila diaktifkan |

## 5. Model ancaman (STRIDE ringkas)

| ID | Ancaman | Kontrol | Uji |
|---|---|---|---|
| T-01 | Tebak/isi kredensial massal | Throttle, penguncian, TOTP akun istimewa, audit kegagalan | SEC-01–03 |
| T-02 | Pembajakan sesi | HTTPS-only, HSTS, cookie aman, regenerasi sesi, timeout | SEC-04–05 |
| T-03 | Akses objek lintas unit/ruang kerja/gudang/penyedia (IDOR) | Policy + scope Query + `public_id`; 404 untuk objek di luar cakupan bila keberadaannya sensitif | AUTHZ-* |
| T-04 | Eskalasi hak melalui administrasi akses | Maker-checker grant istimewa, larangan memberi hak ke diri sendiri, audit | AUTHZ-20–23 |
| T-05 | Kebocoran penawaran penyedia | Klasifikasi CONFIDENTIAL, isolasi penyedia, IX-024 gagal tertutup | AUTHZ-30–34 |
| T-06 | CSRF | Token CSRF Laravel/Inertia; metode aman tidak mengubah state | SEC-06 |
| T-07 | XSS | Escaping React, tanpa HTML mentah kecuali disanitasi, CSP | SEC-07–08 |
| T-08 | Injeksi SQL | Binding parameter, whitelist sort/filter | SEC-09 |
| T-09 | Unggahan berbahaya | Allowlist ekstensi+MIME, ukuran maksimum, disimpan di luar webroot dengan nama acak, unduhan sebagai attachment, pemindaian antivirus opsional | SEC-10–13 |
| T-10 | Template dokumen berbahaya | Hanya DOCX (bukan DOCM), validasi placeholder, konversi di kontainer tanpa akses jaringan keluar | SEC-14 |
| T-11 | Injeksi formula pada ekspor | Nilai yang diawali `= + - @` diberi awalan aman pada CSV/XLSX | SEC-15 |
| T-12 | Mass assignment | DTO/`$fillable` eksplisit; status hanya diubah melalui Action transisi | SEC-16 |
| T-13 | Penyalahgunaan sumber daya (laporan/ekspor berat) | Job berlatar, rate limit per pengguna, `statement_timeout` peran aplikasi | SEC-17 |
| T-14 | Penyalahgunaan orang dalam | Pemisahan tugas, audit, laporan aksi istimewa | AUTHZ-40–45 |
| T-15 | Kebocoran rahasia | `.env` di luar repo dengan izin 0600, tanpa rahasia di log, scrubbing log | SEC-18 |
| T-16 | Rantai pasok dependensi | Lockfile, `composer audit`/`npm audit` di CI, Dependabot alerts | SEC-19 |
| T-17 | Pencurian cadangan | Cadangan terenkripsi, kunci terpisah, akses terbatas | Prosedur P9 |
| T-18 | Agen eksekusi menyentuh produksi | Tanpa kredensial produksi di lingkungan agen/CI, branch protection, deploy dengan persetujuan | Prosedur P9/P11 |

## 6. Berkas, unduhan dan ekspor

- Allowlist awal: PDF, DOCX, XLSX, CSV, JPG, PNG; ukuran maksimum default 20 MB per berkas (dapat diatur Super Admin per tujuan).
- Validasi ekstensi **dan** tanda tangan MIME; nama asli disimpan sebagai metadata saja.
- Berkas disimpan di disk privat dengan path acak; unduhan melalui rute terotorisasi yang memeriksa izin subjek; header `Content-Disposition: attachment`, `X-Content-Type-Options: nosniff`.
- Unduhan berkas CONFIDENTIAL/RESTRICTED dan semua ekspor tercatat di audit (pengguna, filter, jumlah baris).
- Pemindaian antivirus (ClamAV self-hosted) usulan **opsional** karena memakai RAM ±1–2 GB **[KEPUTUSAN OWNER — opsional]**; bila tidak diaktifkan, status `NOT_SCANNED` ditampilkan.

## 7. Transport dan header

HTTPS wajib dengan pengalihan dari HTTP dan sertifikat otomatis (Let's Encrypt) **[KEPUTUSAN OWNER: domain]**; HSTS (`max-age` minimal 6 bulan setelah stabil); `Content-Security-Policy` ketat dengan nonce untuk skrip Vite, `frame-ancestors 'none'`; `Referrer-Policy: strict-origin-when-cross-origin`; `Permissions-Policy` minimal; `X-Content-Type-Options: nosniff`; cookie aman (bagian 4).

## 8. Rahasia

| Rahasia | Penyimpanan | Catatan |
|---|---|---|
| `APP_KEY` | `.env` produksi (0600, pemilik user layanan) + salinan terenkripsi terpisah dari cadangan data | Diperlukan untuk mendekripsi rahasia 2FA; kehilangan = reset 2FA semua pengguna |
| Kredensial DB per peran | `.env` | Peran berbeda untuk migrasi dan aplikasi |
| SMTP (opsional) | `.env` | |
| Kunci cadangan (restic/WAL) | Pengelola cadangan, terpisah dari server | |

Tidak ada rahasia di repositori, issue, PR, log atau dokumen. CI hanya memakai rahasia uji non-produksi. Rotasi dilakukan saat personel berganti atau dugaan kebocoran (runbook P9).

## 9. Pemantauan keamanan

Peristiwa yang dicatat di `audit_events`: login berhasil/gagal, penguncian, reset password, perubahan 2FA, pembuatan/penonaktifan akun, perubahan grant/penugasan, aktivasi kebijakan, aktivasi template, pembacaan rahasia, unduhan rahasia, ekspor, perubahan pengaturan. Peringatan operasi: lonjakan kegagalan login, grant istimewa baru, aktivasi kebijakan, pembacaan rahasia oleh Super Admin, kegagalan tulis audit. Saluran peringatan mengikuti P9.

## 10. Skenario regresi keamanan (otomatis)

| ID | Skenario |
|---|---|
| SEC-01 | Throttle aktif setelah 5 percobaan/menit; respons tidak membedakan akun ada/tidak |
| SEC-02 | Penguncian sementara setelah 10 kegagalan; terbuka setelah 15 menit |
| SEC-03 | SUPER_ADMIN tanpa TOTP terkonfirmasi tidak dapat mengakses Administrasi (bila keputusan Owner mewajibkan) |
| SEC-04 | Sesi diregenerasi saat login; cookie Secure/HttpOnly/SameSite |
| SEC-05 | Timeout idle/absolut berlaku; ganti password mencabut sesi lain |
| SEC-06 | Permintaan perubahan tanpa token CSRF ditolak |
| SEC-07 | Masukan berisi skrip ditampilkan sebagai teks |
| SEC-08 | Header CSP/HSTS/nosniff/frame-ancestors ada pada respons |
| SEC-09 | Parameter sort/filter di luar whitelist ditolak |
| SEC-10 | Ekstensi/MIME tidak diizinkan ditolak; berkas berganda ekstensi ditolak |
| SEC-11 | Berkas tidak dapat diakses melalui URL statis |
| SEC-12 | Unduhan tanpa izin subjek → 403/404; unduhan rahasia tercatat |
| SEC-13 | Ukuran melebihi batas ditolak dengan pesan jelas |
| SEC-14 | Template DOCM/tidak valid ditolak |
| SEC-15 | Ekspor menetralkan formula |
| SEC-16 | Field status/kepemilikan tidak dapat diubah lewat payload form |
| SEC-17 | Rate limit ekspor/laporan per pengguna |
| SEC-18 | Log tidak memuat password/token/rahasia |
| SEC-19 | CI gagal bila `composer audit`/`npm audit` menemukan kerentanan tinggi/kritis tanpa pengecualian tercatat |
| SEC-20 | Reset dibantu admin: password sementara sekali pakai, kedaluwarsa, wajib ganti, tercatat, notifikasi ke pengguna |

Skenario otorisasi AUTHZ-* dimiliki ACCESS_CONTROL; strategi pengujian di [TEST_STRATEGY](../08-testing/TEST_STRATEGY.md).

## 11. Keputusan Owner terkait keamanan

| ID | Keputusan | Usulan |
|---|---|---|
| SEC-D1 | TOTP wajib untuk Super Admin/akses istimewa | Ya |
| SEC-D2 | Timeout idle | 60 menit (30 menit Super Admin) |
| SEC-D3 | Profil password | AICWDF-COMPAT-8 (default framework yang dipilih Owner) |
| SEC-D4 | Antivirus ClamAV | Opsional; aktifkan bila server produksi berukuran 16 GB RAM ([INFRASTRUCTURE §4](../09-operations/INFRASTRUCTURE.md#4-topologi-staging-dan-produksi)) |
| SEC-D5 | Pemeriksaan password bocor eksternal | OFF |
| SEC-D6 | SMTP untuk reset/notifikasi | Opsional; tanpa SMTP tetap berfungsi |
| SEC-D7 | Kanal penyerahan kode aktivasi penyedia | Diserahkan langsung oleh petugas pengadaan |
