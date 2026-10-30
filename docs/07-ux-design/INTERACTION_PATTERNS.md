# Pola halaman dan interaksi — PRANATA UNY

Status: P7 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P7

Dokumen ini memiliki **pola halaman, hierarki aksi, perwujudan IX-001–IX-024, keadaan wajib, perilaku responsif dan aturan formulir**. Hasil bisnis setiap interaksi tetap dimiliki [INTERACTION_CONTRACTS](../03-workflows/INTERACTION_CONTRACTS.md) dan pola pemulihan dimiliki [EXCEPTION_REVISION_PATTERNS](../03-workflows/EXCEPTION_REVISION_PATTERNS.md); dokumen ini menetapkan bentuk UI-nya.

## 1. Pola halaman

| Pola | Struktur | Aturan |
|---|---|---|
| Daftar | `PageHeader` (judul, aksi utama) → `FilterBar` + tampilan tersimpan → `DataTable` → pagination keyset | Filter/sort/cursor di URL; kembali dari detail memulihkan posisi; ekspor sesuai filter terlihat; tabel → daftar kartu di bawah `md` |
| Detail | `PageHeader` (identitas, `StatusBadge`, aksi utama sesuai state) → `NextActionCard` → tab/konten utama di kiri, panel konteks (atribut, pihak, bukti, dokumen) di kanan | Pada layar kecil panel konteks menjadi bagian setelah konten utama; riwayat dan bukti selalu dapat dicapai |
| Buat/ubah | Formulir satu kolom bertahap logis; aksi `Simpan draf` dan aksi utama di bilah bawah lengket | Peringatan perubahan belum tersimpan saat keluar; batal kembali ke asal tanpa menyimpan |
| Bertahap (stepper) | Untuk paket dan HPS: langkah bernama, draf tersimpan per langkah, ringkasan akhir sebelum ajukan | Langkah dapat dibuka ulang; validasi per langkah + validasi akhir |
| Antrean | Daftar pekerjaan dengan alasan menunggu, umur, dan aksi cepat yang aman (buka/ambil) | Aksi keputusan selalu membuka konteks detail, tidak diputus dari daftar |
| Dasbor | Kartu sorot navy "Perlu tindakan Anda" + kartu ringkasan putih + daftar pendek | Setiap kartu bisa diklik menuju daftar terfilter; tanpa angka tanpa sumber |

## 2. Hierarki aksi

- Satu aksi utama (tombol navy) per halaman atau per panel; aksi sekunder outline/ghost; aksi lain dalam menu "Lainnya".
- Aksi destruktif/pembatalan dipisah secara visual, memakai `ConfirmActionDialog` dengan ringkasan akibat dan alasan wajib.
- Tidak ada dua tombol berbeda untuk hasil yang sama pada satu layar.
- Aksi yang belum tersedia karena state/kebijakan/GAP ditampilkan nonaktif dengan alasan (tooltip + `BlockedReasonPanel`), kecuali keberadaannya membocorkan informasi.
- Setiap aksi yang terlihat memiliki rute, otorisasi server dan hasil yang dijelaskan (zero dead interactions).

## 3. Perwujudan kontrak interaksi

