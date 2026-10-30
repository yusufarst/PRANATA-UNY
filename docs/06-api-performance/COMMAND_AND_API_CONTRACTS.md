# Kontrak perintah, rute dan respons — PRANATA UNY

Status: P6 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P6

Dokumen ini memiliki **bentuk permintaan/respons, validasi, model galat, idempotensi, konkurensi optimistis, pagination, unggah/unduh, operasi panjang dan batas laju**. Aturan transaksi, urutan kunci dan invarian basis data dimiliki [CONCURRENCY_AND_INTEGRITY](CONCURRENCY_AND_INTEGRITY.md); target performa dimiliki [PERFORMANCE_AND_SCALE](PERFORMANCE_AND_SCALE.md). PRANATA V1 tidak memiliki API publik; semua rute melayani aplikasi Inertia milik sendiri.

## 1. Jenis rute

| Jenis | Metode | Respons | Contoh nama rute |
|---|---|---|---|
| Halaman | GET | Inertia page + props bertipe | `procurement.packages.index`, `procurement.packages.show` |
| Perintah (aksi state) | POST/PUT/PATCH | Redirect 303 ke halaman konteks + flash hasil; galat validasi via Inertia errors | `procurement.packages.method.determine` |
| Data asinkron | GET | JSON kecil (`application/json`) untuk lookup/autocomplete/status operasi | `lookup.items`, `operations.show` |
| Berkas | GET | Stream berkas terotorisasi | `files.download` |
| Kesehatan | GET | 200/503 | `/up` (bawaan), `/health/ready` (cek DB, antrean, disk, Gotenberg — hanya jaringan internal) |

Konvensi: nama rute `<modul>.<sumber>.<aksi>`; path URL berbahasa Inggris (penamaan teknis, AICWDF §4B.3) sedangkan semua label tampilan terlokalisasi; segmen URL memakai `public_id`; tidak ada aksi pengubah state melalui GET; DELETE tidak dipakai untuk record bisnis (pembatalan adalah perintah POST beralasan). Setiap rute di belakang `auth` kecuali login/aktivasi/reset, dan setiap rute aksi memiliki `can` di controller serta uji otorisasi.

## 2. Perintah (Action)

```text
POST /procurement/packages/{package}/method-determinations
Headers: X-Idempotency-Key: <uuid v4 dari klien>     (wajib untuk perintah material)
Body: { lock_version: 7, method_code: "QUOTATION", reason: "...", overlap_reason: null,
        criteria_evidence_file_ids: [..] }
Alur: authorize → FormRequest → Action::handle(actor, package, dto, idempotencyKey)
Sukses: 303 → procurement.packages.show + flash {type: success, key: "method.determined"}
Gagal validasi: 302 kembali + errors (Inertia)
Konflik versi: 409 → halaman yang sama dengan errors.lock_version + data terbaru
Ditolak kebijakan/izin: 403 (atau 404 bila subjek rahasia) + reason_key
```

| Aturan | Isi |
|---|---|
| Satu perintah = satu Action | Controller tidak menggabungkan beberapa Action; komposisi berada dalam Action. |
| Kontrak masukan | DTO bertipe; nilai uang/kuantitas sebagai string desimal; tanggal ISO `YYYY-MM-DD`; enum sebagai kode. |
| Kontrak keluaran | Redirect ke konteks pemilik (RT) dan flash berkode terjemahan; tidak mengembalikan objek domain mentah. |
| Kode transisi | Setiap perintah perubahan state memetakan ke satu TR P3 yang dicatat di `workflow_transitions`. |
| Alasan | Perintah material (tolak, kembalikan, batalkan, tarik, override, penetapan yang menyimpang dari saran) mewajibkan alasan. |
| Tanggal acuan kebijakan | Perintah yang bergantung kebijakan menyatakan tanggal acuannya: bawaan tanggal bisnis Asia/Jakarta saat commit, atau tanggal bisnis subjek yang ditetapkan kontraknya (tanggal acuan paket, tanggal generasi atau finalisasi dokumen). Resolusi diulang di dalam transaksi; record resmi hanya bila versi setiap keluarga yang dibutuhkan berlaku resmi pada tanggal itu ([POLICY_REGISTRY §3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi)). |

## 3. Validasi dan model galat

| Situasi | Status HTTP | Bentuk | Catatan |
|---|---|---|---|
| Masukan tidak valid | 422 (Inertia: redirect + errors) | `errors.{field}: [message_key]` | Pesan dari `lang/{id,en}` |
| Prasyarat bisnis tidak terpenuhi | 422 | `errors._domain: [reason_key]` + `context` (GAP, bukti kurang) | Mis. stok tidak cukup, tidak ada versi kebijakan yang berlaku resmi pada tanggal acuan (konteks: keluarga, GAP, tanggal mulai berlaku versi terjadwal) |
| Tidak berwenang | 403 | `reason_key` aman | Tanpa membocorkan isi |
| Tidak ditemukan/di luar cakupan rahasia | 404 | Halaman 404 terlokalisasi | |
| Konflik versi | 409 | `errors.lock_version` + snapshot terbaru | UI menampilkan dialog konflik (P7) |
| Kunci idempotensi dipakai ulang dengan isi berbeda | 422 | `errors._idempotency` | |
| Operasi sedang diproses | 202 | `operation_id` | Untuk perintah yang memulai job |
| Batas laju | 429 | `retry_after` | |
| Galat server | 500 | Halaman galat + `request_id` | Detail hanya di log |

