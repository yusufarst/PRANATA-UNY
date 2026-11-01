# Lokalisasi — PRANATA UNY

Status: P7 — APPROVED (APPR-006); baseline beku FRZ-001 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P7 | Dasar: OD-17, AICWDF §4B

## 1. Aturan dasar

| Aturan | Isi |
|---|---|
| Bahasa default | Indonesia (`id`) dengan bahasa sederhana dan istilah pengadaan/aset yang lazim di UNY |
| Bahasa kedua | Inggris (`en`) wajib tersedia untuk seluruh UI |
| Pemilih bahasa | Di header; tersimpan di `users.locale`; berlaku segera tanpa kehilangan konteks |
| Dokumen resmi | Selalu bahasa Indonesia apa pun bahasa UI |
| Penamaan teknis | Kode, rute, tabel, enum dan kunci terjemahan berbahasa Inggris |
| Zona waktu | Tampilan Asia/Jakarta (WIB); penyimpanan UTC |
| Format | `Intl` dengan lokal `id-ID`/`en-US` untuk angka dan tanggal; uang selalu Rupiah |

## 2. Arsitektur terjemahan

| Lapisan | Lokasi | Mekanisme |
|---|---|---|
| UI React | `resources/js/locales/{id,en}/<namespace>.json` (namespace per modul: `common`, `procurement`, `warehouse`, `assets`, `vendor`, `admin`, `states`) | `i18next` + `react-i18next`, pemuatan namespace per halaman |
| Pesan server (validasi, flash, notifikasi, galat) | `lang/{id,en}/*.php` | Lokalisasi Laravel; pesan validasi Indonesia lengkap |
| Label status | Namespace `states`, kunci `<workflow>.<state>` | Satu sumber label per state P3 |
| Dokumen | Template DOCX bahasa Indonesia + fungsi format/terbilang server | Tidak diterjemahkan |

Kunci berbentuk `area.konteks.item` (contoh `procurement.package.actions.submit`). Teks tidak pernah ditulis langsung di komponen. Interpolasi memakai parameter bernama; jamak memakai fitur plural i18next.

## 3. Format

| Data | id-ID | en |
|---|---|---|
| Uang | `Rp1.250.000,00` (tabel boleh tanpa ,00 bila semua nilai bulat — konsisten per tabel) | `IDR 1,250,000.00` |
| Angka | `1.250,5` | `1,250.5` |
| Tanggal | `30 Okt 2026`; lengkap `Jumat, 30 Oktober 2026` | `30 Oct 2026` |
| Waktu | `14.30 WIB` | `2:30 PM WIB` |
| Persen | `10,5%` | `10.5%` |

## 4. Glosarium istilah

| Indonesia (default) | English | Catatan |
|---|---|---|
| Usulan kebutuhan | Need request | |
| Paket pengadaan | Procurement package | |
| Penetapan metode | Method determination | |
| Pengadaan Langsung / Quotation / Tender / Penunjukan Langsung / E-Purchasing | Direct Procurement / Quotation / Tender / Direct Appointment / E-Purchasing | Nama metode resmi dipertahankan di dokumen |
| HPS | Owner estimate (HPS) | Singkatan resmi tetap ditampilkan |
| SPPBJ | Provider appointment letter (SPPBJ) | |
| SPK / Kontrak / Adendum | Work order (SPK) / Contract / Amendment | |
| Serah terima / BAST | Handover / Handover record (BAST) | |
| SPJ | Accountability documents (SPJ) | |
| Penyedia / DPT | Vendor / Selected vendor list (DPT) | |
| Gudang / Permintaan barang / Pengembalian | Warehouse / Item request / Return | |
| Stock opname | Stock count | |
| Aset / Mutasi / Inventarisasi / Penghapusan / Penyusutan | Asset / Transfer / Physical verification / Disposal / Depreciation | |
| KDP | Construction in progress (KDP) | |
| Simulasi — kebijakan belum disahkan | Simulation — policy not yet ratified | Label INV-022 |
| Menunggu pengesahan kebijakan | Awaiting policy ratification | Gerbang GAP |

## 5. Mutu dan pengujian

- CI gagal bila ada kunci yang hilang di salah satu bahasa, kunci tak terpakai berlebihan, atau teks literal di JSX (aturan lint).
- Uji E2E menjalankan perjalanan kritis dalam kedua bahasa dan memeriksa perpindahan bahasa mempertahankan konteks.
- Uji tata letak dengan teks Indonesia panjang (label sering lebih panjang dari Inggris) pada lebar 360 px.
- Review bahasa: teks Indonesia ditinjau agar sederhana dan konsisten dengan glosarium; istilah hukum mengikuti dokumen sumber.