| IX | Bentuk UI |
|---|---|
| IX-001 Buat | Form/Stepper; simpan draf membuat record DRAFT; toast "Draf tersimpan" |
| IX-002 Ubah | Form dengan `lock_version`; konflik → `ConflictDialog` |
| IX-003 Ajukan | `ConfirmActionDialog` berisi ringkasan, kelengkapan dan pihak yang akan menerima; sukses → detail dengan `NextActionCard` diperbarui |
| IX-004 Minta perbaikan | Dialog alasan wajib + pilihan bagian yang perlu diperbaiki |
| IX-005 Ajukan ulang | Banner alasan perbaikan di atas form; perubahan disorot; ajukan ulang membuat versi baru |
| IX-006 Telaah | Panel telaah di detail: catatan, hasil, lampiran |
| IX-007 Terima/setujui | Dialog konfirmasi dengan akibat; untuk jumlah sebagian, kolom jumlah per baris + alasan |
| IX-008 Tolak | Dialog alasan wajib; status dan riwayat terlihat |
| IX-009 Batal/gantikan | Dialog alasan + dampak pada pekerjaan terkait |
| IX-010 Generate dokumen | `DocumentCard` → template dan versi yang berlaku resmi pada tanggal generasi dipilih deterministik dan ditampilkan ([DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md)) → pratinjau data → generate (operasi) → unduh DOCX/PDF; banner DRAF/SIMULASI |
| IX-011 Lampirkan bukti | `FileDropzone` dengan validasi tipe/ukuran, label tujuan, progres unggah |
| IX-012 Lihat riwayat | Tab/panel `StateTimeline` + perubahan field |
| IX-013 Bandingkan | Tampilan dua kolom sebelum–sesudah (versi dokumen, adendum, versi kebijakan) |
| IX-014 Klasifikasi | Daftar baris BAST dengan pilihan klasifikasi + alasan; gerbang kebijakan terlihat |
| IX-015 Selesaikan pengecualian | Panel selisih dengan pilihan resolusi + bukti |
| IX-016 Pindah ruang kerja | `WorkspaceSwitcher`; konteks objek dipertahankan bila diizinkan |
| IX-017 Cari | Palette + halaman hasil |
| IX-018 Laporan | Form parameter (makna waktu jelas: posisi per tanggal/rentang/periode/rekonsiliasi) → operasi → unduh |
| IX-019 Masuk | Halaman masuk tenang: judul, teks pendukung, username/email, password, tombol Masuk, tautan bantuan pemulihan; tanpa login sosial |
| IX-020 Jadwal kegiatan | Kalender minggu/bulan + daftar; panel detail kegiatan; catat hasil terpisah dari jadwal |
| IX-021 Handoff | Panel sumber ↔ tujuan; buka tujuan bila berhak; status draf hilir |
| IX-022 Konfirmasi penerima | Halaman ringkas mobile-first: daftar barang/jumlah yang diserahkan, tombol "Sesuai" dan "Laporkan selisih" (alasan + jumlah per baris) |
| IX-023 Penetapan metode | Kartu saran: metode memenuhi syarat (dengan rujukan pasal), tidak memenuhi (alasan), penanda tumpang tindih; pilihan metode + alasan wajib bila menyimpang/tumpang tindih; banner simulasi bila tidak ada versi kebijakan yang berlaku resmi pada tanggal acuan paket; bila versi yang berlaku berubah antara saran dan penetapan, penetapan meminta tinjau ulang |
| IX-024 Pembacaan teraudit | Isi tersembunyi di balik panel "Akses rahasia — alasan wajib"; setelah alasan dikirim isi tampil read-only dengan tanda "Akses tercatat" |

## 4. Keadaan wajib

| Keadaan | Pola |
|---|---|
| Memuat | Skeleton sesuai bentuk konten; tombol aksi menunjukkan proses dan mencegah klik ganda |
| Kosong | Ilustrasi kecil + penjelasan + aksi pertama yang masuk akal (bila berhak) |
| Tidak ada hasil filter | Pesan + tombol hapus filter |
| Galat validasi | Pesan di bawah field + ringkasan di atas form panjang; fokus ke galat pertama |
| Galat server | `ErrorState` + `request_id` + coba lagi; data yang sudah tersimpan dijelaskan |
| Tidak berwenang/tidak ditemukan | `ForbiddenState`/`NotFoundState` dengan jalur kembali sah |
| Operasi berjalan | `OperationStatus` dengan progres/elapsed; pengguna dapat meninggalkan halaman |
| Simulasi | `SimulationBanner` tetap terlihat; dokumen/ekspor ikut berlabel |
| Terblokir kebijakan/GAP | `BlockedReasonPanel`: apa yang belum tersedia, mengapa, siapa yang perlu memutuskan, langkah aman |
| Sesi berakhir | Dialog masuk ulang; formulir tidak hilang bila memungkinkan (draf lokal sementara) |

## 5. Formulir dan input sekali