Setiap respons membawa header `X-Request-Id` yang juga ditulis ke log, audit dan `workflow_transitions` untuk korelasi.

## 4. Idempotensi

- Klien membangkitkan `X-Idempotency-Key` (UUID v4) saat formulir perintah dibuka dan mengirim ulang kunci yang sama bila pengguna mengulang/menekan ganda.
- Server menyimpan `(user_id, key)` di `platform_idempotency_keys` dengan `request_sha256` dan status. Permintaan kedua dengan kunci dan isi sama: bila `COMPLETED` → kembalikan hasil sebelumnya (redirect ke subjek yang sama); bila `IN_PROGRESS` → 409 "sedang diproses"; isi berbeda → 422.
- Wajib untuk: semua perintah yang membuat record, transisi state, alokasi nomor, serah terima/pengembalian/penyesuaian stok, finalisasi dokumen, unggahan berkas, pemicu job. Kedaluwarsa 24 jam.
- Job antrean idempoten berdasarkan kunci bisnis (mis. `document:{generated_document_id}:rev`), sehingga retry tidak menggandakan efek.

## 5. Konkurensi optimistis

Agregat yang dapat diedit bersamaan membawa `lock_version`. Perintah mengirim versi yang dilihat pengguna; Action melakukan `UPDATE … WHERE id = ? AND lock_version = ?` lalu menaikkan versi. Bila 0 baris berubah → 409. Untuk perubahan yang juga memerlukan kunci pesimistis (stok, nomor), aturan di CONCURRENCY_AND_INTEGRITY berlaku sebelum pemeriksaan versi.

## 6. Daftar, filter dan pagination

| Aturan | Isi |
|---|---|
| Pagination | Keyset (cursor) untuk daftar yang dapat melebihi 10.000 baris (aset, ledger, audit, intake rows); offset dengan maksimum 10.000 baris untuk daftar kecil. Ukuran halaman 25/50/100. |
| Cursor | Opaque base64 dari nilai kolom urut + `id`; urutan selalu diakhiri `id` agar stabil. |
| Filter | Whitelist per Query; tipe dan operator eksplisit; filter cakupan akses selalu ditambahkan server. |
| Sort | Whitelist kolom berindeks; default terdefinisi per daftar. |
| Hitungan | Hitungan tepat hanya untuk tabel kecil; tabel besar menampilkan perkiraan atau "lebih dari N" agar cepat. |
| Status URL | Filter/sort/cursor tercermin di query string sehingga tombol kembali memulihkan daftar (RT kontrak). |
| Pencarian global | `GET /search?q=` minimal 3 karakter, hasil terbatas per modul (maks. 10 per modul), scope akses per modul. |

## 7. Unggah dan unduh

- Unggah: `multipart/form-data` ke rute modul pemilik (bukan endpoint unggah generik tanpa subjek), batas ukuran per tujuan, validasi ekstensi+MIME, SHA-256 dihitung server, `file_objects` + `file_links` dibuat dalam transaksi Action subjek.
- Unduh: `GET /files/{file_public_id}` memeriksa tautan ke subjek dan izin subjek; berkas rahasia dicatat audit; respons stream dengan `Content-Disposition: attachment`.
- Pratinjau PDF/gambar ditampilkan melalui rute yang sama dengan `inline` hanya untuk tipe aman (PDF, PNG, JPG).

## 8. Operasi panjang

| Operasi | Antrean | Kontrak status |
|---|---|---|
| Generasi dokumen | `documents` | `generated_documents.status` + status konversi |
| Ekspor/laporan | `exports` | `report_requests` |
| Impor/intake | `imports` | `intake_batches` |
| Penyusutan | `maintenance` | `asset_depreciation_runs` |
| Partisi audit, pembersihan idempotensi, verifikasi saldo | `maintenance` | Log + audit |

Halaman memulai operasi dengan perintah yang mengembalikan 303 ke halaman status; status diperbarui dengan polling Inertia (interval 2–5 detik, berhenti saat selesai) sesuai EP-010. Tidak ada klaim selesai sebelum job selesai; kegagalan menjelaskan bagian yang sudah tersimpan. Pembatalan operasi hanya bila jenis operasinya mendukung (ekspor/laporan dapat dibatalkan; penerimaan intake tidak dapat dibatalkan di tengah, tetapi berjalan dalam transaksi per bagian yang dapat dilanjutkan).

## 9. Batas laju

| Kelompok | Batas default |
|---|---|
| Login | Lihat SECURITY_MODEL (5/menit per identitas+IP) |
| Perintah umum | 60/menit per pengguna |
| Pencarian/lookup | 120/menit per pengguna |
| Ekspor/laporan | 10/jam per pengguna; maks. 2 berjalan bersamaan per pengguna |
| Unggahan | 30/menit per pengguna |
| Portal penyedia | 30/menit per pengguna |

## 10. Kontrak data ke frontend

- Props halaman dibentuk oleh DTO/Resource eksplisit; tipe TypeScript dibangkitkan dari DTO PHP (alat dipilih saat bootstrap dan dicatat di TOOLCHAIN).
- Props bersama: pengguna (nama, kelompok peran, lokal), ruang kerja yang diizinkan, menu, peta izin global, flash, `request_id`, status lingkungan (produksi/staging/simulasi), hitungan inbox.
- Angka uang dikirim sebagai string desimal dan diformat di klien dengan `Intl.NumberFormat('id-ID')`; tanggal dikirim ISO dan ditampilkan Asia/Jakarta.
- Data rahasia tidak pernah dikirim ke klien yang tidak berhak, termasuk dalam props tersembunyi.
