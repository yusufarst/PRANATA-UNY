# Rencana UAT — PRANATA UNY

Status: P10 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P10

Dokumen ini memiliki **tahap UAT, kriteria masuk/keluar, katalog skenario, peran, tingkat cacat dan berita acara penerimaan**. Penandatangan dan pengguna UAT per ruang kerja ditetapkan Owner (jabatan/peran, bukan nama pribadi di repositori) **[KEPUTUSAN OWNER]**.

## 1. Tahap UAT

| Tahap | Cakupan | Lingkungan dan data |
|---|---|---|
| UAT-1 Pengadaan (pertama) | Barang, Jasa Lainnya, Pekerjaan Konstruksi, Jasa Konsultansi sampai **penyiapan dokumen kontrak**, lima metode sesuai kebijakan DRAFT berlabel simulasi, tanpa pengiriman penawaran penyedia di PRANATA (OD-32); penawaran sintetis dicatat petugas sehingga **evaluasi penawaran — prioritas Owner (OD-36) — dan hasil per lingkup** ikut diuji | Staging, data sintetis |
| UAT-2 Gudang | Permintaan, keputusan, serah terima, konfirmasi, pengembalian, opname, transfer — ALP dan PSRT | Staging, data sintetis |
| UAT-3 Aset | Penerimaan dari BAST, register, mutasi, inventarisasi, perubahan, penghapusan, penyusutan, KDP, intake | Staging, data sintetis + sampel teranonimkan yang disetujui |
| UAT-4 V1 lengkap | Pelaksanaan kontrak, adendum, BAST, SPJ, pembayaran, portal penyedia/DPT, rekonsiliasi, laporan, regresi seluruh perjalanan | Staging |

## 2. Kriteria masuk dan keluar

**Masuk:** increment terkait DONE di TASK_PLAN; staging hijau; skenario dan data siap; pengguna UAT memiliki akun dan grant sesuai peran; panduan singkat tersedia.

**Keluar:** semua skenario wajib lulus; **tidak ada cacat kritis/tinggi yang belum selesai**; cacat sedang memiliki rencana; rubrik "mandiri" ≥ 90% untuk skenario inti (NFR-P04); berita acara ditandatangani.

UAT-1 diterima oleh **pimpinan Pengadaan yang ditunjuk** dengan pemeriksaan PPK, bisnis dan Keuangan (OD-32).

## 3. Katalog skenario UAT-1 (wajib)

| ID | Skenario | Perjalanan |
|---|---|---|
| UAT-P01 | Usulan kebutuhan unit → perbaikan → diterima pusat | J-02 |
| UAT-P02 | Paket barang Pengadaan Langsung sampai SPK/bukti perjanjian dan dokumen BA | J-03 |
| UAT-P03 | Paket jasa konsultansi Quotation pada pita tumpang tindih dengan penetapan beralasan, undangan DPT | J-04 |
| UAT-P04 | Paket pekerjaan konstruksi Tender dengan referensi kanal resmi, hasil, SPPBJ, kontrak | J-05 |
| UAT-P05 | Jasa lainnya E-Purchasing (katalog) dan satu paket Penunjukan Langsung dengan kriteria dan bukti | J-06 |
| UAT-P06 | HPS dengan komponen, pajak dan pembulatan sesuai kebijakan DRAFT; label simulasi terlihat | J-03 |
| UAT-P07 | Penomoran dokumen: saran, konfirmasi, override manual, tabrakan ditolak | J-03 |
| UAT-P08 | Revisi dokumen: koreksi data → revisi baru, versi lama tetap | J-03 |
| UAT-P09 | Pemisahan tugas: PPK tidak dapat ditugaskan sebagai Pokja pada paket sama | AUTHZ-34 |
| UAT-P10 | Isolasi: staf pengadaan tanpa grant aset tidak dapat melihat aset | AUTHZ-03 |
| UAT-P11 | Dasbor dan inbox menunjukkan pemilik tindakan, pihak ditunggu, tindakan berikut | J-03 |
| UAT-P12 | Kedua bahasa dan penggunaan di ponsel untuk melihat status paket | J-01 |
| UAT-P13 | Evaluasi Quotation dua lot: skema beku dari dokumen pengadaan, tiga DPT diundang, dua menawar tepat waktu dan satu terlambat, pembukaan sesudah batas akhir tanpa menggugurkan dengan penawaran terlambat ditolak tanpa dibuka, evaluasi per kriteria/tahap per lot dengan bukti dan alasan (kriteria khusus satu lot hanya pada lot itu), klarifikasi, hasil dengan dua pemenang berbeda dan cadangan, reviu PPK, SPPBJ dan kontrak per pemenang; penilai menyatakan alur evaluasi jelas dan lebih efisien daripada cara saat ini | J-19 |
| UAT-P14 | Langkah dan dokumen menurut metode dan jenis: Pengadaan Langsung pekerjaan konstruksi dibandingkan barang (dokumen teknis tambahan tampil sesuai matriks atau sebagai menunggu validasi), praktik tiga penawaran tampil informatif, E-Purchasing dengan opsi katalog dan negosiasi tanpa penawaran fiktif | J-03, J-06 |

Skenario UAT-2–UAT-4 diturunkan dari J-08–J-18 dan J-20 (pascapenghargaan pada UAT-4) dan disusun sebelum masing-masing tahap dimulai.

## 4. Tingkat cacat

| Tingkat | Definisi | Aturan |
|---|---|---|
| Kritis | Data salah/hilang, keamanan/isolasi bocor, alur inti tidak dapat diselesaikan | Menghentikan penerimaan |
| Tinggi | Fungsi inti salah tanpa jalan keluar wajar | Harus diperbaiki sebelum penerimaan |
| Sedang | Fungsi bekerja dengan jalan keluar; masalah UX signifikan | Rencana perbaikan |
| Rendah | Kosmetik | Backlog |

## 5. Berita acara UAT

Memuat: tahap, versi/SHA staging, periode, peserta (peran), daftar skenario dan hasil, cacat terbuka dan keputusannya, catatan kebijakan yang masih DRAFT (simulasi), keputusan (diterima/diterima bersyarat/ditolak) dan tanda tangan penandatangan yang ditunjuk. Disimpan sebagai bukti (di luar repositori publik bila memuat nama pribadi; repositori hanya menyimpan rujukan).