- Data diturunkan dari sumber terkait (usulan → paket → HPS → kontrak → dokumen; permintaan → serah terima) dan ditampilkan sebagai nilai bawaan yang dapat diperiksa, bukan diketik ulang (OD-08/29).
- Fitur "Salin dari paket/kontrak sebelumnya" untuk spesifikasi dan item, dengan penanda asal.
- Input uang memformat ribuan saat diketik (`1.250.000`), menerima koma desimal, dan mengirim string desimal ke server.
- Tanggal memakai pemilih tanggal berlokal Indonesia (format tampilan `30 Okt 2026`, input `dd/mm/yyyy`).
- Combobox dengan pencarian server untuk daftar besar (barang, unit, penyedia, aset).
- Simpan draf eksplisit; tidak ada autosave yang dapat menimpa perubahan orang lain.
- Field wajib ditandai jelas; bantuan singkat di bawah field memakai bahasa sederhana.

## 6. Kembali, batal dan pelestarian konteks

- Breadcrumb mencerminkan hierarki konteks (ruang kerja → daftar → objek → anak).
- Tombol "Kembali" di header selalu menuju tujuan kontekstual RT, bukan sekadar riwayat browser; riwayat browser tetap berfungsi karena state ada di URL.
- Setelah aksi sukses, pengguna berada pada objek yang sama dengan status baru dan toast hasil; setelah batal, kembali ke asal tanpa perubahan.
- Perpindahan bahasa mempertahankan halaman, filter dan input yang belum tersimpan.

## 7. Responsif

| Lebar | Perilaku |
|---|---|
| < 640 px | Sidebar menjadi drawer; tabel menjadi kartu; panel konteks di bawah konten; bilah aksi utama lengket di bawah; dialog menjadi sheet layar penuh |
| 640–1023 px | Sidebar diciutkan ke ikon; tabel dengan kolom prioritas; panel konteks dapat dibuka sebagai sheet |
| ≥ 1024 px | Tata letak penuh: sidebar, konten, panel konteks kanan |

Perjalanan yang sering dilakukan di lapangan (konfirmasi penerima, permintaan barang, inventarisasi aset, unggah bukti) dirancang mobile-first.

## 8. Umpan balik dan notifikasi

Toast singkat untuk hasil perintah (sukses/galat), inbox untuk pekerjaan yang perlu tindakan, notifikasi in-app untuk perubahan yang relevan. Email hanya bila SMTP dikonfigurasi dan tidak pernah menjadi satu-satunya kanal. Tidak ada notifikasi yang menyatakan hasil yang belum dikonfirmasi server.

## 9. Pintasan keyboard

`Ctrl/⌘ K` palette; `/` fokus pencarian di daftar; `g` lalu `i` ke inbox; `Esc` menutup dialog/panel; `?` daftar pintasan. Pintasan tidak aktif saat fokus berada di input.

## 10. Ruang kerja evaluasi

Pola untuk PG-111/PG-122/PG-123 (OD-36; BR-036; INV-023). Tujuannya: evaluator menyelesaikan evaluasi dengan sedikit klik, setiap keputusan tahap tertelusur ke bukti, dan tidak ada yang tampak resmi sebelum sah.

