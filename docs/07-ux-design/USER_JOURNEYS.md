# Perjalanan pengguna kritis — PRANATA UNY

Status: P7 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P7

Dokumen ini memiliki **perjalanan kritis per peran** yang menjadi dasar desain halaman, uji E2E browser (P8) dan skenario UAT (P10). Setiap perjalanan memakai data sintetis; nilai kebijakan pada perjalanan pengadaan memakai versi kebijakan DRAFT berlabel simulasi di lingkungan non-produksi sampai GAP terkait terselesaikan.

| ID | Perjalanan | Peran | Halaman | Hasil yang diverifikasi | E2E |
|---|---|---|---|---|---|
| J-01 | Masuk, ganti password wajib, pilih ruang kerja, ganti bahasa, keluar | Semua | PG-001/002/003/007 | Sesi sah, konteks benar, bahasa tersimpan, keluar mengakhiri sesi | E2E-01 |
| J-02 | Unit mengajukan usulan kebutuhan; pusat meminta perbaikan; unit mengajukan ulang; pusat menerima | Operator unit, staf pusat | PG-102 | Status, pihak ditunggu, alasan perbaikan dan riwayat konsisten bagi kedua pihak | E2E-02 |
| J-03 | Paket barang Pengadaan Langsung: buat paket dari usulan → HPS → minta saran metode → tetapkan → kegiatan → catat penawaran → evaluasi/negosiasi → hasil → dokumen BA dan SPK | Staf pusat, Pejabat Pengadaan, PPK (penugasan) | PG-104–114 | Data sekali-input mengalir ke dokumen; penetapan metode oleh penugasan; banner simulasi; dokumen DRAF → FINAL dengan nomor unik | E2E-03 |
| J-04 | Paket jasa konsultansi Quotation dengan pita tumpang tindih (GAP-027 a) | Staf pusat, penetap metode | PG-108 | Saran menandai tumpang tindih; penetapan wajib beralasan; DPT dicek pada undangan | E2E-04 |
| J-05 | Paket pekerjaan konstruksi Tender (kanal resmi eksternal) | Staf pusat, Pokja (penugasan) | PG-105/110–113 | Referensi LPSE dicatat; tidak ada kiriman penawaran di PRANATA; hasil, SPPBJ, kontrak disiapkan | E2E-05 |
| J-06 | Jasa lainnya melalui E-Purchasing (katalog) dan Penunjukan Langsung (kriteria) | Staf pusat, PPK | PG-108/114 | Bentuk bukti perjanjian sesuai kebijakan (surat pesanan/kontrak); kriteria penunjukan langsung dengan bukti | E2E-06 |
| J-07 | Adendum kontrak melebihi batas kebijakan memerlukan keputusan pengecualian | PPK, pemberi keputusan | PG-115 | Telaah menghitung persentase; batas terlampaui tidak dapat dilewati; versi kontrak baru setelah tanda tangan | E2E-07 |
| J-08 | BAST → klasifikasi → draf aset dan draf barang masuk gudang | Staf pengadaan, staf aset, petugas gudang | PG-117/119/304/207 | Draf hilir dibuat di modul tujuan; tanpa pengakuan otomatis | E2E-08 |
| J-09 | Permintaan barang gudang ALP: ajukan → setujui sebagian → serah terima parsial → serah terima sisa → konfirmasi penerima dengan selisih | Pemohon, pemberi persetujuan, petugas ALP, penerima | PG-005/202–204 | Stok berkurang hanya saat serah terima; tidak negatif; konfirmasi terpisah; selisih membuka telaah | E2E-09 |
| J-10 | Pengembalian barang: ajukan → terima fisik → pemeriksaan layak/tidak layak | Pengembali, petugas gudang | PG-205 | Stok bertambah hanya untuk layak pakai | E2E-10 |
| J-11 | Isolasi gudang: petugas ALP tidak dapat melihat PSRT lewat menu, URL, pencarian atau ekspor | Petugas ALP | PG-201–211 | 403/404 dan hasil kosong yang benar | E2E-11 |
| J-12 | Register aset 1 juta baris: cari, filter, buka detail, kembali dengan filter terjaga, ekspor sesuai filter | Staf aset | PG-302/303/009 | Waktu respons sesuai anggaran; ekspor asinkron | E2E-12 |
| J-13 | Mutasi aset dan inventarisasi fisik di ponsel | Operator aset unit | PG-305/306 | Alur mobile, foto bukti, riwayat | E2E-13 |
| J-14 | Intake data historis: unggah → validasi → isu → keputusan → terima sebagian | Staf aset | PG-312 | Staging, isu jelas, penerimaan per bagian, kredensial sumber tidak dibawa | E2E-14 |
| J-15 | Penyedia: aktivasi akun, lengkapi profil, unggah dokumen DPT, lihat undangan dan hasil | Penyedia, verifikator | PG-401–404/120 | Isolasi penyedia; verifikasi dokumen; status DPT | E2E-15 |
| J-16 | Super Admin: buat pengguna, beri grant istimewa (disetujui orang kedua), susun dan aktifkan kebijakan (pengesah berbeda), baca penawaran secara teraudit | Super Admin, admin kedua | PG-502/503/507/511 | Maker-checker, audit, IX-024 gagal tertutup | E2E-16 |
| J-17 | Laporan: posisi aset per tanggal dan mutasi stok per rentang, keduanya diekspor | Staf aset/gudang | PG-313/211/009 | Makna waktu benar; label simulasi bila kebijakan non-ACTIVE | E2E-17 |
| J-18 | Pemulihan akun dibantu admin | Pengguna, admin | PG-502/001/002 | Password sementara sekali pakai, wajib ganti, tercatat | E2E-18 |

## Aturan verifikasi perjalanan

Setiap perjalanan E2E memeriksa: tidak ada galat konsol kritis atau permintaan jaringan gagal, tidak ada 404/405/500 tak terduga, aksi utama memberi umpan balik, tombol kembali menuju tujuan kontekstual, kedua bahasa, viewport 390×844 dan 1440×900, serta pemeriksaan axe tanpa pelanggaran serius/kritis. Perjalanan J-03 sampai J-07 menjadi inti UAT Pengadaan pertama (OD-32).