| Bagian | Perilaku |
|---|---|
| Kepala ruang kerja | Paket, pemilihan, metode/varian, status skema (DRAF/BEKU, versi dan dokumen sumbernya), putaran aktif, `SimulationBanner` bila tidak ada versi `procurement.evaluation` yang berlaku resmi pada tanggal acuan, dan strip jumlah: dijajaki, diundang, menawar, memenuhi syarat, dievaluasi. Praktik setempat yang belum disahkan tampil sebagai catatan informatif, bukan peringatan blokir. |
| Pemilih lingkup | Tab per lingkup penghargaan (lot/item); paket tanpa pembagian tidak menampilkan tab. Lingkup gagal atau sudah dihargakan diberi status teks. |
| Matriks evaluasi | Baris = peserta yang penawarannya untuk lingkup itu dibuka; kolom = tahap dan kriteria dari skema beku yang berlaku untuk lingkup itu (kriteria lot lain tidak tampil; tahap per peserta tampil sekali dengan keterangan berlaku untuk semua lingkup peserta). Sel menampilkan LULUS/TIDAK LULUS/TIDAK BERLAKU/BELUM dengan ikon + teks (bukan warna saja) dan penanda bukti. Tahap gugur yang gagal mengunci kolom berikutnya peserta itu dengan keterangan. Navigasi panah antar sel, `Enter` membuka panel; kolom dan baris dapat dibekukan; kepala tetap terlihat saat gulir. |
| Panel kriteria | Teks persyaratan dan sumbernya dalam dokumen pengadaan, bukti yang diminta, penampil berkas penawaran baca-saja (berwatermark "Rahasia", pembukaan berkas tercatat audit), penanda halaman/bagian sebagai rujukan bukti, temuan (wajib bila tidak lulus), tautan klarifikasi. Simpan memakai `lock_version`; konflik → `ConflictDialog` dengan nilai terbaru. |
| Kemajuan dan kekurangan | Penghitung "x dari y kriteria terisi" per lingkup; daftar bukti yang kurang, klarifikasi terbuka dan tahap PENDING; tombol "Selesaikan putaran" hanya bagi penugasan berwenang dan nonaktif dengan alasan selama masih ada yang PENDING. |
| Perbandingan | Per lingkup: nilai penawaran yang dibacakan, nilai terkoreksi beserta catatan koreksi, selisih terhadap HPS bila ada, skor berbobot atau biaya kepemilikan menurut skema, dan usulan urutan — semuanya berlabel simulasi selama kebijakan belum aktif. Tidak ada aksi "tetapkan pemenang" di ruang kerja ini; penetapan berada di PG-112 oleh fungsi berwenang. |
| Klarifikasi dan negosiasi | Dibuat dari sel atau peserta; jawaban menjadi kiriman tertaut, bukan perubahan penawaran. Negosiasi per peserta dan lingkup mencatat nilai sebelum/sesudah dan hasil; E-Purchasing menampilkan opsi katalog (harga satuan, ongkos kirim, instalasi/pelatihan, total) dan mengarahkan negosiasi ke opsi termurah lebih dahulu bila kebijakan menyatakan. |
| Pembukaan (PG-123) | Kepala menampilkan batas akhir yang berlaku (WIB) beserta dasarnya (dokumen pengadaan, adendum atau keputusan perpanjangan) dan aturan menit batas dari kebijakan; tombol catat pembukaan aktif hanya sesudah batas lewat. Satu baris per penawaran yang diterima tepat waktu: nama, waktu terima, nilai yang dibacakan, masa berlaku, kelengkapan + catatan; sampul yang menurut kebijakan tidak dibuka tampil dengan alasannya. Tidak ada aksi gugur; penawaran terlambat tampil terpisah dengan waktu terima dan status ditolak terlambat, tanpa aksi buka. Sebelum pembukaan, penawaran yang diterima sesudah batas sudah ditandai "terlambat menurut batas saat ini" sebagai hasil hitung, bukan isian. Untuk sampul tanpa pembukaan formal (mis. Pengadaan Langsung, dokumen kualifikasi) halaman yang sama mencatat penutupan penerimaan. BA pembukaan dibuat dari data yang sama. |
| Skema (PG-122) | Penyusun tahap dan kriteria dengan sumber dari dokumen pengadaan; setiap tahap ditandai per peserta atau per lingkup beserta sampulnya, dan setiap kriteria per lingkup memilih lingkup penerapannya (pilihan cepat "semua lingkup" disimpan eksplisit per lingkup; tanpa pilihan bawaan tersembunyi); batas akhir pemasukan per sampul beserta adendumnya dicatat di sini; validasi kelengkapan menurut kebijakan sebelum dibekukan; pembekuan memakai `ConfirmActionDialog` yang menyebut akibatnya (tidak dapat diubah; penggantian hanya melalui adendum sebelum pembukaan). |
| Ponsel | Matriks menjadi daftar per peserta dengan kartu kriteria; panel kriteria layar penuh; penampil berkas tetap baca-saja. |
